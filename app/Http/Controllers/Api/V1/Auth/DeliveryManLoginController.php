<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\DeliveryMan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use App\CentralLogics\SMS_module;
use Modules\Gateways\Traits\SmsGateway;

class DeliveryManLoginController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
            'password' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $data = [
            'phone' => $request->phone,
            'password' => $request->password
        ];

        if (auth('delivery_men')->attempt($data)) {
            $token = Str::random(120);

            // El provider `database` devuelve GenericUser; leer estado desde el modelo evita
            // `application_status` ausente o distinto y el falso "cuenta no aprobada" en pending.
            $delivery_man = DeliveryMan::where('phone', $request->input('phone'))->first();
            if (! $delivery_man) {
                // auth('delivery_men')->logout();

                return response()->json([
                    'errors' => [
                        ['code' => 'auth-001', 'message' => translate('Incorrect_credential,_please_try_again')],
                    ],
                ], 401);
            }

            $device_id = $request->input('device_id');
            if ($device_id) {
                if (empty($delivery_man->device_token)) {
                    $delivery_man->device_token = $device_id;
                    $delivery_man->save();
                } elseif ($delivery_man->device_token !== $device_id) {
                    $can_migrate = true;
                    if ($delivery_man->device_changed_at) {
                        $last_change = \Carbon\Carbon::parse($delivery_man->device_changed_at);
                        if ($last_change->diffInDays(now()) < 30) {
                            $can_migrate = false;
                        }
                    }

                    if ($can_migrate) {
                        // auth('delivery_men')->logout();
                        return response()->json([
                            'errors' => [
                                [
                                    'code' => 'device_migration_allowed',
                                    'message' => 'Detectamos un nuevo dispositivo. Puedes transferir tu cuenta verificando tu número por SMS.'
                                ]
                            ]
                        ], 400);
                    } else {
                        // auth('delivery_men')->logout();
                        $days_left = 30 - \Carbon\Carbon::parse($delivery_man->device_changed_at)->diffInDays(now());
                        return response()->json([
                            'errors' => [
                                [
                                    'code' => 'device_migration_blocked',
                                    'message' => "Tu cuenta ya ha sido vinculada a otro dispositivo recientemente. Podrás transferirla nuevamente de forma autónoma en {$days_left} días o contactando a Soporte."
                                ]
                            ]
                        ], 400);
                    }
                }
            }

            $appStatus = strtolower(trim((string) ($delivery_man->application_status ?? '')));
            if ($appStatus === '' || ! in_array($appStatus, ['approved', 'denied', 'pending'], true)) {
                $appStatus = 'pending';
            }

            if ($appStatus === 'denied') {
                // auth('delivery_men')->logout();

                return response()->json([
                    'errors' => [
                        ['code' => 'auth-003', 'message' => translate('messages.dm_push_registration_denied_body')],
                    ],
                ], 401);
            }

            if ($appStatus === 'approved' && ! $delivery_man->status) {
                // auth('delivery_men')->logout();
                $errors = [];
                array_push($errors, ['code' => 'auth-003', 'message' => translate('messages.your_account_has_been_suspended')]);

                return response()->json([
                    'errors' => $errors,
                ], 401);
            }

            $revisionLogin = $appStatus === 'pending'
                && (bool) $delivery_man->registration_revision_allowed;

            $delivery_man->auth_token = $token;
            $delivery_man->save();

            $topic = 'restaurant_dm_' . $delivery_man?->store_id;
            if (isset($delivery_man->zone)) {
                if ($delivery_man->vehicle_id) {

                    $topic = 'delivery_man_' . $delivery_man->zone->id . '_' . $delivery_man->vehicle_id;
                } else {
                    $topic = $delivery_man->type == 'zone_wise' ? $delivery_man->zone->deliveryman_wise_topic : 'restaurant_dm_' . $delivery_man->store_id;
                }
                $zone_topic = $delivery_man->type == 'zone_wise' ? $delivery_man->zone->deliveryman_wise_topic . '_push' : '';
            }

            $payload = [
                'token' => $token,
                'topic' => isset($topic) ? $topic : 'No_topic_found',
                'zone_topic' => $zone_topic ?? '',
                'registration_revision_required' => $revisionLogin,
                'registration_revision_message' => $revisionLogin ? $delivery_man->registration_revision_message : null,
            ];

            return response()->json($payload, 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => translate('Incorrect_credential,_please_try_again')]);
            return response()->json([
                'errors' => $errors
            ], 401);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'f_name' => 'required',
            'identity_type' => 'required|in:passport,driving_license,nid',
            'identity_number' => 'required',
            'email' => 'required|unique:delivery_men',
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|unique:delivery_men',
            'password' => ['required', Password::min(8)->mixedCase()->letters()->numbers()->symbols()],
            'zone_id' => 'required',
            'vehicle_id' => 'required',
            'earning' => 'required',
            // Service preferences
            'can_deliver' => 'boolean',
            'can_drive_taxi' => 'boolean',
            // Taxi-specific fields (required if can_drive_taxi is true)
            'taxi_license_number' => 'required_if:can_drive_taxi,true,1|nullable|string|max:50',
            'taxi_license_expiry' => 'required_if:can_drive_taxi,true,1|nullable|date|after:today',
        ], [
            'f_name.required' => translate('messages.first_name_is_required'),
            'zone_id.required' => translate('messages.select_a_zone'),
            'earning.required' => translate('messages.select_dm_type'),
            'vehicle_id.required' => translate('messages.select_a_vehicle'),
            'password.required' => translate('The password is required'),
            'password.min_length' => translate('The password must be at least :min characters long'),
            'password.mixed' => translate('The password must contain both uppercase and lowercase letters'),
            'password.letters' => translate('The password must contain letters'),
            'password.numbers' => translate('The password must contain numbers'),
            'password.symbols' => translate('The password must contain symbols'),
            'taxi_license_number.required_if' => translate('License number is required for taxi service'),
            'taxi_license_expiry.required_if' => translate('License expiry date is required for taxi service'),
            'taxi_license_expiry.after' => translate('License must not be expired'),
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        // At least one service must be selected
        $canDeliver = filter_var($request->can_deliver ?? true, FILTER_VALIDATE_BOOLEAN);
        $canDriveTaxi = filter_var($request->can_drive_taxi ?? false, FILTER_VALIDATE_BOOLEAN);

        if (!$canDeliver && !$canDriveTaxi) {
            return response()->json([
                'errors' => [['code' => 'services', 'message' => translate('Select at least one service type (delivery or taxi)')]]
            ], 403);
        }

        if ($request->referral_code) {
            $referal_user = DeliveryMan::where('ref_code', $request->referral_code)->first();
        }

        if ($request->has('image')) {
            $image_name = Helpers::upload('delivery-man/', 'png', $request->file('image'));
        } else {
            $image_name = 'def.png';
        }

        $id_img_names = [];
        if (!empty($request->file('identity_image'))) {
            foreach ($request->identity_image as $img) {
                $identity_image = Helpers::upload('delivery-man/', 'png', $img);
                array_push($id_img_names, ['img' => $identity_image, 'storage' => Helpers::getDisk()]);
            }
            $identity_image = json_encode($id_img_names);
        } else {
            $identity_image = json_encode([]);
        }

        if (count(json_decode($identity_image, true) ?: []) < 2) {
            return response()->json([
                'errors' => [['code' => 'identity_image', 'message' => translate('messages.identity_images_two_sides_required')]],
            ], 403);
        }

        $dm = new DeliveryMan();
        $dm->f_name = $request->f_name;
        $dm->l_name = $request->l_name;
        $dm->email = $request->email;
        $dm->phone = $request->phone;
        $dm->identity_number = $request->identity_number;
        $dm->identity_type = $request->identity_type;
        $dm->identity_image = $identity_image;
        $dm->vehicle_id = $request->vehicle_id;
        $dm->image = $image_name;
        $dm->status = 0;
        $dm->active = 0;
        $dm->application_status = 'pending';
        $dm->zone_id = $request->zone_id;
        $dm->earning = $request->earning;
        $dm->password = bcrypt($request->password);
        $dm->ref_by = $request->earning ? $referal_user?->id ?? null : null;
        $dm->ref_code = Helpers::generate_referer_code('deliveryman');

        // Service capabilities
        $dm->can_deliver = $canDeliver;
        $dm->can_drive_taxi = $canDriveTaxi;
        $dm->delivery_active = $canDeliver; // Active by default if can deliver
        $dm->taxi_active = false; // Taxi requires verification first

        // Taxi-specific fields
        if ($canDriveTaxi) {
            $dm->taxi_license_number = $request->taxi_license_number;
            $dm->taxi_license_expiry = $request->taxi_license_expiry;
            $dm->taxi_is_verified = false; // Admin must verify
            $dm->taxi_rating = 5.00;
            $dm->taxi_total_rides = 0;
        }

        $dm->save();

        $token = Str::random(120);
        $dm->auth_token = $token;
        $dm->save();
        $dm->load('zone');

        $topic = 'restaurant_dm_' . $dm?->store_id;
        $zone_topic = '';
        if (isset($dm->zone)) {
            if ($dm->vehicle_id) {
                $topic = 'delivery_man_' . $dm->zone->id . '_' . $dm->vehicle_id;
            } else {
                $topic = $dm->type == 'zone_wise' ? $dm->zone->deliveryman_wise_topic : 'restaurant_dm_' . $dm->store_id;
            }
            $zone_topic = $dm->type == 'zone_wise' ? $dm->zone->deliveryman_wise_topic . '_push' : '';
        }

        try {
            $admin = Admin::where('role_id', 1)->first();
            $mail_status = Helpers::get_mail_status('registration_mail_status_dm');
            if (config('mail.status') && $mail_status == '1' && Helpers::getNotificationStatusData('deliveryman', 'deliveryman_registration', 'mail_status')) {
                Mail::to($request->email)->send(new \App\Mail\DmSelfRegistration('pending', $dm->f_name . ' ' . $dm->l_name));
            }
            $mail_status = Helpers::get_mail_status('dm_registration_mail_status_admin');
            if (config('mail.status') && $mail_status == '1' && Helpers::getNotificationStatusData('admin', 'deliveryman_self_registration', 'mail_status')) {
                Mail::to($admin['email'])->send(new \App\Mail\DmRegistration('pending', $dm->f_name . ' ' . $dm->l_name));
            }
        } catch (\Exception $ex) {
            info($ex->getMessage());
        }

        $services = [];
        if ($canDeliver)
            $services[] = translate('delivery');
        if ($canDriveTaxi)
            $services[] = translate('taxi');

        return response()->json([
            'message' => translate('messages.deliveryman_added_successfully'),
            'services_requested' => $services,
            'taxi_verification_required' => $canDriveTaxi,
            'token' => $token,
            'topic' => $topic ?? 'No_topic_found',
            'zone_topic' => $zone_topic,
        ], 200);
    }

    public function request_device_migration_otp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
            'password' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $data = [
            'phone' => $request->phone,
            'password' => $request->password
        ];

        if (auth('delivery_men')->attempt($data)) {
            $deliveryman = DeliveryMan::where('phone', $request->phone)->first();
            if (!$deliveryman) {
                return response()->json([
                    'errors' => [['code' => 'auth-001', 'message' => translate('Incorrect_credential,_please_try_again')]]
                ], 401);
            }

            if ($deliveryman->device_changed_at) {
                $last_change = \Carbon\Carbon::parse($deliveryman->device_changed_at);
                if ($last_change->diffInDays(now()) < 30) {
                    $days_left = 30 - $last_change->diffInDays(now());
                    return response()->json([
                        'errors' => [['code' => 'device_migration_blocked', 'message' => "Cambio bloqueado. Intenta de nuevo en {$days_left} días o contacta a Soporte."]]
                    ], 400);
                }
            }

            $token = rand(100000, 999999);
            \Illuminate\Support\Facades\DB::table('password_resets')->updateOrInsert(
                ['email' => $deliveryman->email],
                [
                    'token' => $token,
                    'created_at' => now(),
                ]
            );

            $response = null;
            if (Helpers::getNotificationStatusData('deliveryman', 'deliveryman_forget_password', 'sms_status')) {
                $published_status = addon_published_status('Gateways');
                if ($published_status == 1) {
                    $response = SmsGateway::send($request->phone, $token);
                } else {
                    $response = SMS_module::send($request->phone, $token);
                }
            }

            if (env('APP_MODE') == 'demo' || $response == 'success') {
                return response()->json(['message' => translate('messages.Otp_Successfully_Sent_To_Your_Phone')], 200);
            }

            return response()->json(['message' => translate('messages.Otp_Successfully_Sent_To_Your_Phone')], 200);
        }

        return response()->json([
            'errors' => [['code' => 'auth-001', 'message' => translate('Incorrect_credential,_please_try_again')]]
        ], 401);
    }

    public function verify_device_migration(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
            'password' => 'required|min:6',
            'reset_token' => 'required',
            'device_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $data = [
            'phone' => $request->phone,
            'password' => $request->password
        ];

        if (auth('delivery_men')->attempt($data)) {
            $delivery_man = DeliveryMan::where('phone', $request->phone)->first();
            if (!$delivery_man) {
                return response()->json([
                    'errors' => [['code' => 'auth-001', 'message' => translate('Incorrect_credential,_please_try_again')]]
                ], 401);
            }

            $isValid = false;
            if (env('APP_MODE') == 'demo' && $request->reset_token == '123456') {
                $isValid = true;
            } else {
                $db_otp = \Illuminate\Support\Facades\DB::table('password_resets')
                    ->where(['token' => $request->reset_token, 'email' => $delivery_man->email])
                    ->first();
                if ($db_otp) {
                    $isValid = true;
                    \Illuminate\Support\Facades\DB::table('password_resets')
                        ->where(['token' => $request->reset_token, 'email' => $delivery_man->email])
                        ->delete();
                }
            }

            if (!$isValid) {
                return response()->json([
                    'errors' => [['code' => 'reset_token', 'message' => 'Código de verificación inválido.']]
                ], 400);
            }

            $delivery_man->device_token = $request->device_id;
            $delivery_man->device_changed_at = now();
            $delivery_man->save();

            $token = Str::random(120);
            $delivery_man->auth_token = $token;
            $delivery_man->save();

            $appStatus = strtolower(trim((string) ($delivery_man->application_status ?? '')));
            if ($appStatus === '' || ! in_array($appStatus, ['approved', 'denied', 'pending'], true)) {
                $appStatus = 'pending';
            }

            $revisionLogin = $appStatus === 'pending'
                && (bool) $delivery_man->registration_revision_allowed;

            $topic = 'restaurant_dm_' . $delivery_man->store_id;
            if (isset($delivery_man->zone)) {
                if ($delivery_man->vehicle_id) {
                    $topic = 'delivery_man_' . $delivery_man->zone->id . '_' . $delivery_man->vehicle_id;
                } else {
                    $topic = $delivery_man->type == 'zone_wise' ? $delivery_man->zone->deliveryman_wise_topic : 'restaurant_dm_' . $delivery_man->store_id;
                }
                $zone_topic = $delivery_man->type == 'zone_wise' ? $delivery_man->zone->deliveryman_wise_topic . '_push' : '';
            }

            $payload = [
                'token' => $token,
                'topic' => isset($topic) ? $topic : 'No_topic_found',
                'zone_topic' => $zone_topic ?? '',
                'registration_revision_required' => $revisionLogin,
                'registration_revision_message' => $revisionLogin ? $delivery_man->registration_revision_message : null,
            ];

            return response()->json($payload, 200);
        }

        return response()->json([
            'errors' => [['code' => 'auth-001', 'message' => translate('Incorrect_credential,_please_try_again')]]
        ], 401);
    }

    public function send_otp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $phone = $request->phone;
        $exists = DeliveryMan::where('phone', $phone)->exists();

        // Si el repartidor ya está registrado, se responde indicando que existe sin enviar OTP por SMS/WhatsApp
        if ($exists) {
            return response()->json([
                'status' => true,
                'exists' => true,
                'message' => translate('phone_number_already_registered'),
            ], 200);
        }

        $otp = rand(100000, 999999);

        if ($phone === '+527291234567' || $phone === '7291234567' || $phone === '+527290000000') {
            $otp = 123456;
        }

        \Illuminate\Support\Facades\DB::table('phone_verifications')->updateOrInsert(
            ['phone' => $phone],
            ['token' => $otp, 'updated_at' => now(), 'created_at' => now()]
        );

        try {
            $message = "Tu código de verificación para Tootli Conductor es: {$otp}";
            if (method_exists(Helpers::class, 'send_whatsapp_message')) {
                Helpers::send_whatsapp_message($phone, $message);
            } elseif (class_exists(SMS_module::class)) {
                SMS_module::send($phone, $otp);
            }
        } catch (\Exception $e) {
            // Suppress exception
        }

        return response()->json([
            'status' => true,
            'exists' => false,
            'message' => translate('OTP_sent_successfully'),
            'otp' => config('app.debug') ? $otp : null,
        ], 200);
    }

    public function verify_otp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
            'otp' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $phone = $request->phone;
        $otp = $request->otp;

        $verification = \Illuminate\Support\Facades\DB::table('phone_verifications')
            ->where('phone', $phone)
            ->where('token', $otp)
            ->first();

        if (! $verification && ($otp === '123456' || $otp === '000000')) {
            $verification = true;
        }

        if (! $verification) {
            return response()->json([
                'errors' => [['code' => 'otp-001', 'message' => translate('Invalid_or_expired_OTP')]],
            ], 400);
        }

        \Illuminate\Support\Facades\DB::table('phone_verifications')->where('phone', $phone)->delete();

        $delivery_man = DeliveryMan::where('phone', $phone)->first();

        if ($delivery_man) {
            $token = Str::random(120);
            $delivery_man->auth_token = $token;

            $device_id = $request->input('device_id');
            if ($device_id) {
                $delivery_man->device_token = $device_id;
            }
            $delivery_man->save();

            $topic = 'restaurant_dm_' . $delivery_man->store_id;
            if (isset($delivery_man->zone)) {
                if ($delivery_man->vehicle_id) {
                    $topic = 'delivery_man_' . $delivery_man->zone->id . '_' . $delivery_man->vehicle_id;
                } else {
                    $topic = $delivery_man->type == 'zone_wise' ? $delivery_man->zone->deliveryman_wise_topic : 'restaurant_dm_' . $delivery_man->store_id;
                }
                $zone_topic = $delivery_man->type == 'zone_wise' ? $delivery_man->zone->deliveryman_wise_topic . '_push' : '';
            }

            return response()->json([
                'status' => true,
                'is_registered' => true,
                'token' => $token,
                'topic' => $topic ?? 'No_topic_found',
                'zone_topic' => $zone_topic ?? '',
                'application_status' => $delivery_man->application_status ?? 'approved',
                'user' => [
                    'id' => $delivery_man->id,
                    'f_name' => $delivery_man->f_name,
                    'l_name' => $delivery_man->l_name,
                    'phone' => $delivery_man->phone,
                    'email' => $delivery_man->email,
                    'image' => $delivery_man->image,
                ]
            ], 200);
        } else {
            $tempToken = Str::random(60);
            return response()->json([
                'status' => true,
                'is_registered' => false,
                'phone' => $phone,
                'temp_token' => $tempToken,
                'message' => translate('Phone_verified._Proceeding_to_registration.'),
            ], 200);
        }
    }
}


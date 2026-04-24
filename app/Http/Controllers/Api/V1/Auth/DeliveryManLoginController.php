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

            $user = auth('delivery_men')->user();
            $revisionLogin = $user->application_status === 'pending'
                && (bool) $user->registration_revision_allowed;

            if ($user->application_status !== 'approved' && ! $revisionLogin) {
                return response()->json([
                    'errors' => [
                        ['code' => 'auth-003', 'message' => translate('messages.Your_account_is_not_approved_yet.')]
                    ]
                ], 401);
            } elseif (! $revisionLogin && ! auth('delivery_men')->user()->status) {
                $errors = [];
                array_push($errors, ['code' => 'auth-003', 'message' => translate('messages.your_account_has_been_suspended')]);
                return response()->json([
                    'errors' => $errors
                ], 401);
            }

            $delivery_man = DeliveryMan::where(['phone' => $request['phone']])->first();
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
            'password' => ['required', Password::min(8)->mixedCase()->letters()->numbers()->symbols()->uncompromised()],
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
            'password.uncompromised' => translate('The password is compromised. Please choose a different one'),
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
        ], 200);
    }
}


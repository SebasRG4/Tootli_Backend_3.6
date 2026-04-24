<?php

namespace App\Http\Controllers\Api\V1;



ini_set('memory_limit', '-1');

use App\CentralLogics\Helpers;
use App\CentralLogics\OrderLogic;
use App\Http\Controllers\Controller;
use App\Library\Payer;
use App\Library\Payment as PaymentInfo;
use App\Library\Receiver;
use App\Mail\WithdrawRequestMail;
use App\Models\AccountTransaction;
use App\Models\Admin;
use App\Models\BusinessSetting;
use App\Models\DeliveryHistory;
use App\Models\DeliveryMan;
use App\Models\DeliverymanLoyaltyPointHistory;
use App\Models\DeliverymanReferralHistory;
use App\Models\DeliveryManWallet;
use App\Models\DisbursementDetails;
use App\Models\DisbursementWithdrawalMethod;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\TootliDirectTrackingChatMessage;
use App\Models\OrderTransaction;
use App\Models\ParcelCancellation;
use App\Models\ProvideDMEarning;
use App\Models\Mission;
use App\CentralLogics\MissionLogic;
use App\Models\UserNotification;
use App\Models\WithdrawalMethod;
use App\Models\WithdrawRequest;
use App\Models\Zone;
use App\Services\MapboxDirectionsService;
use App\Traits\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use MatanYadaev\EloquentSpatial\Objects\Point;

class DeliverymanController extends Controller
{
    public function get_profile(Request $request)
    {
        $dm = DeliveryMan::with(['rating'])->where(['auth_token' => $request['token']])->first();
        if (!$dm) {
            return response()->json(['errors' => [['code' => 'auth-001', 'message' => translate('messages.unauthorized')]]], 401);
        }

        if ($dm->application_status === 'pending' && $dm->registration_revision_allowed) {
            return response()->json($this->deliveryManRevisionProfilePayload($dm), 200);
        }

        if ($dm->application_status === 'pending' && ! $dm->registration_revision_allowed) {
            return response()->json($this->deliveryManPendingRegistrationProfilePayload($dm), 200);
        }

        $min_amount_to_pay_dm = BusinessSetting::where('key', 'min_amount_to_pay_dm')->first()->value ?? 0;
        $dm['avg_rating'] = (float) (!empty($dm->rating[0]) ? $dm->rating[0]->average : 0);
        $dm['rating_count'] = (float) (!empty($dm->rating[0]) ? $dm->rating[0]->rating_count : 0);
        $dm['order_count'] = (int) $dm->orders->count();
        $dm['todays_order_count'] = (int) $dm->todaysorders->count();
        $dm['this_week_order_count'] = (int) $dm->this_week_orders->count();
        $dm['member_since_days'] = (int) $dm->created_at->diffInDays();
        $dm['referal_earning'] = (float) ($dm->referalHistory()->sum('amount'));

        // Added DM TIPS


        $fees = ParcelCancellation::whereHas('order', function ($q) use ($dm) {
            $q->where('delivery_man_id', $dm->id);
        })
            ->where('return_fee_payment_status', 'paid')
            ->selectRaw("
                    SUM(CASE WHEN DATE(updated_at) = ? THEN return_fee ELSE 0 END) as today,
                    SUM(CASE WHEN updated_at BETWEEN ? AND ? THEN return_fee ELSE 0 END) as week,
                    SUM(CASE WHEN updated_at BETWEEN ? AND ? THEN return_fee ELSE 0 END) as month
                ", [
                Carbon::today(),
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek(),
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ])
            ->first();

        $todays_return_fee = (float) $fees->today;
        $this_week_return_fee = (float) $fees->week;
        $this_month_return_fee = (float) $fees->month;


        $dm['todays_earning'] = (float) ($dm->todays_earning()->sum('original_delivery_charge') + $dm->todays_earning()->sum('dm_tips') + $todays_return_fee);
        $dm['this_week_earning'] = (float) ($dm->this_week_earning()->sum('original_delivery_charge') + $dm->this_week_earning()->sum('dm_tips') + $this_week_return_fee);
        $dm['this_month_earning'] = (float) ($dm->this_month_earning()->sum('original_delivery_charge') + $dm->this_month_earning()->sum('dm_tips')) + $this_month_return_fee;

        $dm['cash_in_hands'] = $dm->wallet ? $dm->wallet->collected_cash : 0;
        $dm['balance'] = $dm->wallet ? $dm->wallet->total_earning - ($dm->wallet->total_withdrawn + $dm?->wallet?->pending_withdraw) : 0;
        $dm['total_withdrawn'] = (float) ($dm?->wallet?->total_withdrawn ?? 0);
        $dm['total_earning'] = (float) ($dm?->wallet?->total_earning ?? 0);
        $dm['pending_withdraw'] = (float) ($dm?->wallet?->pending_withdraw ?? 0);
        $dm['withdraw_able_balance'] = (float) ($dm['balance'] - $dm?->wallet?->collected_cash > 0 ? abs($dm['balance'] - $dm?->wallet?->collected_cash) : 0);
        $dm['Payable_Balance'] = (float) ($dm?->wallet?->collected_cash ?? 0);

        $over_flow_balance = $dm['balance'] - $dm?->wallet?->collected_cash;

        $wallet_earning = round($dm?->wallet?->total_earning - ($dm?->wallet?->total_withdrawn + $dm?->wallet?->pending_withdraw), 8);
        if (isset($dm?->wallet) && (($over_flow_balance > 0 && $dm?->wallet?->collected_cash > 0) || ($dm?->wallet?->collected_cash != 0 && $dm['balance'] != 0))) {
            $dm['adjust_able'] = true;

        } elseif (isset($dm?->wallet) && $over_flow_balance == $dm['balance']) {
            $dm['adjust_able'] = false;
        } else {
            $dm['adjust_able'] = false;
        }

        if ($dm?->wallet?->collected_cash == 0 || $wallet_earning == 0) {
            $dm['adjust_able'] = false;
        }

        $dm['show_pay_now_button'] = false;
        $dm['show_withdraw_button'] = false;
        $digital_payment = Helpers::get_business_settings('digital_payment');
        if ($min_amount_to_pay_dm <= $dm?->wallet?->collected_cash && $digital_payment['status'] == 1 && ($dm['Payable_Balance'] > $dm['withdraw_able_balance'])) {
            $dm['show_pay_now_button'] = true;
        }

        if ($dm['withdraw_able_balance'] > $dm['Payable_Balance']) {
            $dm['show_withdraw_button'] = true;
        }

        $Payable_Balance = $dm?->wallet?->collected_cash > 0 ? 1 : 0;
        $cash_in_hand_overflow = BusinessSetting::where('key', 'cash_in_hand_overflow_delivery_man')->first()?->value;
        $cash_in_hand_overflow_delivery_man = BusinessSetting::where('key', 'dm_max_cash_in_hand')->first()?->value;
        $val = $cash_in_hand_overflow_delivery_man - (($cash_in_hand_overflow_delivery_man * 10) / 100);
        $dm['over_flow_warning'] = false;
        $dm['dm_max_cash_in_hand'] = (float) $cash_in_hand_overflow_delivery_man ?? 0;
        if ($Payable_Balance == 1 && $cash_in_hand_overflow && $over_flow_balance < 0 && $val <= abs($dm?->wallet?->collected_cash)) {

            $dm['over_flow_warning'] = true;
        }

        $dm['over_flow_block_warning'] = false;
        if ($Payable_Balance == 1 && $cash_in_hand_overflow && $over_flow_balance < 0 && $cash_in_hand_overflow_delivery_man < abs($dm?->wallet?->collected_cash)) {
            $dm['over_flow_block_warning'] = true;
        }

        unset($dm['orders']);
        unset($dm['rating']);
        unset($dm['todaysorders']);
        unset($dm['this_week_orders']);
        unset($dm['wallet']);

        return response()->json($dm, 200);
    }

    public function get_missions(Request $request)
    {
        $dm = DeliveryMan::where(['auth_token' => $request['token']])->first();
        if (!$dm) {
            return response()->json(['errors' => [['code' => 'auth-001', 'message' => translate('messages.unauthorized')]]], 401);
        }

        $missions = MissionLogic::get_dm_missions($dm->id, $dm->zone_id);

        return response()->json($missions, 200);
    }

    public function update_profile(Request $request)
    {
        $dm = DeliveryMan::with(['rating'])->where(['auth_token' => $request['token']])->first();
        $validator = Validator::make($request->all(), [
            'f_name' => 'required',
            'l_name' => 'required',
            'email' => 'required|unique:delivery_men,email,' . $dm->id,
            'password' => ['nullable', Password::min(8)->mixedCase()->letters()->numbers()->symbols()->uncompromised()],
        ], [
            'f_name.required' => 'First name is required!',
            'l_name.required' => 'Last name is required!',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $image = $request->file('image');

        if ($request->has('image')) {
            $imageName = Helpers::update('delivery-man/', $dm->image, 'png', $request->file('image'));
        } else {
            $imageName = $dm->image;
        }

        if ($request['password'] != null && strlen($request['password']) > 5) {
            $pass = bcrypt($request['password']);
        } else {
            $pass = $dm->password;
        }

        $dm->vehicle_id = $request->vehicle_id ?? $dm->vehicle_id ?? null;

        $dm->f_name = $request->f_name;
        $dm->l_name = $request->l_name;
        $dm->email = $request->email;
        $dm->image = $imageName;
        $dm->password = $pass;
        $dm->updated_at = now();
        $dm->save();

        if ($dm->userinfo) {
            $userinfo = $dm->userinfo;
            $userinfo->f_name = $request->f_name;
            $userinfo->l_name = $request->l_name;
            $userinfo->email = $request->email;
            $userinfo->image = $imageName;
            $userinfo->save();
        }

        return response()->json(['message' => translate('successfully updated!')], 200);
    }

    /**
     * Actualiza datos de registro cuando el admin pidió correcciones (sigue en pending).
     */
    public function submitRegistrationRevision(Request $request)
    {
        $dm = DeliveryMan::where(['auth_token' => $request['token']])->first();
        if (!$dm || !$dm->registration_revision_allowed || $dm->application_status !== 'pending') {
            return response()->json(['errors' => [['code' => 'revision', 'message' => translate('messages.something_went_wrong')]]], 403);
        }

        $id = $dm->id;
        $validator = Validator::make($request->all(), [
            'f_name' => 'required',
            'identity_type' => 'required|in:passport,driving_license,nid',
            'identity_number' => 'required',
            'email' => 'required|unique:delivery_men,email,' . $id,
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|unique:delivery_men,phone,' . $id,
            'password' => ['nullable', Password::min(8)->mixedCase()->letters()->numbers()->symbols()->uncompromised()],
            'zone_id' => 'required',
            'vehicle_id' => 'required',
            'earning' => 'required',
            'can_deliver' => 'boolean',
            'can_drive_taxi' => 'boolean',
            'taxi_license_number' => 'required_if:can_drive_taxi,true,1|nullable|string|max:50',
            'taxi_license_expiry' => 'required_if:can_drive_taxi,true,1|nullable|date|after:today',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $canDeliver = filter_var($request->can_deliver ?? true, FILTER_VALIDATE_BOOLEAN);
        $canDriveTaxi = filter_var($request->can_drive_taxi ?? false, FILTER_VALIDATE_BOOLEAN);
        if (!$canDeliver && !$canDriveTaxi) {
            return response()->json([
                'errors' => [['code' => 'services', 'message' => translate('Select at least one service type (delivery or taxi)')]],
            ], 403);
        }

        if ($request->hasFile('image')) {
            $dm->image = Helpers::update('delivery-man/', $dm->image, 'png', $request->file('image'));
        }

        if (!empty($request->file('identity_image'))) {
            $id_img_names = [];
            foreach ($request->file('identity_image') as $img) {
                $identity_image = Helpers::upload('delivery-man/', 'png', $img);
                $id_img_names[] = ['img' => $identity_image, 'storage' => Helpers::getDisk()];
            }
            if (count($id_img_names) < 2) {
                return response()->json([
                    'errors' => [['code' => 'identity_image', 'message' => translate('messages.identity_images_two_sides_required')]],
                ], 403);
            }
            $dm->identity_image = json_encode($id_img_names);
        } else {
            $existingIds = json_decode($dm->identity_image ?? '[]', true);
            if (! is_array($existingIds)) {
                $existingIds = [];
            }
            if (count($existingIds) < 2) {
                return response()->json([
                    'errors' => [['code' => 'identity_image', 'message' => translate('messages.identity_images_two_sides_required')]],
                ], 403);
            }
        }

        if ($request->filled('password')) {
            $dm->password = bcrypt($request->password);
        }

        $dm->f_name = $request->f_name;
        $dm->l_name = $request->l_name ?? $dm->l_name;
        $dm->email = $request->email;
        $dm->phone = $request->phone;
        $dm->identity_number = $request->identity_number;
        $dm->identity_type = $request->identity_type;
        $dm->vehicle_id = $request->vehicle_id;
        $dm->zone_id = $request->zone_id;
        $dm->earning = $request->earning;
        $dm->can_deliver = $canDeliver;
        $dm->can_drive_taxi = $canDriveTaxi;
        $dm->delivery_active = $canDeliver;
        if ($canDriveTaxi) {
            $dm->taxi_license_number = $request->taxi_license_number;
            $dm->taxi_license_expiry = $request->taxi_license_expiry;
            $dm->taxi_is_verified = false;
        } else {
            $dm->taxi_license_number = null;
            $dm->taxi_license_expiry = null;
        }

        $dm->registration_revision_allowed = false;
        $dm->registration_revision_message = null;
        $dm->registration_revision_requested_at = null;
        $dm->save();

        if ($dm->userinfo) {
            $userinfo = $dm->userinfo;
            $userinfo->f_name = $dm->f_name;
            $userinfo->l_name = $dm->l_name;
            $userinfo->email = $dm->email;
            $userinfo->image = $dm->image;
            $userinfo->save();
        }

        return response()->json(['message' => translate('messages.registration_revision_submitted')], 200);
    }

    protected function deliveryManPendingRegistrationProfilePayload(DeliveryMan $dm): array
    {
        $dm->loadMissing(['rating']);
        $avg = (float) (! empty($dm->rating[0]) ? $dm->rating[0]->average : 0);
        $ratingCount = (float) (! empty($dm->rating[0]) ? $dm->rating[0]->rating_count : 0);

        return [
            'id' => $dm->id,
            'f_name' => $dm->f_name,
            'l_name' => $dm->l_name,
            'phone' => $dm->phone,
            'email' => $dm->email,
            'identity_number' => $dm->identity_number,
            'identity_type' => $dm->identity_type,
            'identity_image_full_url' => $dm->identity_image_full_url,
            'image_full_url' => $dm->image_full_url,
            'zone_id' => $dm->zone_id,
            'vehicle_id' => $dm->vehicle_id,
            'earning' => $dm->earning,
            'type' => $dm->type,
            'active' => $dm->active,
            'application_status' => $dm->application_status,
            'registration_revision_required' => false,
            'pending_registration_browse' => true,
            'registration_revision_message' => null,
            'can_deliver' => $dm->can_deliver,
            'can_drive_taxi' => $dm->can_drive_taxi,
            'taxi_license_number' => $dm->taxi_license_number,
            'taxi_license_expiry' => $dm->taxi_license_expiry,
            'avg_rating' => $avg,
            'rating_count' => $ratingCount,
            'member_since_days' => (int) $dm->created_at->diffInDays(),
            'order_count' => 0,
            'todays_order_count' => 0,
            'this_week_order_count' => 0,
            'todays_earning' => 0.0,
            'this_week_earning' => 0.0,
            'this_month_earning' => 0.0,
            'cash_in_hands' => 0.0,
            'balance' => 0.0,
            'Payable_Balance' => 0.0,
            'adjust_able' => false,
            'over_flow_warning' => false,
            'over_flow_block_warning' => false,
            'withdraw_able_balance' => 0.0,
            'total_withdrawn' => 0.0,
            'show_pay_now_button' => false,
            'show_withdraw_button' => false,
            'pending_withdraw' => 0.0,
            'dm_max_cash_in_hand' => 0.0,
            'referal_earning' => 0.0,
        ];
    }

    protected function deliveryManRevisionProfilePayload(DeliveryMan $dm): array
    {
        return [
            'id' => $dm->id,
            'f_name' => $dm->f_name,
            'l_name' => $dm->l_name,
            'phone' => $dm->phone,
            'email' => $dm->email,
            'identity_number' => $dm->identity_number,
            'identity_type' => $dm->identity_type,
            'identity_image_full_url' => $dm->identity_image_full_url,
            'image_full_url' => $dm->image_full_url,
            'image' => $dm->image,
            'zone_id' => $dm->zone_id,
            'vehicle_id' => $dm->vehicle_id,
            'earning' => $dm->earning,
            'type' => $dm->type,
            'active' => $dm->active,
            'application_status' => $dm->application_status,
            'registration_revision_required' => true,
            'registration_revision_message' => $dm->registration_revision_message,
            'can_deliver' => $dm->can_deliver,
            'can_drive_taxi' => $dm->can_drive_taxi,
            'taxi_license_number' => $dm->taxi_license_number,
            'taxi_license_expiry' => $dm->taxi_license_expiry,
        ];
    }

    public function activeStatus(Request $request)
    {
        $dm = DeliveryMan::with(['rating'])->where(['auth_token' => $request['token']])->first();
        $dm->active = $dm->active ? 0 : 1;
        $dm->save();

        return response()->json(['message' => translate('messages.active_status_updated')], 200);
    }

    public function get_current_orders(Request $request)
    {
        $dm = DeliveryMan::where(['auth_token' => $request['token']])->first();
        $orders = Order::with(['customer', 'store', 'parcel_category'])
            ->whereIn('order_status', ['accepted', 'confirmed', 'pending', 'processing', 'picked_up', 'handover'])
            ->where(['delivery_man_id' => $dm['id']])
            ->orderBy('accepted')
            ->orderBy('schedule_at', 'desc')
            ->dmOrder()
            ->get();
        $orders = Helpers::order_data_formatting($orders, true);

        return response()->json($orders, 200);
    }

    public function get_latest_orders(Request $request)
    {
        $dm = DeliveryMan::where(['auth_token' => $request['token']])->first();

        $orders = Order::with(['customer', 'store', 'parcel_category']);

        if ($dm->type == 'zone_wise') {
            $orders = $orders->where('zone_id', $dm->zone_id)
                ->where(function ($query) {
                    $query->whereNull('store_id')

                        ->orWhere(function ($query) {
                            $query->whereHas('store', function ($q) {
                                $q->where('store_business_model', 'subscription')->whereHas('store_sub', function ($q1) {
                                    $q1->where('self_delivery', 0);
                                });
                            })
                                ->orWhereHas('store', function ($qu) {
                                    $qu->where('store_business_model', 'commission')->where('self_delivery_system', 0);
                                });
                        });
                });
        } else {
            $orders = $orders->where('store_id', $dm->store_id);
        }

        if (config('order_confirmation_model') == 'deliveryman' && $dm->type == 'zone_wise') {
            $orders = $orders->whereIn('order_status', ['pending', 'confirmed', 'processing', 'handover']);
        } else {
            $orders = $orders->where(function ($query) {
                return $query->whereIn('order_status', ['confirmed', 'processing', 'handover'])
                    ->orWhere(function ($subQuery) {
                        return $subQuery->where('order_type', 'parcel')->whereIn('order_status', ['pending', 'confirmed', 'processing', 'handover']);
                    });
            });
        }
        if (isset($dm->vehicle_id)) {
            $orders = $orders->where('dm_vehicle_id', $dm->vehicle_id);
        }
        $orders = $orders->dmOrder()
            ->Notpos()
            ->NotDigitalOrder()
            ->OrderScheduledIn(30)
            ->whereNull('delivery_man_id')
            ->orderBy('schedule_at', 'desc')
            ->get();
        $orders = Helpers::order_data_formatting($orders, true);

        return response()->json($orders, 200);
    }

    public function ignore_order(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $dm = DeliveryMan::where(['auth_token' => $request['token']])->first();
        $order = Order::where('id', $request['order_id'])
            ->where(function($query) use ($dm) {
                $query->where('delivery_man_id', $dm->id)
                      ->orWhereNull('delivery_man_id');
            })
            ->first();

        if (!$order) {
            return response()->json([
                'errors' => [
                    ['code' => 'order', 'message' => translate('messages.not_found')],
                ],
            ], 404);
        }

        // Add to Redis blacklist so the worker doesn't assign it to this dm again
        if ($dm) {
            Redis::sadd('order:' . $order->id . ':rejected', $dm->id);
            // Decrease current_orders if it was already accepted
            if ($order->delivery_man_id == $dm->id && $order->order_status == 'accepted') {
                $dm->current_orders = $dm->current_orders > 1 ? $dm->current_orders - 1 : 0;
                $dm->save();
            }
        }

        // Return order to pending
        $order->delivery_man_id = null;
        if($order->order_status != 'canceled' && $order->order_status != 'delivered') {
            $order->order_status = 'pending';
        }
        $order->save();

        // Requeue to worker wave_queue
        $payload = [
            'order_id' => $order->id,
            'store_id' => $order->store_id ?? 0,
            'zone_id' => $order->zone_id,
            'attempt' => 2
        ];
        
        $expireTime = now()->addSeconds(5)->timestamp;
        Redis::zadd('wave_queue', $expireTime, json_encode($payload));

        return response()->json(['message' => translate('messages.order_ignored_successfully')], 200);
    }

    public function accept_order(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        // --- Redis Lock to prevent Race Condition ---
        $lockKey = 'order_accept_lock:' . $request['order_id'];
        // Try to acquire lock using SETNX (set if not exists) with 10s expiration
        $lockAcquired = Redis::set($lockKey, $request['token'], 'EX', 10, 'NX');

        if (!$lockAcquired) {
            // Another delivery man is currently accepting this order, or already accepted it
            return response()->json([
                'errors' => [
                    ['code' => 'order', 'message' => translate('messages.Another delivery man is accepting this order, please wait or try again.')],
                ],
            ], 409); // Conflict
        }
        
        $shouldReleaseLock = true;
        
        try {
            $dm = DeliveryMan::where(['auth_token' => $request['token']])->first();
            $order = Order::where('id', $request['order_id'])
                // ->whereIn('order_status', ['pending', 'confirmed'])
                ->whereNull('delivery_man_id')
                ->dmOrder()
                ->first();
            if (!$order) {
                return response()->json([
                    'errors' => [
                        ['code' => 'order', 'message' => translate('messages.can_not_accept')],
                    ],
                ], 404);
            }

            if ($request->has('lat') && $request->has('lng') && $dm && $order->order_type != 'parcel') {
                try {
                    $zoneIds = Zone::whereContains('coordinates', new Point($request->lat, $request->lng, POINT_SRID))->pluck('id')->toArray();
                    if (($dm->zone_id && !in_array($dm->zone_id, $zoneIds))) {
                        return response()->json([
                            'errors' => [
                                ['code' => 'dm_out_of_zone', 'message' => translate('messages.You are outside the service area. Move closer to accept this order.')]
                            ]
                        ], 403);
                    }
                } catch (\Throwable $th) {
                }
            }



            if ($dm->active != 1) {
                return response()->json([
                    'errors' => [
                        ['code' => 'active_status', 'message' => translate('messages.You_can_not_accept_order_on_offline')],
                    ],
                ], 404);
            }
            if ($dm->current_orders >= config('dm_maximum_orders')) {
                return response()->json([
                    'errors' => [
                        ['code' => 'dm_maximum_order_exceed', 'message' => translate('messages.dm_maximum_order_exceed_warning')],
                    ],
                ], 405);
            }

            $payments = $order->payments()->where('payment_method', 'cash_on_delivery')->exists();
            $cash_in_hand = $dm?->wallet?->collected_cash ?? 0;
            $dm_max_cash = BusinessSetting::where('key', 'dm_max_cash_in_hand')->first();
            $value = $dm_max_cash?->value ?? 0;

            if (($order->payment_method == 'cash_on_delivery' || $payments) && (($cash_in_hand + $order->order_amount) >= $value)) {

                return response()->json([
                    'errors' => [
                        ['code' => 'dm_maximum_hand_in_cash', 'message' => \App\CentralLogics\Helpers::format_currency($value) . ' ' . translate('max_cash_in_hand_exceeds')],
                    ],
                ], 405);
            }

            if ($order->order_type == 'parcel' && $order->order_status == 'confirmed') {
                $order->order_status = 'handover';
                $order->handover = now();
                $order->processing = now();
            } else {
                $order->order_status = in_array($order->order_status, ['pending', 'confirmed']) ? 'accepted' : $order->order_status;
            }

            $order->delivery_man_id = $dm->id;
            $order->accepted = now();
            $order->save();

            $dm->current_orders = $dm->current_orders + 1;
            $dm->save();

            $dm->increment('assigned_order_count');

            $fcm_token = $order->is_guest == 0 ? $order?->customer?->cm_firebase_token : $order?->guest?->fcm_token;

            $value = Helpers::order_status_update_message('accepted', $order->module->module_type);
            $value = Helpers::text_variable_data_format(value: $value, store_name: $order->store?->name, order_id: $order->id, user_name: "{$order?->customer?->f_name} {$order?->customer?->l_name}", delivery_man_name: "{$order->delivery_man?->f_name} {$order->delivery_man?->l_name}");
            try {
                if ($value && $fcm_token && Helpers::getNotificationStatusData('customer', 'customer_order_notification', 'push_notification_status')) {
                    $data = [
                        'title' => translate('Order_Notification'),
                        'description' => $value,
                        'order_id' => $order['id'],
                        'image' => '',
                        'type' => 'order_status',
                    ];
                    Helpers::send_push_notif_to_device($fcm_token, $data);
                }
            } catch (\Exception $e) {
            }

            // Success, we don't want to release the lock immediately, let it expire organically
            $shouldReleaseLock = false;
            return response()->json(['message' => 'Order accepted successfully'], 200);

        } catch (\Exception $e) {
            info("Order Accept Error: " . $e->getMessage());
            return response()->json(['errors' => [['code' => 'server_error', 'message' => 'Server correctly stopped by exception']]], 500);
        } finally {
            if ($shouldReleaseLock) {
                Redis::del($lockKey);
            }
        }
    }

    public function record_location_data(Request $request)
    {
        $dm = DeliveryMan::where(['auth_token' => $request['token']])->first();
        DeliveryHistory::updateOrCreate(['delivery_man_id' => $dm['id']], [
            'longitude' => $request['longitude'],
            'latitude' => $request['latitude'],
            'time' => now(),
            'location' => $request['location'],
            'created_at' => now(),
            'updated_at' => now()
        ]);
        return response()->json(['message' => translate('location recorded')], 200);
    }

    public function get_order_history(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $dm = DeliveryMan::where(['auth_token' => $request['token']])->first();

        $history = DeliveryHistory::where(['order_id' => $request['order_id'], 'delivery_man_id' => $dm['id']])->get();

        return response()->json($history, 200);
    }

    public function send_order_otp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $dm = DeliveryMan::where(['auth_token' => $request['token']])->first();
        $order = Order::where(['id' => $request['order_id'], 'delivery_man_id' => $dm['id']]);
        if (config('order_confirmation_model') == 'deliveryman' && $dm->type == 'zone_wise') {
            $order = $order->whereIn('order_status', ['pending', 'confirmed', 'processing', 'handover', 'picked_up']);
        } else {
            $order = $order->where(function ($query) {
                $query->whereIn('order_status', ['confirmed', 'processing', 'handover', 'picked_up'])->orWhere('order_type', 'parcel');
            });
        }
        $order = $order->dmOrder()->first();
        if (!$order) {
            return response()->json([
                'errors' => [
                    ['code' => 'order', 'message' => translate('messages.not_found')],
                ],
            ], 404);
        }
        $value = translate('your_order_is_ready_to_be_delivered,_plesae_share_your_otp_with_delivery_man.') . ' ' . translate('otp:') . $order->otp . ', ' . translate('order_id:') . $order->id;
        try {

            $fcm_token = $order->is_guest == 0 ? $order?->customer?->cm_firebase_token : $order?->guest?->fcm_token;
            if ($value && $fcm_token && Helpers::getNotificationStatusData('customer', 'customer_delivery_verification', 'push_notification_status')) {
                $data = [
                    'title' => translate('messages.order_ready_to_be_delivered'),
                    'description' => $value,
                    'order_id' => $order->id,
                    'image' => '',
                    'type' => 'otp',
                ];

                Helpers::send_push_notif_to_device($fcm_token, $data);
                DB::table('user_notifications')->insert([
                    'data' => json_encode($data),
                    'user_id' => $order->user_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } catch (\Exception $e) {
            info($e->getMessage());

            return response()->json(['message' => translate('messages.push_notification_faild')], 403);
        }

        return response()->json([], 200);
    }

    public function update_order_status(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
            'status' => 'required|in:confirmed,canceled,picked_up,delivered,handover',
            'reason' => 'required_if:status,canceled',
            'order_proof' => 'array|max:5',
            'actual_price' => 'nullable|numeric',
        ]);

        $validator->sometimes('otp', 'required', function ($request) {
            return Config::get('order_delivery_verification') == 1 && $request['status'] == 'delivered';
        });

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $dm = DeliveryMan::where(['auth_token' => $request['token']])->first();

        $order = Order::where(['id' => $request['order_id'], 'delivery_man_id' => $dm['id']])->dmOrder()->first();

        if (!$order || (!$order->store && $order->order_type != 'parcel')) {
            return response()->json([
                'errors' => [
                    ['code' => 'not_found', 'message' => translate('messages.you_can_not_change_the_status_of_this_order')],
                ],
            ], 403);
        }

        if ($order->order_type == 'parcel' && $request['status'] == 'canceled') {
            $cancel_parcel_order = OrderLogic::cancelParcelOrder($order, 'deliveryman', $request);
            if (data_get($cancel_parcel_order, 'status_code') != 200) {
                return response()->json([
                    'errors' => [
                        ['code' => data_get($cancel_parcel_order, 'code'), 'message' => data_get($cancel_parcel_order, 'message')],
                    ],
                ], data_get($cancel_parcel_order, 'status_code'));
            } else {
                return response()->json(['message' => translate('messages.Parcel_canceled_successfully')], 200);
            }
        }

        if ($request['status'] == 'confirmed' && config('order_confirmation_model') == 'store') {
            return response()->json([
                'errors' => [
                    ['code' => 'order-confirmation-model', 'message' => translate('messages.order_confirmation_warning')],
                ],
            ], 403);
        }

        if ($request['status'] == 'canceled' && !config('canceled_by_deliveryman')) {
            return response()->json([
                'errors' => [
                    ['code' => 'status', 'message' => translate('messages.you_can_not_cancel_a_order')],
                ],
            ], 403);
        }

        if ($order->confirmed && $request['status'] == 'canceled') {
            return response()->json([
                'errors' => [
                    ['code' => 'delivery-man', 'message' => translate('messages.order_can_not_cancle_after_confirm')],
                ],
            ], 403);
        }

        if (Config::get('order_delivery_verification') == 1 && $request['status'] == 'delivered' && $order->otp != $request['otp']) {
            return response()->json([
                'errors' => [
                    ['code' => 'otp', 'message' => translate('Otp Not matched')],
                ],
            ], 406);
        }
        if ($request->status == 'delivered') {
            $unpaid_digital_payment = OrderPayment::where('order_id', $order->id)
                ->where('payment_status', 'unpaid')
                ->whereNotIn('payment_method', ['cash_on_delivery', 'card_on_delivery'])
                ->exists();

            if ($unpaid_digital_payment) {
                return response()->json([
                    'errors' => [
                        ['code' => 'payment_pending', 'message' => translate('messages.payment_is_pending_for_this_order')],
                    ],
                ], 403);
            }

            if ($order->transaction == null) {
                $unpaid_payment = OrderPayment::where('payment_status', 'unpaid')->where('order_id', $order->id)->first();
                $pay_method = 'digital_payment';
                if ($unpaid_payment && in_array($unpaid_payment->payment_method, ['cash_on_delivery', 'card_on_delivery'], true)) {
                    $pay_method = $unpaid_payment->payment_method;
                }
                $is_cod_like = in_array($order->payment_method, ['cash_on_delivery', 'card_on_delivery'], true)
                    || in_array($pay_method, ['cash_on_delivery', 'card_on_delivery'], true);
                $reveived_by = $is_cod_like ? ($dm->type != 'zone_wise' ? 'store' : 'deliveryman') : 'admin';

                if (OrderLogic::create_transaction($order, $reveived_by, null)) {
                    $order->payment_status = 'paid';
                } else {
                    return response()->json([
                        'errors' => [
                            ['code' => 'error', 'message' => translate('messages.faield_to_create_order_transaction')],
                        ],
                    ], 406);
                }
                Helpers::deliverymanLoyaltyPointHistory(deliveryManId: $dm->id, amount: $order->order_amount, transactionType: 'earn_on_order_completion', pointConversionType: 'credit', reference: $order->id);
            }
            if ($order->transaction) {
                $order->transaction->update(['delivery_man_id' => $dm->id]);
            }

            // Increment Mission Progress
            try {
                MissionLogic::increment_mission_progress($order);
            } catch (\Exception $e) {
                info("Mission progress err: " . $e->getMessage());
            }

            $order->details->each(function ($item, $key) {
                if ($item->food) {
                    $item->food->increment('order_count');
                }
            });
            $order?->customer?->increment('order_count');

            $dm->current_orders = $dm->current_orders > 1 ? $dm->current_orders - 1 : 0;
            $dm->save();

            $dm->increment('order_count');
            if ($order->store) {
                $order->store->increment('order_count');
            }
            if ($order->parcel_category) {
                $order->parcel_category->increment('orders_count');
            }

            $is_partial = $order->details->where('delivery_status', 'pending')->count() > 0;

            if ($order->module->module_type == 'food') {
                /// logic for food module not changes
            } else {
                // Logic for grocery and other modules with potential partial delivery

                // Refined Logic based on user request:
                // 1. Identify items to mark as delivered.
                //    - Current assumption: The driver delivers items with 'minutes' delivery type first.
                //    - Or, if the order is mixed, we process 'minutes' items.

                $minutes_items = $order->details->filter(function ($detail) {
                    $item_details = json_decode($detail->item_details, true);
                    return isset($item_details['delivery_time_type']) && $item_details['delivery_time_type'] == 'minutes' && $detail->delivery_status == 'pending';
                });

                $other_items = $order->details->filter(function ($detail) {
                    $item_details = json_decode($detail->item_details, true);
                    return isset($item_details['delivery_time_type']) && $item_details['delivery_time_type'] != 'minutes' && $detail->delivery_status == 'pending';
                });

                // If we have minutes items pending, we assume this delivery is for them.
                if ($minutes_items->count() > 0) {
                    foreach ($minutes_items as $detail) {
                        $detail->delivery_status = 'delivered';
                        $detail->save();
                    }
                } elseif ($other_items->count() > 0) {
                    // If no minutes items, deliver the rest
                    foreach ($other_items as $detail) {
                        $detail->delivery_status = 'delivered';
                        $detail->save();
                    }
                }

                // Re-evaluate partial status
                $pending_items = $order->details()->where('delivery_status', 'pending')->count();

                if ($pending_items > 0) {
                    // PARTIAL DELIVERY
                    $order->order_status = 'partial_delivered';
                    $order->delivery_man_id = null; // Release driver
                    $order->save();

                    // Respond with partial delivery message
                    return response()->json(['message' => translate('messages.partial_delivery_successful_driver_released')], 200);
                }
            }

            // Standard full delivery logic (existing or if no pending items left)
            $img_names = [];
            $images = [];
            if (!empty($request->file('order_proof'))) {
                foreach ($request->order_proof as $img) {
                    $image_name = Helpers::upload('order/', 'png', $img);
                    array_push($img_names, ['img' => $image_name, 'storage' => Helpers::getDisk()]);
                }

                $images = $img_names;
            }
            if (count($images) > 0) {
                $order->order_proof = json_encode($images);
            }

            OrderLogic::update_unpaid_order_payment(order_id: $order->id, payment_method: $order->payment_method);
        } elseif ($request->status == 'canceled') {
            if ($order->delivery_man) {
                $dm = $order->delivery_man;
                $dm->current_orders = $dm->current_orders > 1 ? $dm->current_orders - 1 : 0;
                $dm->save();
            }
            if ($order->is_guest == 0) {
                OrderLogic::refund_before_delivered($order);
            }
            $order->cancellation_reason = $request->reason;
            $order->canceled_by = 'deliveryman';
        } elseif ($order->order_type == 'parcel' && $request->status == 'handover') {
            $order->confirmed = now();
            $order->processing = now();
        } elseif ($order->order_type == 'parcel' && $request->status == 'picked_up') {
            if ($request->has('actual_price')) {
                OrderLogic::apply_price_adjustment($order, $request->actual_price);
            }
        } elseif ($order->order_type != 'parcel' && in_array($request->status, ['picked_up'])) {
            Helpers::sendOrderDeliveryVerificationOtp($order);
        }

        $order->order_status = $request['status'];
        $order[$request['status']] = now();
        $order->save();

        Helpers::send_order_notification($order);

        return response()->json(['message' => translate('Status updated')], 200);
    }

    public function get_order_details(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $dm = DeliveryMan::where(['auth_token' => $request['token']])->first();

        $dm = DeliveryMan::where(['auth_token' => $request['token']])->first();
        $order = Order::with(['details'])->where('id', $request['order_id'])->where(function ($query) use ($dm) {
            $query->WhereNull('delivery_man_id')
                ->orWhere('delivery_man_id', $dm['id']);
        })->Notpos()->first();
        if (!$order) {
            return response()->json([
                'errors' => [
                    ['code' => 'order', 'message' => translate('messages.not_found')],
                ],
            ], 404);
        }
        $details = isset($order->details) ? $order->details : null;
        if ($details != null && $details->count() > 0) {
            $details[0]['vendor_id'] = $order?->store?->vendor_id;
            $details = $details = Helpers::order_details_data_formatting($details);
            $details[0]['is_guest'] = (int) $order->is_guest;

            return response()->json($details, 200);
        } elseif ($order->order_type == 'parcel') {
            $order->delivery_address = $order->delivery_address ? json_decode($order->delivery_address, true) : [];

            return response()->json(($order), 200);
        } elseif ($order->prescription_order == 1) {
            return response()->json([], 200);
        }

        return response()->json([
            'errors' => [
                ['code' => 'order', 'message' => translate('messages.not_found')],
            ],
        ], 404);
    }

    public function get_order(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $dm = DeliveryMan::where(['auth_token' => $request['token']])->first();

        $order = Order::with(['customer', 'store', 'details', 'parcel_category', 'payments', 'ParcelCancellation'])
            ->where('id', $request['order_id'])
            ->where(function ($query) use ($dm) {
                $query->whereNull('delivery_man_id')
                    ->orWhere('delivery_man_id', $dm['id']);
            })
            ->Notpos()
            ->first();
        if (!$order) {
            return response()->json([
                'errors' => [
                    ['code' => 'order', 'message' => translate('messages.not_found')],
                ],
            ], 204);
        }

        return response()->json(Helpers::order_data_formatting($order), 200);
    }

    public function get_all_orders(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required',
            'offset' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $dm = DeliveryMan::where(['auth_token' => $request['token']])->first();

        $paginator = Order::with(['customer', 'store', 'parcel_category'])
            ->where(['delivery_man_id' => $dm['id']])
            ->whereIn('order_status', ['delivered', 'canceled', 'returned', 'refund_requested', 'refunded', 'failed'])
            ->orderBy('schedule_at', 'desc')
            ->dmOrder()
            ->paginate($request['limit'], ['*'], 'page', $request['offset']);
        $orders = Helpers::order_data_formatting($paginator->items(), true);
        $data = [
            'total_size' => $paginator->total(),
            'limit' => $request['limit'],
            'offset' => $request['offset'],
            'orders' => $orders,
        ];

        return response()->json($data, 200);
    }

    public function get_last_location(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $last_data = DeliveryHistory::whereHas('delivery_man.orders', function ($query) use ($request) {
            return $query->where('id', $request->order_id);
        })->latest()->first();

        return response()->json($last_data, 200);
    }

    public function order_payment_status_update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
            'status' => 'required|in:paid',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $dm = DeliveryMan::where(['auth_token' => $request['token']])->first();

        if (Order::where(['delivery_man_id' => $dm['id'], 'id' => $request['order_id']])->dmOrder()->first()) {
            Order::where(['delivery_man_id' => $dm['id'], 'id' => $request['order_id']])->update([
                'payment_status' => $request['status'],
            ]);

            return response()->json(['message' => translate('Payment status updated')], 200);
        }

        return response()->json([
            'errors' => [
                ['code' => 'order', 'message' => translate('not found!')],
            ],
        ], 404);
    }

    public function update_fcm_token(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fcm_token' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $dm = DeliveryMan::where(['auth_token' => $request['token']])->first();

        DeliveryMan::where(['id' => $dm['id']])->update([
            'fcm_token' => $request['fcm_token'],
        ]);

        return response()->json(['message' => translate('successfully updated!')], 200);
    }

    public function get_notifications(Request $request)
    {

        $dm = DeliveryMan::where(['auth_token' => $request['token']])->first();

        $notifications = Notification::active()->where(function ($q) use ($dm) {
            $q->whereNull('zone_id')->orWhere('zone_id', $dm->zone_id);
        })->where('tergat', 'deliveryman')->where('created_at', '>=', \Carbon\Carbon::today()->subDays(7))->get();

        $user_notifications = UserNotification::where('delivery_man_id', $dm->id)->where('created_at', '>=', \Carbon\Carbon::today()->subDays(7))->get();

        $notifications->append('data');

        $notifications = $notifications->merge($user_notifications);
        try {
            return response()->json($notifications, 200);
        } catch (\Exception $e) {
            return response()->json([], 200);
        }
    }

    public function remove_account(Request $request)
    {
        $dm = DeliveryMan::where(['auth_token' => $request['token']])->first();

        if (Order::where('delivery_man_id', $dm->id)->whereIn('order_status', ['pending', 'accepted', 'confirmed', 'processing', 'handover', 'picked_up'])->count()) {
            return response()->json(['errors' => [['code' => 'on-going', 'message' => translate('messages.Please_complete_your_ongoing_and_accepted_orders')]]], 203);
        }

        if ($dm->wallet && $dm->wallet->collected_cash > 0) {
            return response()->json(['errors' => [['code' => 'on-going', 'message' => translate('messages.You_have_cash_in_hand,_you_have_to_pay_the_due_to_delete_your_account.')]]], 203);
        }

        Helpers::check_and_delete('delivery-man/', $dm['image']);

        foreach (json_decode($dm['identity_image'], true) as $img) {
            Helpers::check_and_delete('delivery-man/', $img);
        }
        if ($dm->userinfo) {

            $dm->userinfo->delete();
        }
        $dm->delete();

        return response()->json([]);
    }

    public function make_payment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_gateway' => 'required',
            'amount' => 'required|numeric|min:.001',
            'callback' => 'required',
            'token' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $dm = DeliveryMan::where(['auth_token' => $request['token']])->firstOrfail();

        $payer = new Payer(
            $dm->f_name,
            $dm->email,
            $dm->phone,
            ''
        );

        $store_logo = BusinessSetting::where(['key' => 'logo'])->first();
        $additional_data = [
            'business_name' => BusinessSetting::where(['key' => 'business_name'])->first()?->value,
            'business_logo' => \App\CentralLogics\Helpers::get_full_url('business', $store_logo?->value, $store_logo?->storage[0]?->value ?? 'public'),
        ];
        $payment_info = new PaymentInfo(
            success_hook: 'collect_cash_success',
            failure_hook: 'collect_cash_fail',
            currency_code: Helpers::currency_code(),
            payment_method: $request->payment_gateway,
            payment_platform: 'app',
            payer_id: $dm->id,
            receiver_id: '100',
            additional_data: $additional_data,
            payment_amount: $request->amount,
            external_redirect_link: $request->has('callback') ? $request['callback'] : session('callback'),
            attribute: 'deliveryman_collect_cash_payments',
            attribute_id: $dm->id,
        );

        $receiver_info = new Receiver('Admin', 'example.png');
        $redirect_link = Payment::generate_link($payer, $payment_info, $receiver_info);

        $data = [
            'redirect_link' => $redirect_link,
        ];

        return response()->json($data, 200);
    }

    public function make_wallet_adjustment(Request $request)
    {

        $dm = DeliveryMan::where(['auth_token' => $request['token']])->firstOrfail();
        $wallet = DeliveryManWallet::firstOrNew(
            ['delivery_man_id' => $dm->id]
        );
        $wallet_earning = round($wallet->total_earning - ($wallet->total_withdrawn + $wallet->pending_withdraw), 8);
        $adj_amount = $wallet->collected_cash - $wallet_earning;

        if ($wallet->collected_cash == 0 || $wallet_earning == 0) {
            return response()->json(['message' => translate('messages.Already_Adjusted')], 201);
        }

        if ($adj_amount > 0) {
            $wallet->total_withdrawn = $wallet->total_withdrawn + $wallet_earning;
            $wallet->collected_cash = $wallet->collected_cash - $wallet_earning;

            $data = [
                'delivery_man_id' => $dm->id,
                'amount' => $wallet_earning,
                'ref' => 'delivery_man_wallet_adjustment_partial',
                'method' => 'adjustment',
                // 'approved' => 1,
                // 'type' => 'adjustment',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        } else {
            $data = [
                'delivery_man_id' => $dm->id,
                'amount' => $wallet->collected_cash,
                'ref' => 'delivery_man_wallet_adjustment_full',
                'method' => 'adjustment',
                // 'approved' => 1,
                // 'type' => 'adjustment',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $wallet->total_withdrawn = $wallet->total_withdrawn + $wallet->collected_cash;
            $wallet->collected_cash = 0;
        }

        $wallet->save();
        DB::table('provide_d_m_earnings')->insert($data);

        return response()->json(['message' => translate('messages.Delivery_man_wallet_adjustment_successfull')], 200);
    }

    public function wallet_payment_list(Request $request)
    {
        $limit = $request['limit'] ?? 25;
        $offset = $request['offset'] ?? 1;
        $dm = DeliveryMan::where(['auth_token' => $request['token']])->firstOrFail();

        $key = isset($request['search']) ? explode(' ', $request['search']) : [];
        $paginator = AccountTransaction::when(isset($key), function ($query) use ($key) {
            return $query->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('ref', 'like', "%{$value}%");
                }
            });
        })
            ->where('type', 'collected')
            ->where('created_by', 'deliveryman')
            ->where('from_id', $dm->id)
            ->where('from_type', 'deliveryman')
            ->latest()

            ->paginate($limit, ['*'], 'page', $offset);

        $temp = [];

        foreach ($paginator->items() as $item) {
            $item['status'] = 'approved';
            $item['payment_time'] = \App\CentralLogics\Helpers::time_date_format($item->created_at);

            $temp[] = $item;
        }
        $data = [
            'total_size' => $paginator->total(),
            'limit' => $limit,
            'offset' => $offset,
            'transactions' => $temp,
        ];

        return response()->json($data, 200);
    }

    public function wallet_provided_earning_list(Request $request)
    {
        $limit = $request['limit'] ?? 25;
        $offset = $request['offset'] ?? 1;
        $dm = DeliveryMan::where(['auth_token' => $request['token']])->firstOrFail();

        $key = isset($request['search']) ? explode(' ', $request['search']) : [];
        $paginator = ProvideDMEarning::when(isset($key), function ($query) use ($key) {
            return $query->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('ref', 'like', "%{$value}%");
                }
            });
        })
            ->where('delivery_man_id', $dm->id)
            ->where('method', 'adjustment')
            ->whereIn('ref', ['delivery_man_wallet_adjustment_partial', 'delivery_man_wallet_adjustment_full'])
            ->latest()
            ->paginate($limit, ['*'], 'page', $offset);

        $temp = [];

        foreach ($paginator->items() as $item) {
            $item['amount'] = (float) $item['amount'];
            $item['status'] = 'Approved';
            $item['payment_time'] = \App\CentralLogics\Helpers::time_date_format($item->created_at);

            $temp[] = $item;
        }
        $data = [
            'total_size' => $paginator->total(),
            'limit' => $limit,
            'offset' => $offset,
            'transactions' => $temp,
        ];

        return response()->json($data, 200);
    }

    public function get_disbursement_withdrawal_methods(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required',
            'offset' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $dm = DeliveryMan::where(['auth_token' => $request['token']])->first();

        $key = explode(' ', $request['search']);
        $paginator = DisbursementWithdrawalMethod::where('delivery_man_id', $dm['id'])
            ->whereHas('withdraw_method', function ($query) {
                $query->where('is_active', 1);
            })
            ->when(
                isset($key),
                function ($query) use ($key) {
                    $query->where(function ($q) use ($key) {
                        foreach ($key as $value) {
                            $q->orWhere('method_name', 'like', "%{$value}%");
                        }
                    });
                }
            )
            ->latest()
            ->paginate($request['limit'], ['*'], 'page', $request['offset']);

        $datas = [];
        foreach ($paginator->items() as $k => $v) {
            $userInputs = [];
            foreach (json_decode($v->method_fields, true) as $key => $value) {
                $userInput = [
                    'user_input' => $key,
                    'user_data' => $value,
                ];
                $userInputs[] = $userInput;
            }
            $v['method_fields'] = $userInputs;
            $datas[] = $v;
        }

        $data = [
            'total_size' => $paginator->total(),
            'limit' => $request['limit'],
            'offset' => $request['offset'],
            'methods' => $datas,
        ];

        return response()->json($data, 200);
    }

    public function withdraw_method_list()
    {
        $wi = WithdrawalMethod::where('is_active', 1)->get();

        return response()->json($wi, 200);
    }

    public function disbursement_withdrawal_method_store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'withdraw_method_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $dm = DeliveryMan::where(['auth_token' => $request['token']])->first();

        $method = WithdrawalMethod::find($request['withdraw_method_id']);
        $fields = array_column($method->method_fields, 'input_name');
        $values = $request->all();

        $method_data = [];
        foreach ($fields as $field) {
            if (array_key_exists($field, $values)) {
                $method_data[$field] = $values[$field];
            }
        }

        $disbursementMethod = DisbursementWithdrawalMethod::firstOrNew([
            'id' => $request->disbursement_withdrawal_method_id ?? null,
        ]);
        $disbursementMethod->delivery_man_id = $dm['id'];
        $disbursementMethod->withdrawal_method_id = $method['id'];
        $disbursementMethod->method_name = $method['method_name'];
        $disbursementMethod->method_fields = json_encode($method_data);
        $disbursementMethod->is_default = $disbursementMethod->exists ? $disbursementMethod->is_default : 0;
        $disbursementMethod->save();

        return response()->json(['message' => translate('successfully added!')], 200);
    }

    public function disbursement_withdrawal_method_default(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'is_default' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $dm = DeliveryMan::where(['auth_token' => $request['token']])->first();
        $method = DisbursementWithdrawalMethod::find($request->id);
        $method->is_default = $request->is_default;
        $method->save();
        DisbursementWithdrawalMethod::whereNot('id', $request->id)->where('delivery_man_id', $dm['id'])->update(['is_default' => 0]);

        return response()->json(['message' => translate('messages.method_updated_successfully')], 200);
    }

    public function disbursement_withdrawal_method_delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $method = DisbursementWithdrawalMethod::find($request->id);
        $method->delete();

        return response()->json(['message' => translate('messages.method_deleted_successfully')], 200);
    }

    public function disbursement_report(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required',
            'offset' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $limit = $request['limit'] ?? 25;
        $offset = $request['offset'] ?? 1;

        $dm = DeliveryMan::where(['auth_token' => $request['token']])->first();

        $total_disbursements = DisbursementDetails::where('delivery_man_id', $dm['id'])->latest()->get();
        $paginator = DisbursementDetails::where('delivery_man_id', $dm['id'])->latest()->paginate($limit, ['*'], 'page', $offset);

        $paginator->each(function ($data) {
            $data->withdraw_method?->method_fields ? $data->withdraw_method->method_fields = json_decode($data->withdraw_method?->method_fields, true) : '';
        });

        $data = [
            'total_size' => $paginator->total(),
            'limit' => $limit,
            'offset' => $offset,
            'pending' => (float) $total_disbursements->where('status', 'pending')->sum('disbursement_amount'),
            'completed' => (float) $total_disbursements->where('status', 'completed')->sum('disbursement_amount'),
            'canceled' => (float) $total_disbursements->where('status', 'canceled')->sum('disbursement_amount'),
            'complete_day' => (int) BusinessSetting::where(['key' => 'dm_disbursement_waiting_time'])->first()?->value,
            'disbursements' => $paginator->items(),
        ];

        return response()->json($data, 200);
    }



    private function reportData($request)
    {

        $dm = DeliveryMan::where(['auth_token' => $request['token']])->first();
        $start = $request->start_date ?? null;
        $end = $request->end_date ?? null;

        $points = DeliverymanLoyaltyPointHistory::where('delivery_man_id', $dm->id)->applyDateFilter($request->date_range, $start, $end)->where('point_conversion_type', 'debit')->sum('converted_amount');
        $referal = DeliverymanReferralHistory::where('delivery_man_id', $dm->id)->applyDateFilter($request->date_range, $start, $end)->sum('amount');
        $type = $request['type'] ?? 'all';


        $baseQuery = OrderTransaction::with(['order:id,payment_method'])
            ->where('delivery_man_id', $dm['id'])
            ->where(function ($query) {
                $query->where('original_delivery_charge', '>', 0)
                    ->orWhere('dm_tips', '>', 0);
            })->applyDateFilter($request->date_range, $start, $end);
        if ($type === 'delivery_fee') {
            $baseQuery->where('original_delivery_charge', '>', 0);
        } elseif ($type === 'delivery_tips') {
            $baseQuery->where('dm_tips', '>', 0);
        }

        $total_dm_tips = (clone $baseQuery)->sum('dm_tips');
        $total_delivery_charge = (clone $baseQuery)->sum('original_delivery_charge');
        $total_admin_commission = (clone $baseQuery)->sum('delivery_fee_comission');

        return [
            'points' => $points,
            'referal' => $referal,
            'total_dm_tips' => $total_dm_tips,
            'total_delivery_charge' => $total_delivery_charge,
            'total_admin_commission' => $total_admin_commission,
            'type' => $type,
            'dm' => $dm,
            'start' => $start,
            'end' => $end,
            'baseQuery' => $baseQuery
        ];

    }


    public function earningReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|integer',
            'offset' => 'required|integer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'type' => 'nullable|in:all,delivery_fee,delivery_tips',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $limit = $request['limit'] ?? 25;
        $offset = $request['offset'] ?? 1;

        $data = $this->reportData($request);
        $total_dm_tips = $data['total_dm_tips'];
        $total_delivery_charge = $data['total_delivery_charge'];
        $total_admin_commission = $data['total_admin_commission'];
        $baseQuery = $data['baseQuery'];

        $paginated_orders = (clone $baseQuery)
            ->select(['id', 'order_id', 'delivery_man_id', 'dm_tips', 'original_delivery_charge', 'delivery_fee_comission', 'created_at'])
            ->latest()
            ->paginate($limit, ['*'], 'page', $offset);

        $paginated_orders->getCollection()->transform(function ($item) {
            $item->dm_tips = (float) $item->dm_tips;
            $item->original_delivery_charge = (float) $item->original_delivery_charge;
            $item->delivery_fee_comission = (float) $item->delivery_fee_comission;
            return $item;
        });

        $data = [
            'earning' => $paginated_orders,
            'total_loyalty_point_earning' => (float) $data['points'],
            'total_referal' => (float) $data['referal'],
            'total_dm_tips' => (float) $total_dm_tips,
            'total_delivery_charge' => (float) $total_delivery_charge,
            'total_admin_commission' => (float) $total_admin_commission,
            'type' => $data['type'],
            'limit' => $limit,
            'offset' => $offset,
        ];

        return response()->json($data, 200);
    }

    public function loyaltyReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|integer',
            'offset' => 'required|integer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $limit = $request['limit'] ?? 25;
        $offset = $request['offset'] ?? 1;

        $data = $this->reportData($request);
        $total_dm_tips = $data['total_dm_tips'];
        $total_delivery_charge = $data['total_delivery_charge'];
        $total_admin_commission = $data['total_admin_commission'];
        $dm = $data['dm'];

        $loyalityPoints = DeliverymanLoyaltyPointHistory::where('delivery_man_id', $dm->id)->applyDateFilter($request->date_range, $data['start'], $data['end'])->where('point_conversion_type', 'debit')
            ->select(['id', 'transaction_id', 'transaction_type', 'converted_amount', 'point', 'created_at'])
            ->latest()
            ->paginate($limit, ['*'], 'page', $offset);

        $data = [
            'loyalityPoints' => $loyalityPoints->items(),
            'total' => $loyalityPoints->total(),
            'total_loyalty_point_earning' => (float) $data['points'],
            'total_referal' => (float) $data['referal'],
            'total_dm_tips' => (float) $total_dm_tips,
            'total_delivery_charge' => (float) $total_delivery_charge,
            'total_admin_commission' => (float) $total_admin_commission,
            'type' => $data['type'],
            'limit' => $limit,
            'offset' => $offset,
        ];

        return response()->json($data, 200);
    }

    public function referralEarningReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|integer',
            'offset' => 'required|integer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $limit = $request['limit'] ?? 25;
        $offset = $request['offset'] ?? 1;

        $data = $this->reportData($request);
        $total_dm_tips = $data['total_dm_tips'];
        $total_delivery_charge = $data['total_delivery_charge'];
        $total_admin_commission = $data['total_admin_commission'];
        $dm = $data['dm'];


        $refrealEarnings = DeliverymanReferralHistory::where('delivery_man_id', $dm->id)->applyDateFilter($request->date_range, $data['start'], $data['end'])
            ->select(['id', 'transaction_id', 'amount', 'created_at'])
            ->latest()
            ->paginate($limit, ['*'], 'page', $offset);

        $data = [
            'refrealEarnings' => $refrealEarnings->items(),
            'total' => $refrealEarnings->total(),
            'total_loyalty_point_earning' => (float) $data['points'],
            'total_referal' => (float) $data['referal'],
            'total_dm_tips' => (float) $total_dm_tips,
            'total_delivery_charge' => (float) $total_delivery_charge,
            'total_admin_commission' => (float) $total_admin_commission,
            'type' => $data['type'],
            'limit' => $limit,
            'offset' => $offset,
        ];

        return response()->json($data, 200);
    }
    public function referralEarninglist(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|integer',
            'offset' => 'required|integer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $limit = $request['limit'] ?? 25;
        $offset = $request['offset'] ?? 1;
        $dm = DeliveryMan::where(['auth_token' => $request['token']])->first();
        $start = $request->start_date ?? null;
        $end = $request->end_date ?? null;


        $refrealEarnings = DeliverymanReferralHistory::where('delivery_man_id', $dm->id)->applyDateFilter($request->date_range, $start, $end)
            ->select(['id', 'transaction_id', 'amount', 'created_at'])
            ->latest()
            ->paginate($limit, ['*'], 'page', $offset);

        $data = [
            'total' => $refrealEarnings->total(),
            'limit' => $limit,
            'offset' => $offset,
            'refrealEarnings' => $refrealEarnings->items(),
        ];

        return response()->json($data, 200);
    }

    public function loyaltyPointlist(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|integer',
            'offset' => 'required|integer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'type' => 'nullable|in:credit,debit,both',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $limit = $request['limit'] ?? 25;
        $offset = $request['offset'] ?? 1;
        $dm = DeliveryMan::where(['auth_token' => $request['token']])->first();
        $start = $request->start_date ?? null;
        $end = $request->end_date ?? null;
        $type = $request['type'] ?? 'both';



        $loyalityPoints = DeliverymanLoyaltyPointHistory::where('delivery_man_id', $dm->id)->applyDateFilter($request->date_range, $start, $end)
            ->when($type != 'both', function ($q) use ($type) {
                $q->where('point_conversion_type', $type);
            })
            ->select(['id', 'transaction_id', 'transaction_type', 'converted_amount', 'point', 'created_at', 'reference', 'point_conversion_type'])
            ->latest()
            ->paginate($limit, ['*'], 'page', $offset);

        $data = [
            'total' => $loyalityPoints->total(),
            'limit' => $limit,
            'offset' => $offset,
            'loyalityPoints' => $loyalityPoints->items(),
        ];

        return response()->json($data, 200);
    }

    public function withdraw_list(Request $request)
    {
        $dm = DeliveryMan::where(['auth_token' => $request['token']])->first();

        $withdraw_req = WithdrawRequest::where('delivery_man_id', $dm->id)->latest()->get();

        $temp = [];
        $status = [
            0 => 'Pending',
            1 => 'Approved',
            2 => 'Denied',
        ];
        foreach ($withdraw_req as $item) {
            $item->status = $status[$item->approved];
            $item->requested_at = $item->created_at->format('Y-m-d H:i:s');

            if ($item->type == 'disbursement') {

                $item->bank_name = $item->disbursementMethod ? $item->disbursementMethod->method_name : translate('Account');
            } else {
                $item->bank_name = $item->method ? $item->method->method_name : translate('Account');
            }
            $item->detail = json_decode($item->withdrawal_method_fields, true);

            unset($item['created_at']);
            unset($item['approved']);
            $temp[] = $item;
        }

        return response()->json($temp, 200);
    }

    public function request_withdraw(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $dm = DeliveryMan::where(['auth_token' => $request['token']])->first();

        $method = WithdrawalMethod::find($request['id']);
        $fields = array_column($method->method_fields, 'input_name');
        $values = $request->all();

        $method_data = [];
        foreach ($fields as $field) {
            if (array_key_exists($field, $values)) {
                $method_data[$field] = $values[$field];
            }
        }

        $w = $dm?->wallet;
        if ($w?->balance >= $request['amount']) {
            $data = [
                'delivery_man_id' => $w?->delivery_man_id,
                'amount' => $request['amount'],
                'transaction_note' => null,
                'sender_note' => $request['sender_note'],
                'withdrawal_method_id' => $request['id'],
                'withdrawal_method_fields' => json_encode($method_data),
                'approved' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            try {
                DB::table('withdraw_requests')->insert($data);
                $w?->increment('pending_withdraw', $request['amount']);
                $mail_status = Helpers::get_mail_status('dm_withdraw_request_mail_status_admin');
                $admin = Admin::where('role_id', 1)->first();
                $wallet_transaction = WithdrawRequest::where('delivery_man_id', $w->delivery_man_id)->latest()->first();
                if (config('mail.status') && $mail_status == '1' && Helpers::getNotificationStatusData('admin', 'dm_withdraw_request', 'mail_status')) {
                    Mail::to($admin->email)->send(new WithdrawRequestMail('admin_mail', $wallet_transaction, 'dm'));
                }

                return response()->json(['message' => translate('messages.withdraw_request_placed_successfully')], 200);
            } catch (\Exception $e) {
                info($e->getMessage());

                return response()->json($e);
            }
        }

        return response()->json([
            'errors' => [
                ['code' => 'amount', 'message' => translate('messages.insufficient_balance')],
            ],
        ], 403);
    }

    public function addReturnDate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
            'return_date' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $dmId = DeliveryMan::where(['auth_token' => $request['token']])->first()?->id;
        $order = Order::where('id', $request['order_id'])->where('delivery_man_id', $dmId)->with('ParcelCancellation')->first();
        if ($order->ParcelCancellation) {
            $order->ParcelCancellation->return_date = $request['return_date'];
            $order->ParcelCancellation->set_return_date = 1;
            $order->ParcelCancellation->save();

            return response()->json([
                'message' => translate('messages.return_date_added_successfully'),
            ], 200);
        } else {
            return response()->json([
                'errors' => [
                    ['code' => 'amount', 'message' => translate('Order not found')],
                ],
            ], 403);
        }
    }

    public function parcelReturn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
            'order_status' => 'required|in:returned',
            'return_otp' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $dmId = DeliveryMan::where(['auth_token' => $request['token']])->first()?->id;
        $order = Order::where('id', $request['order_id'])->where('delivery_man_id', $dmId)->with('ParcelCancellation')->first();

        $validationCheck = OrderLogic::makeValidationForParcelReturn($request, $order);
        if (data_get($validationCheck, 'status_code') === 403) {

            return response()->json([
                'errors' => [
                    ['code' => data_get($validationCheck, 'code'), 'message' => data_get($validationCheck, 'message')],
                ],
            ], data_get($validationCheck, 'status_code'));
        }

        if (in_array($order->parcelCancellation->cancel_by, ['deliveryman', 'admin_for_deliveryman'])) {
            OrderLogic::deliveryManCancelParcelTransaction($order);
        } else {
            OrderLogic::create_transaction_parcel_cancel($order, $order->payment_status == 'paid' ? 'admin' : 'deliveryman');
        }

        return response()->json(['message' => translate('messages.Parcel_returned_successfully')], 200);
    }

    public function convertLoyaltyPoints(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'points' => 'required|numeric|min:0.001',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        if (Helpers::get_business_settings('dm_loyality_point_status') != 1) {
            return response()->json(['message' => translate('messages.Loyalty_point_is_disabled')], 403);
        } elseif (Helpers::get_business_settings('dm_min_loyality_point_to_convert') > $request->points) {
            return response()->json(['message' => translate('You need to have at least') . ' ' . Helpers::get_business_settings('dm_min_loyality_point_to_convert') . ' ' . translate('points to convert')], 403);
        }

        $dm = DeliveryMan::where(['auth_token' => $request['token']])->first();
        if (!$dm) {
            return response()->json(['message' => translate('Deliveryman not found')], 403);
        } elseif ($dm->loyalty_point < $request->points) {
            return response()->json(['message' => translate('You have insufficent points')], 403);
        }

        $pointHistory = Helpers::deliverymanLoyaltyPointHistory(deliveryManId: $dm->id, amount: $request->points, transactionType: 'converted_to_wallet', pointConversionType: 'debit', reference: null);

        if (data_get($pointHistory, 'status_code') === 403) {

            return response()->json([
                'errors' => [
                    ['code' => data_get($pointHistory, 'code'), 'message' => data_get($pointHistory, 'message')]
                ]
            ], data_get($pointHistory, 'status_code'));
        }

        return response()->json(['message' => translate('messages.Loyalty_point_converted_successfully')], 200);

    }

    /**
     * Toggle active services (delivery and/or taxi)
     * Allows drivers to choose which types of requests they want to receive
     */
    public function toggleServices(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'delivery_active' => 'boolean',
            'taxi_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $dm = DeliveryMan::where(['auth_token' => $request['token']])->first();

        if (!$dm) {
            return response()->json(['message' => translate('Deliveryman not found')], 404);
        }

        // Only allow toggling services the driver is approved for
        if ($request->has('delivery_active')) {
            if (!$dm->can_deliver) {
                return response()->json([
                    'errors' => [['code' => 'service', 'message' => translate('Not approved for delivery service')]]
                ], 403);
            }
            $dm->delivery_active = filter_var($request->delivery_active, FILTER_VALIDATE_BOOLEAN);
        }

        if ($request->has('taxi_active')) {
            if (!$dm->can_drive_taxi) {
                return response()->json([
                    'errors' => [['code' => 'service', 'message' => translate('Not approved for taxi service')]]
                ], 403);
            }
            if (!$dm->taxi_is_verified) {
                return response()->json([
                    'errors' => [['code' => 'service', 'message' => translate('Taxi service not verified yet')]]
                ], 403);
            }
            $dm->taxi_active = filter_var($request->taxi_active, FILTER_VALIDATE_BOOLEAN);
        }

        // At least one service must be active when driver is online
        if ($dm->active && !$dm->delivery_active && !$dm->taxi_active) {
            return response()->json([
                'errors' => [['code' => 'service', 'message' => translate('At least one service must be active when you are online')]]
            ], 403);
        }

        $dm->save();

        return response()->json([
            'message' => translate('Services updated successfully'),
            'services' => [
                'can_deliver' => $dm->can_deliver,
                'can_drive_taxi' => $dm->can_drive_taxi,
                'delivery_active' => $dm->delivery_active,
                'taxi_active' => $dm->taxi_active,
                'taxi_is_verified' => $dm->taxi_is_verified,
            ],
        ], 200);
    }

    /**
     * Get taxi-specific profile and capabilities
     */
    public function getTaxiProfile(Request $request)
    {
        $dm = DeliveryMan::with(['vehicle'])->where(['auth_token' => $request['token']])->first();

        if (!$dm) {
            return response()->json(['message' => translate('Deliveryman not found')], 404);
        }

        return response()->json([
            'can_drive_taxi' => $dm->can_drive_taxi,
            'taxi_active' => $dm->taxi_active,
            'taxi_is_verified' => $dm->taxi_is_verified,
            'taxi_license_number' => $dm->taxi_license_number,
            'taxi_license_expiry' => $dm->taxi_license_expiry,
            'taxi_rating' => $dm->taxi_rating,
            'taxi_total_rides' => $dm->taxi_total_rides,
            'vehicle' => $dm->vehicle ? [
                'id' => $dm->vehicle->id,
                'type' => $dm->vehicle->type,
                'brand' => $dm->vehicle->brand,
                'model' => $dm->vehicle->model,
                'color' => $dm->vehicle->color,
                'license_plate' => $dm->vehicle->license_plate,
                'seats' => $dm->vehicle->seats,
                'can_taxi' => $dm->vehicle->can_taxi,
                'can_delivery' => $dm->vehicle->can_delivery,
            ] : null,
        ], 200);
    }

    public function propose_parcel_price(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $dm = DeliveryMan::where(['auth_token' => $request['token']])->first();
        $order = Order::where('id', $request['order_id'])->where('delivery_man_id', $dm->id)->first();

        if (!$order) {
            return response()->json(['message' => translate('Order not found')], 404);
        }

        $order->proposed_delivery_charge = $request->price;
        if ($order->save()) {
            return response()->json(['message' => translate('Price proposal sent successfully')], 200);
        }

        return response()->json(['message' => translate('Failed to send price proposal')], 400);
    }

    /**
     * Chat del enlace de seguimiento Tootli Directo: mensajes cliente ↔ repartidor (mismo hilo que ve el cliente en la web).
     */
    public function get_tootli_direct_tracking_chat(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $dm = DeliveryMan::where(['auth_token' => $request['token']])->first();
        if (! $dm) {
            return response()->json(['errors' => [['code' => 'auth-001', 'message' => translate('messages.unauthorized')]]], 401);
        }
        $order = Order::withoutGlobalScopes()
            ->where('id', $request->order_id)
            ->where('delivery_man_id', $dm->id)
            ->first();
        if (! $order || ! $order->isTootliDirectTrackable()) {
            return response()->json(['errors' => [['code' => 'order_id', 'message' => trans('messages.order_data_not_found')]]], 404);
        }

        $messages = TootliDirectTrackingChatMessage::query()
            ->where('order_id', $order->id)
            ->whereIn('sender', [
                TootliDirectTrackingChatMessage::SENDER_CUSTOMER,
                TootliDirectTrackingChatMessage::SENDER_DELIVERY_MAN,
            ])
            ->orderBy('id')
            ->limit(200)
            ->get(['id', 'sender', 'body', 'created_at'])
            ->map(fn ($m) => [
                'id' => $m->id,
                'sender' => $m->sender,
                'body' => $m->body,
                'created_at' => $m->created_at?->toIso8601String(),
            ]);

        return response()->json(['messages' => $messages], 200);
    }

    public function post_tootli_direct_tracking_chat(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|integer',
            'message' => 'required|string|max:2000',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $dm = DeliveryMan::where(['auth_token' => $request['token']])->first();
        if (! $dm) {
            return response()->json(['errors' => [['code' => 'auth-001', 'message' => translate('messages.unauthorized')]]], 401);
        }
        $order = Order::withoutGlobalScopes()
            ->where('id', $request->order_id)
            ->where('delivery_man_id', $dm->id)
            ->first();
        if (! $order || ! $order->isTootliDirectTrackable()) {
            return response()->json(['errors' => [['code' => 'order_id', 'message' => trans('messages.order_data_not_found')]]], 404);
        }

        $key = 'td-dm-tracking-chat:'.$dm->id.':'.$order->id;
        if (RateLimiter::tooManyAttempts($key, 30)) {
            return response()->json(['errors' => [['code' => 'rate_limit', 'message' => 'Too many requests']]], 429);
        }
        RateLimiter::hit($key, 60);

        $body = trim(strip_tags((string) $request->message));
        if ($body === '') {
            return response()->json(['errors' => [['code' => 'message', 'message' => 'Mensaje vacío']]], 422);
        }

        TootliDirectTrackingChatMessage::query()->create([
            'order_id' => $order->id,
            'sender' => TootliDirectTrackingChatMessage::SENDER_DELIVERY_MAN,
            'body' => $body,
        ]);

        return response()->json(['message' => 'ok'], 200);
    }

    /**
     * Polilínea por carretera (Mapbox). El token Mapbox solo existe en el servidor (.env).
     */
    public function driving_route(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'origin_lat' => 'required|numeric|between:-90,90',
            'origin_lng' => 'required|numeric|between:-180,180',
            'dest_lat' => 'required|numeric|between:-90,90',
            'dest_lng' => 'required|numeric|between:-180,180',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $oLat = (float) $request->origin_lat;
        $oLng = (float) $request->origin_lng;
        $dLat = (float) $request->dest_lat;
        $dLng = (float) $request->dest_lng;

        /** @var MapboxDirectionsService $mapbox */
        $mapbox = app(MapboxDirectionsService::class);
        $coords = $mapbox->drivingTrafficPolyline($oLng, $oLat, $dLng, $dLat);

        if ($coords === null || $coords === []) {
            return response()->json(['polyline' => []], 200);
        }

        $polyline = [];
        foreach ($coords as $c) {
            $polyline[] = ['latitude' => $c[1], 'longitude' => $c[0]];
        }

        return response()->json(['polyline' => $polyline], 200);
    }

    public function get_orders_count(Request $request)
    {
        $dm = DeliveryMan::where(['auth_token' => $request['token']])->first();
        if (!$dm) {
            return response()->json(['errors' => [['code' => 'auth-001', 'message' => translate('messages.unauthorized')]]], 401);
        }

        $count = Order::where(['delivery_man_id' => $dm['id']])
            ->whereIn('order_status', ['accepted', 'confirmed', 'pending', 'processing', 'picked_up', 'handover'])
            ->dmOrder()
            ->count();

        return response()->json([['key' => $request->type, 'count' => $count]], 200);
    }
}

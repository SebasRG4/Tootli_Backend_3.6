<?php

namespace App\Http\Controllers\Admin\DeliveryMan;

use App\Exports\DeliveryManReferralEarningExport;
use App\Models\DeliverymanReferralHistory;
use Carbon\Carbon;
use Exception;
use App\Models\Order;
use Illuminate\View\View;
use App\Mail\DmSuspendMail;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Mail\DmSelfRegistration;
use App\Traits\NotificationTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Models\DisbursementDetails;
use App\Services\DeliveryManService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\RedirectResponse;
use App\Exports\DeliveryManListExport;
use App\Exports\DeliveryManReviewExport;
use App\Http\Controllers\BaseController;
use App\Exports\DeliveryManEarningExport;
use App\Exports\DisbursementHistoryExport;
use Illuminate\Database\Eloquent\Collection;
use App\Exports\SingleDeliveryManReviewExport;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Enums\ExportFileNames\Admin\DeliveryMan;
use App\Http\Requests\Admin\DeliveryManAddRequest;
use App\Http\Requests\Admin\DeliveryManUpdateRequest;
use App\Contracts\Repositories\ZoneRepositoryInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Contracts\Repositories\MessageRepositoryInterface;
use App\Contracts\Repositories\DmReviewRepositoryInterface;
use App\Contracts\Repositories\UserInfoRepositoryInterface;
use App\Contracts\Repositories\DeliveryManRepositoryInterface;
use App\Contracts\Repositories\TranslationRepositoryInterface;
use App\Contracts\Repositories\ConversationRepositoryInterface;
use App\Enums\ViewPaths\Admin\DeliveryMan as DeliveryManViewPath;
use App\Contracts\Repositories\OrderTransactionRepositoryInterface;
use App\Contracts\Repositories\UserNotificationRepositoryInterface;
use App\Exports\DeliveryManWithdrawTransactionExport;
use App\Exports\SingleDeliveryManLoyaltyPointExport;
use App\Mail\WithdrawRequestMail;
use App\Models\DeliveryIncidentType;
use App\Models\DmTierLimit;
use App\Models\DeliveryManAdminAuditLog;
use App\Models\DeliverymanLoyaltyPointHistory;
use App\Models\DeliveryManWallet;
use App\Models\WithdrawRequest;
use App\Models\DeliveryMan as DeliveryManModel;
use App\Services\DeliveryStrike\DeliveryStrikeService;
use App\Services\DeliveryStrike\RecordDeliveryManStrikeAction;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class DeliveryManController extends BaseController
{
    use NotificationTrait;
    public function __construct(
        protected DeliveryManRepositoryInterface $deliveryManRepo,
        protected ZoneRepositoryInterface $zoneRepo,
        protected TranslationRepositoryInterface $translationRepo,
        protected DmReviewRepositoryInterface $dmReviewRepo,
        protected UserInfoRepositoryInterface $userInfoRepo,
        protected ConversationRepositoryInterface $conversationRepo,
        protected MessageRepositoryInterface $messageRepo,
        protected DeliveryManService $deliveryManService,
        protected OrderTransactionRepositoryInterface $orderTransactionRepo,
    ) {
    }

    public function index(?Request $request): View|Collection|LengthAwarePaginator|null
    {
        return $this->getListView($request);
    }
    private function getListView(Request $request): View
    {
        $zoneId = $request->query('zone_id', 'all');
        $deliveryMen = $this->deliveryManRepo->getFilterWiseListWhere(
            zoneId: $zoneId,
            searchValue: $request['search'],
            filters: ['type' => 'zone_wise', 'application_status' => 'approved'],
            additionalFilter: $request['filter'],
            jobType: $request['job_type'],
            relations: ['zone', 'wallet'],
            dataLimit: config('default_pagination')
        );
        $zone = is_numeric($zoneId) ? $this->zoneRepo->getFirstWhere(params: ['id' => $zoneId]) : null;
        return view(DeliveryManViewPath::LIST [VIEW], compact('deliveryMen', 'zone'));
    }

    public function getAddView(): View
    {
        $language = getWebConfig('language');
        $defaultLang = str_replace('_', '-', app()->getLocale());
        return view(DeliveryManViewPath::ADD[VIEW], compact('language', 'defaultLang'));
    }

    public function getNewDeliveryManView(Request $request): View
    {
        $searchBy = $request->query('search_by');
        $zoneId = $request->query('zone_id', 'all');
        $deliveryMen = $this->deliveryManRepo->getZoneWiseListWhere(
            zoneId: $zoneId,
            searchValue: $searchBy,
            filters: ['type' => 'zone_wise', 'application_status' => 'pending'],
            relations: ['zone'],
            dataLimit: config('default_pagination')
        );
        $zone = is_numeric($zoneId) ? $this->zoneRepo->getFirstWhere(params: ['id' => $zoneId]) : null;
        return view(DeliveryManViewPath::NEW [VIEW], compact('deliveryMen', 'zone', 'searchBy'));
    }

    public function getDeniedDeliveryManView(Request $request): View
    {
        $searchBy = $request->query('search_by');
        $zoneId = $request->query('zone_id', 'all');
        $deliveryMen = $this->deliveryManRepo->getZoneWiseListWhere(
            zoneId: $zoneId,
            searchValue: $searchBy,
            filters: ['type' => 'zone_wise', 'application_status' => 'denied'],
            relations: ['zone'],
            dataLimit: config('default_pagination')
        );
        $zone = is_numeric($zoneId) ? $this->zoneRepo->getFirstWhere(params: ['id' => $zoneId]) : null;
        return view(DeliveryManViewPath::DENY[VIEW], compact('deliveryMen', 'zone', 'searchBy'));
    }

    public function getSearchList(Request $request): JsonResponse
    {
        $deliveryMen = $this->deliveryManRepo->getListWhere(
            searchValue: $request['search'],
            filters: ['type' => 'zone_wise', 'application_status' => 'approved'],
        );
        return response()->json([
            'view' => view(DeliveryManViewPath::SEARCH[VIEW], compact('deliveryMen'))->render(),
            'count' => $deliveryMen->count()
        ]);
    }

    public function getActiveSearchList(Request $request): JsonResponse
    {
        $deliveryMen = $this->deliveryManRepo->getFilterWiseListWhere(
            searchValue: $request['search'],
            filters: ['type' => 'zone_wise', 'status' => 1],
        );
        return response()->json([
            'dm' => $deliveryMen
        ]);
    }

    public function add(DeliveryManAddRequest $request): JsonResponse
    {
        $this->deliveryManRepo->add(data: $this->deliveryManService->getAddData(request: $request));
        Toastr::success(translate('messages.deliveryman_added_successfully'));
        return response()->json([
            'message' => translate('messages.deliveryman_added_successfully'),
            'redirect' => route('admin.users.delivery-man.list')
        ], 200);
    }

    public function getUpdateView(string|int $id): View
    {
        $deliveryMan = $this->deliveryManRepo->getFirstWithoutGlobalScopeWhere(params: ['id' => $id]);
        $language = getWebConfig('language');
        $defaultLang = str_replace('_', '-', app()->getLocale());
        return view(DeliveryManViewPath::UPDATE[VIEW], compact('deliveryMan', 'language', 'defaultLang'));
    }

    public function update(DeliveryManUpdateRequest $request, $id): JsonResponse
    {
        $deliveryMan = $this->deliveryManRepo->getFirstWhere(params: ['id' => $id]);

        $deliveryMan = $this->deliveryManRepo->update(id: $id, data: $this->deliveryManService->getUpdateData(request: $request, deliveryMan: $deliveryMan));
        if ($deliveryMan->userinfo) {
            $this->userInfoRepo->update(id: $deliveryMan->userinfo->id, data: [
                'f_name' => $deliveryMan->f_name,
                'l_name' => $deliveryMan->l_name,
                'email' => $deliveryMan->email,
                'image' => $deliveryMan->image,
            ]);
        }

        Toastr::success(translate('messages.deliveryman_updated_successfully'));
        return response()->json([
            'message' => translate('messages.deliveryman_updated_successfully'),
            'redirect' => route('admin.users.delivery-man.list')
        ], 200);
    }

    public function delete(Request $request): RedirectResponse
    {
        $this->deliveryManRepo->delete(id: $request['id']);
        Toastr::success(translate('messages.deliveryman_deleted_successfully'));
        return back();
    }

    public function updateStatus(Request $request, UserNotificationRepositoryInterface $notificationRepo): RedirectResponse
    {
        $dmBefore = $this->deliveryManRepo->getFirstWhere(params: ['id' => $request['id']]);
        $deliveryMan = $this->deliveryManRepo->update(id: $request['id'], data: ['status' => $request['status']]);

        DeliveryManAdminAuditLog::log(
            deliveryManId: (int) $deliveryMan->id,
            action: (int) $request['status'] === 0
                ? DeliveryManAdminAuditLog::ACTION_DM_SUSPEND
                : DeliveryManAdminAuditLog::ACTION_DM_UNSUSPEND,
            adminId: auth('admin')->id(),
            meta: [
                'previous_status' => (bool) $dmBefore->status,
                'new_status' => (bool) (int) $request['status'],
                'ip' => $request->ip(),
            ],
        );


        if ($request['status'] == 0) {
            $deliveryMan->auth_token = null;

            if (isset($deliveryMan->fcm_token) && Helpers::getNotificationStatusData('deliveryman', 'deliveryman_account_block', 'push_notification_status')) {
                $data = [
                    'title' => translate('messages.suspended'),
                    'description' => translate('messages.your_account_has_been_suspended'),
                    'order_id' => '',
                    'image' => '',
                    'type' => 'block'
                ];
                $this->sendPushNotificationToDevice($deliveryMan->fcm_token, $data);

                $notificationRepo->add([
                    'data' => json_encode($data),
                    'delivery_man_id' => $deliveryMan->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } else {
                Toastr::warning(translate('messages.push_notification_failed'));
            }
        } else {
            if (Helpers::getNotificationStatusData('deliveryman', 'deliveryman_account_unblock', 'push_notification_status') && isset($deliveryMan->fcm_token)) {
                $data = [
                    'title' => translate('messages.Account_activation'),
                    'description' => translate('messages.your_account_has_been_activated'),
                    'order_id' => '',
                    'image' => '',
                    'type' => 'unblock'
                ];
                Helpers::send_push_notif_to_device($deliveryMan->fcm_token, $data);

                DB::table('user_notifications')->insert([
                    'data' => json_encode($data),
                    'delivery_man_id' => $deliveryMan->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
        try {
            if (config('mail.status') && getWebConfigStatus('suspend_mail_status_dm') == '1' && $request['status'] == 0 && Helpers::getNotificationStatusData('deliveryman', 'deliveryman_account_block', 'mail_status')) {
                Mail::to($deliveryMan['email'])->send(new DmSuspendMail('suspend', $deliveryMan['f_name']));
            } elseif (config('mail.status') && getWebConfigStatus('unsuspend_mail_status_dm') == '1' && $request['status'] != 0 && Helpers::getNotificationStatusData('deliveryman', 'deliveryman_account_unblock', 'mail_status')) {
                Mail::to($deliveryMan['email'])->send(new DmSuspendMail('unsuspend', $deliveryMan['f_name']));
            }
        } catch (Exception) {
            Toastr::warning(translate('messages.failed_to_send_mail'));
        }

        Toastr::success(translate('messages.deliveryman_status_updated'));
        return back();
    }

    public function updateTier(Request $request, int|string $id): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'dm_tier' => 'required|in:new,standard,pro,restricted',
            'dm_tier_source' => 'required|in:auto,manual',
            'dm_tier_reason' => 'nullable|string|max:2000',
        ]);
        if ($validator->fails()) {
            Toastr::error($validator->errors()->first());

            return back();
        }

        $this->deliveryManRepo->update(id: $id, data: [
            'dm_tier' => $request['dm_tier'],
            'dm_tier_source' => $request['dm_tier_source'],
            'dm_tier_reason' => $request['dm_tier_reason'],
            'dm_tier_updated_at' => now(),
        ]);

        $dm = $this->deliveryManRepo->getFirstWhere(params: ['id' => $id]);
        DeliveryManAdminAuditLog::log(
            deliveryManId: (int) $dm->id,
            action: DeliveryManAdminAuditLog::ACTION_DM_TIER_MANUAL,
            adminId: auth('admin')->id(),
            meta: [
                'dm_tier' => $request['dm_tier'],
                'dm_tier_source' => $request['dm_tier_source'],
                'ip' => $request->ip(),
            ],
            note: $request['dm_tier_reason'],
        );

        DmTierLimit::forgetCache();

        Toastr::success(translate('messages.updated_successfully'));
        return back();
    }

    public function storeStrikeEvent(Request $request, int|string $id): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'delivery_incident_type_id' => 'required|exists:delivery_incident_types,id',
            'order_id' => 'nullable|exists:orders,id',
            'notes' => 'nullable|string|max:2000',
            'delivery_suspended_until' => 'nullable|date',
        ]);
        if ($validator->fails()) {
            Toastr::error($validator->errors()->first());

            return back();
        }

        $dm = $this->deliveryManRepo->getFirstWhere(params: ['type' => 'zone_wise', 'id' => $id]);
        if (! $dm) {
            Toastr::error(translate('messages.not_found'));

            return back();
        }

        try {
            app(RecordDeliveryManStrikeAction::class)->run(
                (int) $dm->id,
                (int) $request['delivery_incident_type_id'],
                $request->filled('order_id') ? (int) $request['order_id'] : null,
                $request['notes'],
                auth('admin')->id(),
                $request->filled('delivery_suspended_until') ? (string) $request['delivery_suspended_until'] : null,
            );
        } catch (\InvalidArgumentException) {
            Toastr::error(translate('messages.dm_strike_order_not_for_dm'));

            return back();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            Toastr::error(translate('messages.dm_strike_type_invalid'));

            return back();
        }

        Toastr::success(translate('messages.updated_successfully'));

        return back();
    }

    public function updateStrikeSuspension(Request $request, int|string $id): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'delivery_suspended_until' => 'nullable|date',
        ]);
        if ($validator->fails()) {
            Toastr::error($validator->errors()->first());

            return back();
        }

        $dm = $this->deliveryManRepo->getFirstWhere(params: ['type' => 'zone_wise', 'id' => $id]);
        if (! $dm) {
            Toastr::error(translate('messages.not_found'));

            return back();
        }

        $until = $request->filled('delivery_suspended_until') ? $request['delivery_suspended_until'] : null;
        $this->deliveryManRepo->update(id: $id, data: [
            'delivery_suspended_until' => $until,
        ]);

        DeliveryManAdminAuditLog::log(
            deliveryManId: (int) $dm->id,
            action: DeliveryManAdminAuditLog::ACTION_DM_STRIKE_SUSPENSION_SET,
            adminId: auth('admin')->id(),
            meta: [
                'delivery_suspended_until' => $until,
                'ip' => $request->ip(),
            ],
        );

        Toastr::success(translate('messages.updated_successfully'));

        return back();
    }

    public function updateEarning(Request $request): RedirectResponse
    {
        $this->deliveryManRepo->update(id: $request['id'], data: ['earning' => $request['status']]);
        Toastr::success(translate('messages.deliveryman_type_updated'));
        return back();
    }

    public function exportList(Request $request): BinaryFileResponse
    {
        $zoneId = $request->query('zone_id', 'all');
        $deliveryMen = $this->deliveryManRepo->getZoneWiseListWhere(
            zoneId: $zoneId,
            searchValue: $request['search'],
            filters: ['type' => 'zone_wise', 'application_status' => 'approved'],
            relations: ['zone']
        );
        $zone = is_numeric($zoneId) ? $this->zoneRepo->getFirstWhere(params: ['id' => $zoneId]) : null;

        $data = [
            'delivery_men' => $deliveryMen,
            'search' => $request->search ?? null,
            'zone' => is_numeric($zoneId) ? $zone['name'] : null,
        ];

        if ($request['type'] == 'excel') {
            return Excel::download(new DeliveryManListExport($data), DeliveryMan::EXPORT_XLSX);
        }
        return Excel::download(new DeliveryManListExport($data), DeliveryMan::EXPORT_CSV);
    }

    public function getReviewListView(Request $request): View
    {
        $filter = $request['deliveryman_id'] && is_numeric($request['deliveryman_id']) ? ['delivery_man_id' => $request['deliveryman_id']] : [];
        $orderBy = $request['order_by'] && isset($request['order_by']) && in_array($request['order_by'], ['asc', 'desc']) ? ['col' => 'rating', 'type' => $request['order_by']] : [];
        $reviews = $this->dmReviewRepo->getListWhereOrder(
            searchValue: $request['search'],
            filters: $filter,
            relations: ['delivery_man', 'customer', 'order'],
            dataLimit: config('default_pagination'),
            orderBy: $orderBy
        );

        return view(DeliveryManViewPath::REVIEW_LIST[VIEW], compact('reviews'));
    }

    public function getReviewSearchList(Request $request): JsonResponse
    {
        $reviews = $this->dmReviewRepo->getListWhere(searchValue: $request['search'], relations: ['delivery_man', 'customer']);

        return response()->json([
            'view' => view(DeliveryManViewPath::REVIEW_SEARCH_LIST[VIEW], compact('reviews'))->render(),
            'count' => $reviews->count()
        ]);
    }

    public function getAllReviewExportList(Request $request): BinaryFileResponse
    {
        $reviews = $this->dmReviewRepo->getListWhere(searchValue: $request['search'], relations: ['delivery_man', 'customer']);
        $data = [
            'reviews' => $reviews,
            'search' => $request->search ?? null,
        ];

        if ($request['type'] == 'excel') {
            return Excel::download(new DeliveryManReviewExport($data), DeliveryMan::REVIEW_EXPORT_XLSX);
        }
        return Excel::download(new DeliveryManReviewExport($data), DeliveryMan::EXPORT_CSV);

    }

    public function updateReviewStatus(Request $request): RedirectResponse
    {
        $this->dmReviewRepo->update(id: $request['id'], data: ['status' => $request['status']]);
        Toastr::success(translate('messages.review_visibility_updated'));
        return back();
    }

    public function getReviewExportList(Request $request): BinaryFileResponse
    {
        $deliveryMan = $this->deliveryManRepo->getFirstWhere(params: ['type' => 'zone_wise', 'id' => $request['id']], relations: ['reviews']);
        $reviews = $this->dmReviewRepo->getListWhere(searchValue: $request['search'], filters: ['delivery_man_id' => $request['id']]);

        $data = [
            'dm' => $deliveryMan,
            'reviews' => $reviews,
            'search' => $request->search ?? null,
        ];

        if ($request['type'] == 'excel') {
            return Excel::download(new SingleDeliveryManReviewExport($data), DeliveryMan::REVIEW_EXPORT_XLSX);
        }
        return Excel::download(new SingleDeliveryManReviewExport($data), DeliveryMan::EXPORT_CSV);

    }
    public function getLoyaltyPointExportList(Request $request): BinaryFileResponse
    {
        $date = $request->query('dates');

        $deliveryMan = $this->deliveryManRepo->getFirstWhere(params: ['type' => 'zone_wise', 'id' => $request['id']], relations: ['reviews']);
        $loyaltyPointHistory = $this->getLoyaltyHistoryList($request, $deliveryMan, $date)->get();

        $data = [
            'dm' => $deliveryMan,
            'histories' => $loyaltyPointHistory,
            'search' => $request->search ?? null,
        ];

        if ($request['type'] == 'excel') {
            return Excel::download(new SingleDeliveryManLoyaltyPointExport($data), DeliveryMan::LOYALTY_POINT_EXPORT_XLSX);
        }
        return Excel::download(new SingleDeliveryManLoyaltyPointExport($data), DeliveryMan::LOYALTY_POINT_EXPORT_CSV);

    }
    public function getReferralEarnExportList(Request $request): BinaryFileResponse
    {
        $date = $request->query('dates');

        $deliveryMan = $this->deliveryManRepo->getFirstWhere(params: ['type' => 'zone_wise', 'id' => $request['id']], relations: ['reviews']);
        $referralEarnHistory = $this->getReferralHistoryList($request, $deliveryMan, $date)->get();

        $data = [
            'dm' => $deliveryMan,
            'histories' => $referralEarnHistory,
            'search' => $request->search ?? null,
        ];

        if ($request['type'] == 'excel') {
            return Excel::download(new DeliveryManReferralEarningExport($data), DeliveryMan::REFERRAL_EARN_EXPORT_XLSX);
        }
        return Excel::download(new DeliveryManReferralEarningExport($data), DeliveryMan::REFERRAL_EARN_EXPORT_CSV);

    }

    public function getPreview(Request $request, int|string $id, string $tab = 'info'): View
    {
        $deliveryMan = $this->deliveryManRepo->getFirstWhere(params: ['type' => 'zone_wise', 'id' => $id], relations: ['reviews']);
        $strikeIncidentTypes = collect();
        if ($tab == 'info') {
            $reviews = $this->dmReviewRepo->getListWhere(searchValue: $request['search'], filters: ['delivery_man_id' => $id], dataLimit: config('default_pagination'));
            $deliveryMan->loadMissing(['wallet', 'zone']);
            $dmAssignmentSnapshot = null;
            $dmAdminAuditLogs = collect();
            if ($deliveryMan->application_status === 'approved') {
                $maxCashRaw = Helpers::get_business_settings('dm_max_cash_in_hand', false);
                $tierKey = strtolower((string) ($deliveryMan->dm_tier ?? 'standard'));
                $tierLabels = [
                    'new' => translate('messages.dm_tier_label_new'),
                    'standard' => translate('messages.dm_tier_label_standard'),
                    'pro' => translate('messages.dm_tier_label_pro'),
                    'restricted' => translate('messages.dm_tier_label_restricted'),
                ];
                $tierLimit = DmTierLimit::forTier($tierKey);
                $globalMaxOrders = (int) config('dm_maximum_orders', 1);
                $tierMaxOrders = (int) ($tierLimit->max_concurrent_orders ?? 1);
                $globalCash = (float) ($maxCashRaw ?? 0);
                $tierCash = (float) ($tierLimit->max_cash_cod ?? 0);
                $cashEffective = ($globalCash > 0 && $tierCash > 0)
                    ? min($globalCash, $tierCash)
                    : ($tierCash > 0 ? $tierCash : $globalCash);
                $tlAttrs = $tierLimit->getAttributes();
                $maxOrderCod = (array_key_exists('max_order_value_cod', $tlAttrs) && $tlAttrs['max_order_value_cod'] !== null)
                    ? (float) $tlAttrs['max_order_value_cod']
                    : null;
                $dmAssignmentSnapshot = [
                    'tier' => $tierLabels[$tierKey] ?? translate('messages.dm_tier_label_standard'),
                    'tier_key' => $tierKey,
                    'tier_source' => (string) ($deliveryMan->dm_tier_source ?? 'auto'),
                    'tier_reason' => $deliveryMan->dm_tier_reason,
                    'max_concurrent_effective' => max(1, min($globalMaxOrders, $tierMaxOrders)),
                    'max_concurrent_global' => $globalMaxOrders,
                    'max_concurrent_tier' => $tierMaxOrders,
                    'current_orders' => (int) $deliveryMan->current_orders,
                    'collected_cash' => (float) ($deliveryMan->wallet?->collected_cash ?? 0),
                    'max_cash_effective' => $cashEffective,
                    'max_cash_global' => $globalCash,
                    'max_cash_tier' => $tierCash,
                    'max_order_value_cod' => $maxOrderCod,
                    'account_suspended' => ! (bool) $deliveryMan->status,
                ];
                try {
                    $strikeSvc = app(DeliveryStrikeService::class);
                    $dmAssignmentSnapshot['strike_rolling_weight'] = $strikeSvc->rollingStrikeWeight($deliveryMan);
                    $dmAssignmentSnapshot['strike_threshold'] = (int) config('dm_strikes.block_weight_threshold', 12);
                    $dmAssignmentSnapshot['strike_window_days'] = (int) config('dm_strikes.rolling_window_days', 90);
                    $dmAssignmentSnapshot['strike_assignment_blocked'] = $strikeSvc->blocksNewAssignments($deliveryMan);
                    $dmAssignmentSnapshot['delivery_suspended_until_display'] = $deliveryMan->delivery_suspended_until;
                    $strikeIncidentTypes = DeliveryIncidentType::query()
                        ->where('active', true)
                        ->orderBy('sort_order')
                        ->orderBy('id')
                        ->get();
                } catch (\Throwable) {
                }
                $dmAdminAuditLogs = DeliveryManAdminAuditLog::query()
                    ->with('admin')
                    ->where('delivery_man_id', $deliveryMan->id)
                    ->latest()
                    ->limit(25)
                    ->get();
            }

            return view(DeliveryManViewPath::INFO[VIEW], compact('deliveryMan', 'reviews', 'dmAssignmentSnapshot', 'dmAdminAuditLogs', 'strikeIncidentTypes'));
        } else if ($tab == 'transaction') {
            $date = $request->query('dates');
            if ($request->has('date_range') && $request->date_range != 'custom') {
                if ($request->date_range == 'this_week') {
                    $date = now()->startOfWeek()->format('Y-m-d') . ' - ' . now()->endOfWeek()->format('Y-m-d');
                } elseif ($request->date_range == 'this_month') {
                    $date = now()->startOfMonth()->format('Y-m-d') . ' - ' . now()->endOfMonth()->format('Y-m-d');
                } elseif ($request->date_range == 'this_year') {
                    $date = now()->startOfYear()->format('Y-m-d') . ' - ' . now()->endOfYear()->format('Y-m-d');
                } elseif ($request->date_range == 'all_time') {
                    $date = null;
                }
            }
            $digital_transaction = $this->orderTransactionRepo->getListWhere(searchValue: $request['search'], filters: ['delivery_man_id' => $id], dataLimit: config('default_pagination'), orderBy: ['col' => 'created_at', 'type' => 'desc'], date: $date);
            return view(DeliveryManViewPath::TRANSACTION[VIEW], compact('deliveryMan', 'date', 'digital_transaction'));
        } else if ($tab == 'order_list') {
            $order_lists = Order::where('delivery_man_id', $deliveryMan->id)->paginate(config('default_pagination'));
            return view(DeliveryManViewPath::ORDER_LIST[VIEW], compact('deliveryMan', 'order_lists'));
        } else if ($tab == 'loyalty-point') {
            $date = $request->query('dates');

            $points = DeliverymanLoyaltyPointHistory::where('delivery_man_id', $deliveryMan->id)
                ->selectRaw('
                    SUM(CASE WHEN point_conversion_type = "credit" THEN point ELSE 0 END) AS total_loyalty_point,
                    SUM(CASE WHEN point_conversion_type = "debit" THEN point ELSE 0 END) AS total_converted_loyalty_point
                ')
                ->first();


            $total_loyalty_point = $points->total_loyalty_point ?? 0;
            $total_converted_loyalty_point = $points->total_converted_loyalty_point ?? 0;
            $loyalty_points = $this->getLoyaltyHistoryList($request, $deliveryMan, $date)->paginate(config('default_pagination'));

            return view('admin-views.delivery-man.view.loyalty-point', compact('deliveryMan', 'date', 'loyalty_points', 'total_loyalty_point', 'total_converted_loyalty_point'));
        } else if ($tab == 'referal-earn') {
            $date = $request->query('dates');

            $stats = DeliverymanReferralHistory::where('delivery_man_id', $deliveryMan->id)
                ->selectRaw("
                    SUM(CASE WHEN refer_type = 'referral' THEN 1 ELSE 0 END) as total_referred,
                    COALESCE(SUM(amount), 0) as total_referral_earning
                ")
                ->first();

            $totalReferred = max($stats?->total_referred, 0) ?? 0;
            $totalReferralEarning = max($stats?->total_referral_earning, 0) ?? 0;
            $referralEarnings = $this->getReferralHistoryList($request, $deliveryMan, $date)->paginate(config('default_pagination'));

            return view('admin-views.delivery-man.view.referral-earn', compact('deliveryMan', 'date', 'totalReferred', 'totalReferralEarning', 'referralEarnings'));
        } else if ($tab == 'disbursement') {
            $key = explode(' ', $request['search']);
            $disbursements = DisbursementDetails::where('delivery_man_id', $deliveryMan->id)
                ->when(isset($key), function ($q) use ($key) {
                    $q->where(function ($q) use ($key) {
                        foreach ($key as $value) {
                            $q->orWhere('disbursement_id', 'like', "%{$value}%")
                                ->orWhere('status', 'like', "%{$value}%");
                        }
                    });
                })
                ->latest()->paginate(config('default_pagination'));
            return view('admin-views.delivery-man.view.disbursement', compact('deliveryMan', 'disbursements'));
        }

        $user = $this->userInfoRepo->getFirstWhere(params: ['deliveryman_id' => $id]);
        if ($user) {
            $conversations = $this->conversationRepo->getListWithScope(relations: ['sender', 'receiver', 'last_message'], dataLimit: 8, scopes: ['WhereUser' => [$user['id']]], conversation_with: $request?->conversation_with ?? 'customer');
        } else {
            $conversations = [];
        }

        return view(DeliveryManViewPath::CONVERSATION[VIEW], compact('conversations', 'deliveryMan'));

    }
    private function getLoyaltyHistoryList($request, $deliveryMan, $date)
    {
        $key = explode(' ', $request['search']);

        $start = null;
        $end = null;
        if (strpos($date, ' - ') !== false) {
            $dates = explode(' - ', $date);
            $start = Carbon::parse($dates[0]);
            $end = Carbon::parse($dates[1]);
        }
        $loyalty_points = DeliverymanLoyaltyPointHistory::where('delivery_man_id', $deliveryMan->id)
            ->when(isset($key), function ($q) use ($key) {
                $q->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('transaction_id', 'like', "%{$value}%")
                            ->orWhere('transaction_type', 'like', "%{$value}%");
                    }
                });
            })
            ->when($request->point_conversion_type, function ($q) use ($request) {
                return $q->where('point_conversion_type', $request->point_conversion_type);
            })
            ->applyDateFilter($request->date_range, $start, $end)
            ->latest();

        return $loyalty_points;
    }
    private function getReferralHistoryList($request, $deliveryMan, $date)
    {
        $key = explode(' ', $request['search']);

        $start = null;
        $end = null;
        if (strpos($date, ' - ') !== false) {
            $dates = explode(' - ', $date);
            $start = Carbon::parse($dates[0]);
            $end = Carbon::parse($dates[1]);
        }
        $loyalty_points = DeliverymanReferralHistory::where('delivery_man_id', $deliveryMan->id)
            ->when(isset($key), function ($q) use ($key) {
                $q->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('transaction_id', 'like', "%{$value}%")
                            ->orWhere('refer_type', 'like', "%{$value}%");
                    }
                });
            })
            ->applyDateFilter($request->date_range, $start, $end)
            ->latest();

        return $loyalty_points;
    }

    public function getEarningListExport(Request $request, OrderTransactionRepositoryInterface $orderTransactionRepo): BinaryFileResponse
    {
        $deliveryMan = $this->deliveryManRepo->getFirstWhere(params: ['type' => 'zone_wise', 'id' => $request['id']], relations: ['reviews']);
        $earnings = $orderTransactionRepo->getDmEarningList(request: $request);

        $data = [
            'dm' => $deliveryMan,
            'earnings' => $earnings,
            'date' => $request->date ?? null,
        ];

        if ($request['type'] == 'excel') {
            return Excel::download(new DeliveryManEarningExport($data), 'DeliveryManEarnings.xlsx');
        }
        return Excel::download(new DeliveryManEarningExport($data), 'DeliveryManEarnings.csv');

    }

    public function getDropdownList(Request $request): JsonResponse
    {
        $data = $this->deliveryManRepo->getDropdownList(request: $request);
        return response()->json($data);
    }

    public function getAccountData(Request $request): JsonResponse
    {
        $deliveryMan = $this->deliveryManRepo->getFirstWhere(params: ['id' => $request['id']]);
        $wallet = $deliveryMan['wallet'];
        $cashInHand = 0;
        $balance = 0;

        if ($wallet) {
            $cashInHand = $wallet->collected_cash;
            $balance = round($wallet->total_earning - $wallet->total_withdrawn - $wallet->pending_withdraw, config('round_up_to_digit'));
        }
        return response()->json(['cash_in_hand' => $cashInHand, 'earning_balance' => $balance]);

    }

    public function getConversationList(Request $request): JsonResponse
    {
        // dd($request->all());
        $user = $this->userInfoRepo->getFirstWhere(params: ['deliveryman_id' => $request['user_id']]);
        $deliveryMan = $this->deliveryManRepo->getFirstWhere(params: ['id' => $request['user_id']]);
        if ($user) {
            $conversations = $this->conversationRepo->getDmConversationList(request: $request, dataLimit: 8, user: $user->id);
        } else {
            $conversations = [];
        }
        $view = view(DeliveryManViewPath::CONVERSATION_LIST[VIEW], compact('conversations', 'deliveryMan'))->render();

        return response()->json(['html' => $view]);

    }

    public function getConversationView($conversation_id, $user_id): JsonResponse
    {
        $conversations = $this->messageRepo->getListWhere(filters: ['conversation_id' => $conversation_id]);
        $conversation = $this->conversationRepo->getFirstWhere(params: ['id' => $conversation_id], relations: ['receiver', 'sender']);
        $receiver = $conversation['receiver'];
        $user = $this->userInfoRepo->getFirstWhere(params: ['id' => $user_id]);
        return response()->json([
            'view' => view(DeliveryManViewPath::CONVERSATIONS[VIEW], compact('conversations', 'user', 'receiver'))->render()
        ]);
    }

    public function updateApplication(Request $request, UserNotificationRepositoryInterface $notificationRepo): RedirectResponse
    {
        $deliveryMan = $this->deliveryManRepo->update(id: $request['id'], data: ['application_status' => $request['status']]);
        if ($request['status'] == 'approved')
            $this->deliveryManRepo->update(id: $request['id'], data: ['status' => 1]);
        try {
            if ($request['status'] == 'approved') {

                $mail_status = getWebConfigStatus('approve_mail_status_dm');
                if (config('mail.status') && $mail_status == '1' && Helpers::getNotificationStatusData('deliveryman', 'deliveryman_registration_approval', 'mail_status')) {
                    Mail::to($deliveryMan->email)->send(new DmSelfRegistration('approved', $deliveryMan->f_name . ' ' . $deliveryMan->l_name));
                }
            } else {

                $mail_status = getWebConfigStatus('deny_mail_status_dm');
                if (config('mail.status') && $mail_status == '1' && Helpers::getNotificationStatusData('deliveryman', 'deliveryman_registration_deny', 'mail_status')) {
                    Mail::to($deliveryMan->email)->send(new DmSelfRegistration('denied', $deliveryMan->f_name . ' ' . $deliveryMan->l_name));
                }
            }
        } catch (Exception $ex) {
            info($ex->getMessage());
        }

        $dmForPush = DeliveryManModel::find($request['id']);
        if ($dmForPush) {
            if ($request['status'] == 'approved') {
                $this->notifyDeliverymanRegistrationPush($dmForPush, 'deliveryman_registration_approval', [
                    'title' => translate('messages.dm_push_registration_approved_title'),
                    'description' => translate('messages.dm_push_registration_approved_body'),
                    'order_id' => '',
                    'image' => '',
                    'type' => 'dm_registration_approved',
                ], $notificationRepo);
            } elseif ($request['status'] == 'denied') {
                $this->notifyDeliverymanRegistrationPush($dmForPush, 'deliveryman_registration_deny', [
                    'title' => translate('messages.dm_push_registration_denied_title'),
                    'description' => translate('messages.dm_push_registration_denied_body'),
                    'order_id' => '',
                    'image' => '',
                    'type' => 'dm_registration_denied',
                ], $notificationRepo);
            }
        }

        Toastr::success(translate('messages.application_status_updated_successfully'));
        return back();
    }

    public function requestRegistrationRevision(Request $request, int|string $id, UserNotificationRepositoryInterface $notificationRepo): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'revision_message' => 'required|string|max:2000',
        ]);
        if ($validator->fails()) {
            Toastr::error(translate('messages.failed_to_update'));

            return back();
        }

        if (! DeliveryManModel::where('id', $id)->where('application_status', 'pending')->exists()) {
            Toastr::error(translate('messages.failed_to_update'));

            return back();
        }

        $this->deliveryManRepo->update(id: (string) $id, data: [
            'registration_revision_message' => $request->input('revision_message'),
            'registration_revision_allowed' => true,
            'registration_revision_requested_at' => now(),
        ]);

        $dm = DeliveryManModel::find($id);
        if ($dm) {
            $revisionText = (string) $request->input('revision_message');
            $this->notifyDeliverymanRegistrationPush($dm, 'deliveryman_registration_revision_request', [
                'title' => translate('messages.dm_push_registration_revision_title'),
                'description' => Str::limit($revisionText, 200),
                'order_id' => '',
                'image' => '',
                'type' => 'dm_registration_revision',
            ], $notificationRepo);
        }

        Toastr::success(translate('messages.registration_revision_requested'));

        return back();
    }

    public function disbursement_export(Request $request, $id, $type)
    {
        $key = explode(' ', $request['search']);

        $dm = \App\Models\DeliveryMan::find($id);
        $disbursements = DisbursementDetails::where('delivery_man_id', $dm->id)
            ->when(isset($key), function ($q) use ($key) {
                $q->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('disbursement_id', 'like', "%{$value}%")
                            ->orWhere('status', 'like', "%{$value}%");
                    }
                });
            })
            ->latest()->get();
        $data = [
            'disbursements' => $disbursements,
            'search' => $request->search ?? null,
            'delivery_man' => $dm->f_name . ' ' . $dm->l_name,
            'type' => 'dm',
        ];

        if ($request->type == 'excel') {
            return Excel::download(new DisbursementHistoryExport($data), 'Disbursementlist.xlsx');
        } else if ($request->type == 'csv') {
            return Excel::download(new DisbursementHistoryExport($data), 'Disbursementlist.csv');
        }
    }

    public function status_filter(Request $request)
    {
        session()->put('withdraw_status_filter', $request['withdraw_status_filter']);
        return response()->json(session('withdraw_status_filter'));
    }


    public function withdraw_list(Request $request)
    {
        $key = isset($request['search']) ? explode(' ', $request['search']) : [];
        $all = session()->has('withdraw_status_filter') && session('withdraw_status_filter') == 'all' ? 1 : 0;
        $active = session()->has('withdraw_status_filter') && session('withdraw_status_filter') == 'approved' ? 1 : 0;
        $denied = session()->has('withdraw_status_filter') && session('withdraw_status_filter') == 'denied' ? 1 : 0;
        $pending = session()->has('withdraw_status_filter') && session('withdraw_status_filter') == 'pending' ? 1 : 0;

        $withdraw_req = WithdrawRequest::with(['deliveryman'])
            ->when($all, function ($query) {
                return $query;
            })
            ->when($active, function ($query) {
                return $query->where('approved', 1);
            })
            ->when($denied, function ($query) {
                return $query->where('approved', 2);
            })
            ->when($pending, function ($query) {
                return $query->where('approved', 0);
            })
            ->when(isset($key), function ($query) use ($key) {
                return $query->whereHas('deliveryman', function ($query) use ($key) {
                    foreach ($key as $value) {
                        $query->where(function ($query) use ($value) {
                            $query->where('f_name', 'like', "%{$value}%")
                                ->orWhere('l_name', 'like', "%{$value}%");
                        });
                    }
                });
            })
            ->where('delivery_man_id', '!=', null)
            ->latest()
            ->paginate(config('default_pagination'));

        return view('admin-views.wallet.dm-withdraw', compact('withdraw_req'));
    }
    public function withdraw_export(Request $request)
    {
        $key = isset($request['search']) ? explode(' ', $request['search']) : [];
        $all = session()->has('withdraw_status_filter') && session('withdraw_status_filter') == 'all' ? 1 : 0;
        $active = session()->has('withdraw_status_filter') && session('withdraw_status_filter') == 'approved' ? 1 : 0;
        $denied = session()->has('withdraw_status_filter') && session('withdraw_status_filter') == 'denied' ? 1 : 0;
        $pending = session()->has('withdraw_status_filter') && session('withdraw_status_filter') == 'pending' ? 1 : 0;

        $withdraw_req = WithdrawRequest::with(['deliveryman'])
            ->when($all, function ($query) {
                return $query;
            })
            ->when($active, function ($query) {
                return $query->where('approved', 1);
            })
            ->when($denied, function ($query) {
                return $query->where('approved', 2);
            })
            ->when($pending, function ($query) {
                return $query->where('approved', 0);
            })
            ->when(isset($key), function ($query) use ($key) {
                return $query->whereHas('deliveryman', function ($query) use ($key) {
                    foreach ($key as $value) {
                        $query->where('f_name', 'like', "%{$value}%")
                            ->orWhere('l_name', 'like', "%{$value}%");
                    }
                });
            })
            ->where('delivery_man_id', '!=', null)
            ->latest()->get();

        $data = [
            'withdraw_requests' => $withdraw_req,
            'search' => $request->search ?? null,
            'request_status' => session()->has('withdraw_status_filter') ? session('withdraw_status_filter') : null,

        ];

        if ($request->type == 'excel') {
            return Excel::download(new DeliveryManWithdrawTransactionExport($data), 'WithdrawRequests.xlsx');
        } else if ($request->type == 'csv') {
            return Excel::download(new DeliveryManWithdrawTransactionExport($data), 'WithdrawRequests.csv');
        }
    }

    public function getWithdrawDetails(Request $request)
    {
        $withdraw = WithdrawRequest::with(['deliveryman'])->where(['id' => $request->withdraw_id])->first();
        return response()->json([
            'view' => view('admin-views.wallet.dm-partials._side_view', compact('withdraw'))->render(),
        ]);
    }

    public function withdraw_search(Request $request)
    {
        $key = explode(' ', $request['search']);
        $withdraw_req = WithdrawRequest::
            whereHas('deliveryman', function ($query) use ($key) {
                foreach ($key as $value) {
                    $query->where('f_name', 'like', "%{$value}%")
                        ->orWhere('l_name', 'like', "%{$value}%");
                }
            })->get();
        $total = $withdraw_req->count();
        return response()->json([
            'view' => view('admin-views.wallet.dm-partials._table', compact('withdraw_req'))->render(),
            'total' => $total
        ]);
    }

    public function withdraw_view($withdraw_id, $seller_id)
    {
        $wr = WithdrawRequest::with(['vendor'])->where(['id' => $withdraw_id])->first();
        return view('admin-views.wallet.withdraw-view', compact('wr'));
    }

    public function withdrawStatus(Request $request, $id)
    {
        $request->validate([
            'note' => 'max:200',
        ]);
        $withdraw = WithdrawRequest::findOrFail($id);
        $withdraw->approved = $request->approved;
        $withdraw->transaction_note = $request['note'];

        $wallet = DeliveryManWallet::where('delivery_man_id', $withdraw->delivery_man_id)->first();
        if ((string) $wallet->total_earning < (string) ($wallet->total_withdrawn + $wallet->pending_withdraw)) {
            Toastr::error(translate('messages.Blalnce_mismatched_total_earning_is_too_low'));
            return redirect()->route('admin.transactions.delivery-man.withdraw_list');
        }

        $delivery_man = $withdraw->deliveryman;

        if ($request->approved == 1) {
            $wallet->increment('total_withdrawn', $withdraw->amount);
            $wallet->decrement('pending_withdraw', $withdraw->amount);
            $withdraw->save();
            $push_notification_status = Helpers::getNotificationStatusData('deliveryman', 'deliveryman_withdraw_approve', 'push_notification_status', $delivery_man->id);
            $push_notification_status = $push_notification_status == 1 && $delivery_man?->fcm_token && $delivery_man?->fcm_token != '@' ? 1 : 0;
            $mail_status = (config('mail.status') && Helpers::get_mail_status('withdraw_approve_mail_status_dm') == '1' && Helpers::getNotificationStatusData('deliveryman', 'deliveryman_withdraw_approve', 'mail_status', $delivery_man->id));
            $this->sentWithdrawRequestNotification($withdraw, $delivery_man->fcm_token, $delivery_man->email, 'approved', $push_notification_status, $mail_status);
            Toastr::success(translate('messages.deliveryman_withdraw_request_approved'));
            return redirect()->route('admin.transactions.delivery-man.withdraw_list');
        } else if ($request->approved == 2) {
            $wallet->decrement('pending_withdraw', $withdraw->amount);
            $withdraw->save();
            $push_notification_status = Helpers::getNotificationStatusData('deliveryman', 'deliveryman_withdraw_rejaction', 'push_notification_status', $delivery_man->id);
            $push_notification_status = $push_notification_status == 1 && $delivery_man?->fcm_token ? 1 : 0;
            $mail_status = (config('mail.status') && Helpers::get_mail_status('withdraw_deny_mail_status_dm') == '1' && Helpers::getNotificationStatusData('deliveryman', 'deliveryman_withdraw_rejaction', 'mail_status', $delivery_man->id));
            $this->sentWithdrawRequestNotification($withdraw, $delivery_man->fcm_token, $delivery_man->email, 'denied', $push_notification_status, $mail_status);
            Toastr::info(translate('messages.deliveryman_withdraw_request_denied'));
            return redirect()->route('admin.transactions.delivery-man.withdraw_list');
        } else {
            Toastr::error(translate('messages.not_found'));
            return back();
        }
    }

    private function sentWithdrawRequestNotification($withdraw, $token, $email, $type = 'approved', $push_notification_status = '1', $mail_status = '1')
    {
        try {
            if ($push_notification_status == 1) {
                $data = [
                    'title' => $type == 'approved' ? translate('Withdraw_approved') : translate('Withdraw_rejected'),
                    'description' => $type == 'approved' ? translate('Withdraw_request_approved_by_admin') : translate('Withdraw_request_rejected_by_admin'),
                    'order_id' => '',
                    'image' => '',
                    'type' => 'withdraw',
                    'order_status' => '',
                ];
                Helpers::send_push_notif_to_device($token, $data);
                DB::table('user_notifications')->insert([
                    'data' => json_encode($data),
                    'delivery_man_id' => $withdraw->delivery_man_id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            if ($mail_status == 1) {
                Mail::to($email)->send(new WithdrawRequestMail($type, $withdraw, 'dm'));
            }
        } catch (\Exception $e) {
            info($e->getMessage());
        }
        return true;
    }

    /**
     * Push FCM al repartidor según ajustes (negocio → notificaciones → repartidor).
     */
    private function notifyDeliverymanRegistrationPush(
        DeliveryManModel $dm,
        string $notificationSettingKey,
        array $pushData,
        UserNotificationRepositoryInterface $notificationRepo
    ): void {
        $token = $dm->fcm_token ?? null;
        if (empty($token) || $token === '@') {
            return;
        }
        if (! Helpers::getNotificationStatusData('deliveryman', $notificationSettingKey, 'push_notification_status')) {
            return;
        }
        try {
            Helpers::send_push_notif_to_device($token, $pushData);
            $notificationRepo->add([
                'data' => json_encode($pushData),
                'delivery_man_id' => $dm->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (Exception $e) {
            info('Delivery man registration push: '.$e->getMessage());
        }
    }

}

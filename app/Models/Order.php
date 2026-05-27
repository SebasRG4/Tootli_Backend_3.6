<?php

namespace App\Models;

use App\CentralLogics\Helpers;
use Carbon\Carbon;
use App\Scopes\ZoneScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\ReportFilter;
use Illuminate\Support\Str;
use Modules\TaxModule\Entities\OrderTax;

class Order extends Model
{
    use HasFactory, ReportFilter;

    protected $casts = [
        'order_amount' => 'float',
        'coupon_discount_amount' => 'float',
        'total_tax_amount' => 'float',
        'store_discount_amount' => 'float',
        'flash_admin_discount_amount' => 'float',
        'flash_store_discount_amount' => 'float',
        'delivery_address_id' => 'integer',
        'delivery_man_id' => 'integer',
        'delivery_charge' => 'float',
        'additional_charge' => 'float',
        'original_delivery_charge' => 'float',
        'user_id' => 'integer',
        'zone_id' => 'integer',
        'scheduled' => 'integer',
        'store_id' => 'integer',
        'details_count' => 'integer',
        'module_id' => 'integer',
        'dm_vehicle_id' => 'integer',
        'processing_time' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'extra_packaging_amount' => 'float',
        'receiver_details' => 'array',
        'dm_tips' => 'float',
        'distance' => 'float',
        'tax_percentage' => 'float',
        'prescription_order' => 'boolean',
        'cutlery' => 'boolean',
        'is_guest' => 'boolean',
        'ref_bonus_amount' => 'float',
        'bring_change_amount' => 'integer',
        'parcel_pickup_at' => 'string',
        'parcel_delivery_at' => 'string',
        'parcel_item_estimated_price' => 'float',
        'actual_parcel_item_price' => 'float',
        'proposed_parcel_item_price' => 'float',
        'adjustment_status' => 'string',
        'ecartpay_gateway_fee' => 'float',
        'ecartpay_card_international' => 'boolean',
        'tootli_direct' => 'boolean',
        'store_pos_customer_id' => 'integer',
        'pos_payment_meta' => 'array',
    ];

    protected $appends = ['module_type', 'order_attachment_full_url', 'order_proof_full_url', 'customer_tracking_url', 'cash_on_pickup_amount'];

    public function getOrderAttachmentFullUrlAttribute()
    {
        $images = [];
        $value = is_array($this->order_attachment)
            ? $this->order_attachment
            : ($this->order_attachment && is_string($this->order_attachment) && $this->isValidJson($this->order_attachment)
                ? json_decode($this->order_attachment, true)
                : []);
        if ($value) {
            foreach ($value as $item) {
                $item = is_array($item) ? $item : (is_object($item) && get_class($item) == 'stdClass' ? json_decode(json_encode($item), true) : ['img' => $item, 'storage' => 'public']);
                $images[] = Helpers::get_full_url('order', $item['img'], $item['storage']);
            }
        }

        return $images;
    }

    /**
     * Pedido que puede usar la página pública de rastreo Tootli Directo.
     * Incluye pedidos antiguos POS con domicilio y pago Tootli aunque `tootli_direct` venga en 0 en BD.
     */
    public function isTootliDirectTrackable(): bool
    {
        if (strtolower((string) ($this->order_type ?? '')) !== 'delivery') {
            return false;
        }
        $rawTd = $this->attributes['tootli_direct'] ?? $this->getRawOriginal('tootli_direct') ?? null;
        if ($rawTd === 1 || $rawTd === '1' || $rawTd === true || $this->tootli_direct === true) {
            return true;
        }
        if ($this->store_pos_customer_id && in_array((string) ($this->payment_method ?? ''), [
            'cash_on_delivery',
            'cash',
            'card_tootli_direct',
            'paid_at_restaurant',
        ], true)) {
            return true;
        }

        return false;
    }

    /**
     * El chat del enlace público de seguimiento Tootli Directo sigue activo
     * hasta que el pedido quede entregado (o entrega parcial).
     */
    public function isTootliDirectPublicTrackingChatOpen(): bool
    {
        return ! in_array(strtolower((string) ($this->order_status ?? '')), ['delivered', 'partial_delivered'], true);
    }

    /**
     * URL pública de rastreo (Tootli Directo + domicilio + token vigente).
     * Expuesto en JSON de la API vendor vía $appends.
     */
    public function getCustomerTrackingUrlAttribute(): ?string
    {
        if (! $this->isTootliDirectTrackable()) {
            return null;
        }
        $id = $this->getKey();
        if (! $id) {
            return null;
        }

        $row = TootliDirectTrackingToken::where('order_id', $id)->first();
        if (! $row || ($row->expires_at !== null && $row->expires_at->isPast())) {
            try {
                $row = TootliDirectTrackingToken::updateOrCreate(
                    ['order_id' => $id],
                    [
                        'token' => Str::random(48),
                        'expires_at' => now()->addDays(21),
                    ]
                );
            } catch (\Throwable) {
                return null;
            }
        }

        return url('/rastreo-orden/tootli-directo/'.$row->token);
    }

    public function getOrderProofFullUrlAttribute()
    {
        $images = [];
        $value = is_array($this->order_proof)
            ? $this->order_proof
            : ($this->order_proof && is_string($this->order_proof) && $this->isValidJson($this->order_proof)
                ? json_decode($this->order_proof, true)
                : []);
        if ($value) {
            foreach ($value as $item) {
                $item = is_array($item) ? $item : (is_object($item) && get_class($item) == 'stdClass' ? json_decode(json_encode($item), true) : ['img' => $item, 'storage' => 'public']);
                $images[] = Helpers::get_full_url('order', $item['img'], $item['storage']);
            }
        }

        return $images;
    }

    private function isValidJson($string)
    {
        json_decode($string);
        return (json_last_error() === JSON_ERROR_NONE);
    }

    public function setDeliveryChargeAttribute($value)
    {
        $this->attributes['delivery_charge'] = round($value, 3);
    }

    public function cashback_history()
    {
        return $this->hasOne(CashBackHistory::class, 'order_id');
    }
    public function parcelCancellation()
    {
        return $this->hasOne(ParcelCancellation::class, 'order_id');
    }

    public function auditEvents()
    {
        return $this->hasMany(OrderAuditEvent::class)->orderBy('created_at');
    }

    public function strikeReviewQueueItems()
    {
        return $this->hasMany(OrderStrikeReviewQueue::class);
    }

    public function dmCancelReason()
    {
        return $this->belongsTo(OrderCancelReason::class, 'cancel_reason_id');
    }


    public function offline_payments()
    {
        return $this->belongsTo(OfflinePayments::class, 'id', 'order_id');
    }

    public function details()
    {
        return $this->hasMany(OrderDetail::class);
    }
    public function reviews()
    {
        return $this->hasMany(Review::class, 'order_id');
    }

    public function payments()
    {
        return $this->hasMany(OrderPayment::class);
    }

    public function delivery_man()
    {
        return $this->belongsTo(DeliveryMan::class, 'delivery_man_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function storePosCustomer()
    {
        return $this->belongsTo(StorePosCustomer::class, 'store_pos_customer_id');
    }

    public function guest()
    {
        return $this->belongsTo(Guest::class, 'user_id', 'id');
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_code', 'code');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function delivery_history()
    {
        return $this->hasMany(DeliveryHistory::class, 'order_id');
    }

    /**
     * Última posición GPS del repartidor asignado (misma fila que DeliveryMan::last_location: mayor id en delivery_histories).
     * Antes se delegaba a delivery_man->last_location() como “relación” en Order, lo que podía cachear datos incorrectos.
     *
     * @return array{latitude: mixed, longitude: mixed, location: string}|null
     */
    public function getDmLastLocationAttribute(): ?array
    {
        if (! $this->delivery_man_id) {
            return null;
        }
        $hist = DeliveryHistory::query()
            ->where('delivery_man_id', $this->delivery_man_id)
            ->orderByDesc('id')
            ->first();
        if (! $hist || $hist->latitude === null || $hist->longitude === null || $hist->latitude === '' || $hist->longitude === '') {
            return null;
        }

        return [
            'latitude' => $hist->latitude,
            'longitude' => $hist->longitude,
            'location' => (string) ($hist->location ?? ''),
        ];
    }

    public function transaction()
    {
        return $this->hasOne(OrderTransaction::class);
    }

    public function parcel_category()
    {
        return $this->belongsTo(ParcelCategory::class);
    }

    public function refund()
    {
        return $this->hasOne(Refund::class, 'order_id');
    }
    public function OrderReference()
    {
        return $this->hasOne(OrderReference::class, 'order_id');
    }

    public function getModuleTypeAttribute()
    {
        return $this->module ? $this->module->module_type : null;
    }

    public function getCashOnPickupAmountAttribute()
    {
        if ($this->payment_method === 'cash_on_delivery' && $this->order_type === 'delivery') {
            return (float) max(0.0, $this->order_amount - $this->delivery_charge - ($this->dm_tips ?? 0));
        }
        return 0.0;
    }

    public function scopeAccepteByDeliveryman($query)
    {
        return $query->where('order_status', 'accepted');
    }

    public function scopePreparing($query)
    {
        return $query->whereIn('order_status', ['confirmed', 'processing', 'handover']);
    }

    public function scopeModule($query, $module_id)
    {
        return $query->where('module_id', $module_id);
    }

    public function scopeOngoing($query)
    {
        return $query->whereIn('order_status', ['accepted', 'confirmed', 'processing', 'handover', 'picked_up', 'partial_delivered']);
    }

    public function scopeItemOnTheWay($query)
    {
        return $query->where('order_status', 'picked_up');
    }

    public function scopePending($query)
    {
        return $query->where('order_status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('order_status', 'failed')->whereDoesntHave('offline_payments');
    }

    public function scopeCanceled($query)
    {
        return $query->where('order_status', 'canceled');
    }

    public function scopeDelivered($query)
    {
        return $query->where('order_status', 'delivered');
    }

    public function scopePartialDelivered($query)
    {
        return $query->where('order_status', 'partial_delivered');
    }

    public function scopeNotRefunded($query)
    {
        return $query->where(function ($query) {
            $query->whereNotIn('order_status', ['refunded']);
        });
    }

    public function scopeRefunded($query)
    {
        return $query->where('order_status', 'refunded');
    }
    public function scopeRefund_requested($query)
    {
        return $query->where('order_status', 'refund_requested');
    }

    public function scopeRefund_request_canceled($query)
    {
        return $query->where('order_status', 'refund_request_canceled');
    }

    public function scopeSearchingForDeliveryman($query)
    {
        return $query->whereNull('delivery_man_id')->whereIn('order_type', ['delivery', 'parcel'])->whereNotIn('order_status', ['delivered', 'failed', 'canceled', 'refund_requested', 'refund_request_canceled', 'refunded']);
    }

    public function scopeDelivery($query)
    {
        return $query->where('order_type', '=', 'delivery');
    }

    public function scopeScheduled($query)
    {
        return $query->whereRaw('created_at <> schedule_at')->where('scheduled', '1');
    }

    public function scopeOrderScheduledIn($query, $interval)
    {
        return $query->where(function ($query) use ($interval) {
            $query->whereRaw('created_at <> schedule_at')->where(function ($q) use ($interval) {
                $q->whereBetween('schedule_at', [Carbon::now()->toDateTimeString(), Carbon::now()->addMinutes($interval)->toDateTimeString()]);
            })->orWhere('schedule_at', '<', Carbon::now()->toDateTimeString());
        })->orWhereRaw('created_at = schedule_at');
    }


    public function scopeStoreOrder($query)
    {
        return $query->where(function ($q) {
            $q->whereIn('order_type', ['take_away', 'dine_in'])->orWhere('order_type', 'delivery');
        });
    }

    public function scopeDmOrder($query)
    {
        return $query->where(function ($q) {
            $q->where('order_type', 'parcel')->orWhere('order_type', 'delivery');
        });
    }

    public function scopeParcelOrder($query)
    {
        return $query->where('order_type', 'parcel');
    }

    public function scopePos($query)
    {
        return $query->where('order_type', '=', 'pos');
    }

    public function scopeNotpos($query)
    {
        return $query->where('order_type', '<>', 'pos');
    }

    public function scopePaymentApproval($query, $status)
    {
        return $query->where('payment_approval', $status);
    }

    public function scopeNotDigitalOrder($query)
    {
        return $query->where(function ($q) {
            $q->whereNotIn('payment_method', ['digital_payment', 'offline_payment'])->orwhereNot('order_status', 'pending');
        });
    }

    public function getCreatedAtAttribute($value)
    {
        return date('Y-m-d H:i:s', strtotime($value));
    }

    protected static function booted()
    {
        static::addGlobalScope(new ZoneScope);
        static::addGlobalScope('storage', function ($builder) {
            $builder->with('storage');
        });
    }
    public function storage()
    {
        return $this->morphMany(Storage::class, 'data');
    }
    protected static function boot()
    {
        parent::boot();
    }
    public function orderTaxes()
    {
        return $this->morphMany(OrderTax::class, 'order');
    }
}

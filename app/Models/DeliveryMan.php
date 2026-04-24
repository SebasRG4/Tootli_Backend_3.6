<?php

namespace App\Models;

use App\CentralLogics\Helpers;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Scopes\ZoneScope;

class DeliveryMan extends Authenticatable
{
    use Notifiable;

    protected $casts = [
        'zone_id' => 'integer',
        'status' => 'boolean',
        'active' => 'integer',
        'available' => 'integer',
        'earning' => 'float',
        'store_id' => 'integer',
        'current_orders' => 'integer',
        'vehicle_id' => 'integer',
        'ref_by' => 'integer',
        'loyalty_point' => 'float',
        // Taxi service capabilities
        'can_deliver' => 'boolean',
        'can_drive_taxi' => 'boolean',
        'delivery_active' => 'boolean',
        'taxi_active' => 'boolean',
        'taxi_is_verified' => 'boolean',
        'taxi_rating' => 'float',
        'taxi_total_rides' => 'integer',
        'taxi_license_expiry' => 'date',
        'taxi_documents' => 'array',
        'identity_image' => 'array',
        'registration_revision_allowed' => 'boolean',
        'registration_revision_requested_at' => 'datetime',
        'dm_tier_updated_at' => 'datetime',
        'delivery_suspended_until' => 'datetime',
    ];

    protected $hidden = [
        'password',
        'auth_token',
    ];

    protected $appends = ['image_full_url', 'identity_image_full_url'];

    public function getFullNameAttribute()
    {
        return $this->f_name . ' ' . $this->l_name;
    }

    public function getRefCodeAttribute($value)
    {
        if ($value) {
            return $value;
        }

        $code = Helpers::generate_referer_code('deliveryman');

        $this->newQuery()
            ->where('id', $this->id)
            ->update(['ref_code' => $code]);

        $this->attributes['ref_code'] = $code;

        return $code;
    }

    public function referalHistory()
    {
        return $this->hasMany(DeliverymanReferralHistory::class);
    }
    public function total_canceled_orders()
    {
        return $this->hasMany(Order::class)->where('order_status', 'canceled');
    }
    public function total_ongoing_orders()
    {
        return $this->hasMany(Order::class)->whereIn('order_status', ['handover', 'picked_up']);
    }

    public function userinfo()
    {
        return $this->hasOne(UserInfo::class, 'deliveryman_id', 'id');
    }

    public function vehicle()
    {
        return $this->belongsTo(DMVehicle::class);
    }

    public function wallet()
    {
        return $this->hasOne(DeliveryManWallet::class);
    }

    public function adminAuditLogs()
    {
        return $this->hasMany(DeliveryManAdminAuditLog::class);
    }

    public function strikeEvents()
    {
        return $this->hasMany(DeliveryManStrikeEvent::class, 'delivery_man_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function order_transaction()
    {
        return $this->hasMany(OrderTransaction::class);
    }

    public function todays_earning()
    {
        return $this->hasMany(OrderTransaction::class)->whereDate('created_at', now());
    }

    public function this_week_earning()
    {
        return $this->hasMany(OrderTransaction::class)->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
    }

    public function this_month_earning()
    {
        return $this->hasMany(OrderTransaction::class)->whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'));
    }

    public function todaysorders()
    {
        return $this->hasMany(Order::class)->whereDate('accepted', now());
    }

    public function total_delivered_orders()
    {
        return $this->hasMany(Order::class)->where('order_status', 'delivered');
    }

    public function this_week_orders()
    {
        return $this->hasMany(Order::class)->whereBetween('accepted', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
    }

    public function delivery_history()
    {
        return $this->hasMany(DeliveryHistory::class, 'delivery_man_id');
    }

    public function last_location()
    {
        return $this->hasOne(DeliveryHistory::class, 'delivery_man_id')->latestOfMany();
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function reviews()
    {
        return $this->hasMany(DMReview::class);
    }

    public function disbursement_method()
    {
        return $this->hasOne(DisbursementWithdrawalMethod::class)->where('is_default', 1);
    }

    public function rating()
    {
        return $this->hasMany(DMReview::class)
            ->select(DB::raw('avg(rating) average, count(delivery_man_id) rating_count, delivery_man_id'))
            ->groupBy('delivery_man_id');
    }

    // ==========================================
    // TAXI SERVICE RELATIONSHIPS & METHODS
    // ==========================================

    /**
     * Taxi rides for this driver
     */
    public function taxiRides()
    {
        return $this->hasMany(TaxiRide::class, 'delivery_man_id');
    }

    /**
     * Scope: Drivers who can do deliveries
     */
    public function scopeCanDelivery($query)
    {
        return $query->where('can_deliver', true);
    }

    /**
     * Scope: Drivers who can do taxi
     */
    public function scopeCanTaxi($query)
    {
        return $query->where('can_drive_taxi', true);
    }

    /**
     * Scope: Verified taxi drivers only
     */
    public function scopeTaxiVerified($query)
    {
        return $query->where('can_drive_taxi', true)
            ->where('taxi_is_verified', true);
    }

    /**
     * Scope: Drivers with delivery service active
     */
    public function scopeDeliveryActive($query)
    {
        return $query->where('delivery_active', true);
    }

    /**
     * Scope: Drivers with taxi service active
     */
    public function scopeTaxiActive($query)
    {
        return $query->where('taxi_active', true);
    }

    /**
     * Scope: Drivers available for taxi rides
     */
    public function scopeTaxiAvailable($query)
    {
        return $query->canTaxi()
            ->taxiActive()
            ->active()
            ->available();
    }

    /**
     * Check if driver can accept taxi rides
     */
    public function canAcceptTaxiRides(): bool
    {
        return $this->can_drive_taxi
            && $this->taxi_is_verified
            && $this->taxi_active
            && $this->active
            && $this->status;
    }

    /**
     * Check if driver can accept delivery orders
     */
    public function canAcceptDeliveryOrders(): bool
    {
        return $this->can_deliver
            && $this->delivery_active
            && $this->active
            && $this->status;
    }

    /**
     * Go online for taxi service
     */
    public function goOnlineTaxi(): void
    {
        $this->update(['taxi_active' => true, 'active' => 1]);
    }

    /**
     * Go offline for taxi service
     */
    public function goOfflineTaxi(): void
    {
        $this->update(['taxi_active' => false]);
    }

    /**
     * Toggle taxi service active status
     */
    public function toggleTaxiService(): bool
    {
        $this->taxi_active = !$this->taxi_active;
        $this->save();
        return $this->taxi_active;
    }

    /**
     * Get services summary for API response
     */
    public function getServicesAttribute(): array
    {
        return [
            'can_deliver' => $this->can_deliver,
            'can_drive_taxi' => $this->can_drive_taxi,
            'delivery_active' => $this->delivery_active,
            'taxi_active' => $this->taxi_active,
            'taxi_is_verified' => $this->taxi_is_verified,
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('active', 1)->where('application_status', 'approved');
    }
    public function scopeInActive($query)
    {
        return $query->where('active', 0)->where('application_status', 'approved');
    }

    public function scopeEarning($query)
    {
        return $query->where('earning', 1);
    }

    public function scopeAvailable($query)
    {
        return $query->where('current_orders', '<', config('dm_maximum_orders') ?? 1);
    }

    public function scopeUnavailable($query)
    {
        return $query->where('current_orders', '>', config('dm_maximum_orders') ?? 1);
    }

    public function scopeZonewise($query)
    {
        return $query->where('type', 'zone_wise');
    }

    public function getImageFullUrlAttribute()
    {
        $value = $this->image;
        if (count($this->storage) > 0) {
            foreach ($this->storage as $storage) {
                if ($storage['key'] == 'image') {
                    return Helpers::get_full_url('delivery-man', $value, $storage['value']);
                }
            }
        }

        return Helpers::get_full_url('delivery-man', $value, 'public');
    }
    public function getIdentityImageFullUrlAttribute()
    {
        $images = [];
        $value = is_array($this->identity_image)
            ? $this->identity_image
            : ($this->identity_image && is_string($this->identity_image) && $this->isValidJson($this->identity_image)
                ? json_decode($this->identity_image, true)
                : []);
        if ($value) {
            foreach ($value as $item) {
                $item = is_array($item) ? $item : (is_object($item) && get_class($item) == 'stdClass' ? json_decode(json_encode($item), true) : ['img' => $item, 'storage' => 'public']);
                $images[] = Helpers::get_full_url('delivery-man', $item['img'], $item['storage']);
            }
        }

        return $images;
    }

    private function isValidJson($string)
    {
        json_decode($string);
        return (json_last_error() === JSON_ERROR_NONE);
    }

    public function storage()
    {
        return $this->morphMany(Storage::class, 'data');
    }

    protected static function booted()
    {
        static::addGlobalScope('storage', function ($builder) {
            $builder->with('storage');
        });
        static::addGlobalScope(new ZoneScope);
    }

    protected static function boot()
    {
        parent::boot();
        static::saved(function ($model) {
            if ($model->isDirty('image')) {
                $value = Helpers::getDisk();

                DB::table('storages')->updateOrInsert([
                    'data_type' => get_class($model),
                    'data_id' => $model->id,
                    'key' => 'image',
                ], [
                    'value' => $value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

    }
}

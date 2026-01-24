<?php

namespace Modules\Taxi\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Models\Admin;

class TaxiSafetyAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'taxi_ride_id',
        'user_id',
        'alert_type',
        'status',
        'user_location_lat',
        'user_location_lng',
        'admin_id',
        'admin_notes',
        'contacted_at',
        'resolved_at',
    ];

    protected $casts = [
        'user_location_lat' => 'float',
        'user_location_lng' => 'float',
        'contacted_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    // Alert types
    const TYPE_INSECURE = 'insecure';
    const TYPE_EMERGENCY = 'emergency';

    // Statuses
    const STATUS_PENDING = 'pending';
    const STATUS_CONTACTED = 'contacted';
    const STATUS_RESOLVED = 'resolved';
    const STATUS_ESCALATED = 'escalated';

    public function taxiRide(): BelongsTo
    {
        return $this->belongsTo(TaxiRide::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Scope for pending alerts
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for emergency alerts
     */
    public function scopeEmergency($query)
    {
        return $query->where('alert_type', self::TYPE_EMERGENCY);
    }

    /**
     * Scope for active alerts (pending or contacted)
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_CONTACTED]);
    }

    /**
     * Check if this is an emergency alert
     */
    public function isEmergency(): bool
    {
        return $this->alert_type === self::TYPE_EMERGENCY;
    }

    /**
     * Mark as contacted by admin
     */
    public function markAsContacted(int $adminId, ?string $notes = null): void
    {
        $this->update([
            'status' => self::STATUS_CONTACTED,
            'admin_id' => $adminId,
            'admin_notes' => $notes,
            'contacted_at' => now(),
        ]);
    }

    /**
     * Mark as resolved
     */
    public function markAsResolved(?string $notes = null): void
    {
        $this->update([
            'status' => self::STATUS_RESOLVED,
            'admin_notes' => $notes ? ($this->admin_notes . "\n" . $notes) : $this->admin_notes,
            'resolved_at' => now(),
        ]);
    }

    /**
     * Escalate to authorities
     */
    public function escalate(?string $notes = null): void
    {
        $this->update([
            'status' => self::STATUS_ESCALATED,
            'admin_notes' => $notes ? ($this->admin_notes . "\n[ESCALADO] " . $notes) : $this->admin_notes,
        ]);
    }
}

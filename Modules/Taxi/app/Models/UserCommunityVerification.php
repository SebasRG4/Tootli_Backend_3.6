<?php

namespace Modules\Taxi\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Models\DeliveryMan;
use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\Storage;

class UserCommunityVerification extends Model
{
    use HasFactory;

    protected $table = 'user_community_verifications';

    protected $fillable = [
        'user_id',
        'delivery_man_id',
        'organization_id',
        'role',
        'document_type',
        'document_number',
        'institutional_email',
        'id_card_image',
        'id_card_back_image',
        'verification_status',
        'ai_verified',
        'ai_confidence_score',
        'ai_extracted_data',
        'ai_review_notes',
        'rejection_reason',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'ai_verified' => 'boolean',
        'ai_confidence_score' => 'float',
        'ai_extracted_data' => 'array',
    ];

    protected $appends = [
        'id_card_image_url',
        'id_card_back_image_url',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function deliveryMan(): BelongsTo
    {
        return $this->belongsTo(DeliveryMan::class, 'delivery_man_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(TaxiCommunityOrganization::class, 'organization_id');
    }

    public function isApproved(): bool
    {
        return $this->verification_status === 'approved';
    }

    public function scopePending($query)
    {
        return $query->where('verification_status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('verification_status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('verification_status', 'rejected');
    }

    public function getIdCardImageUrlAttribute(): ?string
    {
        if (!$this->id_card_image) return null;
        if (Storage::disk('public')->exists('community_cards/' . $this->id_card_image)) {
            return asset('storage/community_cards/' . $this->id_card_image);
        }
        return Helpers::get_full_url('community_cards', $this->id_card_image, 'public');
    }

    public function getIdCardBackImageUrlAttribute(): ?string
    {
        if (!$this->id_card_back_image) return null;
        if (Storage::disk('public')->exists('community_cards/' . $this->id_card_back_image)) {
            return asset('storage/community_cards/' . $this->id_card_back_image);
        }
        return Helpers::get_full_url('community_cards', $this->id_card_back_image, 'public');
    }
}

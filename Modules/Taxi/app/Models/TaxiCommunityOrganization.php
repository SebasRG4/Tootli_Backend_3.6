<?php

namespace Modules\Taxi\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaxiCommunityOrganization extends Model
{
    use HasFactory;

    protected $table = 'taxi_community_organizations';

    protected $fillable = [
        'name',
        'short_name',
        'type',
        'allowed_email_domains',
        'address',
        'latitude',
        'longitude',
        'logo',
        'is_active',
    ];

    protected $casts = [
        'allowed_email_domains' => 'array',
        'latitude' => 'float',
        'longitude' => 'float',
        'is_active' => 'boolean',
    ];

    public function verifications(): HasMany
    {
        return $this->hasMany(UserCommunityVerification::class, 'organization_id');
    }

    public function routes(): HasMany
    {
        return $this->hasMany(TaxiCarpoolRoute::class, 'organization_id');
    }

    public function requests(): HasMany
    {
        return $this->hasMany(TaxiCarpoolRequest::class, 'organization_id');
    }
}

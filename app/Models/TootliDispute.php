<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TootliDispute extends Model
{
    use HasFactory;

    protected $table = 'tootli_disputes';

    protected $fillable = [
        'disputable_type',
        'disputable_id',
        'claimant_id',
        'defendant_id',
        'reason',
        'description',
        'evidence_photos',
        'requested_solution',
        'status',
        'refund_amount',
        'resolution_notes',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'evidence_photos' => 'array',
        'refund_amount' => 'double',
        'resolved_at' => 'datetime',
    ];

    protected $appends = ['evidence_full_urls'];

    public function claimant()
    {
        return $this->belongsTo(User::class, 'claimant_id');
    }

    public function defendant()
    {
        return $this->belongsTo(User::class, 'defendant_id');
    }

    public function disputable()
    {
        return $this->morphTo();
    }

    public function getEvidenceFullUrlsAttribute()
    {
        $urls = [];
        if (!empty($this->evidence_photos) && is_array($this->evidence_photos)) {
            foreach ($this->evidence_photos as $photo) {
                $urls[] = asset('storage/app/public/disputes/' . $photo);
            }
        }
        return $urls;
    }
}

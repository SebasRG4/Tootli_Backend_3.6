<?php

namespace Modules\Taxi\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class TaxiSafetyRecording extends Model
{
    use HasFactory;

    protected $fillable = [
        'taxi_ride_id',
        'user_id',
        'file_path',
        'duration_seconds',
        'file_size_kb',
    ];

    protected $casts = [
        'duration_seconds' => 'integer',
        'file_size_kb' => 'integer',
    ];

    public function taxiRide(): BelongsTo
    {
        return $this->belongsTo(TaxiRide::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the full URL to the audio file
     */
    public function getAudioUrlAttribute(): string
    {
        $baseUrl = request()->getSchemeAndHttpHost();
        return $baseUrl . '/storage/' . $this->file_path;
    }

    /**
     * Get formatted duration (mm:ss)
     */
    public function getFormattedDurationAttribute(): string
    {
        if (!$this->duration_seconds) {
            return '00:00';
        }

        $minutes = floor($this->duration_seconds / 60);
        $seconds = $this->duration_seconds % 60;

        return sprintf('%02d:%02d', $minutes, $seconds);
    }
}

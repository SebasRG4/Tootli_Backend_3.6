<?php

namespace Modules\Espacios\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class EspacioReview extends Model
{
    protected $table = 'espacios_reviews';

    protected $fillable = [
        'booking_id',
        'listing_id',
        'user_id',
        'rating_overall',
        'rating_cleanliness',
        'rating_location',
        'rating_value',
        'rating_communication',
        'comment',
        'is_visible',
    ];

    protected $casts = [
        'is_visible'            => 'boolean',
        'rating_overall'        => 'integer',
        'rating_cleanliness'    => 'integer',
        'rating_location'       => 'integer',
        'rating_value'          => 'integer',
        'rating_communication'  => 'integer',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(EspacioListing::class, 'listing_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(EspacioBooking::class, 'booking_id');
    }
}

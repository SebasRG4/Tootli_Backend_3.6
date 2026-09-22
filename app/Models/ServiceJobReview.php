<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceJobReview extends Model
{
    use HasFactory;

    protected $table = 'service_job_reviews';

    protected $fillable = [
        'service_job_id',
        'store_id',
        'user_id',
        'rating',
        'comment',
        'tags',
    ];

    protected $casts = [
        'rating' => 'integer',
        'tags'   => 'array',
    ];

    public function serviceJob()
    {
        return $this->belongsTo(ServiceJob::class, 'service_job_id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

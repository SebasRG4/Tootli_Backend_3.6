<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceBid extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'job_id' => 'integer',
        'store_id' => 'integer',
        'price' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function job()
    {
        return $this->belongsTo(ServiceJob::class, 'job_id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }
}

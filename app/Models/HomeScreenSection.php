<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeScreenSection extends Model
{
    protected $table = 'home_screen_sections';

    protected $fillable = [
        'key',
        'title',
        'module_id',
        'priority',
        'status',
    ];

    protected $casts = [
        'priority' => 'integer',
        'status' => 'boolean',
        'module_id' => 'integer',
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('priority', 'asc');
    }
}

<?php

namespace App\Models;

use App\CentralLogics\Helpers;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;
use App\Models\Store;

class DynamicSectionEcommerce extends Model
{
    use HasFactory;

    protected $fillable = [
        'image',
        'module_id',
        'priority',
        'status',
    ];

    protected $casts = [
        'module_id' => 'integer',
        'priority' => 'integer',
        'status' => 'boolean',
    ];

    protected $appends = ['image_full_url'];

    /**
     * Module relationship.
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * Stores in this section (many-to-many with Restaurant).
     */
    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class, 'dynamic_section_ecommerce_stores', 'dynamic_section_ecommerce_id', 'store_id')
            ->withPivot('priority')
            ->orderByPivot('priority')
            ->withTimestamps();
    }

    /**
     * Storage morph for image.
     */
    public function storage(): MorphMany
    {
        return $this->morphMany(Storage::class, 'data');
    }

    /**
     * Full URL for image.
     */
    public function getImageFullUrlAttribute(): ?string
    {
        $value = $this->image;
        if (!$value) {
            return null;
        }
        if (count($this->storage) > 0) {
            foreach ($this->storage as $storage) {
                if ($storage['key'] === 'image') {
                    return Helpers::get_full_url('dynamic_section_ecommerce', $value, $storage['value']);
                }
            }
        }
        return Helpers::get_full_url('dynamic_section_ecommerce', $value, 'public');
    }

    /**
     * Scope: only active sections.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    /**
     * Scope: filter by module.
     */
    public function scopeByModule(Builder $query, int $moduleId): Builder
    {
        return $query->where('module_id', $moduleId);
    }

    /**
     * Boot: auto-load storage and track image changes.
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('storage', function ($builder) {
            $builder->with('storage');
        });

        static::saved(function ($model) {
            if ($model->isDirty('image')) {
                DB::table('storages')->updateOrInsert([
                    'data_type' => get_class($model),
                    'data_id' => $model->id,
                    'key' => 'image',
                ], [
                    'value' => Helpers::getDisk(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }
}

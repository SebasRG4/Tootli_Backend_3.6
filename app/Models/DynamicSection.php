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

class DynamicSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'background_image',
        'module_id',
        'priority',
        'status',
    ];

    protected $casts = [
        'module_id' => 'integer',
        'priority' => 'integer',
        'status' => 'boolean',
    ];

    protected $appends = ['background_image_full_url'];

    /**
     * Get the module that owns the section.
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * Get the items for the section.
     */
    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'dynamic_section_items')
            ->withPivot('priority')
            ->orderByPivot('priority')
            ->withTimestamps();
    }

    /**
     * Get the storage records for background image.
     */
    public function storage(): MorphMany
    {
        return $this->morphMany(Storage::class, 'data');
    }

    /**
     * Get full URL for background image.
     */
    public function getBackgroundImageFullUrlAttribute(): ?string
    {
        $value = $this->background_image;

        if (!$value) {
            return null;
        }

        if (count($this->storage) > 0) {
            foreach ($this->storage as $storage) {
                if ($storage['key'] == 'background_image') {
                    return Helpers::get_full_url('dynamic_section', $value, $storage['value']);
                }
            }
        }

        return Helpers::get_full_url('dynamic_section', $value, 'public');
    }

    /**
     * Scope for active sections.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    /**
     * Scope for module.
     */
    public function scopeByModule(Builder $query, int $moduleId): Builder
    {
        return $query->where('module_id', $moduleId);
    }

    /**
     * Boot method for model events.
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('storage', function ($builder) {
            $builder->with('storage');
        });

        static::saved(function ($model) {
            if ($model->isDirty('background_image')) {
                $value = Helpers::getDisk();

                DB::table('storages')->updateOrInsert([
                    'data_type' => get_class($model),
                    'data_id' => $model->id,
                    'key' => 'background_image',
                ], [
                    'value' => $value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }
}

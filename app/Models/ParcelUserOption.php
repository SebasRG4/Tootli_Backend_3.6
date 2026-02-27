<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\DB;

class ParcelUserOption extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $casts = [
        'charge_multiplier' => 'double',
        'base_price' => 'double',
        'status' => 'integer',
        'parcel_category_id' => 'integer',
    ];

    public function translations()
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    public function getTitleAttribute($value)
    {
        if (count($this->translations) > 0) {
            foreach ($this->translations as $translation) {
                if ($translation['key'] == 'title') {
                    return $translation['value'];
                }
            }
        }
        return $value;
    }

    public function getDescriptionAttribute($value)
    {
        if (count($this->translations) > 0) {
            foreach ($this->translations as $translation) {
                if ($translation['key'] == 'description') {
                    return $translation['value'];
                }
            }
        }
        return $value;
    }

    protected $appends = ['image_full_url'];

    public function getImageFullUrlAttribute()
    {
        $value = $this->icon;
        if ($value && (substr($value, 0, 7) == 'http://' || substr($value, 0, 8) == 'https://')) {
            return $value;
        }
        if ($value) {
            return asset('storage/parcel_category') . '/' . $value;
        }
        return asset('assets/admin/img/160x160/img2.jpg');
    }

    protected static function booted()
    {
        static::addGlobalScope('translate', function (Builder $builder) {
            $builder->with([
                'translations' => function ($query) {
                    return $query->where('locale', app()->getLocale());
                }
            ]);
        });
    }
}

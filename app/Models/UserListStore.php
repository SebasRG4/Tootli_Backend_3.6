<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserListStore extends Model
{
    protected $fillable = ['user_list_id', 'store_id', 'note'];

    protected $casts = [
        'user_list_id' => 'integer',
        'store_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function userList(): BelongsTo
    {
        return $this->belongsTo(UserList::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}

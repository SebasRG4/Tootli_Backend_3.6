<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminTodoCategory extends Model
{
    use HasFactory;

    protected $table = 'admin_todo_categories';

    protected $fillable = [
        'name',
        'color',
        'icon',
        'position',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function todos()
    {
        return $this->hasMany(AdminTodo::class, 'category_id');
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    /**
     * Percentage of completed tasks in this category (0-100).
     */
    public function getCompletionPercentageAttribute(): int
    {
        $total = $this->todos()->count();
        if ($total === 0) return 0;
        $done = $this->todos()->where('status', 'completed')->count();
        return (int) round(($done / $total) * 100);
    }

    public function getTaskCountAttribute(): int
    {
        return $this->todos()->count();
    }

    public function getPendingCountAttribute(): int
    {
        return $this->todos()->where('status', 'pending')->count();
    }

    public function getInProgressCountAttribute(): int
    {
        return $this->todos()->where('status', 'in_progress')->count();
    }

    public function getCompletedCountAttribute(): int
    {
        return $this->todos()->where('status', 'completed')->count();
    }

    /**
     * Lighter version of the hex color (for backgrounds).
     */
    public function getLightColorAttribute(): string
    {
        // Append low opacity via CSS — return the raw hex for inline use
        return $this->color;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminTodo extends Model
{
    use HasFactory;

    protected $table = 'admin_todos';

    protected $fillable = [
        'title',
        'description',
        'priority',
        'status',
        'due_date',
        'created_by',
        'category_id',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function category()
    {
        return $this->belongsTo(AdminTodoCategory::class, 'category_id');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'high'   => 'danger',
            'medium' => 'warning',
            'low'    => 'success',
            default  => 'secondary',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending'     => 'secondary',
            'in_progress' => 'primary',
            'completed'   => 'success',
            default       => 'secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'     => 'Pendiente',
            'in_progress' => 'En progreso',
            'completed'   => 'Completado',
            default       => ucfirst($this->status),
        };
    }

    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            'high'   => 'Alta',
            'medium' => 'Media',
            'low'    => 'Baja',
            default  => ucfirst($this->priority),
        };
    }
}

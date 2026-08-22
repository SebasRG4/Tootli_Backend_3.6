<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class DeliveryManStreak extends Model
{
    protected $table = 'delivery_man_streaks';

    protected $fillable = [
        'delivery_man_id',
        'current_streak',
        'longest_streak',
        'last_active_date',
    ];

    protected $casts = [
        'current_streak'   => 'integer',
        'longest_streak'   => 'integer',
        'last_active_date' => 'date',
    ];

    public function deliveryMan()
    {
        return $this->belongsTo(DeliveryMan::class);
    }

    /**
     * Actualiza (o crea) la racha del repartidor cuando se entrega un pedido.
     * Se llama al marcar un pedido como 'delivered'.
     */
    public static function updateForDeliveryMan(int $deliveryManId): void
    {
        $today  = Carbon::today()->toDateString();
        $streak = static::firstOrCreate(
            ['delivery_man_id' => $deliveryManId],
            ['current_streak' => 0, 'longest_streak' => 0, 'last_active_date' => null]
        );

        $lastActive = $streak->last_active_date;

        if ($lastActive === null) {
            // Primera vez que entrega
            $streak->current_streak  = 1;
        } elseif ($lastActive->toDateString() === $today) {
            // Ya entregó hoy — no modificar la racha
            return;
        } elseif ($lastActive->toDateString() === Carbon::yesterday()->toDateString()) {
            // Entregó ayer → racha continua
            $streak->current_streak += 1;
        } else {
            // Hubo un día o más sin entregas → reiniciar
            $streak->current_streak = 1;
        }

        if ($streak->current_streak > $streak->longest_streak) {
            $streak->longest_streak = $streak->current_streak;
        }

        $streak->last_active_date = $today;
        $streak->save();
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\Cart;
use App\Models\User;
use App\CentralLogics\Helpers;

class CartMarketingReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cart-marketing-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia recordatorios de marketing para usuarios con carritos pendientes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now();
        
        // Buscar a todos los usuarios con carritos (is_guest = 0) 
        // agrupando usando Eloquent para garantizar el casting correcto de fechas y zonas horarias
        $userCarts = Cart::where('is_guest', 0)
            ->get()
            ->groupBy('user_id');

        foreach ($userCarts as $userId => $carts) {
            $lastUpdated = $carts->max('updated_at');
            $totalItems = $carts->count();
            $item_id = $carts->first()->item_id;

            // Cargar el nombre del articulo (usamos el primero que encontramos)
            $item_name = 'tus productos';
            $item = \App\Models\Item::find($item_id);
            if ($item) {
                $item_name = $item->name;
                if ($totalItems > 1) {
                    $item_name .= ' y otros productos';
                }
            }

            $user = User::find($userId);
            if (!$user || !$user->cm_firebase_token) {
                continue;
            }

            $diffInMinutes = abs((int) $now->diffInMinutes($lastUpdated));

            // Logica Aviso 1 (3 a 10 minutos de inactividad)
            if ($diffInMinutes >= 3 && $diffInMinutes <= 10) {
                $cacheKey = 'cart_reminder_1_' . $user->id;
                
                if (!Cache::has($cacheKey)) {
                    Cache::put($cacheKey, true, now()->addHours(24));
                    
                    $data = [
                        'title' => '¡No lo dejes escapar!',
                        'description' => "Disfruta de {$item_name}, estás muy cerca de que sean tuyos.",
                        'order_id' => '',
                        'image' => $item ? $item->image_full_url : '',
                        'type' => 'cart_reminder'
                    ];

                    Helpers::send_push_notif_to_device($user->cm_firebase_token, $data);
                    
                    // Opcional: Guardar en notificaciones de usuario
                    DB::table('user_notifications')->insert([
                        'data' => json_encode($data),
                        'user_id' => $user->id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            // Logica Aviso 2 (2 a 4 horas de inactividad)
            if ($diffInMinutes >= 120 && $diffInMinutes <= 240) {
                $cacheKey = 'cart_reminder_2_' . $user->id;
                
                if (!Cache::has($cacheKey)) {
                    Cache::put($cacheKey, true, now()->addHours(24));
                    
                    $data = [
                        'title' => '¡Tenemos un descuento para ti!',
                        'description' => "Disfruta de descuentos exclusivos para ti y pide {$item_name}.",
                        'order_id' => '',
                        'image' => $item ? $item->image_full_url : '',
                        'type' => 'cart_reminder'
                    ];

                    Helpers::send_push_notif_to_device($user->cm_firebase_token, $data);
                    
                    DB::table('user_notifications')->insert([
                        'data' => json_encode($data),
                        'user_id' => $user->id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        }
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    /**
     * Send push notification via Firebase Cloud Messaging
     */
    public static function send(array $payload)
    {
        $serverKey = config('services.firebase.server_key');

        if (!$serverKey) {
            Log::warning('Firebase server key not configured');
            return null;
        }

        try {
            return Http::withHeaders([
                'Authorization' => 'key=' . $serverKey,
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', $payload);
        } catch (\Exception $e) {
            Log::error('FCM send failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Send notification when driver accepts trip
     */
    public static function sendDriverAcceptedNotification($trip)
    {
        $user = $trip->user;

        if (!$user || !$user->cm_firebase_token) {
            Log::info('User has no FCM token or user not found');
            return null;
        }

        $driverName = $trip->driver->f_name ?? 'Tu conductor';

        return self::send([
            'to' => $user->cm_firebase_token,
            'notification' => [
                'title' => '🚗 ¡Conductor en camino!',
                'body' => $driverName . ' llegará en ' . ($trip->eta_minutes ?? 'unos') . ' minutos',
                'sound' => 'driver_found.wav',
                'android_channel_id' => 'driver_found_channel',
            ],
            'data' => [
                'type' => 'driver_accepted',
                'ride_id' => (string) $trip->id,
                'driver_name' => $driverName,
                'eta_minutes' => (string) ($trip->eta_minutes ?? 0),
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                'sound' => 'driver_found.wav',
                'channel_id' => 'driver_found_channel',
            ],
            'priority' => 'high',
        ]);
    }

    /**
     * Send notification when search times out (no driver found)
     */
    public static function sendSearchTimeoutNotification($trip)
    {
        $user = $trip->user;

        if (!$user || !$user->cm_firebase_token) {
            return null;
        }

        return self::send([
            'to' => $user->cm_firebase_token,
            'notification' => [
                'title' => '🚫 Sin conductores disponibles',
                'body' => 'Lamentablemente no encontramos un conductor cerca en este momento.',
                'sound' => 'no_driver.wav',
                'android_channel_id' => 'no_driver_channel',
            ],
            'data' => [
                'type' => 'ride_timeout',
                'ride_id' => (string) $trip->id,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                'sound' => 'no_driver.wav',
                'channel_id' => 'no_driver_channel',
            ],
            'priority' => 'high',
        ]);
    }

    /**
     * Send notification when driver is arriving at pickup
     */
    public static function sendDriverArrivingNotification($trip)
    {
        $user = $trip->user;
        if (!$user || !$user->cm_firebase_token)
            return null;

        $driverName = $trip->driver->f_name ?? 'Tu conductor';

        return self::send([
            'to' => $user->cm_firebase_token,
            'notification' => [
                'title' => '🚕 Conductor cerca',
                'body' => $driverName . ' está muy cerca de tu ubicación',
                'sound' => 'default',
            ],
            'data' => [
                'type' => 'driver_arriving',
                'ride_id' => (string) $trip->id,
            ],
            'priority' => 'high',
        ]);
    }

    /**
     * Send notification when driver arrives at pickup
     */
    public static function sendDriverArrivedNotification($trip)
    {
        $user = $trip->user;
        if (!$user || !$user->cm_firebase_token)
            return null;

        $driverName = $trip->driver->f_name ?? 'Tu conductor';

        return self::send([
            'to' => $user->cm_firebase_token,
            'notification' => [
                'title' => '✅ Conductor llegó',
                'body' => $driverName . ' está esperando en el punto de encuentro',
                'sound' => 'default',
            ],
            'data' => [
                'type' => 'driver_arrived',
                'ride_id' => (string) $trip->id,
            ],
            'priority' => 'high',
        ]);
    }

    /**
     * Send notification when ride is completed
     */
    public static function sendRideCompletedNotification($trip)
    {
        $user = $trip->user;
        if (!$user || !$user->cm_firebase_token)
            return null;

        return self::send([
            'to' => $user->cm_firebase_token,
            'notification' => [
                'title' => '🏁 Viaje completado',
                'body' => 'Esperamos que hayas disfrutado tu viaje. ¡Califica a tu conductor!',
                'sound' => 'default',
            ],
            'data' => [
                'type' => 'ride_completed',
                'ride_id' => (string) $trip->id,
                'final_fare' => (string) $trip->final_fare,
            ],
            'priority' => 'high',
        ]);
    }

    /**
     * Send notification when ride is cancelled (notifies user)
     */
    public static function sendRideCancelledNotification($trip, $by = 'driver')
    {
        $user = $trip->user;
        if (!$user || !$user->cm_firebase_token)
            return null;

        $title = '🚫 Viaje cancelado';
        $body = ($by === 'driver') ? 'Tu conductor ha cancelado el viaje.' : 'El viaje ha sido cancelado.';

        return self::send([
            'to' => $user->cm_firebase_token,
            'notification' => [
                'title' => $title,
                'body' => $body,
                'sound' => 'default',
            ],
            'data' => [
                'type' => 'ride_cancelled',
                'ride_id' => (string) $trip->id,
            ],
            'priority' => 'high',
        ]);
    }

    /**
     * Send notification when ride is cancelled by user (notifies driver)
     */
    public static function sendRideCancelledByUserNotification($trip)
    {
        $driver = $trip->driver;
        if (!$driver || !$driver->cm_firebase_token)
            return null;

        return self::send([
            'to' => $driver->cm_firebase_token,
            'notification' => [
                'title' => '🚫 Viaje cancelado por el cliente',
                'body' => 'El cliente ha cancelado el viaje solicitado.',
                'sound' => 'default',
            ],
            'data' => [
                'type' => 'ride_cancelled_by_user',
                'ride_id' => (string) $trip->id,
            ],
            'priority' => 'high',
        ]);
    }

    /**
     * Send SOS alert notification to all admins via topic
     */
    public static function sendSOSAlertToAdminNotification($alert)
    {
        $user = $alert->user;
        $userName = $user->f_name . ' ' . $user->l_name;
        $alertType = ($alert->alert_type === 'emergency') ? '⚠️ EMERGENCIA CRÍTICA (911)' : '🚨 ALERTA DE INSEGURIDAD';

        $data = [
            'title' => $alertType,
            'description' => $userName . " ha activado una alerta SOS en el viaje #" . $alert->taxi_ride_id,
            'order_id' => $alert->taxi_ride_id,
            'image' => '',
            'type' => 'taxi_sos',
            'alert_id' => (string) $alert->id,
        ];

        // Send to admin topic
        return \App\CentralLogics\Helpers::send_push_notif_to_topic($data, 'admin_message', 'taxi_sos');
    }

    /**
     * Send notification to a driver when a new taxi ride request is available nearby
     */
    public static function sendNewTaxiRideRequestNotification($trip, $driverToken)
    {
        if (!$driverToken) {
            return null;
        }

        $pickupAddress = $trip->pickup_address ?? 'Origen no especificado';
        $fare = number_format($trip->estimated_fare, 2);

        return self::send([
            'to' => $driverToken,
            'notification' => [
                'title' => '🚗 ¡Nuevo Viaje Disponible! ($' . $fare . ')',
                'body' => 'Recoger en: ' . $pickupAddress,
                'sound' => 'order_request.wav',
                'android_channel_id' => 'order_request_channel',
            ],
            'data' => [
                'type' => 'taxi_request',
                'title' => '🚗 ¡Nuevo Viaje Disponible!',
                'body' => 'Recoger en: ' . $pickupAddress,
                'order_id' => (string) $trip->id,
                'ride_id' => (string) $trip->id,
                'module_type' => 'taxi',
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                'sound' => 'order_request.wav',
                'channel_id' => 'order_request_channel',
            ],
            'priority' => 'high',
        ]);
    }
}

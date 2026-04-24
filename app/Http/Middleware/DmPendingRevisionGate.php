<?php

namespace App\Http\Middleware;

use App\Models\DeliveryMan;
use Closure;
use Illuminate\Http\Request;

/**
 * Repartidor en revisión de registro: solo perfil, actualización de perfil,
 * FCM y reenvío de solicitud. El resto de la API queda bloqueado hasta aprobación.
 */
class DmPendingRevisionGate
{
    private const ALLOWED_PATHS = [
        'api/v1/delivery-man/profile',
        'api/v1/delivery-man/update-profile',
        'api/v1/delivery-man/submit-registration-revision',
        'api/v1/delivery-man/update-fcm-token',
    ];

    public function handle(Request $request, Closure $next)
    {
        if (!$request->has('token') && $request->hasHeader('Authorization')) {
            $request->merge(['token' => str_replace('Bearer ', '', $request->header('Authorization'))]);
        }

        $token = $request->input('token');
        if (!$token) {
            return response()->json(['errors' => [['code' => 'auth-001', 'message' => translate('messages.unauthorized')]]], 401);
        }

        $dm = DeliveryMan::where('auth_token', $token)->first();
        if (!$dm) {
            return response()->json(['errors' => [['code' => 'auth-001', 'message' => translate('messages.unauthorized')]]], 401);
        }

        $app = strtolower(trim((string) ($dm->application_status ?? '')));
        if ($app === '' || ! in_array($app, ['approved', 'denied', 'pending'], true)) {
            $app = 'pending';
        }

        if ($app === 'approved') {
            return $next($request);
        }

        if ($app === 'denied') {
            return response()->json([
                'errors' => [[
                    'code' => 'auth-003',
                    'message' => translate('messages.dm_push_registration_denied_body'),
                ]],
            ], 403);
        }

        if ($app === 'pending' && $dm->registration_revision_allowed) {
            $path = $request->path();
            foreach (self::ALLOWED_PATHS as $allowed) {
                if ($path === $allowed) {
                    return $next($request);
                }
            }

            return response()->json([
                'errors' => [[
                    'code' => 'revision-only',
                    'message' => translate('messages.dm_registration_revision_only_access'),
                ]],
            ], 403);
        }

        // Registro enviado, pendiente de aprobación (sin flujo de revisión): mapa + perfil + ubicación + FCM.
        if ($app === 'pending' && ! $dm->registration_revision_allowed) {
            $path = $request->path();
            $browsePaths = [
                'api/v1/delivery-man/profile',
                'api/v1/delivery-man/update-fcm-token',
                'api/v1/delivery-man/record-location-data',
            ];
            foreach ($browsePaths as $allowed) {
                if ($path === $allowed) {
                    return $next($request);
                }
            }

            return response()->json([
                'errors' => [[
                    'code' => 'registration-pending',
                    'message' => translate('messages.dm_registration_pending_no_action'),
                ]],
            ], 403);
        }

        return response()->json([
            'errors' => [[
                'code' => 'auth-003',
                'message' => translate('messages.Your_account_is_not_approved_yet.'),
            ]],
        ], 403);
    }
}

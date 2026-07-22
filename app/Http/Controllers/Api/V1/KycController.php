<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DeliveryMan;
use App\Models\User;
use App\Models\UserInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class KycController extends Controller
{
    /**
     * Webhook receptor de MetaMap/Mati.
     * MetaMap envía un POST payload cuando el estado de una verificación cambia.
     *
     * Lookup order (de más a menos confiable):
     *  1. users.metamap_verification_id = verificationId
     *  2. users.phone = metadata.userId  (la app envía el teléfono como userId)
     *  3. delivery_man.phone = metadata.userId → UserInfo → User
     */
    public function webhook(Request $request)
    {
        Log::info('MetaMap Webhook Recibido:', $request->all());

        $eventName      = $request->input('eventName');
        $verificationId = $request->input('verificationId') ?? $request->input('id');

        // MetaMap envía el identificador externo en metadata.user_id o metadata.userId
        // La app del repartidor envía el TELÉFONO como userId
        $metaUserId = $request->input('metadata.user_id')
            ?? $request->input('metadata.userId')
            ?? null;

        $user = null;

        // 1. Buscar por verification_id guardado previamente (más confiable)
        if ($verificationId) {
            $user = User::where('metamap_verification_id', $verificationId)->first();
        }

        // 2. Buscar por teléfono en tabla users
        if (!$user && $metaUserId) {
            $user = User::where('phone', $metaUserId)->first();
        }

        // 3. Buscar por teléfono en delivery_man → UserInfo → User
        if (!$user && $metaUserId) {
            $dm = DeliveryMan::withoutGlobalScopes()->where('phone', $metaUserId)->first();
            if ($dm) {
                $user = $this->findUserForDeliveryMan($dm);
            }
        }

        if (!$user) {
            Log::warning("MetaMap Webhook: No se encontró usuario para verificationId={$verificationId}, metaUserId={$metaUserId}");
            // Retornamos 200 para que MetaMap no reintente indefinidamente
            return response()->json(['message' => 'User not found — webhook acknowledged'], 200);
        }

        // Guardar el verification ID para futuros lookups
        if ($verificationId && !$user->metamap_verification_id) {
            $user->metamap_verification_id = $verificationId;
        }

        // Determinar nuevo estado según el payload de MetaMap
        $status         = strtolower((string) $request->input('status', ''));
        $identityStatus = strtolower((string) ($request->input('identityStatus', '')));

        if (
            $eventName === 'verification_completed'
            || in_array($status, ['verified', 'completed'])
        ) {
            if ($identityStatus === 'verified' || $request->input('verified') === true) {
                $user->identity_verified = 'approved';
                Log::info("KYC: Usuario {$user->id} ({$user->phone}) → approved vía MetaMap webhook.");
            } else {
                $user->identity_verified = 'rejected';
                Log::warning("KYC: Usuario {$user->id} ({$user->phone}) → rejected vía MetaMap webhook.");
            }
        } elseif (in_array($status, ['pending', 'started']) || $eventName === 'verification_started') {
            $user->identity_verified = 'pending';
        }

        $user->save();

        return response()->json(['message' => 'Webhook processed successfully'], 200);
    }

    /**
     * Endpoint para clientes (app usuario): registrar inicio de verificación MetaMap.
     * Ruta: POST /api/v1/customer/kyc/start  (autenticado con auth:api)
     */
    public function startVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'verification_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $user = $request->user();
        $user->identity_verified        = 'pending';
        $user->metamap_verification_id  = $request->verification_id;
        $user->save();

        return response()->json([
            'message'                  => 'Verification status updated to pending',
            'identity_verified'        => $user->identity_verified,
            'metamap_verification_id'  => $user->metamap_verification_id,
        ], 200);
    }

    /**
     * Endpoint para repartidores (app repartidor): registrar inicio de verificación MetaMap.
     * Ruta: POST /api/v1/delivery-man/kyc/start  (autenticado con dm.api)
     *
     * Lógica de verificación cruzada:
     *   Si el User vinculado al repartidor ya tiene identity_verified = 'approved'
     *   (p. ej. ya se verificó desde la app de usuario con el mismo teléfono),
     *   retorna 'approved' automáticamente sin necesitar repetir MetaMap.
     */
    public function startVerificationDm(Request $request)
    {
        $dm = DeliveryMan::where('auth_token', $request->token ?? $request->bearerToken())->first();

        if (!$dm) {
            return response()->json([
                'errors' => [['code' => 'auth-001', 'message' => 'No autorizado']],
            ], 401);
        }

        // Buscar el User vinculado al repartidor
        $user = $this->findUserForDeliveryMan($dm);

        // ─── Verificación cruzada ──────────────────────────────────────────
        // Si ya está aprobado en su cuenta de usuario → no necesita MetaMap de nuevo
        if ($user && $user->identity_verified === 'approved') {
            Log::info("KYC cross-check: DM {$dm->id} ({$dm->phone}) ya tiene identity_verified=approved en User {$user->id}. Sin acción.");
            return response()->json([
                'message'                 => 'Tu identidad ya está verificada.',
                'identity_verified'       => 'approved',
                'metamap_verification_id' => $user->metamap_verification_id,
                'cross_verified'          => true,
            ], 200);
        }
        // ──────────────────────────────────────────────────────────────────

        $validator = Validator::make($request->all(), [
            'verification_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Si no hay User vinculado, no podemos guardar identity_verified
        if (!$user) {
            Log::warning("KYC startVerificationDm: DM {$dm->id} ({$dm->phone}) no tiene User vinculado. No se puede guardar estado KYC.");
            return response()->json([
                'errors' => [['code' => 'kyc-001', 'message' => 'No se encontró cuenta de usuario vinculada al repartidor.']],
            ], 422);
        }

        $user->identity_verified       = 'pending';
        $user->metamap_verification_id = $request->verification_id;
        $user->save();

        Log::info("KYC startVerificationDm: DM {$dm->id} ({$dm->phone}) → pending. verificationId={$request->verification_id}");

        return response()->json([
            'message'                  => 'Verification status updated to pending',
            'identity_verified'        => $user->identity_verified,
            'metamap_verification_id'  => $user->metamap_verification_id,
            'cross_verified'           => false,
        ], 200);
    }

    /**
     * Encuentra el modelo User vinculado a un DeliveryMan.
     *
     * Orden de búsqueda:
     *  1. UserInfo.deliveryman_id → User.id   (relación canónica)
     *  2. users.phone = delivery_man.phone     (fallback por teléfono)
     *  3. users.email = delivery_man.email     (fallback por email)
     */
    protected function findUserForDeliveryMan(DeliveryMan $dm): ?User
    {
        // 1. Vía UserInfo (relación establecida)
        $userInfo = UserInfo::where('deliveryman_id', $dm->id)->first();
        if ($userInfo) {
            $user = User::find($userInfo->user_id);
            if ($user) return $user;
        }

        // 2. Por teléfono
        if ($dm->phone) {
            $user = User::where('phone', $dm->phone)->first();
            if ($user) return $user;
        }

        // 3. Por email
        if ($dm->email) {
            $user = User::where('email', $dm->email)->first();
            if ($user) return $user;
        }

        return null;
    }
}

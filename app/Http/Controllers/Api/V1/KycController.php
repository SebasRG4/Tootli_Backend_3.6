<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class KycController extends Controller
{
    /**
     * Webhook receptor de MetaMap/Mati.
     * MetaMap envía un POST payload cuando el estado de una verificación cambia.
     */
    public function webhook(Request $request)
    {
        Log::info('MetaMap Webhook Recibido:', $request->all());

        // El eventName nos indica si finalizó la verificación (e.g., 'verification_completed')
        $eventName = $request->input('eventName');
        $resource = $request->input('resource');

        if (!$resource) {
            return response()->json(['message' => 'No resource found'], 400);
        }

        // Obtener el ID de la verificación de MetaMap y el ID de usuario asociado (metadata)
        $verificationId = $request->input('verificationId') ?? $request->input('id');
        
        // MetaMap permite enviar metadata en el flujo de verificación.
        // Asumimos que pasaremos 'user_id' como metadata externa ('metadata.user_id')
        $userId = $request->input('metadata.user_id') ?? $request->input('metadata.userId');

        // Si no viene en metadata, intentamos buscar el usuario por el metamap_verification_id guardado
        if (!$userId && $verificationId) {
            $user = User::where('metamap_verification_id', $verificationId)->first();
        } else if ($userId) {
            $user = User::find($userId);
        } else {
            $user = null;
        }

        if (!$user) {
            Log::warning("MetaMap Webhook: No se encontró usuario para verificationId: {$verificationId} o userId: {$userId}");
            return response()->json(['message' => 'User not found'], 404);
        }

        // Procesamos según el estado del documento/verificación
        // MetaMap envía el status en step/flow o en el root (e.g. 'status': 'verified' o 'rejected')
        $status = strtolower($request->input('status') ?? '');
        
        if ($eventName === 'verification_completed' || $status === 'verified' || $status === 'completed') {
            // Revisamos si la verificación fue exitosa (approved) o fallida (rejected)
            // MetaMap usualmente tiene una propiedad 'identityStatus' o 'verified'
            $identityStatus = $request->input('identityStatus') ?? '';
            
            if ($identityStatus === 'verified' || $request->input('verified') === true) {
                $user->identity_verified = 'approved';
                Log::info("Usuario {$user->id} verificado con éxito vía MetaMap.");
            } else {
                $user->identity_verified = 'rejected';
                Log::warning("Usuario {$user->id} rechazado vía MetaMap.");
            }
        } else if ($status === 'pending' || $status === 'started') {
            $user->identity_verified = 'pending';
        }

        // Guardamos el ID de verificación para auditoría
        if ($verificationId) {
            $user->metamap_verification_id = $verificationId;
        }

        $user->save();

        return response()->json(['message' => 'Webhook processed successfully'], 200);
    }

    /**
     * Endpoint opcional para iniciar o registrar que el usuario comenzó el flujo de verificación.
     * Guarda el metamap_verification_id temporalmente en el usuario.
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
        $user->identity_verified = 'pending';
        $user->metamap_verification_id = $request->verification_id;
        $user->save();

        return response()->json([
            'message' => 'Verification status updated to pending',
            'identity_verified' => $user->identity_verified,
            'metamap_verification_id' => $user->metamap_verification_id
        ], 200);
    }
}

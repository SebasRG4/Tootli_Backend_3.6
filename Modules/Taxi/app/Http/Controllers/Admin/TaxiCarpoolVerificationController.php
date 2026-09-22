<?php

namespace Modules\Taxi\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Taxi\Models\UserCommunityVerification;
use Modules\Taxi\Models\TaxiCommunityOrganization;
use Brian2694\Toastr\Facades\Toastr;

class TaxiCarpoolVerificationController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $status = $request->status ?? 'all';
        $orgId = $request->organization_id;
        $docType = $request->document_type;

        $stats = [
            'total' => UserCommunityVerification::count(),
            'pending' => UserCommunityVerification::pending()->count(),
            'approved' => UserCommunityVerification::approved()->count(),
            'rejected' => UserCommunityVerification::rejected()->count(),
        ];

        $verifications = UserCommunityVerification::with(['user', 'deliveryMan', 'organization'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('document_number', 'like', "%{$search}%")
                        ->orWhere('institutional_email', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($uq) use ($search) {
                            $uq->where('f_name', 'like', "%{$search}%")
                                ->orWhere('l_name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        })
                        ->orWhereHas('organization', function ($oq) use ($search) {
                            $oq->where('name', 'like', "%{$search}%")
                                ->orWhere('short_name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status && $status !== 'all', function ($query) use ($status) {
                $query->where('verification_status', $status);
            })
            ->when($orgId, function ($query) use ($orgId) {
                $query->where('organization_id', $orgId);
            })
            ->when($docType, function ($query) use ($docType) {
                $query->where('document_type', $docType);
            })
            ->orderByRaw("CASE WHEN verification_status = 'pending' THEN 1 WHEN verification_status = 'approved' THEN 2 ELSE 3 END")
            ->latest()
            ->paginate(config('default_pagination', 20));

        $organizations = TaxiCommunityOrganization::where('is_active', true)->orderBy('name', 'asc')->get();

        return view('admin-views.taxi.carpool.verification-list', compact(
            'verifications',
            'stats',
            'search',
            'status',
            'orgId',
            'docType',
            'organizations'
        ));
    }

    public function show($id)
    {
        $verification = UserCommunityVerification::with(['user', 'deliveryMan', 'organization'])->findOrFail($id);
        return view('admin-views.taxi.carpool.verification-details', compact('verification'));
    }

    public function updateStatus(Request $request, $id)
    {
        $verification = UserCommunityVerification::findOrFail($id);

        $request->validate([
            'status' => 'required|in:approved,rejected,pending',
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $verification->verification_status = $request->status;
        $verification->rejection_reason = $request->status === 'rejected' ? $request->rejection_reason : null;
        $verification->verified_at = $request->status === 'approved' ? now() : null;
        $verification->save();

        if ($request->status === 'approved') {
            Toastr::success('¡Credencial / Documento verificado y aprobado con éxito!');

            if ($verification->user && !empty($verification->user->cm_firebase_token)) {
                $orgName = $verification->organization?->name ?? 'tu comunidad';
                $data = [
                    'title' => '¡Tu acreditación Carpool fue Aprobada! 🎉',
                    'description' => "Tu credencial para {$orgName} ha sido verificada. Ya puedes reservar y publicar viajes.",
                    'order_id' => '',
                    'image' => '',
                    'type' => 'carpool_verified',
                ];
                try {
                    \App\CentralLogics\Helpers::send_push_notif_to_device($verification->user->cm_firebase_token, $data);
                } catch (\Throwable $e) {}
            }
        } elseif ($request->status === 'rejected') {
            Toastr::warning('La verificación de la credencial ha sido rechazada.');

            if ($verification->user && !empty($verification->user->cm_firebase_token)) {
                $reason = $request->rejection_reason ? ": {$request->rejection_reason}" : '';
                $data = [
                    'title' => 'Actualización de verificación Carpool',
                    'description' => "Tu credencial requiere atención{$reason}. Puedes volver a subirla en la app.",
                    'order_id' => '',
                    'image' => '',
                    'type' => 'carpool_rejected',
                ];
                try {
                    \App\CentralLogics\Helpers::send_push_notif_to_device($verification->user->cm_firebase_token, $data);
                } catch (\Throwable $e) {}
            }
        } else {
            Toastr::info('Estado de verificación actualizado a pendiente.');
        }

        return redirect()->back();
    }

    public function reanalyzeAi($id)
    {
        $verification = UserCommunityVerification::with(['user', 'organization'])->findOrFail($id);

        if (!$verification->id_card_image) {
            Toastr::error('No hay fotografía frontal registrada para auditar.');
            return redirect()->back();
        }

        $aiResult = \Modules\Taxi\Services\CredentialAiVerificationService::analyze(
            user: $verification->user,
            organization: $verification->organization,
            frontImage: $verification->id_card_image,
            backImage: $verification->id_card_back_image,
            documentType: $verification->document_type,
            documentNumber: $verification->document_number
        );

        $verification->ai_verified = !empty($aiResult['ai_verified']);
        $verification->ai_confidence_score = $aiResult['confidence_score'] ?? null;
        $verification->ai_extracted_data = $aiResult['extracted_data'] ?? null;
        $verification->ai_review_notes = $aiResult['notes'] ?? null;

        if (!empty($aiResult['is_approved'])) {
            $verification->verification_status = 'approved';
            $verification->verified_at = now();
            Toastr::success('¡Auditoría de IA completada y aprobada automáticamente!');

            if ($verification->user && !empty($verification->user->cm_firebase_token)) {
                $orgName = $verification->organization?->name ?? 'tu comunidad';
                $data = [
                    'title' => '¡Tu acreditación Carpool fue Aprobada! 🎉',
                    'description' => "Tu credencial para {$orgName} ha sido validada con éxito. Ya puedes reservar y publicar viajes.",
                    'order_id' => '',
                    'image' => '',
                    'type' => 'carpool_verified',
                ];
                try {
                    \App\CentralLogics\Helpers::send_push_notif_to_device($verification->user->cm_firebase_token, $data);
                } catch (\Throwable $e) {}
            }
        } else {
            Toastr::info('Auditoría ejecutada: ' . ($aiResult['notes'] ?? 'Revisar detalles'));
        }

        $verification->save();
        return redirect()->back();
    }
}

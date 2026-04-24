<?php

namespace App\Services\OrderCancel;

use App\CentralLogics\Helpers;
use App\Models\OrderCancelReason;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DeliveryOrderCancelMetadata
{
    /**
     * @return array{cancel_reason_id: ?int, display_reason: string, evidence: array}|JsonResponse
     */
    public function resolve(Request $request): array|JsonResponse
    {
        $requireId = (bool) config('dm_delivery_cancel.require_cancel_reason_id', true);
        $allowLegacy = (bool) config('dm_delivery_cancel.allow_legacy_free_text', true);
        $maxPhotos = max(0, (int) config('dm_delivery_cancel.max_cancel_evidence_photos', 3));
        $maxPhotoKb = max(100, (int) config('dm_delivery_cancel.max_photo_kb', 4096));
        $maxAudioKb = max(100, (int) (config('dm_delivery_cancel.max_audio_bytes', 5242880) / 1024));

        $rules = [
            'cancel_lat' => 'nullable|numeric|between:-90,90',
            'cancel_lng' => 'nullable|numeric|between:-180,180',
            'cancellation_detail' => 'nullable|string|max:2000',
            'cancel_evidence' => 'nullable|array|max:'.$maxPhotos,
            'cancel_evidence.*' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:'.$maxPhotoKb,
            'cancel_audio' => 'nullable|file|max:'.$maxAudioKb,
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $cancelReasonId = null;
        $displayReason = '';

        if ($request->filled('cancel_reason_id')) {
            $row = OrderCancelReason::query()
                ->whereKey((int) $request['cancel_reason_id'])
                ->where('user_type', 'deliveryman')
                ->where('status', 1)
                ->first();
            if (! $row) {
                return response()->json([
                    'errors' => [['code' => 'cancel_reason', 'message' => translate('messages.dm_cancel_reason_invalid')]],
                ], 403);
            }
            $cancelReasonId = (int) $row->id;
            $displayReason = (string) OrderCancelReason::withoutGlobalScope('translate')
                ->whereKey($row->id)
                ->value('reason');
        } elseif ($requireId && ! $allowLegacy) {
            return response()->json([
                'errors' => [['code' => 'cancel_reason', 'message' => translate('messages.dm_cancel_reason_required')]],
            ], 403);
        } elseif ($request->filled('reason')) {
            $displayReason = (string) $request['reason'];
        } elseif ($requireId) {
            return response()->json([
                'errors' => [['code' => 'cancel_reason', 'message' => translate('messages.dm_cancel_reason_required')]],
            ], 403);
        } else {
            $displayReason = translate('messages.dm_cancel_reason_unspecified');
        }

        $evidence = $this->buildEvidencePayload($request);

        return [
            'cancel_reason_id' => $cancelReasonId,
            'display_reason' => $displayReason,
            'evidence' => $evidence,
        ];
    }

    private function buildEvidencePayload(Request $request): array
    {
        $out = [
            'lat' => $request->filled('cancel_lat') ? (float) $request['cancel_lat'] : null,
            'lng' => $request->filled('cancel_lng') ? (float) $request['cancel_lng'] : null,
            'photos' => [],
            'audio' => null,
        ];

        if ($request->hasFile('cancel_evidence')) {
            foreach ($request->file('cancel_evidence', []) as $file) {
                if ($file === null) {
                    continue;
                }
                $name = Helpers::upload('order-cancel/', 'png', $file);
                $out['photos'][] = ['img' => $name, 'storage' => Helpers::getDisk()];
            }
        }

        if ($request->hasFile('cancel_audio')) {
            $file = $request->file('cancel_audio');
            $ext = $file->getClientOriginalExtension() ?: 'bin';
            $safeExt = preg_match('/^[a-z0-9]{1,8}$/i', $ext) ? strtolower($ext) : 'bin';
            $dir = 'order-cancel-audio/';
            if (! Storage::disk(Helpers::getDisk())->exists($dir)) {
                Storage::disk(Helpers::getDisk())->makeDirectory($dir);
            }
            $audioName = now()->toDateString().'-'.uniqid('', true).'.'.$safeExt;
            Storage::disk(Helpers::getDisk())->putFileAs($dir, $file, $audioName);
            $out['audio'] = ['img' => $audioName, 'storage' => Helpers::getDisk()];
        }

        return $out;
    }
}

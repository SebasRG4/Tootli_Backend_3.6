<?php

$envBool = static function (?string $value, bool $default = true): bool {
    if ($value === null || $value === '') {
        return $default;
    }

    return ! in_array(strtolower($value), ['0', 'false', 'no', 'off'], true);
};

return [
    /**
     * Si true, al cancelar (no parcel) el repartidor debe enviar cancel_reason_id (tabla order_cancel_reasons, user_type=deliveryman).
     */
    'require_cancel_reason_id' => $envBool(env('DM_CANCEL_REQUIRE_REASON_ID'), true),

    /**
     * Si require_cancel_reason_id es true y esto es true, se acepta solo el campo legacy "reason" (texto) sin id (no encola revisión con catálogo).
     */
    'allow_legacy_free_text' => $envBool(env('DM_CANCEL_ALLOW_LEGACY_TEXT'), true),

    /** Máximo de fotos de evidencia al cancelar */
    'max_cancel_evidence_photos' => (int) env('DM_CANCEL_MAX_EVIDENCE_PHOTOS', 3),

    /** Tamaño máximo por foto (KB) */
    'max_photo_kb' => (int) env('DM_CANCEL_MAX_PHOTO_KB', 4096),

    /** Audio opcional (bytes) — 5 MB por defecto */
    'max_audio_bytes' => (int) env('DM_CANCEL_MAX_AUDIO_BYTES', 5242880),
];

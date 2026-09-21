<?php

namespace Modules\Taxi\Services;

use App\Models\BusinessSetting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\Taxi\Models\TaxiCommunityOrganization;

class CredentialAiVerificationService
{
    /**
     * Analiza una credencial o documento usando Google Gemini Vision (2.5 Flash).
     *
     * @param User $user
     * @param TaxiCommunityOrganization|null $organization
     * @param string|UploadedFile $frontImage Ruta relativa en storage public o UploadedFile
     * @param string|UploadedFile|null $backImage
     * @param string|null $documentType
     * @param string|null $documentNumber
     * @return array
     */
    public static function analyze(
        User $user,
        ?TaxiCommunityOrganization $organization,
        $frontImage,
        $backImage = null,
        ?string $documentType = 'credencial',
        ?string $documentNumber = null
    ): array {
        $apiKey = BusinessSetting::where('key', 'gemini_api_key')->value('value') 
            ?? env('GEMINI_API_KEY', 'AIzaSyAun36m_HffV9s-BoTD8f0gxcsvnJVvKac');

        if (empty($apiKey)) {
            Log::warning('[CredentialAi] Gemini API Key no configurada. Fallback a revisión manual.');
            return [
                'is_approved' => false,
                'confidence_score' => 0.0,
                'ai_verified' => false,
                'extracted_data' => null,
                'notes' => 'API Key de IA no configurada. Requiere revisión manual.',
                'rejection_reason' => null,
            ];
        }

        try {
            $parts = [];

            // 1. Contexto y Prompt estructurado
            $userName = trim("{$user->f_name} {$user->l_name}");
            $orgName = $organization ? ($organization->name . ' (' . ($organization->short_name ?? '') . ')') : 'Institución no catalogada';
            $docTypeLabel = match ($documentType) {
                'tira_materias' => 'Tira de Materias Oficial / Horario de Clases',
                'gafete' => 'Gafete o Identificación Laboral',
                'constancia' => 'Constancia Oficial de Estudios',
                default => 'Credencial de Estudiante',
            };

            $prompt = <<<PROMPT
Eres el sistema oficial de Inteligencia Artificial y Auditoría de Identidad para la plataforma de Carpool Seguro universitario "Tootli".
Tu objetivo es analizar la(s) fotografía(s) adjunta(s) de una credencial escolar, tira de materias o gafete oficial y determinar si pertenece al usuario registrado.

DATOS DEL USUARIO REGISTRADO:
- Nombre registrado en cuenta: "{$userName}"
- Institución / Universidad declarada: "{$orgName}"
- Tipo de documento declarado: "{$docTypeLabel}"
- Matrícula / Folio declarado por el usuario: "{$documentNumber}"

REGLAS DE EVALUACIÓN:
1. LEGITIMIDAD: ¿La imagen corresponde a un documento de identidad estudiantil, escolar o laboral creíble, nítido y legible?
2. NOMBRE: Extrae el nombre del titular en el documento. Compara contra "{$userName}". Considera variaciones normales (ej. omisión de un segundo nombre, orden apellido-nombre o abreviaturas menores). Asigna un puntaje de coincidencia de 0 a 100.
3. INSTITUCIÓN: Extrae la universidad, escuela o centro laboral (busca nombres, siglas como UNAM, IPN, UVM, UAEMex, ITESM, etc. o logotipos visibles). ¿Coincide con "{$orgName}"?
4. VIGENCIA: Busca fechas, años (2024, 2025, 2026), semestres, hologramas o sellos de ciclo escolar. ¿El documento parece vigente o actual?
5. MATRÍCULA: Extrae el número de cuenta / matrícula si es visible.
6. CRITERIO DE APROBACIÓN AUTOMÁTICA:
   - "is_approved" debe ser true SÓLO SI:
     a) Es un documento legítimo y legible.
     b) La coincidencia del nombre es >= 75%.
     c) La institución coincide o es muy afín.
     d) No muestra evidencia evidente de alteración digital o vencimiento grave.
   - En caso contrario "is_approved" debe ser false.

Genera ÚNICAMENTE un JSON con esta estructura exacta:
{
  "is_legitimate_document": true,
  "extracted_name": "Nombre exacto leído",
  "extracted_institution": "Institución leída",
  "extracted_matricula": "Matrícula leída o N/A",
  "extracted_validity": "Vigencia / ciclo leído",
  "name_match_score": 95,
  "institution_match": true,
  "is_currently_valid": true,
  "is_approved": true,
  "confidence_score": 0.95,
  "notes": "Resumen breve del análisis realizado en español (1-2 oraciones).",
  "rejection_reason": null
}
PROMPT;

            $parts[] = ['text' => $prompt];

            // 2. Procesar imagen frontal (obligatoria)
            $frontPart = self::imageToInlineData($frontImage);
            if ($frontPart) {
                $parts[] = $frontPart;
            } else {
                throw new \Exception('No se pudo codificar la imagen frontal');
            }

            // 3. Procesar imagen de reverso (opcional)
            if ($backImage) {
                $backPart = self::imageToInlineData($backImage);
                if ($backPart) {
                    $parts[] = $backPart;
                }
            }

            // 4. Llamada HTTP a Gemini 2.5 Flash
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}";

            $response = Http::timeout(25)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, [
                    'contents' => [
                        ['parts' => $parts]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.1,
                        'response_mime_type' => 'application/json',
                    ],
                ]);

            if (!$response->successful()) {
                Log::error('[CredentialAi] Error en respuesta de Gemini API: ' . $response->body());
                return [
                    'is_approved' => false,
                    'confidence_score' => 0.0,
                    'ai_verified' => false,
                    'extracted_data' => null,
                    'notes' => 'Error de conexión con servicio de IA. Pendiente de validación manual.',
                    'rejection_reason' => null,
                ];
            }

            $respData = $response->json();
            $candidateText = $respData['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
            $parsed = json_decode($candidateText, true);

            if (!is_array($parsed)) {
                Log::error('[CredentialAi] Respuesta no parseable de Gemini: ' . $candidateText);
                return [
                    'is_approved' => false,
                    'confidence_score' => 0.0,
                    'ai_verified' => false,
                    'extracted_data' => null,
                    'notes' => 'Respuesta no parseable de IA. Pendiente de validación manual.',
                    'rejection_reason' => null,
                ];
            }

            $isApproved = (bool) ($parsed['is_approved'] ?? false);
            $confidence = (float) ($parsed['confidence_score'] ?? 0.0);

            // Umbral estricto de seguridad: solo aprobamos si confianza >= 0.75 y name_match_score >= 70
            $finalApproved = $isApproved && $confidence >= 0.75 && ($parsed['name_match_score'] ?? 0) >= 70;

            return [
                'is_approved' => $finalApproved,
                'confidence_score' => $confidence,
                'ai_verified' => true,
                'extracted_data' => $parsed,
                'notes' => $parsed['notes'] ?? 'Análisis completado por IA.',
                'rejection_reason' => $finalApproved ? null : ($parsed['rejection_reason'] ?? null),
            ];

        } catch (\Throwable $e) {
            Log::error('[CredentialAi] Excepción durante análisis: ' . $e->getMessage());
            return [
                'is_approved' => false,
                'confidence_score' => 0.0,
                'ai_verified' => false,
                'extracted_data' => null,
                'notes' => 'Excepción en análisis de IA: ' . $e->getMessage(),
                'rejection_reason' => null,
            ];
        }
    }

    /**
     * Convierte una imagen (UploadedFile o ruta) a estructura inline_data para Gemini API.
     */
    protected static function imageToInlineData($image): ?array
    {
        try {
            if ($image instanceof UploadedFile) {
                $mimeType = $image->getMimeType() ?: 'image/jpeg';
                $data = base64_encode(file_get_contents($image->getRealPath()));
                return [
                    'inline_data' => [
                        'mime_type' => $mimeType,
                        'data' => $data,
                    ],
                ];
            }

            if (is_string($image)) {
                // Puede ser ruta en storage/app/public/community_cards/...
                if (Storage::disk('public')->exists('community_cards/' . $image)) {
                    $content = Storage::disk('public')->get('community_cards/' . $image);
                    $mime = Storage::disk('public')->mimeType('community_cards/' . $image) ?: 'image/jpeg';
                    return [
                        'inline_data' => [
                            'mime_type' => $mime,
                            'data' => base64_encode($content),
                        ],
                    ];
                }

                if (file_exists($image)) {
                    $mime = mime_content_type($image) ?: 'image/jpeg';
                    return [
                        'inline_data' => [
                            'mime_type' => $mime,
                            'data' => base64_encode(file_get_contents($image)),
                        ],
                    ];
                }
            }

            return null;
        } catch (\Throwable $e) {
            Log::error('[CredentialAi] Error al codificar imagen: ' . $e->getMessage());
            return null;
        }
    }
}

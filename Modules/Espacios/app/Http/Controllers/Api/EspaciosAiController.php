<?php

namespace Modules\Espacios\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Modules\Espacios\Models\EspacioBooking;
use Modules\Espacios\Models\EspacioListing;

/**
 * EspaciosAiController — Integración con Tootli IA (Google Gemini) para Concierge y Asistente del Espacio.
 */
class EspaciosAiController extends Controller
{
    // ——————————————————————————————————————
    // POST /api/v1/espacios/ai/concierge
    // Asistente virtual para huéspedes de un espacio
    // ——————————————————————————————————————
    public function concierge(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'listing_id' => 'required|integer|exists:espacios_listings,id',
            'message'    => 'required|string|max:1000',
            'booking_id' => 'nullable|integer|exists:espacios_bookings,id',
            'history'    => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $listing = EspacioListing::with(['amenities', 'images'])->findOrFail($request->listing_id);

        $booking = null;
        if ($request->booking_id) {
            $booking = EspacioBooking::find($request->booking_id);
        }

        $userName = $request->user() ? ($request->user()->f_name ?? 'Huésped') : 'Huésped';
        $amenitiesText = $listing->amenities->pluck('name')->implode(', ');

        // Construir contexto rico del espacio
        $context = "Eres 'Tootli Concierge IA', el asistente virtual inteligente y hospitalario de Tootli para el espacio de alojamiento.\n";
        $context .= "Tu objetivo es resolver dudas del huésped con amabilidad, precisión, calidez y brevedad.\n\n";
        $context .= "DATOS DEL ESPACIO:\n";
        $context .= "- Nombre: {$listing->title}\n";
        $context .= "- Tipo: {$listing->type}\n";
        $context .= "- Ubicación: {$listing->address}, {$listing->city}, {$listing->state}\n";
        $context .= "- Capacidad: {$listing->max_guests} personas, {$listing->num_rooms} habitaciones, {$listing->num_bathrooms} baños\n";
        $context .= "- Servicios/Amenidades: {$amenitiesText}\n";
        if ($listing->wifi_ssid) {
            $context .= "- Red WiFi: {$listing->wifi_ssid}\n";
            $context .= "- Contraseña WiFi: {$listing->wifi_password}\n";
        }
        if ($listing->access_instructions) {
            $context .= "- Instrucciones de acceso / Check-in: {$listing->access_instructions}\n";
        }
        if ($listing->house_rules) {
            $context .= "- Reglas de la casa: {$listing->house_rules}\n";
        }
        if ($listing->cancellation_policy) {
            $context .= "- Política de cancelación: {$listing->cancellation_policy}\n";
        }

        if ($booking) {
            $context .= "\nDATOS DE LA RESERVA ACTIVA:\n";
            $context .= "- Check-in: {$booking->check_in}\n";
            $context .= "- Check-out: {$booking->check_out}\n";
            $context .= "- Huéspedes: {$booking->guests}\n";
            if ($booking->digital_key_pin) {
                $context .= "- Código PIN cerradura digital: {$booking->digital_key_pin}\n";
            }
        }

        $context .= "\nINSTRUCCIONES DE RESPUESTA:\n";
        $context .= "- Responde en español amigable y conciso (máximo 2 párrafos breves o lista con viñetas claras).\n";
        $context .= "- Si te preguntan por WiFi, reglas o acceso, responde con los datos provistos arriba.\n";
        $context .= "- Si te preguntan por lugares para comer o qué hacer cerca, recomienda opciones típicas de {$listing->city} mencionando que pueden pedirlas o explorarlas en Tootli.\n";
        $context .= "- No inventes contraseñas o reglas no existentes; si algo no está especificado, sugiere contactar al anfitrión por el chat directo de Tootli.\n";

        $prompt = "{$context}\nPregunta del usuario ({$userName}): {$request->message}\nRespuesta:";

        $geminiKey = env('GEMINI_API_KEY') ?: env('GOOGLE_API_KEY');

        if (empty($geminiKey)) {
            return response()->json([
                'answer' => "¡Hola {$userName}! Actualmente estoy en modo de mantenimiento. Puedes escribirle directamente al anfitrión a través del chat de la app para resolver cualquier duda sobre {$listing->title}.",
                'suggested_followups' => ['¿Cómo hacer el check-in?', '¿Cuál es la clave de WiFi?', 'Reglas del espacio'],
            ]);
        }

        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->timeout(10)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$geminiKey}", [
                    'contents' => [
                        [
                            'role'  => 'user',
                            'parts' => [['text' => $prompt]],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature'     => 0.4,
                        'maxOutputTokens' => 500,
                    ],
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $answer = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

                return response()->json([
                    'answer' => trim($answer),
                    'suggested_followups' => [
                        '¿Cuál es la clave del WiFi?',
                        '¿Cuáles son las reglas de la casa?',
                        'Recomiéndame lugares para comer cerca',
                    ],
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Espacios AI Concierge Exception: ' . $e->getMessage());
        }

        return response()->json([
            'answer' => "No pude procesar tu solicitud con el asistente en este momento. Por favor utiliza el botón de chat directo con el anfitrión.",
            'suggested_followups' => ['Clave de WiFi', 'Instrucciones de llegada'],
        ]);
    }

    // ——————————————————————————————————————
    // POST /api/v1/espacios/ai/generate-description
    // Asistente para el host: genera título y descripción optimizada
    // ——————————————————————————————————————
    public function generateDescription(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type'        => 'required|string',
            'city'        => 'required|string',
            'highlights'  => 'nullable|string', // ej: "alberca, terraza panorámica, cerca del centro"
            'num_rooms'   => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $prompt = "Eres un experto en hospitalidad y marketing inmobiliario de Airbnb y Tootli.\n";
        $prompt .= "Genera un título atractivo (máximo 60 caracteres) y una descripción seductora para un espacio de renta con las siguientes características:\n";
        $prompt .= "- Tipo: {$request->type}\n";
        $prompt .= "- Ciudad: {$request->city}\n";
        if ($request->highlights) {
            $prompt .= "- Puntos clave / Amenidades: {$request->highlights}\n";
        }
        if ($request->num_rooms) {
            $prompt .= "- Habitaciones: {$request->num_rooms}\n";
        }
        $prompt .= "\nDevuelve únicamente un JSON válido con esta estructura exacta:\n";
        $prompt .= "{\n  \"title\": \"Título aquí\",\n  \"description\": \"Descripción completa estructurada en 2 o 3 párrafos cálidos aquí\"\n}";

        $geminiKey = env('GEMINI_API_KEY') ?: env('GOOGLE_API_KEY');
        if (empty($geminiKey)) {
            return response()->json([
                'title'       => ucfirst($request->type) . ' acogedor en ' . $request->city,
                'description' => "Hermoso espacio ubicado en una excelente zona de {$request->city}, ideal para descansar o trabajar con total comodidad.",
            ]);
        }

        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->timeout(10)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$geminiKey}", [
                    'contents' => [
                        [
                            'role'  => 'user',
                            'parts' => [['text' => $prompt]],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature'     => 0.7,
                        'maxOutputTokens' => 600,
                    ],
                ]);

            if ($response->successful()) {
                $text = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? '';
                // Limpiar posibles bloques ```json ```
                $clean = preg_replace('/```json\s*|\s*```/', '', trim($text));
                $decoded = json_decode($clean, true);

                if ($decoded && isset($decoded['title'])) {
                    return response()->json($decoded);
                }
            }
        } catch (\Exception $e) {
            Log::error('Espacios AI Description Exception: ' . $e->getMessage());
        }

        return response()->json([
            'title'       => ucfirst($request->type) . ' en ' . $request->city,
            'description' => "Disfruta de este increíble espacio en {$request->city} equipado para una estancia inolvidable.",
        ]);
    }
}

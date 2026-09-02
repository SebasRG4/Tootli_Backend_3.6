<?php

namespace Modules\AI\app\Http\Controllers\Admin\Web\LaboratorioIA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Brian2694\Toastr\Facades\Toastr;
use App\Models\BusinessSetting;

class LaboratorioIAController extends Controller
{
    /**
     * Vista principal del Laboratorio IA con tabs de Laboratorio y Configuración.
     */
    public function index()
    {
        $modalApiKey    = BusinessSetting::where('key', 'modal_api_key')->value('value') ?? env('MODAL_API_KEY', '');
        $modalWorkspace = BusinessSetting::where('key', 'modal_workspace')->value('value') ?? env('MODAL_WORKSPACE', '');
        $apiKeyConfigurada = !empty($modalApiKey);

        return view('admin-views.laboratorio-ia.index', compact(
            'modalApiKey',
            'modalWorkspace',
            'apiKeyConfigurada'
        ));
    }

    /**
     * Guarda la configuración de Modal.com en BusinessSettings (DB).
     */
    public function guardarConfiguracion(Request $request)
    {
        $request->validate([
            'modal_api_key'   => 'required|string',
            'modal_workspace' => 'required|string',
        ]);

        BusinessSetting::updateOrCreate(['key' => 'modal_api_key'],   ['value' => $request->modal_api_key]);
        BusinessSetting::updateOrCreate(['key' => 'modal_workspace'],  ['value' => $request->modal_workspace]);

        Toastr::success('Configuración de Modal.com guardada correctamente.', 'Éxito');
        return redirect()->route('admin.laboratorio-ia.index', ['tab' => 'configuracion']);
    }

    /**
     * Genera contenido UGC real usando Gemini API directamente desde PHP.
     */
    public function generarUGC(Request $request)
    {
        $request->validate([
            'producto'   => 'required|string|max:200',
            'plataforma' => 'required|string|in:instagram,tiktok,facebook,twitter,email',
            'tono'       => 'required|string|in:profesional,casual,divertido,emotivo,urgente',
            'objetivo'   => 'nullable|string|max:300',
        ]);

        $geminiKey = env('GEMINI_API_KEY');
        if (!$geminiKey) {
            return response()->json(['success' => false, 'error' => 'GEMINI_API_KEY no configurado en .env'], 500);
        }

        $prompt = $this->buildUGCPrompt(
            $request->producto,
            $request->plataforma,
            $request->tono,
            $request->objetivo ?? ''
        );

        try {
            $response = Http::timeout(30)->post(
                "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash:generateContent?key={$geminiKey}",
                [
                    'contents'         => [['parts' => [['text' => $prompt]]]],
                    'generationConfig' => ['temperature' => 0.85, 'maxOutputTokens' => 600],
                ]
            );

            if ($response->successful()) {
                $text = $response->json('candidates.0.content.parts.0.text') ?? '';
                return response()->json(['success' => true, 'content' => trim($text)]);
            }

            return response()->json(['success' => false, 'error' => 'Error Gemini: ' . $response->body()], 500);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Error de conexión: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Genera prompts de marketing usando Gemini para crear copies personalizados.
     */
    public function generarPromptMarketing(Request $request)
    {
        $request->validate([
            'tipo_campana' => 'required|string',
            'descripcion'  => 'required|string|max:500',
            'audiencia'    => 'nullable|string|max:200',
        ]);

        $geminiKey = env('GEMINI_API_KEY');
        $audiencia = $request->audiencia ?? 'público general en México';

        $prompt = "Eres experto en marketing digital para el mercado mexicano.
Crea 4 prompts de marketing listos para usar en herramientas de IA (ChatGPT, Gemini, Claude).
Campaña: {$request->tipo_campana} | Producto: {$request->descripcion} | Audiencia: {$audiencia}

Responde SOLO con JSON válido, sin texto adicional:
{\"templates\":[{\"nombre\":\"...\",\"icono\":\"...\",\"formato\":\"...\",\"prompt\":\"...\"},...]}" ;

        if ($geminiKey) {
            try {
                $response = Http::timeout(30)->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash:generateContent?key={$geminiKey}",
                    [
                        'contents'         => [['parts' => [['text' => $prompt]]]],
                        'generationConfig' => [
                            'temperature'      => 0.7,
                            'maxOutputTokens'  => 1200,
                            'responseMimeType' => 'application/json',
                        ],
                    ]
                );

                if ($response->successful()) {
                    $raw  = $response->json('candidates.0.content.parts.0.text') ?? '{}';
                    $data = json_decode($raw, true);
                    if (!empty($data['templates'])) {
                        return response()->json(['success' => true, 'templates' => $data['templates']]);
                    }
                }
            } catch (\Exception $e) {
                // cae al fallback
            }
        }

        return response()->json([
            'success'   => true,
            'templates' => $this->getMarketingTemplates($request->tipo_campana, $request->descripcion, $audiencia),
        ]);
    }

    /**
     * Genera un video usando Modal.com como plataforma serverless.
     */
    public function generarVideo(Request $request)
    {
        $request->validate([
            'prompt_video' => 'required|string|max:500',
            'duracion'     => 'nullable|integer|in:5,10,15',
            'estilo'       => 'nullable|string|in:cinematico,dinamico,minimalista,colorido',
        ]);

        $modalApiKey    = BusinessSetting::where('key', 'modal_api_key')->value('value')    ?? env('MODAL_API_KEY', '');
        $modalWorkspace = BusinessSetting::where('key', 'modal_workspace')->value('value')  ?? env('MODAL_WORKSPACE', '');

        if (empty($modalApiKey)) {
            return response()->json([
                'success' => false,
                'error'   => 'Modal.com no está configurado. Ve a ⚙️ Configuración y agrega tu API key.',
            ], 422);
        }

        try {
            $response = Http::timeout(120)
                ->withToken($modalApiKey)
                ->post("https://api.modal.com/v1/{$modalWorkspace}/tootli-video-gen/generate", [
                    'prompt'   => $request->prompt_video,
                    'duration' => $request->duracion ?? 5,
                    'style'    => $request->estilo ?? 'dinamico',
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return response()->json([
                    'success'   => true,
                    'video_url' => $data['video_url'] ?? null,
                    'job_id'    => $data['job_id']    ?? null,
                    'status'    => $data['status']    ?? 'processing',
                ]);
            }

            return response()->json([
                'success' => false,
                'error'   => 'Modal.com error ' . $response->status() . ': ' . $response->body(),
            ], $response->status());

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Error de conexión con Modal.com: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Genera un Avatar Hablante usando ElevenLabs y Modal.
     */
    public function generarAvatar(Request $request)
    {
        $request->validate([
            'imagen_avatar' => 'required|image|mimes:jpeg,png,jpg|max:5120', // Max 5MB
            'guion'         => 'required|string|max:1000',
            'voice_id'      => 'nullable|string',
        ]);

        $elevenLabsKey = BusinessSetting::where('key', 'elevenlabs_api_key')->value('value') ?? env('ELEVENLABS_API_KEY', '');
        $voiceId       = $request->voice_id ?? 'CwhRBWXzGAHq8TQ4Fs17'; // Voz premade por defecto para capas gratuitas
        $modalWebhook  = 'https://sebasrg4--video-avatar-generator-generate-web.modal.run'; // Webhook de Modal

        if (empty($elevenLabsKey)) {
            return response()->json([
                'success' => false,
                'error'   => 'ElevenLabs API Key no está configurada.',
            ], 422);
        }

        try {
            // 1. Generar Audio con ElevenLabs (TTS)
            $ttsResponse = Http::timeout(30)->withHeaders([
                'Accept'       => 'audio/mpeg',
                'Content-Type' => 'application/json',
                'xi-api-key'   => $elevenLabsKey,
            ])->post("https://api.elevenlabs.io/v1/text-to-speech/{$voiceId}", [
                'text'     => $request->guion,
                'model_id' => 'eleven_multilingual_v2',
                'voice_settings' => [
                    'stability'        => 0.5,
                    'similarity_boost' => 0.75,
                ],
            ]);

            if (!$ttsResponse->successful()) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Error en ElevenLabs: ' . $ttsResponse->body(),
                ], $ttsResponse->status());
            }

            $audioBytes = $ttsResponse->body();
            $imageFile = $request->file('imagen_avatar');
            $imageBytes = file_get_contents($imageFile->getRealPath());

            // 2. Enviar Imagen + Audio al Webhook de Modal
            $modalResponse = Http::timeout(600)->attach(
                'image', $imageBytes, 'avatar.jpg'
            )->attach(
                'audio', $audioBytes, 'voice.mp3'
            )->post($modalWebhook);

            if (!$modalResponse->successful()) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Error en generador de video (Modal): ' . $modalResponse->body(),
                ], $modalResponse->status());
            }

            $videoBytes = $modalResponse->body();

            // 3. Guardar el video final en el storage público
            $filename = 'avatar_' . time() . '.mp4';
            $path = 'avatars/' . $filename;
            \Illuminate\Support\Facades\Storage::disk('public')->put($path, $videoBytes);

            return response()->json([
                'success'   => true,
                'video_url' => asset('storage/' . $path),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Excepción al generar avatar: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtiene los trending topics de TikTok usando Apify
     */
    public function obtenerTendencias(Request $request)
    {
        $apifyToken = env('APIFY_API_TOKEN');
        if (empty($apifyToken)) {
            return response()->json([
                'success' => false,
                'error'   => 'El API Token de Apify no está configurado en .env',
            ], 422);
        }

        try {
            $response = Http::timeout(60)->withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://api.apify.com/v2/acts/data_xplorer~tiktok-trends/run-sync-get-dataset-items?token={$apifyToken}", [
                'trendType'   => 'hashtags',
                'countryCode' => 'MX',
                'period'      => 7, // últimos 7 días
            ]);

            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Error en Apify: ' . $response->body(),
                ], $response->status());
            }

            $data = $response->json();
            
            // Limitamos a los top 15 hashtags para no saturar la UI
            $topHashtags = array_slice($data, 0, 15);

            return response()->json([
                'success'   => true,
                'tendencias' => $topHashtags
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Excepción al obtener tendencias: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Fastlane Clone: Analiza una URL y genera Hooks con Gemini
     */
    public function analizarUrl(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
        ]);

        $url = $request->input('url');

        try {
            // 1. Extraer metadata básica de la URL (Title, Description)
            $html = Http::timeout(10)->get($url)->body();
            
            preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $titleMatch);
            $title = $titleMatch[1] ?? 'Sin título';
            
            preg_match('/<meta\s+name=["\']description["\']\s+content=["\'](.*?)["\']/is', $html, $descMatch);
            $description = $descMatch[1] ?? 'Sin descripción';
            
            $contexto = "Sitio: $title. Descripción: $description.";

            // 2. Usar Gemini para generar 3 Hooks UGC
            $geminiKey = BusinessSetting::where('key', 'gemini_api_key')->value('value') ?? env('GEMINI_API_KEY', '');
            if (empty($geminiKey)) {
                return response()->json(['success' => false, 'error' => 'API Key de Gemini no configurada'], 422);
            }

            $prompt = "Eres un experto en marketing de TikTok (estilo Fastlane AI). Acabo de darte el contexto de una página web: '{$contexto}'.
Necesito que generes 3 guiones (hooks) cortos para videos de TikTok (UGC). Cada guion debe durar unos 10 segundos hablado.
El JSON de respuesta debe tener este formato exacto:
[
  {\"titulo\": \"Problema / Solución\", \"texto\": \"¿Cansado de X? Prueba nuestro servicio y cambia tu vida...\"},
  {\"titulo\": \"Hook Controversial\", \"texto\": \"La industria no quiere que sepas esto...\"},
  {\"titulo\": \"Oferta Directa\", \"texto\": \"Solo por hoy, obtén un descuento...\"}
]
Responde ÚNICAMENTE con el JSON, sin markdown ni backticks.";

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash:generateContent?key={$geminiKey}", [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ]
            ]);

            if (!$response->successful()) {
                throw new \Exception('Error de Gemini: ' . $response->body());
            }

            $data = $response->json();
            $rawText = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
            
            // Limpiar posible formato markdown de la respuesta
            $rawText = str_replace(['```json', '```'], '', $rawText);
            $hooks = json_decode(trim($rawText), true);

            if (!is_array($hooks) || count($hooks) === 0) {
                // Fallback de hooks si falla el JSON
                $hooks = [
                    ["titulo" => "Descubrimiento", "texto" => "Acabo de encontrar el mejor producto de todos. Se llama $title."],
                    ["titulo" => "El Secreto", "texto" => "No vas a creer lo que hace $title por ti..."]
                ];
            }

            return response()->json([
                'success' => true,
                'hooks'   => $hooks
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Error al analizar URL: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Pipeline completo de Fastlane (Modal + JSON2Video)
     */
    public function generarFastlane(Request $request)
    {
        $request->validate([
            'imagen_avatar' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'guion'         => 'required|string|max:1000',
        ]);

        $elevenLabsKey = BusinessSetting::where('key', 'elevenlabs_api_key')->value('value') ?? env('ELEVENLABS_API_KEY', '');
        $json2videoKey = env('JSON2VIDEO_API_KEY', '');
        $voiceId       = 'CwhRBWXzGAHq8TQ4Fs17'; // Voz gratuita premade
        $modalWebhook  = 'https://sebasrg4--video-avatar-generator-generate-web.modal.run';

        if (empty($elevenLabsKey) || empty($json2videoKey)) {
            return response()->json(['success' => false, 'error' => 'Faltan API Keys de ElevenLabs o JSON2Video'], 422);
        }

        // 1. Guardar la imagen localmente para poder usarla después de cerrar la petición
        $imagePath = $request->file('imagen_avatar')->store('temp_avatars', 'public');
        $absoluteImagePath = storage_path('app/public/' . $imagePath);
        $originalImageName = $request->file('imagen_avatar')->getClientOriginalName();
        $guion = $request->guion;

        // 2. Crear Job ID y registrar en caché
        $jobId = uniqid('fastlane_');
        \Illuminate\Support\Facades\Cache::put($jobId, ['status' => 'processing'], 900); // 15 mins TTL

        // 3. Responder de inmediato al cliente para evitar el Timeout de Nginx (60s)
        response()->json([
            'success' => true,
            'job_id'  => $jobId,
            'message' => 'Procesamiento en segundo plano iniciado'
        ])->send();
        
        // Desconectar cliente web pero mantener PHP corriendo
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }

        // === INICIO DE PROCESAMIENTO EN SEGUNDO PLANO ===
        try {
            // A. Audio TTS ElevenLabs
            $ttsResponse = Http::timeout(30)->withHeaders([
                'Accept'       => 'audio/mpeg',
                'Content-Type' => 'application/json',
                'xi-api-key'   => $elevenLabsKey,
            ])->post("https://api.elevenlabs.io/v1/text-to-speech/{$voiceId}", [
                'text'     => $guion,
                'model_id' => 'eleven_multilingual_v2',
                'voice_settings' => ['stability' => 0.5, 'similarity_boost' => 0.75]
            ]);

            if (!$ttsResponse->successful()) {
                \Illuminate\Support\Facades\Cache::put($jobId, ['status' => 'error', 'error' => 'Error TTS: ' . $ttsResponse->body()], 900);
                return;
            }

            // B. Avatar con Modal.com
            $modalResponse = Http::timeout(600)->attach(
                'image',
                file_get_contents($absoluteImagePath),
                $originalImageName
            )->attach(
                'audio',
                $ttsResponse->body(),
                'audio.mp3'
            )->post($modalWebhook);

            if (!$modalResponse->successful()) {
                \Illuminate\Support\Facades\Cache::put($jobId, ['status' => 'error', 'error' => 'Error Modal: ' . $modalResponse->body()], 900);
                return;
            }

            $avatarBytes = $modalResponse->body();
            $filename = 'avatar_' . time() . '.mp4';
            $localPath = 'avatars/' . $filename;
            \Illuminate\Support\Facades\Storage::disk('public')->put($localPath, $avatarBytes);

            // C. Subir a catbox.moe para obtener un link de descarga directo real
            $absoluteVideoPath = storage_path('app/public/' . $localPath);
            $tmpResponse = Http::attach(
                'fileToUpload',
                file_get_contents($absoluteVideoPath),
                $filename
            )->post('https://catbox.moe/user/api.php', [
                'reqtype' => 'fileupload'
            ]);

            if (!$tmpResponse->successful()) {
                \Illuminate\Support\Facades\Cache::put($jobId, ['status' => 'error', 'error' => 'Error subiendo a Catbox'], 900);
                return;
            }

            $directUrl = trim($tmpResponse->body());

            // D. Ensamblar en JSON2Video
            $payload = [
                'resolution' => 'tiktok',
                'elements' => [
                    [
                        'type' => 'image',
                        'src'  => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=1080&auto=format&fit=crop',
                        'duration' => 15
                    ],
                    [
                        'type'   => 'video',
                        'src'    => $directUrl,
                        'width'  => 500,
                        'x'      => 'center',
                        'y'      => 'center',
                        'settings' => ['borderRadius' => 20]
                    ],
                    [
                        'type'  => 'text',
                        'text'  => $guion,
                        'style' => 'color: white; font-size: 50px; font-weight: bold; font-family: Montserrat; text-shadow: 2px 2px 4px #000;',
                        'x'     => 'center',
                        'y'     => 100,
                        'settings' => ['shadow' => true, 'shadowColor' => '#000000']
                    ]
                ]
            ];

            $j2vResponse = Http::withHeaders([
                'x-api-key' => $json2videoKey,
                'Content-Type' => 'application/json'
            ])->post('https://api.json2video.com/v2/movies', $payload);

            if (!$j2vResponse->successful()) {
                \Illuminate\Support\Facades\Cache::put($jobId, ['status' => 'error', 'error' => 'Error JSON2Video: ' . $j2vResponse->body()], 900);
                return;
            }

            $project = $j2vResponse->json();
            $projectId = $project['project'] ?? null;
            if (!$projectId) {
                \Illuminate\Support\Facades\Cache::put($jobId, ['status' => 'error', 'error' => 'No Project ID de JSON2Video'], 900);
                return;
            }

            // E. Polling interno de JSON2Video (puede durar hasta 60s)
            $finalUrl = '';
            for ($i = 0; $i < 20; $i++) {
                sleep(5);
                $check = Http::withHeaders(['x-api-key' => $json2videoKey])
                            ->get("https://api.json2video.com/v2/movies?project={$projectId}");
                
                $checkData = $check->json();
                if (isset($checkData['movies']) && count($checkData['movies']) > 0) {
                    $status = $checkData['movies'][0]['status'] ?? '';
                    if ($status === 'done' || $status === 'success') {
                        $finalUrl = $checkData['movies'][0]['url'];
                        break;
                    } elseif ($status === 'error') {
                        $errorMsg = $checkData['movies'][0]['message'] ?? 'Error desconocido en JSON2Video';
                        \Illuminate\Support\Facades\Cache::put($jobId, ['status' => 'error', 'error' => 'Fallo al ensamblar video: ' . $errorMsg], 900);
                        return;
                    }
                }
            }

            if (empty($finalUrl)) {
                \Illuminate\Support\Facades\Cache::put($jobId, ['status' => 'error', 'error' => 'El renderizado tardó demasiado en la nube.'], 900);
                return;
            }

            // ¡Éxito Final! Actualizamos la caché con la URL
            \Illuminate\Support\Facades\Cache::put($jobId, ['status' => 'done', 'video_url' => $finalUrl], 3600);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Cache::put($jobId, ['status' => 'error', 'error' => $e->getMessage()], 900);
        }
    }

    /**
     * Polling Endpoint para revisar el estado del trabajo en segundo plano
     */
    public function estadoFastlane($jobId)
    {
        $status = \Illuminate\Support\Facades\Cache::get($jobId);
        if (!$status) {
            return response()->json(['success' => false, 'error' => 'Trabajo no encontrado o expirado.']);
        }
        return response()->json(['success' => true, 'data' => $status]);
    }

    /* ─────────────── Helpers privados ─────────────── */

    private function buildUGCPrompt(string $producto, string $plataforma, string $tono, string $objetivo): string
    {
        $tonoDesc = [
            'profesional' => 'formal, confiable y elegante',
            'casual'      => 'amigable, cercano y natural',
            'divertido'   => 'divertido, con humor y emojis',
            'emotivo'     => 'emotivo, que conecte sentimentalmente',
            'urgente'     => 'urgente, con sentido de escasez y acción inmediata',
        ][$tono] ?? $tono;

        $contexto = $objetivo ? "Contexto adicional: {$objetivo}" : '';

        return "Eres un experto en marketing digital para el mercado mexicano con 10 años de experiencia.
Crea contenido de alta calidad para {$plataforma} sobre: \"{$producto}\".
Tono de voz: {$tonoDesc}.
{$contexto}

Genera:
1. Un caption principal impactante con emojis relevantes
2. Una llamada a la acción (CTA) clara
3. 8 hashtags en español optimizados para el mercado mexicano

Formato de respuesta:
📝 CAPTION:
[caption aquí]

🎯 CTA:
[llamada a la acción aquí]

#️⃣ HASHTAGS:
[hashtags aquí separados por espacios]";
    }

    private function getMarketingTemplates(string $tipoCampana, string $descripcion, string $audiencia): array
    {
        return [
            ['nombre' => 'Post de Lanzamiento', 'icono' => '🚀', 'formato' => 'Instagram / Facebook',
             'prompt' => "Crea un post de lanzamiento para {$descripcion}, dirigido a {$audiencia}. Incluye emoción, beneficio clave y CTA urgente."],
            ['nombre' => 'Story de Oferta',      'icono' => '⚡', 'formato' => 'Instagram Stories / TikTok',
             'prompt' => "Crea una story con oferta flash para {$descripcion}. Máx 3 líneas, emoji llamativo, urgencia real. Audiencia: {$audiencia}."],
            ['nombre' => 'Email Marketing',       'icono' => '✉️', 'formato' => 'Email',
             'prompt' => "Escribe un email de marketing para {$audiencia} sobre {$descripcion}. Asunto llamativo, cuerpo breve con beneficios y botón CTA."],
            ['nombre' => 'Guión para Video',      'icono' => '🎬', 'formato' => 'TikTok / Reels',
             'prompt' => "Escribe un guión de 30 segundos para video de {$descripcion} dirigido a {$audiencia}. Gancho inicial (5s), desarrollo (20s) y CTA final (5s)."],
        ];
    }

    private function generarContenidoFallback(string $producto, string $plataforma, string $tono): string
    {
        $emojis = ['instagram' => '📸', 'tiktok' => '🎵', 'facebook' => '👍', 'twitter' => '🐦', 'email' => '✉️'];
        $emoji = $emojis[$plataforma] ?? '✨';

        return "✨ {$producto} — ahora disponible en Tootli {$emoji}\n\nDescubre la mejor experiencia de entrega a domicilio en México. Pedidos rápidos, seguros y con la calidad que mereces.\n\n#Tootli #DeliveryMexico #PedidosOnline #ComidaADomicilio #MexicoFoodie";
    }
}

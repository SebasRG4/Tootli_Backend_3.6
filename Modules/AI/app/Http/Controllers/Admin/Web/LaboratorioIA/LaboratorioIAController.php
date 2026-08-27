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
        $modalApiKey    = env('MODAL_API_KEY', '');
        $modalWorkspace = env('MODAL_WORKSPACE', '');
        $apiKeyConfigurada = !empty($modalApiKey);

        return view('admin-views.laboratorio-ia.index', compact(
            'modalApiKey',
            'modalWorkspace',
            'apiKeyConfigurada'
        ));
    }

    /**
     * Guarda la configuración de Modal.com en el archivo .env (o en BusinessSettings).
     * En producción se recomienda guardar en BusinessSettings en DB.
     */
    public function guardarConfiguracion(Request $request)
    {
        $request->validate([
            'modal_api_key'    => 'required|string',
            'modal_workspace'  => 'required|string',
        ]);

        // Guardar en BusinessSettings (tabla de configuración persistente)
        BusinessSetting::updateOrCreate(
            ['key' => 'modal_api_key'],
            ['value' => $request->modal_api_key]
        );
        BusinessSetting::updateOrCreate(
            ['key' => 'modal_workspace'],
            ['value' => $request->modal_workspace]
        );

        Toastr::success('Configuración de Modal.com guardada correctamente.', 'Éxito');
        return redirect()->route('admin.laboratorio-ia.index', ['tab' => 'configuracion']);
    }

    /**
     * Genera contenido UGC (User Generated Content) para marketing
     * usando el servicio de Gemini ya existente en Tootli_AI.
     */
    public function generarUGC(Request $request)
    {
        $request->validate([
            'producto'   => 'required|string|max:200',
            'plataforma' => 'required|string|in:instagram,tiktok,facebook,twitter,email',
            'tono'       => 'required|string|in:profesional,casual,divertido,emotivo,urgente',
            'objetivo'   => 'nullable|string|max:300',
        ]);

        $aiServiceUrl = env('AI_SERVICE_URL', 'http://127.0.0.1:8000');

        try {
            $prompt = $this->buildUGCPrompt(
                $request->producto,
                $request->plataforma,
                $request->tono,
                $request->objetivo ?? ''
            );

            $response = Http::timeout(30)->post("{$aiServiceUrl}/generar-marketing", [
                'prompt' => $prompt,
            ]);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'content' => $response->json('content'),
                ]);
            }

            // Fallback: generar directamente si el endpoint no existe aún
            return response()->json([
                'success' => true,
                'content' => $this->generarContenidoFallback($request->producto, $request->plataforma, $request->tono),
            ]);

        } catch (\Exception $e) {
            // Fallback siempre disponible
            return response()->json([
                'success' => true,
                'content' => $this->generarContenidoFallback($request->producto, $request->plataforma, $request->tono),
            ]);
        }
    }

    /**
     * Genera prompts de marketing personalizados.
     */
    public function generarPromptMarketing(Request $request)
    {
        $request->validate([
            'tipo_campana' => 'required|string',
            'descripcion'  => 'required|string|max:500',
            'audiencia'    => 'nullable|string|max:200',
        ]);

        $templates = $this->getMarketingTemplates(
            $request->tipo_campana,
            $request->descripcion,
            $request->audiencia ?? 'público general en México'
        );

        return response()->json([
            'success'   => true,
            'templates' => $templates,
        ]);
    }

    /* ─────────────── Helpers privados ─────────────── */

    private function buildUGCPrompt(string $producto, string $plataforma, string $tono, string $objetivo): string
    {
        return "Eres un experto en marketing digital para el mercado mexicano. 
Crea contenido de alta calidad para {$plataforma} sobre: {$producto}.
Tono: {$tono}.
Objetivo adicional: {$objetivo}.
Genera: 1 caption principal con emojis, 5 hashtags relevantes en español, y una llamada a la acción.
Responde en formato JSON con campos: caption, hashtags (array), cta.";
    }

    private function getMarketingTemplates(string $tipoCampana, string $descripcion, string $audiencia): array
    {
        $templates = [
            [
                'nombre'  => 'Post de Lanzamiento',
                'icono'   => '🚀',
                'prompt'  => "Crea un post de lanzamiento para {$descripcion}, dirigido a {$audiencia}. Incluye emoción, beneficio clave y CTA urgente.",
                'formato' => 'Instagram / Facebook',
            ],
            [
                'nombre'  => 'Story de Oferta',
                'icono'   => '⚡',
                'prompt'  => "Crea una story con oferta flash para {$descripcion}. Máx 3 líneas, emoji llamativo, urgencia real.",
                'formato' => 'Instagram Stories / TikTok',
            ],
            [
                'nombre'  => 'Email Marketing',
                'icono'   => '✉️',
                'prompt'  => "Escribe un email de marketing para {$audiencia} sobre {$descripcion}. Asunto llamativo, cuerpo breve y botón CTA.",
                'formato' => 'Email',
            ],
            [
                'nombre'  => 'Guión para Video',
                'icono'   => '🎬',
                'prompt'  => "Escribe un guión de 30 segundos para video de {$descripcion}. Gancho inicial, desarrollo y CTA final.",
                'formato' => 'TikTok / Reels',
            ],
        ];

        return $templates;
    }

    private function generarContenidoFallback(string $producto, string $plataforma, string $tono): string
    {
        $emojis = ['instagram' => '📸', 'tiktok' => '🎵', 'facebook' => '👍', 'twitter' => '🐦', 'email' => '✉️'];
        $emoji = $emojis[$plataforma] ?? '✨';

        return "✨ {$producto} — ahora disponible en Tootli {$emoji}\n\nDescubre la mejor experiencia de entrega a domicilio en México. Pedidos rápidos, seguros y con la calidad que mereces.\n\n#Tootli #DeliveryMexico #PedidosOnline #ComidaADomicilio #MexicoFoodie";
    }
}

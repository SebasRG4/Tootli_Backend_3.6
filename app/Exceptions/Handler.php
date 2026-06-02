<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

use Illuminate\Support\Facades\Cache;
use App\CentralLogics\Helpers;
use Illuminate\Support\Str;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
       protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            // Ignorar excepciones comunes que no son críticas
            if ($this->shouldntReport($e)) {
                return;
            }

            // Excepciones HTTP, de validación, etc., usualmente están en shouldntReport de Laravel,
            // pero podemos asegurarnos ignorando ciertas clases.
            $ignoredClasses = [
                \Illuminate\Auth\AuthenticationException::class,
                \Illuminate\Validation\ValidationException::class,
                \Symfony\Component\HttpKernel\Exception\HttpException::class,
                \Illuminate\Database\Eloquent\ModelNotFoundException::class,
            ];

            foreach ($ignoredClasses as $class) {
                if ($e instanceof $class) {
                    return;
                }
            }

            try {
                // Crear un hash único para este error (mensaje + archivo + línea)
                $errorHash = md5($e->getMessage() . $e->getFile() . $e->getLine());
                $cacheKey = 'critical_error_alert_' . $errorHash;

                // Verificar si ya notificamos este error en los últimos 15 minutos
                if (!Cache::has($cacheKey)) {
                    // Guardar en caché por 15 minutos (anti-spam)
                    Cache::put($cacheKey, true, now()->addMinutes(15));

                    // Preparar payload de notificación
                    $errorMessage = Str::limit($e->getMessage(), 150);
                    $errorFile = basename($e->getFile());
                    $errorLine = $e->getLine();

                    $data = [
                        'title' => '⚠️ Error Crítico en Servidor',
                        'description' => "{$errorMessage} en {$errorFile}:{$errorLine}",
                        'order_id' => '',
                        'image' => '',
                        'type' => 'system_error'
                    ];

                    // Enviar notificación push a los administradores
                    Helpers::send_push_notif_to_topic($data, 'admin_message', 'system_error');
                }
            } catch (\Throwable $notificationError) {
                // Capturar silenciosamente si falla el sistema de notificaciones para no romper la app
                \Log::error('Fallo al enviar alerta de error crítico: ' . $notificationError->getMessage());
            }
        });
    }
}

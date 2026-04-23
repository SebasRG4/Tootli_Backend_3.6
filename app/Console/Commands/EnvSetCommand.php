<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Escribe o actualiza una variable en el archivo .env sin editar a mano en el servidor.
 *
 * Ejemplos:
 *   php artisan env:set MAPBOX_ACCESS_TOKEN sk.eyJ...
 *   echo -n 'sk.eyJ...' | php artisan env:set MAPBOX_ACCESS_TOKEN --stdin
 *   php artisan env:set MAPBOX_ACCESS_TOKEN --prompt
 */
class EnvSetCommand extends Command
{
    protected $signature = 'env:set
                            {key : Nombre de la variable (ej. MAPBOX_ACCESS_TOKEN)}
                            {value? : Valor en línea (opcional si usas --stdin o --prompt)}
                            {--stdin : Leer el valor completo desde stdin (evita dejar el token en el history del shell)}
                            {--prompt : Pedir el valor de forma oculta}';

    protected $description = 'Define o actualiza una variable en .env (útil para tokens sin abrir el editor en el servidor)';

    public function handle(): int
    {
        $key = strtoupper(trim($this->argument('key')));
        if ($key === '' || ! preg_match('/^[A-Z][A-Z0-9_]*$/', $key)) {
            $this->error('Clave inválida. Use solo A–Z, 0–9 y guion bajo, empezando por letra.');

            return self::FAILURE;
        }

        $value = $this->resolveValue();
        if ($value === null) {
            return self::FAILURE;
        }

        $path = base_path('.env');
        if (! is_file($path)) {
            $this->error("No existe el archivo .env en: {$path}");

            return self::FAILURE;
        }

        $content = file_get_contents($path);
        if ($content === false) {
            $this->error('No se pudo leer .env');

            return self::FAILURE;
        }

        $line = $this->formatEnvLine($key, $value);
        $pattern = '/^'.preg_quote($key, '/').'\s*=.*/m';

        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, $line, $content, 1);
            $this->info("Actualizado: {$key}");
        } else {
            $content = rtrim($content).PHP_EOL.$line.PHP_EOL;
            $this->info("Añadido: {$key}");
        }

        if (file_put_contents($path, $content) === false) {
            $this->error('No se pudo escribir .env');

            return self::FAILURE;
        }

        $this->comment('Ejecuta: php artisan config:clear (o config:cache en producción tras desplegar).');

        return self::SUCCESS;
    }

    private function resolveValue(): ?string
    {
        $arg = $this->argument('value');
        if ($arg !== null && $arg !== '') {
            return (string) $arg;
        }

        if ($this->option('stdin')) {
            $raw = stream_get_contents(STDIN);
            if ($raw === false) {
                $this->error('No se pudo leer stdin.');

                return null;
            }
            $v = trim($raw);
            if ($v === '') {
                $this->error('stdin vacío.');

                return null;
            }

            return $v;
        }

        if ($this->option('prompt')) {
            $v = $this->secret('Valor para '.$this->argument('key'));
            if ($v === null || $v === '') {
                $this->error('Valor vacío.');

                return null;
            }

            return $v;
        }

        $this->error('Indica el valor como segundo argumento, o usa --stdin, o --prompt.');
        $this->line('  php artisan env:set MAPBOX_ACCESS_TOKEN sk.eyJ...');
        $this->line('  echo -n \'sk.eyJ...\' | php artisan env:set MAPBOX_ACCESS_TOKEN --stdin');
        $this->line('  php artisan env:set MAPBOX_ACCESS_TOKEN --prompt');

        return null;
    }

    /**
     * Formato seguro para .env: entre comillas si hace falta.
     */
    private function formatEnvLine(string $key, string $value): string
    {
        if ($value === '') {
            return "{$key}=";
        }

        $needsQuotes = strpbrk($value, " \t\n\r#\"'`\$\\") !== false;

        if (! $needsQuotes) {
            return "{$key}={$value}";
        }

        $escaped = str_replace(['\\', '"'], ['\\\\', '\\"'], $value);

        return "{$key}=\"{$escaped}\"";
    }
}

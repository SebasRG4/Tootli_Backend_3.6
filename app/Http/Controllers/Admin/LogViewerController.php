<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LogViewerController extends Controller
{
    public function index(Request $request)
    {
        $logPath = storage_path('logs/laravel.log');
        if (!File::exists($logPath)) {
            $parsedLogs = [];
            $search = $request->get('search', '');
            $limit = $request->get('limit', 500);
            return view('admin-views.logs.index', compact('parsedLogs', 'search', 'limit'))->with('warning', 'El archivo laravel.log no existe todavía.');
        }

        $limit = (int) $request->get('limit', 500);
        $search = $request->get('search', '');
        
        // Uso de SplFileObject para posicionarnos al final del archivo de forma extremadamente rápida sin cargar todo en memoria
        $file = new \SplFileObject($logPath, 'r');
        $file->seek(PHP_INT_MAX);
        $totalLines = $file->key();

        $lines = [];
        $startLine = max(0, $totalLines - $limit);
        
        $file->seek($startLine);
        while (!$file->eof()) {
            $line = $file->current();
            if ($line !== false) {
                $lines[] = $line;
            }
            $file->next();
        }

        $parsedLogs = [];
        $currentEntry = null;

        // Expresión regular para parsear el formato estandarizado de Laravel: [YYYY-MM-DD HH:MM:SS] env.LEVEL: mensaje
        foreach ($lines as $line) {
            $lineTrimmed = trim($line);
            if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] ([a-zA-Z0-9_-]+)\.([A-Z]+): (.*)$/u', $lineTrimmed, $matches)) {
                if ($currentEntry) {
                    $parsedLogs[] = $currentEntry;
                }
                $currentEntry = [
                    'timestamp' => $matches[1],
                    'environment' => $matches[2],
                    'level' => $matches[3],
                    'message' => $matches[4],
                    'stacktrace' => ''
                ];
            } else {
                if ($currentEntry && $lineTrimmed !== '') {
                    $currentEntry['stacktrace'] .= $line;
                }
            }
        }
        if ($currentEntry) {
            $parsedLogs[] = $currentEntry;
        }

        // Mostrar logs más nuevos primero
        $parsedLogs = array_reverse($parsedLogs);

        // Filtrar si el usuario ingresó un término de búsqueda
        if (!empty($search)) {
            $searchLower = strtolower($search);
            $parsedLogs = array_filter($parsedLogs, function ($log) use ($searchLower) {
                return str_contains(strtolower($log['message']), $searchLower) || 
                       str_contains(strtolower($log['level']), $searchLower) ||
                       str_contains(strtolower($log['stacktrace']), $searchLower);
            });
        }

        return view('admin-views.logs.index', compact('parsedLogs', 'search', 'limit'));
    }

    public function clear()
    {
        $logPath = storage_path('logs/laravel.log');
        if (File::exists($logPath)) {
            File::put($logPath, '');
        }
        return redirect()->back()->with('success', 'Historial de registros limpiado correctamente.');
    }
}

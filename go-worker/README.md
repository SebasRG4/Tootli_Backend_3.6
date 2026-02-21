# Tootli Go Worker

Servicio de alto rendimiento escrito en **Go** para procesar tareas pesadas de forma asíncrona, desacoplando la carga del backend Laravel.

## Arquitectura

```
Laravel (PHP) ──► Redis list "tootli:go_jobs" ──► Go Worker ──► Resultado
```

El worker escucha una **lista Redis** (`BLPOP`) con hasta 5 goroutines en paralelo (configurable). Laravel simplemente hace un `RPUSH` con un JSON y el worker lo procesa.

## Estructura

```
go-worker/
├── main.go            # Entry point, graceful shutdown
├── config/
│   └── config.go      # Lee variables de entorno
├── jobs/
│   ├── job.go         # Registro + dispatcher de jobs
│   └── export_data.go # Ejemplo: exportación de datos
├── worker/
│   └── worker.go      # Motor: BLPop loop + goroutine pool
├── Makefile
└── go.mod
```

## Comandos

```bash
# Modo desarrollo (sin compilar)
make dev

# Compilar binario
make build

# Ejecutar binario
make run

# Tests
make test
```

## Variables de entorno

| Variable       | Default       | Descripción           |
|----------------|---------------|-----------------------|
| `REDIS_HOST`   | `127.0.0.1`   | Host de Redis         |
| `REDIS_PORT`   | `6379`        | Puerto de Redis       |
| `REDIS_PASSWORD` | *(vacío)*   | Password de Redis     |

## Enviar un job desde Laravel

```php
// En cualquier parte del código Laravel:
use Illuminate\Support\Facades\Redis;

Redis::rpush('tootli:go_jobs', json_encode([
    'type' => 'export_data',
    'data' => [
        'user_id'  => auth()->id(),
        'format'   => 'csv',
        'resource' => 'orders',
    ],
]));
```

## Agregar un nuevo tipo de job

1. Crear un archivo en `jobs/mi_nuevo_job.go`
2. Definir un struct con el payload
3. Llamar `jobs.Register("nombre_del_job", handler)` dentro de `init()`
4. El worker lo detectará automáticamente por el campo `"type"` del JSON

---

**Concurrencia:** 5 goroutines simultáneas por defecto.
**Graceful shutdown:** `Ctrl+C` → espera que los jobs activos terminen antes de salir.

<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Redis;

// Boot minimal App for Redis support
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$jobData = [
    'type' => 'push_campaign',
    'data' => [
        'message' => [
            'topic' => 'testing_go_worker',
            'notification' => [
                'title' => 'Prueba de Go Worker',
                'body' => 'Si ves esto, el worker procesó el job correctamente.'
            ],
            'data' => [
                'status' => 'success',
                'engine' => 'go'
            ]
        ]
    ]
];

echo "Enviando job a la cola 'tootli:go_jobs'...\n";
\Illuminate\Support\Facades\Redis::rpush('tootli:go_jobs', json_encode($jobData));
echo "Job enviado! Revisa los logs del Go Worker.\n";

<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Filtrar latest-orders por elegibilidad
    |--------------------------------------------------------------------------
    |
    | Si es true, se ocultan pedidos que el repartidor no podría aceptar según
    | DeliveryEligibilityService (COD, máximo concurrentes, lista Redis de
    | rechazados, etc.). La comprobación de zona no aplica aquí (no hay GPS
    | en el listado).
    |
    */
    'filter_latest_orders_by_eligibility' => env('DM_FILTER_LATEST_ORDERS_ELIGIBILITY', true),

    /*
    |--------------------------------------------------------------------------
    | TTL del bloqueo "ignore" por repartidor (segundos)
    |--------------------------------------------------------------------------
    |
    | Cuando un repartidor ignora un pedido, se almacena en Redis un set
    | `order:{id}:rejected` que impide que le sea asignado de nuevo.
    |
    | Este valor define cuánto tiempo dura ese bloqueo. Pasado ese tiempo,
    | si el pedido sigue en estado `pending` y sin repartidor asignado,
    | el repartidor volverá a verlo en `latest-orders`.
    |
    | Valor recomendado: 600 (10 minutos). Ajustar según tiempos de demanda.
    |
    */
    'ignore_ttl_seconds' => (int) env('DM_IGNORE_TTL_SECONDS', 120),

];

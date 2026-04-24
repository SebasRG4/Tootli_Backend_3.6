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

];

<?php

return [
    /**
     * Ventana móvil (días) para sumar peso de strikes al bloquear nuevas asignaciones.
     */
    'rolling_window_days' => (int) env('DM_STRIKES_ROLLING_DAYS', 90),

    /**
     * Suma de weight_snapshot (excl. apelaciones pendientes y aceptadas) ≥ umbral → no puede aceptar pedidos.
     */
    'block_weight_threshold' => (int) env('DM_STRIKES_BLOCK_WEIGHT', 12),
];

# API: errores al aceptar pedido (`PUT /api/v1/delivery-man/accept-order`)

Contrato de códigos estables (Fase 1 del plan de asignación). La app debe priorizar `errors[0].code` y, si existe, `errors[0].message_key` para i18n; `message` sigue siendo texto ya traducido por el backend.

## Cuerpo de error

```json
{
  "errors": [
    {
      "code": "cash_limit",
      "message_key": "cash_limit",
      "message": "…"
    }
  ]
}
```

## Códigos HTTP y `code`

| HTTP | code | Cuándo |
|------|------|--------|
| 401 | auth-001 | Token válido en middleware pero DM no encontrado (caso raro). |
| 404 | order | Pedido no disponible para asignar (ya asignado o no cumple `dmOrder`). |
| 403 | not_approved | Cuenta no aprobada. |
| 403 | dm_suspended | Cuenta suspendida (`status` en falso). |
| 403 | order_rejected | El DM ignoró antes este pedido (Redis `order:{id}:rejected`). |
| 404 | offline | DM no en línea (`active != 1`). |
| 405 | max_orders | `current_orders` ≥ `config('dm_maximum_orders')`. |
| 405 | cash_limit | COD superaría `dm_max_cash_in_hand` con efectivo en mano. |
| 403 | out_of_zone | Coordenadas fuera de zona de servicio (no aplica a `parcel`). |
| 409 | order | Otro DM está aceptando (lock Redis). `message_key`: `order_lock`. |

## `tier_restricted` (Fase 2)

| HTTP | code | Cuándo |
|------|------|--------|
| 403 | tier_restricted | Pedido COD y el importe supera `max_order_value_cod` del nivel (`dm_tier_limits`) del repartidor. |

## Strikes e incidencias (Fase 3)

| HTTP | code | message_key | Cuándo |
|------|------|-------------|--------|
| 403 | strike_blocked | strike_temp_suspension | `delivery_suspended_until` del repartidor está en el futuro (suspensión temporal operativa). |
| 403 | strike_blocked | strike_weight_limit | Suma de `weight_snapshot` en ventana `config('dm_strikes.rolling_window_days')` ≥ `config('dm_strikes.block_weight_threshold')`, excluyendo apelaciones `pending` y `accepted`. |

Variables de entorno: `DM_STRIKES_ROLLING_DAYS`, `DM_STRIKES_BLOCK_WEIGHT`.

## Telemetría

Intentos denegados se registran en `assignment_events` (`event_type = accept_denied`, `reason_code` = `code`).

## Listado `latest-orders`

Si `config('dm_assignment.filter_latest_orders_by_eligibility')` es `true` (por defecto; env `DM_FILTER_LATEST_ORDERS_ELIGIBILITY`), cada pedido se filtra con la misma elegibilidad que `accept-order`, **sin** comprobar zona (no hay GPS en el listado). Si el DM está al máximo de concurrentes, la lista queda vacía.

## Perfil repartidor (`GET profile`)

Incluye (cuando existen columnas migradas): `dm_tier`, `dm_tier_source` (`auto`|`manual`), `dm_tier_reason` (opcional), `dm_tier_limits` (`max_concurrent_orders`, `max_cash_cod`, `max_order_value_cod`). Con Fase 3: `dm_strike_summary` (`rolling_weight`, `block_threshold`, `rolling_window_days`, `pending_appeals`, `blocked_by_strikes`, `delivery_suspended_until` ISO) o `null` si no hay tablas.

### Listado de strikes (`GET strike-events`)

Paginación estándar de Laravel; cada ítem incluye `incident_type` y `order` si existen.

### Apelación (`POST strike-events/{id}/appeal`)

Cuerpo: `appeal_text` (requerido, máx. 2000). Respuestas: `409` si ya hay apelación pendiente; `422` si la apelación ya fue resuelta.

## Admin (suspensión)

Al suspender o reactivar desde el panel (ruta existente `delivery-man/status/{id}/{status}`), se inserta una fila en `delivery_man_admin_audit_logs` (`dm_suspend` / `dm_unsuspend`, `admin_id`, `meta` con IP y estados anterior/nuevo).

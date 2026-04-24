# Plan de mejoras: asignación de pedidos, riesgo y repartidores (Tootli)

Documento vivo para alinear producto, backend (`back3.6`) y app repartidor. Última revisión: abril 2026.

---

## 1. Objetivos de negocio

| Objetivo | Indicador |
|----------|-----------|
| Proteger ingresos y liquidez de Tootli | Comisión cobrada, COD conciliado, menos chargebacks por mala entrega |
| Reducir pedidos colgados o mal asignados | Tiempo a primera aceptación, % reasignaciones, cancelaciones post-asignación |
| Operar con reglas claras y auditables | Cambios de política trazables; repartidor entiende por qué no ve/acepta un pedido |
| Escalar sin reescribir todo | Reutilizar `latest-orders`, `accept-order`, Redis, `wave_queue`, wallet, `dm_maximum_orders` |

---

## 2. Estado actual (baseline en código)

- **Oferta:** `GET /api/v1/delivery-man/latest-orders` — pedidos sin `delivery_man_id`, filtros por zona/tienda, vehículo, estados, ventana de agenda (`OrderScheduledIn`), etc.
- **Activos:** `GET /api/v1/delivery-man/current-orders` — asignados al DM.
- **Aceptación:** `PUT /api/v1/delivery-man/accept-order` — candado Redis anti-carrera, DM `active`, límite `config('dm_maximum_orders')`, chequeo de zona (no parcel).
- **Rechazo / reasignación:** `POST /api/v1/delivery-man/ignore-order` — Redis `order:{id}:rejected`, vuelta a `pending`, reencolado `wave_queue`.
- **Efectivo:** perfil expone `cash_in_hands`, `dm_max_cash_in_hand`, flags overflow (`BusinessSetting`: `cash_in_hand_overflow_delivery_man`, `dm_max_cash_in_hand`).
- **App:** polling `latest-orders`, FCM/Pusher, comparación COD en UI (p. ej. `order_request_screen`).

**Deuda:** la elegibilidad está repartida entre listado, aceptación y worker; no hay **niveles de repartidor** ni **strikes** unificados ni **COD proyectado** obligatorio en servidor al aceptar.

---

## 3. Principios de diseño

1. **Un solo cerebro de elegibilidad** en backend (servicio tipo `DeliveryEligibilityService`): misma lógica para oferta (worker/API) y para `accept_order`.
2. **Reglas configurables** (`BusinessSetting` y/o tablas `assignment_policies`, `dm_tier_limits`) con historial de cambios en admin.
3. **Validación siempre en servidor**; la app solo mejora UX y mensajes.
4. **Separar** fallo operativo vs **culpa atribuible** al repartidor antes de aplicar strikes.

---

## 4. Pilares del plan

### 4.1 Motor de asignación (oleadas y fairness)

- Formalizar **olas** (`wave_queue` / `attempt`): tiempos, radio o ampliación de pool, criterios por tipo de pedido (parcel vs food).
- **Score** (versión 1): distancia estimada, tasa de aceptación, cancelaciones atribuibles, antigüedad del rechazo a ese `order_id`.
- **Anti-abuso:** rate limit por DM/dispositivo; penalizar ignorar sistemáticamente o en último instante (definir ventanas).

**Entregables:** documento de política por zona + servicio; ajustes en worker y/o `get_latest_orders` para usar el servicio de elegibilidad.

### 4.2 Niveles de repartidor (tiers)

| Nivel (ejemplo) | Comportamiento sugerido |
|-----------------|-------------------------|
| Nuevo | Menos pedidos concurrentes, menor techo COD, sin alto ticket COD |
| Estándar | Parámetros actuales (`dm_maximum_orders`, límites globales) |
| Pro | Más concurrentes, techo COD mayor, prioridad leve en score |
| Restringido / revisión | Solo bajo COD o bloqueo temporal hasta liquidar o resolver incidencias |

**Datos:** campos o tabla `delivery_man_tiers` (`tier`, `tier_updated_at`, `tier_reason`); job nocturno de recálculo; notificación al cambiar tier.

### 4.3 Efectivo máximo (COD acumulado + pedido)

- Regla recomendada: `collected_cash + COD_estimado_pedidos_activos + COD_nuevo ≤ límite(tier)`.
- **Hard check** en `accept_order` (y opcionalmente excluir en oferta para no frustrar en la app).
- Superar techo → **bloquear nuevas aceptaciones COD** hasta liquidación (mantener prepagos si aplica).

### 4.4 Fallos de entrega y bloqueos (strikes)

- **Taxonomía** en admin: cliente ausente, DM no intentó contacto, entrega fraudulenta/marcada sin evidencia, daño, robo, etc.
- **Pesos** por tipo; ventana rolling (ej. 30/90 días); apelación con evidencia (POD, chat, GPS).
- **Escalera:** advertencia → restricción COD → suspensión temporal → revisión grave.
- **Campos:** `delivery_suspended_until`, `restriction_flags` (JSON), tabla `delivery_man_strike_events`.

### 4.5 Riesgo financiero y fraude (Tootli no pierde)

- Pedidos **alto ticket** → tier mínimo, doble confirmación o prepago forzoso por umbral.
- **Fraude:** patrones GPS imposibles, multi-cuenta mismo dispositivo, aceptar/cancelar en bucle.
- **Conciliación COD** diaria; bloqueo automático por desfase configurable.

### 4.6 SLA y reasignación

- Tiempo máximo “buscando repartidor” antes de siguiente ola o incentivo.
- Reasignación si no hay movimiento hacia pickup (definir umbrales y avisos al DM).

---

## 5. Roadmap por fases (orden sugerido)

### Fase 1 — Fundaciones (prioridad alta)

| # | Tarea | Detalle |
|---|--------|---------|
| 1.1 | Crear `DeliveryEligibilityService` (nombre final a convenir) | Entrada: DM + Order (o id); salida: `allowed` + `code` + `message_key`. Reglas: aprobado, no suspendido, activo, max pedidos, rechazo Redis, límites básicos. |
| 1.2 | Integrar en `accept_order` | Al inicio (tras lock): si no elegible → 4xx con código estable. |
| 1.3 | Códigos de error contractuales | Ej. `cash_limit`, `tier_restricted`, `dm_suspended`, `max_orders`, `out_of_zone`. Documentar en este repo + app (`es.json`). |
| 1.4 | Telemetría | Tabla o log `assignment_events` (order_id, dm_id, wave, razón exclude). |
| 1.5 | Admin mínimo | Ver flags de suspensión, cash, tier (cuando exista); acción manual suspender/liberar con auditoría. **Hecho:** panel en preview repartidor + tabla `delivery_man_admin_audit_logs` al suspender/liberar. |

**Criterio de hecho:** ningún pedido aceptado que viole las reglas que el servicio implemente; logs revisables.

### Fase 2 — Tiers y límites dinámicos

| # | Tarea | Detalle |
|---|--------|---------|
| 2.1 | Modelo de datos tier | Campos en `delivery_men` o tabla relacionada + migración. **Hecho:** `dm_tier`, `dm_tier_source` (auto/manual), `dm_tier_updated_at`, `dm_tier_reason`. |
| 2.2 | Tabla o config `dm_tier_limits` | `max_concurrent_orders`, `max_cash_cod`, `max_order_value_cod` por tier. **Hecho:** tabla + seed en migración. |
| 2.3 | Job recálculo tier | Cron + notificación push/email al cambio. **Hecho:** `dm:recalculate-tiers` diario 03:15 + push; respeta `dm_tier_source=manual`. |
| 2.4 | Enlazar elegibilidad | Servicio Fase 1 lee tier y límites. **Hecho:** `DeliveryEligibilityService` + `tier_restricted`. |
| 2.5 | App | Mostrar tier, límites y mensajes de bloqueo en sheet de pedido / perfil. **Hecho (mínimo):** perfil + `es.json` / errores aceptar. |

### Fase 3 — Strikes e incidencias

| # | Tarea | Detalle |
|---|--------|---------|
| 3.1 | Catálogo de incidencias | CRUD admin + peso y si genera strike. |
| 3.2 | `delivery_man_strike_events` | dm_id, order_id, tipo, created_by, apelación. |
| 3.3 | Motor de strikes | Reglas en config; decaimiento temporal. |
| 3.4 | Cola apelaciones | Estado pendiente/aceptada/rechazada; congelar efecto hasta resolver. |

### Fase 4 — Asignación avanzada

| # | Tarea | Detalle |
|---|--------|---------|
| 4.1 | Score v1 | Fórmula documentada; usar en orden de oferta dentro de ola. |
| 4.2 | Políticas por zona | `zone_id` + tipo servicio → parámetros de ola y tiempos. |
| 4.3 | Incentivos (opcional) | Bonus pedido difícil; requiere definición financiera. |

### Fase 5 — Fraude y finanzas duras (continuo)

| # | Tarea | Detalle |
|---|--------|---------|
| 5.1 | Reglas antifraude v1 | Device fingerprint, límites de cuenta, GPS anómalo. |
| 5.2 | Reportes COD | Dashboard interno; alertas por desfase. |

---

## 6. Checklist de implementación técnica (backend)

- [x] Servicio de elegibilidad + tests unitarios (casos: OK, cash, max orders, suspendido, rechazo Redis).
- [x] `accept_order`: llamada única al servicio; respuestas con `code` en JSON de errores.
- [x] `get_latest_orders` y/o worker: opcional filtrar pedidos no elegibles para ese DM (`config/dm_assignment.php` + `DM_FILTER_LATEST_ORDERS_ELIGIBILITY`).
- [x] Migraciones: tier (`delivery_men` + `dm_tier_limits`); pendiente strikes/suspensiones dedicadas Fase 3.
- [x] Seeds / defaults: filas iniciales `dm_tier_limits` en migración.
- [x] Documentación API (códigos de error) para equipo app (`docs/API_DELIVERY_ACCEPT_ORDER_ERRORS.md`).
- [x] Admin mínimo (1.5): snapshot asignación + historial de suspensiones en preview del repartidor; auditoría en `delivery_man_admin_audit_logs`.

---

## 7. Checklist app repartidor (Flutter)

- [x] Mapear códigos de error del `accept_order` a strings en `es.json`.
- [ ] Mostrar “efectivo estimado si aceptas este pedido” y comparación con `dm_max_cash_in_hand` / tier.
- [x] Sección “Tu nivel” en perfil (API `dm_tier`, `dm_tier_limits`, `dm_tier_reason`). Strikes: Fase 3.
- [ ] Opcional: `getProfile()` en `resumed` si se aprueba o cambian límites (fuera de scope estricto de este doc; enlazar si se implementa).

---

## 8. KPIs sugeridos (seguimiento semanal)

- Tiempo medio asignación → aceptación; % pedidos sin DM en T minutos.
- Cancelaciones post-asignación por motivo y atribución.
- COD medio por DM y % cerca del techo.
- Ingreso neto por pedido (comisión − costos de incidencias/soporte).

---

## 9. Riesgos y mitigación

| Riesgo | Mitigación |
|--------|------------|
| Reglas demasiado agresivas | Strikes con decaimiento; apelaciones; rollout por zona. |
| Duplicidad de lógica | Un solo servicio de elegibilidad; prohibido copiar condiciones en 3 sitios. |
| Opacidad para repartidores | Texto claro en app + FAQ “cómo subir de nivel”. |

---

## 10. Próximo paso recomendado

Implementar **Fase 1 (1.1–1.3)** primero: máximo impacto en consistencia y control operativo con el menor cambio de esquema de datos.

---

## Referencias rápidas en código (backend)

- Rutas API repartidor: `routes/api/v1/api.php` (grupo `delivery-man` + middlewares `dm.api`, `dm.pending_revision_gate`).
- Controlador principal: `app/Http/Controllers/Api/V1/DeliverymanController.php` (`get_latest_orders`, `accept_order`, `ignore_order`, `update_order_status`, perfil/efectivo).
- Config: `config('dm_maximum_orders')`, `BusinessSetting` claves efectivo DM.

---

*Mantener este archivo actualizado al cerrar cada fase (fecha, PR, decisiones de producto).*

<?php

namespace Modules\Gateways\Traits;

/**
 * Puente hacia la implementación central (incluye LabsMobile y demás gateways).
 * Los controladores usan este namespace cuando el addon Gateways está publicado.
 */
trait SmsGateway
{
    use \App\Traits\SmsGateway;
}

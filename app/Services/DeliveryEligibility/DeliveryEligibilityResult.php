<?php

namespace App\Services\DeliveryEligibility;

/**
 * Resultado de elegibilidad para aceptar u ofrecer un pedido.
 *
 * @see docs/API_DELIVERY_ACCEPT_ORDER_ERRORS.md
 */
final class DeliveryEligibilityResult
{
    public function __construct(
        public readonly bool $allowed,
        public readonly ?string $code = null,
        public readonly ?string $messageKey = null,
        public readonly int $httpStatus = 200,
        public readonly ?string $message = null,
    ) {}

    public static function ok(): self
    {
        return new self(allowed: true);
    }

    public static function deny(
        string $code,
        string $messageKey,
        int $httpStatus,
        string $message,
    ): self {
        return new self(
            allowed: false,
            code: $code,
            messageKey: $messageKey,
            httpStatus: $httpStatus,
            message: $message,
        );
    }
}

<?php

namespace Tests\Unit;

use App\Models\DeliveryMan;
use App\Models\Order;
use App\Services\DeliveryEligibility\DeliveryEligibilityService;
use Tests\TestCase;

/**
 * Modelos sin persistir: solo atributos usados por el servicio.
 *
 * @internal
 */
/**
 * @internal
 */
final class DeliveryEligibilityTierRestrictStub extends DeliveryEligibilityService
{
    public function __construct(?\Closure $redisSisMember = null)
    {
        parent::__construct($redisSisMember);
    }

    protected function orderExceedsTierCodOrderValue(DeliveryMan $dm, Order $order): bool
    {
        return true;
    }
}

final class DeliveryEligibilityStrikeBlockStub extends DeliveryEligibilityService
{
    public function __construct(
        ?\Closure $redisSisMember = null,
        private readonly string $mode = 'weight',
    ) {
        parent::__construct($redisSisMember);
    }

    protected function isBlockedByTemporaryStrikeSuspension(DeliveryMan $dm): bool
    {
        return $this->mode === 'suspend';
    }

    protected function exceedsStrikeWeightThreshold(DeliveryMan $dm): bool
    {
        return $this->mode === 'weight';
    }
}

final class DeliveryEligibilityServiceStub extends DeliveryEligibilityService
{
    public function __construct(
        ?\Closure $redisSisMember = null,
        private readonly bool $forceCashExceeded = false,
        private readonly float $cashLimitForDisplay = 100,
    ) {
        parent::__construct($redisSisMember);
    }

    protected function exceedsCashInHandLimit(DeliveryMan $dm, Order $order): bool
    {
        return $this->forceCashExceeded;
    }

    protected function getDmMaxCashInHandLimit(DeliveryMan $dm): float
    {
        return $this->cashLimitForDisplay;
    }

    protected function formatCashLimitDeniedMessage(float $limit): string
    {
        return 'test-cash-limit-message';
    }
}

class DeliveryEligibilityServiceTest extends TestCase
{
    private function dm(array $overrides = []): DeliveryMan
    {
        $dm = new DeliveryMan;
        $dm->forceFill(array_merge([
            'id' => 1,
            'application_status' => 'approved',
            'status' => true,
            'active' => 1,
            'current_orders' => 0,
            'zone_id' => null,
        ], $overrides));

        return $dm;
    }

    private function order(array $overrides = []): Order
    {
        $order = new Order;
        $order->forceFill(array_merge([
            'id' => 100,
            'order_type' => 'delivery',
            'payment_method' => 'digital_payment',
            'order_amount' => 50,
        ], $overrides));

        return $order;
    }

    public function test_denies_when_not_approved(): void
    {
        $svc = new DeliveryEligibilityService(fn () => false);
        $r = $svc->evaluateForAccept($this->dm(['application_status' => 'pending']), $this->order(), null, null);

        $this->assertFalse($r->allowed);
        $this->assertSame('not_approved', $r->code);
        $this->assertSame(403, $r->httpStatus);
    }

    public function test_denies_when_suspended(): void
    {
        $svc = new DeliveryEligibilityService(fn () => false);
        $r = $svc->evaluateForAccept($this->dm(['status' => false]), $this->order(), null, null);

        $this->assertFalse($r->allowed);
        $this->assertSame('dm_suspended', $r->code);
    }

    public function test_denies_when_offline(): void
    {
        $svc = new DeliveryEligibilityService(fn () => false);
        $r = $svc->evaluateForAccept($this->dm(['active' => 0]), $this->order(), null, null);

        $this->assertFalse($r->allowed);
        $this->assertSame('offline', $r->code);
        $this->assertSame(404, $r->httpStatus);
    }

    public function test_denies_when_order_rejected_in_redis(): void
    {
        $svc = new DeliveryEligibilityService(fn (string $key, int $member) => $key === 'order:100:rejected' && $member === 1);
        $r = $svc->evaluateForAccept($this->dm(), $this->order(['id' => 100]), null, null);

        $this->assertFalse($r->allowed);
        $this->assertSame('order_rejected', $r->code);
    }

    public function test_denies_max_orders(): void
    {
        config(['dm_maximum_orders' => 2]);
        $svc = new DeliveryEligibilityService(fn () => false);
        $r = $svc->evaluateForAccept($this->dm(['current_orders' => 2]), $this->order(), null, null);

        $this->assertFalse($r->allowed);
        $this->assertSame('max_orders', $r->code);
        $this->assertSame(405, $r->httpStatus);
    }

    public function test_denies_tier_restricted_via_stub(): void
    {
        $svc = new DeliveryEligibilityTierRestrictStub(fn () => false);
        $r = $svc->evaluateForAccept(
            $this->dm(),
            $this->order(['payment_method' => 'cash_on_delivery', 'order_amount' => 900]),
            null,
            null,
        );

        $this->assertFalse($r->allowed);
        $this->assertSame('tier_restricted', $r->code);
        $this->assertSame(403, $r->httpStatus);
    }

    public function test_denies_cash_limit_via_stub(): void
    {
        \App\Models\BusinessSetting::updateOrCreate(
            ['key' => 'high_value_strategy'],
            ['value' => 'strict_block']
        );

        $svc = new DeliveryEligibilityServiceStub(
            redisSisMember: fn () => false,
            forceCashExceeded: true,
            cashLimitForDisplay: 250,
        );
        $r = $svc->evaluateForAccept($this->dm(), $this->order(['order_amount' => 800]), null, null);

        $this->assertFalse($r->allowed);
        $this->assertSame('cash_limit', $r->code);
        $this->assertSame(405, $r->httpStatus);
    }

    public function test_allows_when_no_cod_and_under_max(): void
    {
        config(['dm_maximum_orders' => 10]);
        $svc = new DeliveryEligibilityService(fn () => false);
        $r = $svc->evaluateForAccept($this->dm(), $this->order(), null, null);

        $this->assertTrue($r->allowed);
    }

    public function test_denies_strike_weight_via_stub(): void
    {
        $svc = new DeliveryEligibilityStrikeBlockStub(fn () => false, 'weight');
        $r = $svc->evaluateForAccept($this->dm(), $this->order(), null, null);

        $this->assertFalse($r->allowed);
        $this->assertSame('strike_blocked', $r->code);
        $this->assertSame('strike_weight_limit', $r->messageKey);
        $this->assertSame(403, $r->httpStatus);
    }

    public function test_denies_strike_temp_suspension_via_stub(): void
    {
        $svc = new DeliveryEligibilityStrikeBlockStub(fn () => false, 'suspend');
        $r = $svc->evaluateForAccept($this->dm(), $this->order(), null, null);

        $this->assertFalse($r->allowed);
        $this->assertSame('strike_blocked', $r->code);
        $this->assertSame('strike_temp_suspension', $r->messageKey);
    }
}

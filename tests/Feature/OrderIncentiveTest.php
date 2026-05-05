<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Store;
use App\Models\Zone;
use App\CentralLogics\OrderLogic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;

class OrderIncentiveTest extends TestCase
{
    // Use RefreshDatabase if you want a clean DB, but since the user has a live Herd setup, 
    // I will create temp records and delete them.

    public function test_order_incentive_progression()
    {
        // 1. Setup: Create a test order
        $zone = Zone::first();
        $store = Store::where('zone_id', $zone->id)->first();
        
        $order = new Order();
        $order->user_id = 1;
        $order->order_amount = 500;
        $order->delivery_charge = 50;
        $order->original_delivery_charge = 50;
        $order->order_status = 'pending';
        $order->order_type = 'delivery';
        $order->payment_method = 'cash_on_delivery';
        $order->store_id = $store->id;
        $order->zone_id = $zone->id;
        $order->created_at = now();
        $order->save();

        $this->assertEquals(0, $order->incentive_level);
        $this->assertEquals(0, $order->incentive_amount);

        // 2. Simulate 6 minutes passing (Level 1 threshold)
        Carbon::setTestNow(now()->addMinutes(6));
        Artisan::call('order:incentivize');
        
        $order->refresh();
        $this->assertEquals(1, $order->incentive_level);
        $this->assertGreaterThan(0, $order->incentive_amount);
        
        $net_earning_v1 = OrderLogic::dm_net_earning($order);
        // At Level 1, dm_net_earning should be equal to original_delivery_charge (50)
        $this->assertEquals(50, $net_earning_v1);

        // 3. Simulate 12 minutes passing (Level 2 threshold)
        Carbon::setTestNow(now()->addMinutes(12));
        Artisan::call('order:incentivize');
        
        $order->refresh();
        $this->assertEquals(2, $order->incentive_level);
        
        $net_earning_v2 = OrderLogic::dm_net_earning($order);
        // At Level 2, should be 50 + 10% admin profit
        $this->assertGreaterThan(50, $net_earning_v2);

        // Clean up
        Carbon::setTestNow();
        $order->delete();
    }
}

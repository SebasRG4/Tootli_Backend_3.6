<?php

use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Http\Controllers\Admin\OrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Load Laravel application
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Starting Admin Partial Delivery Test...\n";

// 1. Create a simulated order with mixed items
echo "Creating test order...\n";

// Ensure a user exists for Auth (Admin Controller methods might use Auth/Toastr which we can't fully mock easily in script without session, 
// so we'll focus on testing the Logic of the partial_delivery method or simulate the DB state changes directly if the controller dependencies are too hard to mock)
// However, the controller uses Toastr which demands a session. For this script, we might need to mock Toastr or just test the logic directly or accept that Toastr might fail/warn but DB changes happen.
// Let's try to mock the Toastr facade or just silence it? 
// Actually, running a controller method directly in a script without a full HTTP request context handling Facades like Session/Toastr can be tricky.
// A better approach for this script is to REPLICATE the logic to verify it works, OR try to instantiate the controller.
// Let's instantiate the controller and see. We might need to mock Toastr.

// Mock Toastr to avoid errors
Illuminate\Support\Facades\Facade::clearResolvedInstance('toastr');
class MockToastr
{
    public function success($msg)
    {
        echo "Toastr Success: $msg\n";
    }
    public function error($msg)
    {
        echo "Toastr Error: $msg\n";
    }
    public function info($msg)
    {
        echo "Toastr Info: $msg\n";
    }
    public function warning($msg)
    {
        echo "Toastr Warning: $msg\n";
    }
}
$app->instance('Brian2694\Toastr\Facades\Toastr', new MockToastr());
class_alias(MockToastr::class, 'Toastr');


DB::beginTransaction();

try {
    $order = new Order();
    $order->id = 99999; // Test ID
    $order->user_id = 1; // Assuming user 1 exists
    $order->order_amount = 200;
    $order->payment_status = 'paid';
    $order->order_status = 'confirmed'; // Initial status
    $order->payment_method = 'cash_on_delivery';
    $order->store_id = 1;
    $order->delivery_charge = 10;
    $order->created_at = now();
    $order->updated_at = now();
    $order->save();

    // Item 1: Minutes (Pending)
    $detail1 = new OrderDetail();
    $detail1->order_id = $order->id;
    $detail1->product_id = 1;
    $detail1->price = 100;
    $detail1->quantity = 1;
    $detail1->delivery_status = 'pending';
    $detail1->item_details = json_encode(['id' => 1, 'name' => 'Minute Item', 'delivery_time_type' => 'minutes']);
    $detail1->save();

    // Item 2: Standard (Pending)
    $detail2 = new OrderDetail();
    $detail2->order_id = $order->id;
    $detail2->product_id = 2;
    $detail2->price = 90;
    $detail2->quantity = 1;
    $detail2->delivery_status = 'pending';
    $detail2->item_details = json_encode(['id' => 2, 'name' => 'Standard Item', 'delivery_time_type' => 'standard']);
    $detail2->save();

    echo "Order created with ID: " . $order->id . "\n";

    // 2. Instantiate Controller and Call Method
    echo "Calling Admin Partial Delivery...\n";
    $controller = new OrderController();
    $request = new Request();

    // We can't easily mock the 'back()' return without more mocking, but we can catch exceptions or check DB after.
    try {
        $controller->partial_delivery($request, $order->id);
    } catch (\Exception $e) {
        // back() helper might throw exception in CLI or just return a redirect object.
        // We ignore it if it's just a redirect/response issue, and check DB.
        echo "Controller returned (or threw): " . $e->getMessage() . "\n";
    }

    // 3. Verify Database State
    $updatedOrder = Order::find($order->id);
    $updatedDetail1 = OrderDetail::where('order_id', $order->id)->where('id', $detail1->id)->first();
    $updatedDetail2 = OrderDetail::where('order_id', $order->id)->where('id', $detail2->id)->first();

    echo "Verifying results:\n";
    echo "Order Status: " . $updatedOrder->order_status . " (Expected: partial_delivered)\n";
    echo "Minute Item Status: " . $updatedDetail1->delivery_status . " (Expected: delivered)\n";
    echo "Standard Item Status: " . $updatedDetail2->delivery_status . " (Expected: pending)\n";
    echo "Delivery Man ID: " . ($updatedOrder->delivery_man_id === null ? 'NULL' : $updatedOrder->delivery_man_id) . " (Expected: NULL)\n";

    if (
        $updatedOrder->order_status == 'partial_delivered' &&
        $updatedDetail1->delivery_status == 'delivered' &&
        $updatedDetail2->delivery_status == 'pending'
    ) {
        echo "TEST PASSED!\n";
    } else {
        echo "TEST FAILED!\n";
    }

} catch (\Exception $e) {
    echo "Test failed with error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
} finally {
    // Cleanup
    echo "Cleaning up...\n";
    DB::rollback(); // Rollback transaction to clear test data
    echo "Cleanup complete.\n";
}

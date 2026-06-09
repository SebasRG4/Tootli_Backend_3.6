<?php
 
namespace App\Http\Controllers\Api\V1;
 
use App\Http\Controllers\Controller;
use App\Models\DeliveryMan;
use App\Models\Order;
use App\Models\DmCustomerCallAttempt;
use Illuminate\Http\Request;
 
class PlivoController extends Controller
{
    /**
     * Webhook called by Plivo when a call is placed to the proxy number.
     * Directs the call to the customer or deliveryman depending on who is calling.
     */
    public function answer(Request $request)
    {
        $from = $request->input('From');
        $to = $request->input('To'); // Plivo proxy number
 
        if (empty($from) || empty($to)) {
            return $this->errorXml('Información de llamada incompleta.');
        }
 
        // Normalize "From" phone number: strip non-digits and extract last 10 digits
        $cleanFrom = preg_replace('/\D/', '', $from);
        if (strlen($cleanFrom) < 10) {
            return $this->errorXml('Número de origen no válido.');
        }
        $suffix = substr($cleanFrom, -10);
 
        // 1. Check if the caller is a DeliveryMan
        $dm = DeliveryMan::where('phone', 'like', '%' . $suffix)->first();
        if ($dm) {
            // Find active order for this delivery man
            $order = Order::where('delivery_man_id', $dm->id)
                ->whereIn('order_status', ['accepted', 'confirmed', 'processing', 'handover', 'picked_up'])
                ->first();
 
            if ($order) {
                // Get customer phone number
                $custPhoneRaw = $order->delivery_address->contact_person_number ?? $order->customer->phone ?? null;
                $cleanCustPhone = preg_replace('/\D/', '', $custPhoneRaw ?? '');
                
                if (!empty($cleanCustPhone)) {
                    // Log the attempt on the backend
                    $lastAttempt = DmCustomerCallAttempt::where('order_id', $order->id)
                        ->where('delivery_man_id', $dm->id)
                        ->max('attempt_number') ?? 0;
 
                    DmCustomerCallAttempt::create([
                        'order_id' => $order->id,
                        'delivery_man_id' => $dm->id,
                        'attempt_number' => $lastAttempt + 1,
                        'confirmed_at_ms' => now()->getTimestampMs(),
                        'confirmed_at' => now(),
                    ]);
 
                    return $this->dialXml($cleanCustPhone, $to);
                }
            }
        }
 
        // 2. Check if the caller is a Customer of an active order
        $order = Order::whereIn('order_status', ['accepted', 'confirmed', 'processing', 'handover', 'picked_up'])
            ->where(function ($q) use ($suffix) {
                $q->whereHas('customer', function ($sq) use ($suffix) {
                    $sq->where('phone', 'like', '%' . $suffix);
                })->orWhereHas('delivery_address', function ($sq) use ($suffix) {
                    $sq->where('contact_person_number', 'like', '%' . $suffix);
                });
            })
            ->first();
 
        if ($order && $order->delivery_man) {
            $dmPhoneRaw = $order->delivery_man->phone;
            $cleanDmPhone = preg_replace('/\D/', '', $dmPhoneRaw ?? '');
 
            if (!empty($cleanDmPhone)) {
                // Return XML linking the customer to the delivery man
                return $this->dialXml($cleanDmPhone, $to);
            }
        }
 
        return $this->errorXml('No pudimos enlazar su llamada con el destinatario.');
    }
 
    /**
     * Generates Plivo Dial XML.
     */
    private function dialXml($toPhone, $callerId)
    {
        // Enforce E.164 format with country code (defaults to +52 for Mexico)
        if (!str_starts_with($toPhone, '+')) {
            if (str_starts_with($toPhone, '52')) {
                $toPhone = '+' . $toPhone;
            } else {
                $toPhone = '+52' . $toPhone;
            }
        }
 
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<Response>';
        $xml .= '<Dial callerId="' . $callerId . '">';
        $xml .= '<Number>' . $toPhone . '</Number>';
        $xml .= '</Dial>';
        $xml .= '</Response>';
 
        return response($xml)->header('Content-Type', 'text/xml');
    }
 
    /**
     * Generates Plivo Speak Error XML.
     */
    private function errorXml($message)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<Response>';
        $xml .= '<Speak>' . htmlspecialchars($message, ENT_XML1) . '</Speak>';
        $xml .= '</Response>';
 
        return response($xml)->header('Content-Type', 'text/xml');
    }
}

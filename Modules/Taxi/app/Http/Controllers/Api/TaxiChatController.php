<?php

namespace Modules\Taxi\Http\Controllers\Api;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\DeliveryMan;
use App\Models\UserInfo;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaxiChatController extends Controller
{
    /**
     * Get messages for a taxi driver conversation
     * Ensures UserInfo exists for the driver before fetching messages
     */
    public function getTaxiDriverMessages(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'delivery_man_id' => 'required|integer',
            'offset' => 'integer',
            'limit' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $limit = $request['limit'] ?? 10;
        $offset = $request['offset'] ?? 1;
        $deliveryManId = $request['delivery_man_id'];

        // Get user (customer)
        $user = UserInfo::where('user_id', $request->user()->id)->first();
        if (!$user) {
            $user = new UserInfo();
            $user->user_id = $request->user()->id;
            $user->f_name = $request->user()->f_name;
            $user->l_name = $request->user()->l_name;
            $user->phone = $request->user()->phone;
            $user->email = $request->user()->email;
            $user->image = $request->user()->image;
            $user->save();
        }

        // Get delivery man (taxi driver)
        $deliveryMan = DeliveryMan::find($deliveryManId);
        if (!$deliveryMan) {
            return response()->json([
                'errors' => [['code' => 'delivery_man', 'message' => 'Delivery man not found']]
            ], 404);
        }

        // Ensure UserInfo exists for delivery man
        $dmUserInfo = UserInfo::where('deliveryman_id', $deliveryManId)->first();
        if (!$dmUserInfo) {
            $dmUserInfo = new UserInfo();
            $dmUserInfo->deliveryman_id = $deliveryMan->id;
            $dmUserInfo->f_name = $deliveryMan->f_name;
            $dmUserInfo->l_name = $deliveryMan->l_name;
            $dmUserInfo->phone = $deliveryMan->phone;
            $dmUserInfo->email = $deliveryMan->email;
            $dmUserInfo->image = $deliveryMan->image;
            $dmUserInfo->save();
        }

        // Find or create conversation
        $conversation = Conversation::whereConversation($user->id, $dmUserInfo->id)->first();

        if (!$conversation) {
            $conversation = new Conversation();
            $conversation->sender_id = $user->id;
            $conversation->sender_type = 'customer';
            $conversation->receiver_id = $dmUserInfo->id;
            $conversation->receiver_type = 'delivery_man';
            $conversation->unread_message_count = 0;
            $conversation->last_message_time = now();
            $conversation->save();
        }

        // Get messages
        $messages = Message::where(['conversation_id' => $conversation->id])
            ->with('taxiRide')
            ->latest()
            ->paginate($limit, ['*'], 'page', $offset);

        $messages->getCollection()->transform(function ($message) {
            if ($message->taxiRide) {
                $message->taxiRide->id = (int) $message->taxiRide->id;
                $message->taxiRide->estimated_fare = (float) $message->taxiRide->estimated_fare;
            }
            return $message;
        });

        // Load conversation with relationships
        $conv = Conversation::with('sender', 'receiver', 'last_message')->find($conversation->id);

        $data = [
            'total_size' => intval($messages->total()),
            'limit' => intval($limit),
            'offset' => intval($offset),
            'status' => true,
            'messages' => $messages->items(),
            'conversation' => $conv,
        ];

        return response()->json($data, 200);
    }
}

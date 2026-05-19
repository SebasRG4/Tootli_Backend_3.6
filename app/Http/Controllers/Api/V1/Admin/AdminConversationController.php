<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\UserInfo;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminConversationController extends Controller
{
    /**
     * Autentica al admin por Bearer token.
     */
    private function getAdmin(Request $request): ?Admin
    {
        return Admin::where('auth_token', $request->bearerToken())->first();
    }

    /**
     * GET /api/v1/admin/message/list
     * Lista todas las conversaciones donde el receptor o emisor es admin.
     */
    public function list(Request $request)
    {
        $admin = $this->getAdmin($request);
        if (!$admin) {
            return response()->json(['errors' => [['code' => 'auth-001', 'message' => 'No autorizado']]], 401);
        }

        $limit  = $request->get('limit', 10);
        $offset = $request->get('offset', 1);
        $key    = $request->get('key');

        $query = Conversation::with(['sender', 'receiver', 'last_message'])
            ->whereUserType('admin');

        if ($key) {
            $terms = explode(' ', $key);
            $query->where(function ($q) use ($terms) {
                $q->whereHas('sender', function ($q2) use ($terms) {
                    foreach ($terms as $t) {
                        $q2->where('f_name', 'like', "%{$t}%")
                           ->orWhere('l_name', 'like', "%{$t}%")
                           ->orWhere('phone',  'like', "%{$t}%");
                    }
                })->orWhereHas('receiver', function ($q2) use ($terms) {
                    foreach ($terms as $t) {
                        $q2->where('f_name', 'like', "%{$t}%")
                           ->orWhere('l_name', 'like', "%{$t}%")
                           ->orWhere('phone',  'like', "%{$t}%");
                    }
                });
            });
        }

        $conversations = $query->orderBy('last_message_time', 'DESC')
            ->paginate($limit, ['*'], 'page', $offset);

        return response()->json([
            'total_size'    => intval($conversations->total()),
            'limit'         => intval($limit),
            'offset'        => intval($offset),
            'conversations' => $conversations->items(),
        ], 200);
    }

    /**
     * GET /api/v1/admin/message/details?conversation_id=X
     * Devuelve los mensajes de una conversación y la marca como leída.
     */
    public function details(Request $request)
    {
        $admin = $this->getAdmin($request);
        if (!$admin) {
            return response()->json(['errors' => [['code' => 'auth-001', 'message' => 'No autorizado']]], 401);
        }

        $limit  = $request->get('limit', 20);
        $offset = $request->get('offset', 1);

        $conversation = Conversation::with(['sender', 'receiver', 'last_message'])
            ->find($request->conversation_id);

        if (!$conversation) {
            return response()->json(['errors' => [['code' => 'conv-001', 'message' => 'Conversación no encontrada']]], 404);
        }

        // Marcar como leída
        $conversation->unread_message_count = 0;
        $conversation->save();

        $messages = Message::where('conversation_id', $conversation->id)
            ->latest()
            ->paginate($limit, ['*'], 'page', $offset);

        return response()->json([
            'total_size'   => intval($messages->total()),
            'limit'        => intval($limit),
            'offset'       => intval($offset),
            'conversation' => $conversation,
            'messages'     => $messages->items(),
        ], 200);
    }

    /**
     * POST /api/v1/admin/message/send
     * Envía un mensaje como administrador al usuario/repartidor/tienda.
     *
     * Body: { conversation_id: X, message: "texto" }
     */
    public function send(Request $request)
    {
        $admin = $this->getAdmin($request);
        if (!$admin) {
            return response()->json(['errors' => [['code' => 'auth-001', 'message' => 'No autorizado']]], 401);
        }

        if (!$request->filled('message') && !$request->has('image')) {
            return response()->json(['errors' => [['code' => 'val-001', 'message' => 'El mensaje no puede estar vacío']]], 422);
        }

        // Obtener o crear el UserInfo del admin (admin_id = 0 es el super-admin)
        $adminUserInfo = UserInfo::where('admin_id', 0)->first();
        if (!$adminUserInfo) {
            $adminUserInfo = new UserInfo();
            $adminUserInfo->admin_id = 0;
            $adminUserInfo->f_name   = $admin->f_name ?? 'Admin';
            $adminUserInfo->l_name   = $admin->l_name ?? 'Tootli';
            $adminUserInfo->email    = $admin->email  ?? '';
            $adminUserInfo->save();
        }

        $conversation = Conversation::with(['sender', 'receiver'])
            ->find($request->conversation_id);

        if (!$conversation) {
            return response()->json(['errors' => [['code' => 'conv-001', 'message' => 'Conversación no encontrada']]], 404);
        }

        // Determinar el receptor (quien NO es admin)
        $receiverInfo = null;
        if ($conversation->sender_id == $adminUserInfo->id) {
            $receiverInfo = $conversation->receiver;
        } else {
            $receiverInfo = $conversation->sender;
        }

        // Guardar mensaje
        $message                  = new Message();
        $message->conversation_id = $conversation->id;
        $message->sender_id       = $adminUserInfo->id;
        $message->message         = $request->message;
        $message->save();

        $conversation->unread_message_count = ($conversation->unread_message_count ?? 0) + 1;
        $conversation->last_message_id      = $message->id;
        $conversation->last_message_time    = Carbon::now()->toDateTimeString();
        $conversation->save();

        // Enviar notificación push al receptor
        if ($receiverInfo) {
            $fcmToken = null;
            if ($receiverInfo->deliveryman_id) {
                $fcmToken = $receiverInfo->delivery_man?->fcm_token;
            } elseif ($receiverInfo->vendor_id) {
                $fcmToken = $receiverInfo->vendor?->auth_token;
            } else {
                $fcmToken = $receiverInfo->user?->cm_firebase_token;
            }

            if ($fcmToken) {
                Helpers::send_push_notif_to_device($fcmToken, [
                    'title'           => 'Respuesta del administrador',
                    'description'     => $request->message,
                    'order_id'        => '',
                    'image'           => '',
                    'type'            => 'message',
                    'conversation_id' => $conversation->id,
                    'sender_type'     => 'admin',
                ]);
            }
        }

        return response()->json([
            'message'      => 'Mensaje enviado',
            'sent_message' => $message,
            'conversation' => $conversation->fresh(['sender', 'receiver', 'last_message']),
        ], 200);
    }

    /**
     * GET /api/v1/admin/message/unread-count
     * Número total de mensajes no leídos dirigidos al admin.
     */
    public function unreadCount(Request $request)
    {
        $admin = $this->getAdmin($request);
        if (!$admin) {
            return response()->json(['errors' => [['code' => 'auth-001', 'message' => 'No autorizado']]], 401);
        }

        $adminUserInfo = UserInfo::where('admin_id', 0)->first();
        $adminUserInfoId = $adminUserInfo ? $adminUserInfo->id : 0;

        $count = Conversation::whereUserType('admin')
            ->where('unread_message_count', '>', 0)
            ->whereHas('sender')
            ->whereHas('receiver')
            ->whereHas('last_message', function ($q) use ($adminUserInfoId) {
                $q->where('sender_id', '!=', $adminUserInfoId);
            })
            ->sum('unread_message_count');

        return response()->json(['count' => intval($count)], 200);
    }
}

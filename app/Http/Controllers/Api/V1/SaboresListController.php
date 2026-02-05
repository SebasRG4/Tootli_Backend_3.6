<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\UserList;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SaboresListController extends Controller
{
    /**
     * GET /api/v1/sabores/lists
     * Obtener todas las listas del usuario autenticado
     */
    public function index(Request $request)
    {
        $lists = UserList::where('user_id', $request->user()->id)
            ->with([
                'stores' => function ($query) use ($request) {
                    $longitude = $request->header('longitude');
                    $latitude = $request->header('latitude');

                    // Cargar relaciones necesarias pero NO filtrar por módulo/zona actual strictamente
                    // para que el usuario pueda ver todo lo que guardó.
                    $query->with(['module'])
                        ->withOpen($longitude ?? 0, $latitude ?? 0);
                }
            ])
            ->get();

        // Formatear stores con Helpers existente
        $formattedLists = $lists->map(function ($list) {
            $formattedStores = [];
            foreach ($list->stores as $store) {
                $note = $store->pivot ? $store->pivot->note : null;
                $formattedStore = Helpers::store_data_formatting($store, false);
                $formattedStore['note'] = $note;
                $formattedStores[] = $formattedStore;
            }

            return [
                'id' => $list->id,
                'name' => $list->name,
                'store_count' => count($formattedStores),
                'cover_image' => count($formattedStores) > 0
                    ? ($formattedStores[0]['cover_photo_full_url'] ?? $formattedStores[0]['logo_full_url'] ?? null)
                    : null,
                'stores' => $formattedStores,
                'created_at' => $list->created_at,
            ];
        });

        return response()->json([
            'lists' => $formattedLists
        ], 200);
    }

    /**
     * POST /api/v1/sabores/lists
     * Crear una nueva lista
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        // Verificar si ya existe una lista con ese nombre
        $exists = UserList::where('user_id', $request->user()->id)
            ->where('name', $request->name)
            ->exists();

        if ($exists) {
            return response()->json([
                'errors' => [['code' => 'name', 'message' => translate('messages.list_name_already_exists')]]
            ], 409);
        }

        $list = UserList::create([
            'user_id' => $request->user()->id,
            'name' => $request->name,
        ]);

        return response()->json([
            'message' => translate('messages.list_created_successfully'),
            'list' => [
                'id' => $list->id,
                'name' => $list->name,
                'store_count' => 0,
                'cover_image' => null,
                'stores' => [],
                'created_at' => $list->created_at,
            ]
        ], 201);
    }

    /**
     * DELETE /api/v1/sabores/lists/{id}
     * Eliminar una lista
     */
    public function destroy(Request $request, $id)
    {
        $list = UserList::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->first();

        if (!$list) {
            return response()->json([
                'errors' => [['code' => 'list', 'message' => translate('messages.list_not_found')]]
            ], 404);
        }

        $list->delete();

        return response()->json([
            'message' => translate('messages.list_deleted_successfully')
        ], 200);
    }

    /**
     * POST /api/v1/sabores/lists/{id}/stores
     * Agregar un store a una lista
     */
    public function addStore(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => 'required|integer|exists:stores,id',
            'note' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $list = UserList::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->first();

        if (!$list) {
            return response()->json([
                'errors' => [['code' => 'list', 'message' => translate('messages.list_not_found')]]
            ], 404);
        }

        // Verificar si el store ya está en la lista
        if ($list->stores()->where('store_id', $request->store_id)->exists()) {
            return response()->json([
                'errors' => [['code' => 'store', 'message' => translate('messages.store_already_in_list')]]
            ], 409);
        }

        $list->stores()->attach($request->store_id, ['note' => $request->note]);

        return response()->json([
            'message' => translate('messages.store_added_to_list')
        ], 200);
    }

    /**
     * DELETE /api/v1/sabores/lists/{id}/stores/{store_id}
     * Quitar un store de una lista
     */
    public function removeStore(Request $request, $id, $storeId)
    {
        $list = UserList::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->first();

        if (!$list) {
            return response()->json([
                'errors' => [['code' => 'list', 'message' => translate('messages.list_not_found')]]
            ], 404);
        }

        if (!$list->stores()->where('store_id', $storeId)->exists()) {
            return response()->json([
                'errors' => [['code' => 'store', 'message' => translate('messages.store_not_in_list')]]
            ], 404);
        }

        $list->stores()->detach($storeId);

        return response()->json([
            'message' => translate('messages.store_removed_from_list')
        ], 200);
    }
}

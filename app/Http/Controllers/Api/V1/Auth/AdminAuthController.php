<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AdminAuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $data = [
            'email' => $request->email,
            'password' => $request->password
        ];

        if (auth('admin')->attempt($data)) {
            $token = Str::random(120);

            $admin = Admin::with('role')->where('email', $request->email)->first();
            
            $isAuthorized = ($admin->role_id == 1) || 
                            ($admin->role && in_array(strtolower($admin->role->name), ['inversionista', 'inversionistas', 'investor']));

            if (!$isAuthorized) {
                auth('admin')->logout();
                return response()->json([
                    'errors' => [
                        ['code' => 'auth-001', 'message' => 'Unauthorized']
                    ]
                ], 401);
            }

            $admin->auth_token = $token;
            $admin->save();

            return response()->json(['token' => $token, 'admin' => $admin], 200);
        } else {
            return response()->json([
                'errors' => [
                    ['code' => 'auth-001', 'message' => 'Incorrect credentials']
                ]
            ], 401);
        }
    }
}

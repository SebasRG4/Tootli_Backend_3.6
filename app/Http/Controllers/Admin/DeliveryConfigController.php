<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;

class DeliveryConfigController extends Controller
{
    public function cash_settings()
    {
        return view('admin-views.business-settings.delivery-cash-settings');
    }

    public function update_cash_settings(Request $request)
    {
        $request->validate([
            'dm_max_cash_in_hand' => 'required|numeric|min:0',
            'high_value_threshold' => 'required|numeric|min:0',
            'max_time_without_deposit_minutes' => 'required|numeric|min:0',
        ]);

        $keys = [
            'dm_max_cash_in_hand',
            'high_value_threshold',
            'max_time_without_deposit_minutes',
            'high_value_strategy',
            'admin_whatsapp_number'
        ];

        foreach ($keys as $key) {
            BusinessSetting::updateOrInsert(['key' => $key], [
                'value' => $request->get($key)
            ]);
        }

        Toastr::success('Configuraciones de efectivo actualizadas correctamente.');
        return back();
    }
}

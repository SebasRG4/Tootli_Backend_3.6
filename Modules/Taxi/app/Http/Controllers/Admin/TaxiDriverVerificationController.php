<?php

namespace Modules\Taxi\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryMan;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;

class TaxiDriverVerificationController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $status = $request->status;

        $drivers = DeliveryMan::canTaxi()
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('f_name', 'like', "%{$search}%")
                        ->orWhere('l_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($status, function ($q) use ($status) {
                if ($status == 'verified') {
                    $q->where('taxi_is_verified', 1);
                } elseif ($status == 'pending') {
                    $q->where('taxi_is_verified', 0);
                }
            })
            ->orderBy('taxi_is_verified', 'asc') // Pendings first
            ->latest()
            ->paginate(config('default_pagination'));

        return view('admin-views.taxi.driver.verification-list', compact('drivers', 'search', 'status'));
    }

    public function show($id)
    {
        $driver = DeliveryMan::findOrFail($id);
        return view('admin-views.taxi.driver.verification-details', compact('driver'));
    }

    public function updateStatus(Request $request, $id)
    {
        $driver = DeliveryMan::findOrFail($id);

        $request->validate([
            'status' => 'required|in:0,1',
            'rejection_reason' => 'nullable|string'
        ]);

        $driver->taxi_is_verified = $request->status;

        // If rejected, maybe specific logic is needed to notify driver? 
        // For now just save.

        $driver->save();

        if ($request->status == 1) {
            Toastr::success(translate('messages.driver_verified_successfully'));
        } else {
            Toastr::info(translate('messages.driver_verification_rejected'));
        }

        return redirect()->route('admin.taxi.driver.verification.index');
    }

    public function updateDocuments(Request $request, $id)
    {
        // This method allows Admin to manually upload/update documents if needed
        $driver = DeliveryMan::findOrFail($id);

        $request->validate([
            'selfie_image' => 'nullable|image',
            'identity_image' => 'nullable|array', // Logic for existing identity_image
            'taxi_documents.*' => 'nullable|image',
        ]);

        // Logic for handling file uploads would go here, updating json fields.
        // For now, focus is on Viewing/Verifying.

        return back();
    }
}

<?php

namespace Modules\Taxi\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Modules\Taxi\Models\TaxiSafetyAlert;
use Modules\Taxi\Models\TaxiSafetyRecording;
use Modules\Taxi\Models\TaxiRide;
use Illuminate\Http\Request;

class TaxiSafetyController extends Controller
{
    /**
     * Display safety alerts dashboard
     */
    public function index(Request $request)
    {
        $query = TaxiSafetyAlert::with(['taxiRide', 'user', 'admin'])
            ->latest();

        // Filter by status
        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->type && $request->type !== 'all') {
            $query->where('alert_type', $request->type);
        }

        $alerts = $query->paginate(config('default_pagination', 25));

        // Get counts for quick stats
        $stats = [
            'pending' => TaxiSafetyAlert::pending()->count(),
            'emergency' => TaxiSafetyAlert::emergency()->pending()->count(),
            'today' => TaxiSafetyAlert::whereDate('created_at', today())->count(),
        ];

        return view('admin-views.taxi.safety.index', compact('alerts', 'stats'));
    }

    /**
     * Show a specific alert with full details
     */
    public function show($id)
    {
        $alert = TaxiSafetyAlert::with([
            'taxiRide.driver',
            'taxiRide.user',
            'user',
            'admin'
        ])->findOrFail($id);

        // Get related recordings for this ride
        $recordings = TaxiSafetyRecording::where('taxi_ride_id', $alert->taxi_ride_id)
            ->latest()
            ->get();

        return view('admin-views.taxi.safety.show', compact('alert', 'recordings'));
    }

    /**
     * Mark alert as contacted
     */
    public function markContacted(Request $request, $id)
    {
        $alert = TaxiSafetyAlert::findOrFail($id);

        $alert->markAsContacted(
            auth('admin')->id(),
            $request->notes
        );

        return redirect()->back()->with('success', 'Alerta marcada como contactada');
    }

    /**
     * Mark alert as resolved
     */
    public function markResolved(Request $request, $id)
    {
        $alert = TaxiSafetyAlert::findOrFail($id);

        $alert->markAsResolved($request->notes);

        return redirect()->back()->with('success', 'Alerta marcada como resuelta');
    }

    /**
     * Escalate alert to authorities
     */
    public function escalate(Request $request, $id)
    {
        $alert = TaxiSafetyAlert::findOrFail($id);

        $alert->escalate($request->notes);

        return redirect()->back()->with('success', 'Alerta escalada a autoridades');
    }

    /**
     * Get real-time alert data (for AJAX polling)
     */
    public function getPendingAlerts()
    {
        $alerts = TaxiSafetyAlert::with(['taxiRide', 'user'])
            ->pending()
            ->latest()
            ->limit(10)
            ->get();

        $emergencyCount = TaxiSafetyAlert::emergency()->pending()->count();

        return response()->json([
            'alerts' => $alerts,
            'emergency_count' => $emergencyCount,
            'total_pending' => TaxiSafetyAlert::pending()->count(),
        ]);
    }

    /**
     * Safety recordings list
     */
    public function recordings(Request $request)
    {
        $recordings = TaxiSafetyRecording::with(['taxiRide', 'user'])
            ->latest()
            ->paginate(config('default_pagination', 25));

        return view('admin-views.taxi.safety.recordings', compact('recordings'));
    }

    /**
     * Generate PDF report for authorities
     */
    public function generateReport($id)
    {
        $alert = TaxiSafetyAlert::with([
            'taxiRide.driver.vehicle',
            'taxiRide.user',
            'user'
        ])->findOrFail($id);

        // Render the view to HTML
        $html = view('admin-views.taxi.safety.report-pdf', compact('alert'))->render();

        // Generate PDF using MPDF
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 15,
            'margin_bottom' => 15,
        ]);

        $mpdf->SetTitle('Reporte de Seguridad #' . $alert->id);
        $mpdf->SetAuthor('Tootli - Sistema de Taxi');
        $mpdf->SetCreator('Tootli Admin Panel');

        $mpdf->WriteHTML($html);

        // Generate filename
        $filename = 'reporte_seguridad_' . $alert->id . '_' . now()->format('Ymd_His') . '.pdf';

        // Return PDF for download
        return $mpdf->Output($filename, 'D'); // 'D' = Download, 'I' = Inline display
    }
}

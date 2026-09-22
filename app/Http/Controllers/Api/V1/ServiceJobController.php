<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\CentralLogics\CustomerLogic;
use App\Http\Controllers\Controller;
use App\Models\ServiceJob;
use App\Models\ServiceBid;
use App\Models\Store;
use App\Models\StoreWallet;
use App\Models\TootliDispute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ServiceJobController extends Controller
{
    /**
     * Create a new service job posting (Client).
     */
    public function store(Request $request)
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json(['errors' => [['code' => 'auth-001', 'message' => translate('messages.unauthorized')]]], 401);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'budget' => 'required|numeric|min:0',
            'category_id' => 'required|integer',
            'address' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $job = new ServiceJob();
        $job->user_id = $user->id;
        $job->category_id = $request->category_id;
        $job->title = $request->title;
        $job->description = $request->description;
        $job->budget = $request->budget;
        $job->address = $request->address;
        $job->latitude = $request->latitude;
        $job->longitude = $request->longitude;
        $job->status = 'pending';
        $job->payment_status = 'pending';
        $job->save();

        return response()->json([
            'message' => translate('messages.job_posted_successfully'),
            'job' => $job
        ], 201);
    }

    /**
     * List open/pending jobs in the zone (For Professionals).
     */
    public function index(Request $request)
    {
        $jobs = ServiceJob::with(['user', 'category'])
            ->where('status', 'pending')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($jobs, 200);
    }

    /**
     * List jobs posted by the authenticated client.
     */
    public function myJobs(Request $request)
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json(['errors' => [['code' => 'auth-001', 'message' => translate('messages.unauthorized')]]], 401);
        }

        $jobs = ServiceJob::with(['category', 'acceptedBid.store', 'dispute'])
            ->where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($jobs, 200);
    }

    /**
     * Send a bid/offer on a job (Professional).
     */
    public function storeBid(Request $request, $job_id)
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json(['errors' => [['code' => 'auth-001', 'message' => translate('messages.unauthorized')]]], 401);
        }

        $validator = Validator::make($request->all(), [
            'store_id' => 'required|integer',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $job = ServiceJob::find($job_id);
        if (!$job) {
            return response()->json(['errors' => [['code' => 'job-001', 'message' => translate('messages.job_not_found')]]], 404);
        }

        if ($job->status !== 'pending') {
            return response()->json(['errors' => [['code' => 'job-002', 'message' => translate('messages.job_already_assigned')]]], 400);
        }

        // Validate store exists
        $store = Store::find($request->store_id);
        if (!$store) {
            return response()->json(['errors' => [['code' => 'store-001', 'message' => translate('messages.store_not_found')]]], 404);
        }

        // Check if professional already placed a bid
        $existingBid = ServiceBid::where('job_id', $job_id)->where('store_id', $request->store_id)->first();
        if ($existingBid) {
            return response()->json(['errors' => [['code' => 'bid-001', 'message' => translate('messages.already_placed_bid')]]], 400);
        }

        $bid = new ServiceBid();
        $bid->job_id = $job_id;
        $bid->store_id = $request->store_id;
        $bid->price = $request->price;
        $bid->description = $request->description;
        $bid->status = 'pending';
        $bid->save();

        return response()->json([
            'message' => translate('messages.bid_placed_successfully'),
            'bid' => $bid
        ], 201);
    }

    /**
     * Get all bids for a specific job (Client).
     */
    public function getBids(Request $request, $job_id)
    {
        $job = ServiceJob::find($job_id);
        if (!$job) {
            return response()->json(['errors' => [['code' => 'job-001', 'message' => translate('messages.job_not_found')]]], 404);
        }

        $bids = ServiceBid::with('store')
            ->where('job_id', $job_id)
            ->orderBy('price', 'asc')
            ->get();

        return response()->json($bids, 200);
    }

    /**
     * Accept a specific bid (Client). Handles wallet payments.
     */
    public function acceptBid(Request $request, $bid_id)
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json(['errors' => [['code' => 'auth-001', 'message' => translate('messages.unauthorized')]]], 401);
        }

        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|string|in:wallet,card',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $bid = ServiceBid::find($bid_id);
        if (!$bid) {
            return response()->json(['errors' => [['code' => 'bid-002', 'message' => translate('messages.bid_not_found')]]], 404);
        }

        $job = ServiceJob::find($bid->job_id);
        if (!$job) {
            return response()->json(['errors' => [['code' => 'job-001', 'message' => translate('messages.job_not_found')]]], 404);
        }

        if ($job->user_id !== $user->id) {
            return response()->json(['errors' => [['code' => 'job-003', 'message' => translate('messages.unauthorized_job_owner')]]], 403);
        }

        if ($job->status !== 'pending') {
            return response()->json(['errors' => [['code' => 'job-002', 'message' => translate('messages.job_already_assigned_or_completed')]]], 400);
        }

        $payment_method = $request->payment_method;

        if ($payment_method === 'wallet') {
            if ($user->wallet_balance < $bid->price) {
                return response()->json(['errors' => [['code' => 'wallet-001', 'message' => translate('messages.insufficient_wallet_balance')]]], 400);
            }

            // Deduct from wallet and register transaction
            DB::transaction(function () use ($user, $bid, $job) {
                CustomerLogic::create_wallet_transaction($user->id, $bid->price, 'order_place', 'job_payment_' . $job->id);

                // Update job
                $job->accepted_bid_id = $bid->id;
                $job->status = 'accepted';
                $job->payment_status = 'paid';
                $job->payment_method = 'wallet';
                $job->save();

                // Accept this bid, reject others
                $bid->status = 'accepted';
                $bid->save();

                ServiceBid::where('job_id', $job->id)
                    ->where('id', '!=', $bid->id)
                    ->update(['status' => 'rejected']);
            });
        } else {
            // Card payment flow placeholder (to be updated by webhook/gateway callback)
            $job->accepted_bid_id = $bid->id;
            $job->status = 'accepted';
            $job->payment_status = 'paid'; // Automatically paid for mock cards, in real it updates on success
            $job->payment_method = 'card';
            $job->save();

            $bid->status = 'accepted';
            $bid->save();

            ServiceBid::where('job_id', $job->id)
                ->where('id', '!=', $bid->id)
                ->update(['status' => 'rejected']);
        }

        return response()->json([
            'message' => translate('messages.bid_accepted_successfully'),
            'job' => $job->load('acceptedBid.store')
        ], 200);
    }

    /**
     * Mark job as completed and release held funds to professional's StoreWallet (Client).
     */
    public function completeJob(Request $request, $job_id)
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json(['errors' => [['code' => 'auth-001', 'message' => translate('messages.unauthorized')]]], 401);
        }

        $job = ServiceJob::with('acceptedBid.store')->find($job_id);
        if (!$job) {
            return response()->json(['errors' => [['code' => 'job-001', 'message' => translate('messages.job_not_found')]]], 404);
        }

        if ($job->user_id !== $user->id) {
            return response()->json(['errors' => [['code' => 'job-003', 'message' => translate('messages.unauthorized_job_owner')]]], 403);
        }

        if ($job->status === 'disputed') {
            return response()->json(['errors' => [['code' => 'job-005', 'message' => 'No se puede finalizar el trabajo mientras se encuentre en disputa']]], 400);
        }

        if ($job->status !== 'accepted') {
            return response()->json(['errors' => [['code' => 'job-004', 'message' => translate('messages.job_not_active')]]], 400);
        }

        DB::transaction(function () use ($job) {
            $job->status = 'completed';
            $job->save();

            // Release funds to professional's StoreWallet
            if ($job->payment_status === 'paid' && $job->acceptedBid) {
                $store = $job->acceptedBid->store;
                if ($store) {
                    $store_wallet = StoreWallet::firstOrNew(['vendor_id' => $store->vendor_id]);
                    $store_wallet->total_earning += $job->acceptedBid->price;
                    $store_wallet->save();
                }
            }
        });

        return response()->json([
            'message' => translate('messages.job_completed_and_funds_released'),
            'job' => $job
        ], 200);
    }

    /**
     * Abrir disputa en un trabajo contratado (Tootli Protector).
     */
    public function dispute(Request $request, $job_id)
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json(['errors' => [['code' => 'auth-001', 'message' => translate('messages.unauthorized')]]], 401);
        }

        $job = ServiceJob::with(['acceptedBid.store.vendor', 'dispute'])->find($job_id);
        if (!$job) {
            return response()->json(['errors' => [['code' => 'job-001', 'message' => translate('messages.job_not_found')]]], 404);
        }

        if ($job->user_id !== $user->id) {
            return response()->json(['errors' => [['code' => 'job-003', 'message' => translate('messages.unauthorized_job_owner')]]], 403);
        }

        if ($job->status !== 'accepted' || $job->payment_status !== 'paid') {
            return response()->json(['errors' => [['code' => 'job-006', 'message' => 'Solo se pueden disputar trabajos en curso con fondos en garantía']]], 400);
        }

        if ($job->dispute && in_array($job->dispute->status, ['open', 'under_review'])) {
            return response()->json(['errors' => [['code' => 'job-007', 'message' => 'Ya existe una disputa activa para este trabajo']]], 400);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:100',
            'description' => 'required|string|min:10|max:1000',
            'requested_solution' => 'nullable|in:full_refund,partial_refund,service_correction',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $evidencePhotos = [];
        if ($request->has('images')) {
            $images = $request->file('images');
            if (is_array($images)) {
                foreach ($images as $img) {
                    $evidencePhotos[] = Helpers::upload('disputes/', 'png', $img);
                }
            } elseif ($request->file('images')) {
                $evidencePhotos[] = Helpers::upload('disputes/', 'png', $request->file('images'));
            }
        }

        $defendantId = $job->acceptedBid?->store?->vendor_id;

        DB::transaction(function () use ($job, $user, $defendantId, $request, $evidencePhotos) {
            TootliDispute::create([
                'disputable_type' => ServiceJob::class,
                'disputable_id' => $job->id,
                'claimant_id' => $user->id,
                'defendant_id' => $defendantId,
                'reason' => $request->reason,
                'description' => $request->description,
                'evidence_photos' => $evidencePhotos,
                'requested_solution' => $request->requested_solution ?? 'full_refund',
                'status' => 'open',
            ]);

            $job->status = 'disputed';
            $job->save();
        });

        return response()->json([
            'message' => 'Disputa de servicio abierta exitosamente bajo garantía Tootli Protector',
            'job' => $job->fresh()->load(['category', 'acceptedBid.store', 'dispute'])
        ], 201);
    }

    /**
     * Obtener el detalle de la disputa de un trabajo.
     */
    public function getDispute(Request $request, $job_id)
    {
        $job = ServiceJob::findOrFail($job_id);
        $dispute = TootliDispute::where('disputable_type', ServiceJob::class)
            ->where('disputable_id', $job->id)
            ->with(['claimant', 'defendant'])
            ->latest()
            ->first();

        if (!$dispute) {
            return response()->json(['errors' => [['code' => 'dispute-001', 'message' => 'No se encontró disputa para este trabajo']]], 404);
        }

        return response()->json($dispute, 200);
    }

    /**
     * Cancelar disputa de servicio.
     */
    public function cancelDispute(Request $request, $job_id)
    {
        $user = auth('api')->user();
        $job = ServiceJob::findOrFail($job_id);
        $dispute = TootliDispute::where('disputable_type', ServiceJob::class)
            ->where('disputable_id', $job->id)
            ->where('status', 'open')
            ->latest()
            ->first();

        if (!$dispute) {
            return response()->json(['errors' => [['code' => 'dispute-001', 'message' => 'No hay disputa activa']]], 404);
        }

        if ($dispute->claimant_id !== $user->id) {
            return response()->json(['errors' => [['code' => 'dispute-002', 'message' => 'No autorizado para cancelar']]], 403);
        }

        DB::transaction(function () use ($job, $dispute) {
            $dispute->status = 'cancelled';
            $dispute->save();

            $job->status = 'accepted';
            $job->save();
        });

        return response()->json([
            'message' => 'Disputa cancelada exitosamente',
            'job' => $job->fresh()->load(['category', 'acceptedBid.store', 'dispute'])
        ], 200);
    }

    /**
     * Resolver disputa de servicio.
     */
    public function resolveDispute(Request $request, $job_id)
    {
        $validator = Validator::make($request->all(), [
            'resolution' => 'required|in:refund_buyer,payout_seller,partial_refund',
            'refund_amount' => 'required_if:resolution,partial_refund|numeric|min:0',
            'resolution_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $job = ServiceJob::with(['acceptedBid.store'])->findOrFail($job_id);
        $dispute = TootliDispute::where('disputable_type', ServiceJob::class)
            ->where('disputable_id', $job->id)
            ->whereIn('status', ['open', 'under_review'])
            ->latest()
            ->first();

        if (!$dispute) {
            return response()->json(['errors' => [['code' => 'dispute-001', 'message' => 'No hay disputa activa']]], 404);
        }

        $resolution = $request->resolution;
        $notes = $request->resolution_notes ?? 'Resolución emitida por Tootli Protector';
        $bidPrice = $job->acceptedBid ? $job->acceptedBid->price : $job->budget;

        DB::transaction(function () use ($job, $dispute, $resolution, $notes, $bidPrice, $request) {
            $dispute->resolution_notes = $notes;
            $dispute->resolved_at = now();

            if ($resolution === 'refund_buyer') {
                $dispute->status = 'resolved_buyer_refund';
                $dispute->refund_amount = $bidPrice;
                $dispute->save();

                $customer = $job->user;
                if ($customer) {
                    $customer->wallet_balance += $bidPrice;
                    $customer->save();

                    CustomerLogic::create_wallet_transaction(
                        $customer->id,
                        $bidPrice,
                        'order_refund',
                        'job_dispute_refund_' . $job->id
                    );
                }

                $job->status = 'cancelled';
                $job->payment_status = 'refunded';
                $job->save();
            } elseif ($resolution === 'payout_seller') {
                $dispute->status = 'resolved_seller_payout';
                $dispute->save();

                if ($job->acceptedBid && $job->acceptedBid->store) {
                    $store = $job->acceptedBid->store;
                    $store_wallet = StoreWallet::firstOrNew(['vendor_id' => $store->vendor_id]);
                    $store_wallet->total_earning += $bidPrice;
                    $store_wallet->save();
                }

                $job->status = 'completed';
                $job->save();
            } elseif ($resolution === 'partial_refund') {
                $refundAmt = min($request->refund_amount, $bidPrice);
                $storeAmt = $bidPrice - $refundAmt;

                $dispute->status = 'resolved_partial';
                $dispute->refund_amount = $refundAmt;
                $dispute->save();

                $customer = $job->user;
                if ($customer && $refundAmt > 0) {
                    $customer->wallet_balance += $refundAmt;
                    $customer->save();

                    CustomerLogic::create_wallet_transaction(
                        $customer->id,
                        $refundAmt,
                        'order_refund',
                        'job_partial_refund_' . $job->id
                    );
                }

                if ($job->acceptedBid && $job->acceptedBid->store && $storeAmt > 0) {
                    $store = $job->acceptedBid->store;
                    $store_wallet = StoreWallet::firstOrNew(['vendor_id' => $store->vendor_id]);
                    $store_wallet->total_earning += $storeAmt;
                    $store_wallet->save();
                }

                $job->status = 'completed';
                $job->save();
            }
        });

        return response()->json([
            'message' => 'Disputa de servicio resuelta exitosamente',
            'dispute' => $dispute->fresh(),
            'job' => $job->fresh()->load(['category', 'acceptedBid.store', 'dispute'])
        ], 200);
    }
}

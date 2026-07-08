<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\CentralLogics\CustomerLogic;
use App\Http\Controllers\Controller;
use App\Models\ServiceJob;
use App\Models\ServiceBid;
use App\Models\Store;
use App\Models\StoreWallet;
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

        $jobs = ServiceJob::with(['category', 'acceptedBid.store'])
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
}

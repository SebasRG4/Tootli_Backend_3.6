<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Store;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;

class SaboresReviewsController extends Controller
{
    public function list(Request $request)
    {
        $search = $request->search;
        $store_id = $request->store_id;

        $reviews = Review::with(['customer', 'item', 'store'])
            ->whereHas('store.module', function ($query) {
                $query->where('module_type', 'food');
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('comment', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($q) use ($search) {
                            $q->where('f_name', 'like', "%{$search}%")
                                ->orWhere('l_name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($store_id, function ($query) use ($store_id) {
                $query->where('store_id', $store_id);
            })
            ->latest()
            ->paginate(config('default_pagination'));

        $stores = Store::whereHas('module', function ($query) {
            $query->where('module_type', 'food');
        })->get();

        return view('admin-views.sabores.reviews.list', compact('reviews', 'search', 'stores', 'store_id'));
    }

    public function edit($id)
    {
        $review = Review::with(['customer', 'item', 'store'])->findOrFail($id);
        return view('admin-views.sabores.reviews.edit', compact('review'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|boolean',
            'comment' => 'nullable|string'
        ]);

        $review = Review::findOrFail($id);
        $review->status = $request->status;
        $review->comment = $request->comment;
        $review->save();

        Toastr::success(translate('messages.review_updated_successfully'));
        return redirect()->route('admin.sabores.reviews.list');
    }

    public function delete(Request $request)
    {
        $review = Review::findOrFail($request->id);
        $review->delete();

        Toastr::success(translate('messages.review_deleted_successfully'));
        return back();
    }
}

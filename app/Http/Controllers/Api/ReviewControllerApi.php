<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ReviewsProduct;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewControllerApi extends Controller
{
    // 'user_id',
    // 'order_id',
    // 'gambar_produk',
    // 'ulasan',
    // 'ratings',
    // 'balasan_admin',
    // 'reply_by',
    public function addReviews(Request $request, $id)
    {
        $user = User::findOrFail(Auth::id());
        if ($user) {
            try {
                $order = Order::findOrFail($id);

                // Pastikan order milik user dan belum direview
                if ($order->user_id !== $user->id) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Unauthorized: this order does not belong to you'
                    ], 403);
                }
        
                if ($order->status !== 'Selesai' || $order->isReviewed) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Order is not eligible for review'
                    ], 400);
                }
        
                $validatedData = $request->validate([
                    'ratings' => 'required|integer|min:1|max:5',
                    'ulasan' => 'required|string|max:255',
                    'gambar_produk.*' => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4,webp|max:20480',
                ]);
                
                // Handle upload gambar
                $gambarProdukPaths = [];
                $gambarFiles = $request->file('gambar_produk');
        
                if (!empty($gambarFiles)) {
                    $gambarFiles = is_array($gambarFiles) ? $gambarFiles : [$gambarFiles];
                    foreach ($gambarFiles as $file) {
                        $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                        $file->move(public_path('uploads/reviews/'), $fileName);
                        $gambarProdukPaths[] = 'uploads/reviews/' . $fileName;
                    }
                }
        
                // Simpan review
                $review = $order->reviews()->create([
                    'user_id' => $user->id,
                    'ratings' => $validatedData['ratings'],
                    'ulasan' => $validatedData['ulasan'],
                    'gambar_produk' => json_encode($gambarProdukPaths),
                ]);
                

                // Update order
                $order->update([
                    'ulasan_id' => $review->id,
                    'isReviewed' => true,
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Review added successfully',
                    'data' => [
                        'review' => $review,
                        'order' => $order,
                        'gambar_produk' => $gambarProdukPaths
                    ]

                ], 200);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to add review',
                    'error' => $e->getMessage()
                ], 500);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
    }

    public function detailReviews(Request $request, $id)
    {
        try {
            $review = ReviewsProduct::with(['order', 'user'])
                ->where('id', $id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$review) {
                return response()->json([
                    'status' => false,
                    'message' => 'Review not found'
                ], 404);
            }

            $orderDetails = $review->order;

            return response()->json([
                'status' => true,
                'data' => [
                    'review' => $review,
                    'order_details' => $orderDetails
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve review',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getAllReviews(Request $request)
    {
        try {
            $reviews = ReviewsProduct::with(['order', 'user'])
                ->whereHas('order', function ($query) {
                    $query->where('isReviewed', true);
                })
                ->get();

            if ($reviews->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No reviews found'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'data' => $reviews
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve reviews',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    public function replyReviews(Request $request, $id)
    {
        $user = User::findOrFail(Auth::id());
        if ($user && ($user->isAdmin() || $user->isOwner())) {
            try {
                $order = Order::findOrFail($id);

                // Ensure the order is eligible for a reply
                if ($order->status !== 'Selesai' || !$order->isReviewed) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Order cannot be replied to'
                    ], 400);
                }

                $validatedData = $request->validate([
                    'balasan_admin' => 'required|string|max:255',
                    'gambar_reply' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                ]);

                // Update the review with the admin's reply
                $review = $order->review;
                if (!$review) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Review not found'
                    ], 404);
                }

                $review->update([
                    'balasan_admin' => $validatedData['balasan_admin'],
                    'reply_by' => $user->id,
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Reply added successfully',
                    'data' => $review
                ], 200);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to add reply',
                    'error' => $e->getMessage()
                ], 500);
            }
        }

        return response()->json([
            'status' => false,
            'message' => 'Unauthorized'
        ], 403);
    }
}

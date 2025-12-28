<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Review;
use App\Models\Apartment; // [FIX] Added missing import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    // Get reviews for an apartment
    public function index($id)
    {
        $reviews = Review::where('apartment_id', $id)
            ->with('tenant:id,first_name,last_name,profile_image')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['success' => true, 'data' => $reviews]);
    }

    // Submit a review
    public function store(Request $request, $bookingId)
    {
        $request->validate([
            'rating' => 'required|numeric|min:1|max:5', // Allows 4.5
            'comment' => 'nullable|string|max:1000',
        ]);

        $booking = Booking::with('apartment')->findOrFail($bookingId);

        // 1. Authorization
        if ($booking->tenant_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // 2. Status check
        if ($booking->status !== 'completed') {
            return response()->json(['message' => 'You can only review completed stays.'], 400);
        }

        // 3. Duplicate check
        $existingReview = Review::where('booking_id', $bookingId)->first();
        if ($existingReview) {
            return response()->json(['message' => 'You have already reviewed this stay.'], 400);
        }

        // 4. Create Review
        $review = Review::create([
            'tenant_id' => Auth::id(),
            'apartment_id' => $booking->apartment_id,
            'booking_id' => $booking->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        // 5. Update Apartment Average Rating
        $avg = Review::where('apartment_id', $booking->apartment_id)->avg('rating');

        // Handle case where avg might be null (though unlikely after create)
        $avg = $avg ? round($avg, 1) : 0;

        $booking->apartment()->update(['rating' => $avg]);

        // [IMPORTANT] Return simple JSON. Flutter sometimes chokes on complex nested objects in the response 
        // if the model parsing isn't perfect.
        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully',
            'data' => $review
        ], 201);
    }
}

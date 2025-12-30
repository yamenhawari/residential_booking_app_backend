<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApartmentRequest;
use App\Models\Apartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApartmentController extends Controller
{
    // Public: List all apartments (WITH FILTERS & Availability Check)
    public function index(Request $request)
    {
        $query = Apartment::with(['owner', 'governorate', 'city', 'categories', 'features', 'images']);

        // 1. Basic Availability Check
        $query->where('status', 'available');

        // [NEW] Exclude My Own Apartments
        // We use 'sanctum' guard explicitly because this is a public route, 
        // so standard Auth::id() might be null even if a token is sent.
        $user = auth('sanctum')->user();
        if ($user) {
            $query->where('owner_id', '!=', $user->id);
        }

        // 2. Filter by City
        if ($request->filled('city_id')) {
            $query->where('city_id', $request->city_id);
        }

        // 3. Filter by Governorate
        if ($request->filled('governorate_id')) {
            $govIds = $request->governorate_id;
            if (is_array($govIds)) {
                $query->whereIn('governorate_id', $govIds);
            } else {
                $query->where('governorate_id', $govIds);
            }
        }

        // 4. Filter by Price
        if ($request->filled('min_price')) {
            $query->where('price_per_month', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price_per_month', '<=', $request->max_price);
        }

        // 5. Filter by Room Count
        if ($request->filled('room_count')) {
            $query->where('room_count', '>=', $request->room_count);
        }

        // 6. Filter by Date Availability
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $start = $request->start_date;
            $end = $request->end_date;

            $query->whereDoesntHave('bookings', function ($q) use ($start, $end) {
                $q->whereIn('status', ['pending', 'confirmed'])
                    ->where(function ($subQ) use ($start, $end) {
                        $subQ->where('start_date', '<', $end)
                            ->where('end_date', '>', $start);
                    });
            });
        }

        // 7. Filter by Features
        if ($request->filled('feature_ids')) {
            $featureIds = $request->feature_ids;
            $query->whereHas('features', function ($q) use ($featureIds) {
                $q->whereIn('features.id', $featureIds);
            });
        }

        $apartments = $query->get();
        return response()->json($apartments, 200);
    }

    // Owner: List MY apartments (Returns ALL statuses)
    public function myApartments()
    {
        $userId = Auth::id();

        $apartments = Apartment::with(['owner', 'governorate', 'city', 'categories', 'features', 'images'])
            ->where('owner_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($apartments, 200);
    }

    // Public: Show specific apartment
    public function show($id)
    {
        $apartment = Apartment::with(['owner', 'governorate', 'city', 'categories', 'features', 'images'])
            ->findOrFail($id);
        return response()->json($apartment, 200);
    }

    // [NEW] Make Unavailable Apartment Available Again
    public function activate($id)
    {
        $apartment = Apartment::findOrFail($id);

        if ($apartment->owner_id !== Auth::id()) {
            return response()->json(['message' => 'Not authorized'], 403);
        }

        $apartment->update(['status' => 'available']);

        return response()->json(['message' => 'Apartment is now available']);
    }

    // [NEW] Permanently Delete (Only if no active bookings)
    public function forceDelete($id)
    {
        $apartment = Apartment::findOrFail($id);

        if ($apartment->owner_id !== Auth::id()) {
            return response()->json(['message' => 'Not authorized'], 403);
        }

        // Check for active bookings (Pending or Confirmed future/current bookings)
        $hasActiveBookings = $apartment->bookings()
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('end_date', '>=', date('Y-m-d'))
            ->exists();

        if ($hasActiveBookings) {
            return response()->json(['message' => 'Cannot delete: Active bookings exist.'], 400);
        }

        // Hard Delete
        $apartment->delete();

        return response()->json(['message' => 'Apartment permanently deleted']);
    }

    // Owner: Create apartment
    public function store(StoreApartmentRequest $request)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $data = $request->validated();
        $data['owner_id'] = Auth::id();
        $data['status'] = 'available';

        $apartment = Apartment::create($data);

        if ($request->filled('categories')) {
            $apartment->categories()->sync($data['categories']);
        }
        if ($request->filled('features')) {
            $apartment->features()->sync($data['features']);
        }
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('apartments', 'public');
                $apartment->images()->create(['image_url' => $path]);
            }
        }

        return response()->json([
            'message' => 'Apartment created successfully',
            'data' => $apartment->load(['categories', 'features', 'images'])
        ], 201);
    }

    // Owner: Update apartment
    public function update(StoreApartmentRequest $request, $id)
    {
        $apartment = Apartment::findOrFail($id);

        if ($apartment->owner_id !== Auth::id()) {
            return response()->json(['message' => 'Not authorized'], 403);
        }

        $data = $request->validated();
        unset($data['owner_id']);

        $apartment->update($data);

        if ($request->filled('categories')) {
            $apartment->categories()->sync($data['categories']);
        }
        if ($request->filled('features')) {
            $apartment->features()->sync($data['features']);
        }
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('apartments', 'public');
                $apartment->images()->create(['image_url' => $path]);
            }
        }

        return response()->json([
            'message' => 'Updated successfully',
            'data' => $apartment->load(['images'])
        ], 200);
    }

    // Owner: Soft Delete (Mark as unavailable)
    public function destroy($id)
    {
        $apartment = Apartment::findOrFail($id);

        if ($apartment->owner_id !== Auth::id()) {
            return response()->json(['message' => 'Not authorized'], 403);
        }

        // Mark as unavailable
        $apartment->update(['status' => 'unavailable']);

        return response()->json(['message' => 'Apartment marked as unavailable'], 200);
    }
}

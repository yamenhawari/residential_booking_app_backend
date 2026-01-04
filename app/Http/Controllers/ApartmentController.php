<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApartmentRequest;
use App\Models\Apartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApartmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Apartment::with(['owner', 'governorate', 'city', 'categories', 'features', 'images']);

        // 1. Exclude Hard-Deleted/Unavailable apartments always
        $query->where('status', '!=', 'unavailable');

        // 2. Exclude Own Apartments (If logged in)
        $user = auth('sanctum')->user();
        if ($user) {
            $query->where('owner_id', '!=', $user->id);
        }

        // 3. DATE FILTER LOGIC (The Fix)
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $start = $request->start_date;
            $end = $request->end_date;

            // Logic: Get apartments that do NOT have a conflicting booking
            $query->whereDoesntHave('bookings', function ($q) use ($start, $end) {
                // Check against Pending OR Confirmed bookings
                $q->whereIn('status', ['pending', 'confirmed'])
                    // Check for Overlap: (StartA < EndB) and (EndA > StartB)
                    ->where(function ($subQ) use ($start, $end) {
                        $subQ->where('start_date', '<', $end)
                            ->where('end_date', '>', $start);
                    });
            });

            // IMPORTANT: We DO NOT check $query->where('status', 'available') here. 
            // Because an apartment might be marked 'rented' generally, but free for next year.
        } else {
            // If NO dates selected, only show currently available ones
            $query->where('status', 'available');
        }

        // ... [Rest of filters: city, price, etc.] ...
        if ($request->filled('city_id')) {
            $query->where('city_id', $request->city_id);
        }
        if ($request->filled('governorate_id')) {
            $govIds = $request->governorate_id;
            if (is_array($govIds)) {
                $query->whereIn('governorate_id', $govIds);
            } else {
                $query->where('governorate_id', $govIds);
            }
        }
        if ($request->filled('min_price')) {
            $query->where('price_per_month', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price_per_month', '<=', $request->max_price);
        }
        if ($request->filled('room_count')) {
            $query->where('room_count', '>=', $request->room_count);
        }
        if ($request->filled('feature_ids')) {
            $featureIds = $request->feature_ids;
            $query->whereHas('features', function ($q) use ($featureIds) {
                $q->whereIn('features.id', $featureIds);
            });
        }

        return response()->json($query->get(), 200);
    }

    // ... [Include the rest of your functions: myApartments, store, update, etc.] ...
    public function myApartments()
    {
        $userId = Auth::id();
        $apartments = Apartment::with(['owner', 'governorate', 'city', 'categories', 'features', 'images'])
            ->where('owner_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($apartments, 200);
    }

    public function show($id)
    {
        $apartment = Apartment::with(['owner', 'governorate', 'city', 'categories', 'features', 'images'])
            ->findOrFail($id);
        return response()->json($apartment, 200);
    }

    public function activate($id)
    {
        $apartment = Apartment::findOrFail($id);
        if ($apartment->owner_id !== Auth::id()) {
            return response()->json(['message' => 'Not authorized'], 403);
        }
        $apartment->update(['status' => 'available']);
        return response()->json(['message' => 'Apartment is now available']);
    }

    public function forceDelete($id)
    {
        $apartment = Apartment::findOrFail($id);
        if ($apartment->owner_id !== Auth::id()) {
            return response()->json(['message' => 'Not authorized'], 403);
        }
        $hasActiveBookings = $apartment->bookings()
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('end_date', '>=', date('Y-m-d'))
            ->exists();
        if ($hasActiveBookings) {
            return response()->json(['message' => 'Cannot delete: Active bookings exist.'], 400);
        }
        $apartment->delete();
        return response()->json(['message' => 'Apartment permanently deleted']);
    }

    public function store(StoreApartmentRequest $request)
    {
        if (!Auth::check()) return response()->json(['message' => 'Unauthorized'], 401);
        $data = $request->validated();
        $data['owner_id'] = Auth::id();
        $data['status'] = 'available';
        $apartment = Apartment::create($data);
        if ($request->filled('categories')) $apartment->categories()->sync($data['categories']);
        if ($request->filled('features')) $apartment->features()->sync($data['features']);
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('apartments', 'public');
                $apartment->images()->create(['image_url' => $path]);
            }
        }
        return response()->json(['message' => 'Apartment created successfully', 'data' => $apartment->load(['categories', 'features', 'images'])], 201);
    }

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
        if ($request->filled('features')) $apartment->features()->sync($data['features']);
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('apartments', 'public');
                $apartment->images()->create(['image_url' => $path]);
            }
        }
        return response()->json(['message' => 'Updated successfully', 'data' => $apartment->load(['images'])], 200);
    }

    public function destroy($id)
    {
        $apartment = Apartment::findOrFail($id);
        if ($apartment->owner_id !== Auth::id()) return response()->json(['message' => 'Not authorized'], 403);
        $apartment->update(['status' => 'unavailable']);
        return response()->json(['message' => 'Apartment marked as unavailable'], 200);
    }
}

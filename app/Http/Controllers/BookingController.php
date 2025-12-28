<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\Booking;
use App\Models\BookingUpdateRequest;
use App\Services\FCMService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    protected $fcm;

    public function __construct(FCMService $fcm)
    {
        $this->fcm = $fcm;
    }

    private function updateCompletedBookings()
    {
        $today = date('Y-m-d');
        Booking::where('status', 'confirmed')
            ->where('end_date', '<', $today)
            ->update(['status' => 'completed']);
    }

    public function myAllBookings()
    {
        $this->updateCompletedBookings();
        $userId = Auth::id();
        $today = date('Y-m-d');

        $bookings = Booking::where('tenant_id', $userId)
            ->with(['apartment.images', 'review'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($booking) use ($today) {
                if ($booking->status === 'cancelled') $booking->type = 'cancelled';
                elseif ($booking->status === 'rejected') $booking->type = 'rejected';
                elseif ($booking->status === 'completed') $booking->type = 'completed';
                elseif ($booking->start_date <= $today && $booking->end_date >= $today) $booking->type = 'current';
                elseif ($booking->start_date > $today) $booking->type = 'upcoming';
                return $booking;
            });

        return response()->json(['success' => true, 'data' => $bookings]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'apartment_id' => 'required|exists:apartments,id',
            'start_date'   => 'required|date|after_or_equal:today',
            'end_date'     => 'required|date|after:start_date',
        ]);

        $apartment = Apartment::with('owner')->findOrFail($request->apartment_id);

        if ($apartment->owner_id === Auth::id()) {
            return response()->json(['success' => false, 'message' => 'You cannot book your own apartment.'], 403);
        }

        if ($apartment->status !== 'available') {
            return response()->json(['success' => false, 'message' => 'This apartment is currently not available.'], 400);
        }

        $pricePerDay = $apartment->price_per_month / 30;
        $days = (strtotime($request->end_date) - strtotime($request->start_date)) / 86400;
        $days = $days < 1 ? 1 : $days;
        $totalPrice = $days * $pricePerDay;

        $overlap = Booking::where('apartment_id', $apartment->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where(function ($query) use ($request) {
                $query->where('start_date', '<', $request->end_date)
                    ->where('end_date', '>', $request->start_date);
            })
            ->exists();

        if ($overlap) {
            return response()->json(['success' => false, 'message' => 'This period is already booked.'], 400);
        }

        $booking = Booking::create([
            'apartment_id' => $apartment->id,
            'tenant_id'    => Auth::id(),
            'start_date'   => $request->start_date,
            'end_date'     => $request->end_date,
            'total_price'  => $totalPrice,
            'status'       => 'pending',
        ]);

        if ($apartment->owner && $apartment->owner->fcm_token) {
            $this->fcm->send(
                $apartment->owner->fcm_token,
                'New Booking Request',
                'Someone wants to book ' . $apartment->title
            );
        }

        return response()->json(['success' => true, 'message' => 'Booking request sent!', 'data' => $booking], 201);
    }

    public function checkout($id)
    {
        $booking = Booking::with('apartment')->findOrFail($id);
        if (Auth::id() != $booking->tenant_id) return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        if ($booking->status !== 'confirmed') return response()->json(['success' => false, 'message' => 'Only confirmed bookings can be checked out.'], 400);
        $booking->update(['status' => 'completed']);
        $booking->apartment()->update(['status' => 'available']);
        return response()->json(['success' => true, 'message' => 'Checked out successfully.']);
    }

    public function confirm($id)
    {
        $booking = Booking::with(['apartment', 'tenant'])->findOrFail($id);
        if ($booking->apartment->owner_id !== Auth::id()) return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);

        $booking->update(['status' => 'confirmed']);
        $booking->apartment()->update(['status' => 'rented']);

        if ($booking->tenant && $booking->tenant->fcm_token) {
            $this->fcm->send(
                $booking->tenant->fcm_token,
                'Booking Confirmed!',
                'Your stay at ' . $booking->apartment->title . ' has been confirmed.'
            );
        }

        return response()->json(['success' => true, 'message' => 'Booking confirmed.', 'data' => $booking]);
    }

    public function reject($id)
    {
        $booking = Booking::with(['apartment', 'tenant'])->findOrFail($id);
        if ($booking->apartment->owner_id !== Auth::id()) return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);

        $booking->update(['status' => 'rejected']);

        if ($booking->tenant && $booking->tenant->fcm_token) {
            $this->fcm->send(
                $booking->tenant->fcm_token,
                'Booking Rejected',
                'Your request for ' . $booking->apartment->title . ' was declined.'
            );
        }

        return response()->json(['success' => true, 'message' => 'Booking rejected']);
    }

    public function cancel($id)
    {
        $booking = Booking::with('apartment')->findOrFail($id);
        if (Auth::id() != $booking->tenant_id && Auth::id() != $booking->apartment->owner_id) return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);

        $wasConfirmed = $booking->status === 'confirmed';
        $booking->update(['status' => 'cancelled']);
        if ($wasConfirmed) $booking->apartment()->update(['status' => 'available']);

        return response()->json(['success' => true, 'message' => 'Booking cancelled']);
    }

    public function ownerRequests()
    {
        $userId = Auth::id();

        $newBookings = Booking::whereHas('apartment', function ($q) use ($userId) {
            $q->where('owner_id', $userId);
        })
            ->where('status', 'pending')
            ->with(['apartment.images', 'tenant'])
            ->get()
            ->map(function ($b) {
                $b->request_type = 'new_booking';
                return $b;
            });

        $modifications = Booking::whereHas('apartment', function ($q) use ($userId) {
            $q->where('owner_id', $userId);
        })
            ->whereHas('updateRequests', function ($q) {
                $q->where('status', 'pending');
            })
            ->with(['apartment.images', 'tenant', 'updateRequests' => function ($q) {
                $q->where('status', 'pending');
            }])
            ->get()
            ->map(function ($b) {
                $b->request_type = 'modification';
                $update = $b->updateRequests->first();
                if ($update) {
                    $b->pending_update_id = $update->id;
                    $b->requested_start = $update->requested_start_date;
                    $b->requested_end = $update->requested_end_date;
                }
                return $b;
            });

        $merged = $newBookings->merge($modifications)->sortByDesc('created_at')->values();

        return response()->json(['success' => true, 'data' => $merged]);
    }

    public function requestUpdate(Request $request, $id)
    {
        $booking = Booking::with('apartment.owner')->findOrFail($id);
        if (Auth::id() != $booking->tenant_id) return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);

        $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date'   => 'required|date|after:start_date',
        ]);

        BookingUpdateRequest::create([
            'booking_id' => $booking->id,
            'requested_start_date' => $request->start_date,
            'requested_end_date' => $request->end_date,
            'status' => 'pending'
        ]);

        if ($booking->apartment->owner && $booking->apartment->owner->fcm_token) {
            $this->fcm->send(
                $booking->apartment->owner->fcm_token,
                'Date Change Request',
                'A tenant wants to change dates for ' . $booking->apartment->title
            );
        }

        return response()->json(['success' => true, 'message' => 'Update request sent']);
    }

    public function approveUpdate($requestId)
    {
        $update = BookingUpdateRequest::with(['booking.apartment', 'booking.tenant'])->findOrFail($requestId);
        $booking = $update->booking;

        if ($booking->apartment->owner_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $booking->update([
            'start_date' => $update->requested_start_date,
            'end_date'   => $update->requested_end_date,
        ]);

        $update->update(['status' => 'approved']);

        if ($booking->tenant && $booking->tenant->fcm_token) {
            $this->fcm->send(
                $booking->tenant->fcm_token,
                'Update Approved',
                'Your date change request was approved.'
            );
        }

        return response()->json(['success' => true, 'message' => 'Update approved']);
    }

    public function rejectUpdate($requestId)
    {
        $update = BookingUpdateRequest::with(['booking.apartment', 'booking.tenant'])->findOrFail($requestId);
        if ($update->booking->apartment->owner_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        $update->update(['status' => 'rejected']);

        if ($update->booking->tenant && $update->booking->tenant->fcm_token) {
            $this->fcm->send(
                $update->booking->tenant->fcm_token,
                'Update Rejected',
                'Your date change request was declined.'
            );
        }

        return response()->json(['success' => true, 'message' => 'Update rejected']);
    }

    public function getEarnings()
    {
        $userId = Auth::id();
        $total = Booking::whereHas('apartment', function ($q) use ($userId) {
            $q->where('owner_id', $userId);
        })
            ->where('status', 'completed')
            ->sum('total_price');

        return response()->json(['success' => true, 'data' => $total]);
    }
}

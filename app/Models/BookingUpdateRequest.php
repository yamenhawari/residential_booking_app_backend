<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingUpdateRequest extends Model
{
    protected $fillable = [
        'booking_id',
        'requested_start_date',
        'requested_end_date',
        'status',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}

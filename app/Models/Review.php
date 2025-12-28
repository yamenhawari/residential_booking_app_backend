<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'apartment_id',
        'booking_id',
        'rating', // Ensure this matches the DB column
        'comment'
    ];

    // Cast rating to float so Flutter receives 4.0 instead of "4"
    protected $casts = [
        'rating' => 'float',
    ];

    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}

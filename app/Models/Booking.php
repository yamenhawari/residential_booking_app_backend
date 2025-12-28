<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'apartment_id',
        'tenant_id',
        'start_date',
        'end_date',
        'total_price',
        'status'
    ];

    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function updateRequests()
    {
        return $this->hasMany(BookingUpdateRequest::class);
    }
    public function review()
    {
        return $this->hasOne(Review::class);
    }
}

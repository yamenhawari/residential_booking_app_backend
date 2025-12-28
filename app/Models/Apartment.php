<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Apartment extends Model
{
protected $fillable = [
    'owner_id',
    'governorate_id',
    'city_id',
    'title',
    'description',
    'address',
    'room_count',
    'price_per_month',
    'available_from',
    'available_to',
    'status'
];


    public function governorate()
    {
        return $this->belongsTo(Governorate::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function images()
    {
        return $this->hasMany(ApartmentImage::class);
    }

    public function features()
    {
        return $this->belongsToMany(Feature::class, 'apartment_features');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_apartment');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}

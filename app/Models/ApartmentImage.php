<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApartmentImage extends Model
{
    protected $fillable = ['apartment_id', 'image_url'];

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }
}


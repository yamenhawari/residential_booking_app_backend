<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Governorate extends Model
{    protected $fillable = ['name'];

    public function cities()
    {
        return $this->hasMany(City::class);
    }

    public function apartments()
    {
        return $this->hasMany(Apartment::class);
    }
}

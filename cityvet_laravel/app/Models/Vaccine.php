<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vaccine extends Model
{
    //
    protected $fillable = [
        'name',
        'description',
        'stock',
        'image_url',
        'image_public_id',
    ];

    public function animals() {
        return $this->belongsToMany(Animal::class);
    }
}

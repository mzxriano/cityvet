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
        'protect_against',
        'affected',
        'schedule',
        'expiration_date',
    ];

    public function animals() {
        return $this->belongsToMany(Animal::class)
            ->withPivot('dose', 'date_given', 'administrator', 'activity_id')
            ->withTimestamps();
    }
}

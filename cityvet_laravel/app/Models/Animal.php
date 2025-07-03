<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Animal extends Model
{
    
    protected $fillable = [
        'type',
        'name',
        'breed',
        'birth_date',
        'gender',
        'weight',
        'height',
        'color',
        'user_id',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class);
    }

}

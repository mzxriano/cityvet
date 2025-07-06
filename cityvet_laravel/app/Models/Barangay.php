<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barangay extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    // Relationship with Activity
    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    public function users() {
        return $this->hasMany(User::class);
    }
}
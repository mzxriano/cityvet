<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Animal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'name',
        'breed',
        'birth_date',
        'gender',
        'weight',
        'height',
        'color',
        'code',
        'image',
        'image_url',
        'image_public_id'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($animal) {
            // Generate unique QR code when creating new animal
            $animal->code = self::generateUniqueQrCode();
        });
    }

    /**
     * Get the user that owns the animal
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vaccines() {
        return $this->belongsToMany(Vaccine::class)
            ->withPivot('dose', 'date_given', 'administrator')
            ->withTimestamps();
    }

    /**
     * Get the route key name for route model binding
     */
    public function getRouteKeyName()
    {
        return 'code';
    }

    /**
     * Generate unique QR code
     */
    private static function generateUniqueQrCode()
    {
        do {
            $code = 'ANM-' . strtoupper(Str::random(8));
        } while (self::where('code', $code)->exists());
        
        return $code;
    }

    /**
     * Get the URL for the QR code
     */
    public function getQrCodeUrl()
    {
        return url('/animals/' . $this->code);
    }

    /**
     * Get age in years from birth_date
     */
    // public function getAgeAttribute()
    // {
    //     return $this->birth_date ? $this->birth_date->age : null;
    // }
}
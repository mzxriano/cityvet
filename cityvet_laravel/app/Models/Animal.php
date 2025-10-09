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
        'unique_spot',
        'known_conditions',
        'code',
        'image',
        'image_url',
        'image_public_id',
        'status',
        'deceased_date',
        'deceased_cause',
        'deceased_notes'
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
            ->withPivot('dose', 'date_given', 'administrator', 'activity_id')
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

    /**
     * Status helper methods
     */
    public function isAlive()
    {
        return $this->status === 'alive';
    }

    public function isDeceased()
    {
        return $this->status === 'deceased';
    }

    public function isMissing()
    {
        return $this->status === 'missing';
    }

    public function isTransferred()
    {
        return $this->status === 'transferred';
    }

    /**
     * Mark animal as deceased
     */
    public function markAsDeceased($deceasedDate, $cause = null, $notes = null)
    {
        $this->update([
            'status' => 'deceased',
            'deceased_date' => $deceasedDate,
            'deceased_cause' => $cause,
            'deceased_notes' => $notes,
        ]);

        return $this;
    }

    /**
     * Scopes for filtering by status
     */
    public function scopeAlive($query)
    {
        return $query->where('status', 'alive');
    }

    public function scopeDeceased($query)
    {
        return $query->where('status', 'deceased');
    }

    public function scopeMissing($query)
    {
        return $query->where('status', 'missing');
    }

    public function scopeTransferred($query)
    {
        return $query->where('status', 'transferred');
    }

    public function scopeByStatus($query, $status)
    {
        if ($status && $status !== 'all') {
            return $query->where('status', $status);
        }
        return $query;
    }

    /**
     * Get the archive records for this animal
     */
    public function archives()
    {
        return $this->hasMany(AnimalArchive::class);
    }

    /**
     * Get the most recent archive record
     */
    public function latestArchive()
    {
        return $this->hasOne(AnimalArchive::class)->latest();
    }

    /**
     * Check if animal is archived
     */
    public function isArchived()
    {
        return $this->archives()->exists();
    }
}
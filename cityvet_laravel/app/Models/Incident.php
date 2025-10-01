<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Incident extends Model
{
    use HasFactory;

    protected $fillable = [
        'victim_name',
        'age',
        'species',
        'bite_provocation',
        'latitude',
        'longitude',
        'location_address',
        'incident_time',
        'remarks',
        'photo_path',
        'reported_by',
        'status',
        'confirmed_by',
        'confirmed_at',
    ];

    protected $casts = [
        'incident_time' => 'datetime',
        'reported_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'age' => 'integer',
    ];

    protected $dates = [
        'incident_time',
        'reported_at',
        'created_at',
        'updated_at',
    ];

    // Scope for recent incidents
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('incident_time', '>=', now()->subDays($days));
    }

    // Scope for filtering by species
    public function scopeBySpecies($query, $species)
    {
        return $query->where('species', 'like', '%' . $species . '%');
    }

    // Status-related scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeUnderReview($query)
    {
        return $query->where('status', 'under_review');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeDisputed($query)
    {
        return $query->where('status', 'disputed');
    }

    // Scope for filtering by provocation
    public function scopeByProvocation($query, $provocation)
    {
        return $query->where('bite_provocation', $provocation);
    }

    // Scope for searching
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('victim_name', 'like', '%' . $term . '%')
              ->orWhere('species', 'like', '%' . $term . '%')
              ->orWhere('location_address', 'like', '%' . $term . '%')
              ->orWhere('bite_provocation', 'like', '%' . $term . '%');
        });
    }

    // Scope for date range
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('incident_time', [$from, $to]);
    }

    // Get photo URL
    public function getPhotoUrlAttribute()
    {
        if ($this->photo_path) {
            return asset('storage/' . $this->photo_path);
        }
        return null;
    }

    // Status check methods
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isUnderReview()
    {
        return $this->status === 'under_review';
    }

    public function isConfirmed()
    {
        return $this->status === 'confirmed';
    }

    public function isDisputed()
    {
        return $this->status === 'disputed';
    }

    // Status change methods (used by barangay personnel via API)
    public function confirm($confirmedBy)
    {
        $this->update([
            'status' => 'confirmed',
            'confirmed_by' => $confirmedBy,
            'confirmed_at' => now(),
        ]);
    }

    public function dispute($confirmedBy)
    {
        $this->update([
            'status' => 'disputed',
            'confirmed_by' => $confirmedBy,
            'confirmed_at' => now(),
        ]);
    }
}

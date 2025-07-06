<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'reason',
        'barangay_id',
        'details',
        'time',
        'date',
        'status'
    ];

    protected $casts = [
        'date' => 'date',
        'time' => 'datetime:H:i'
    ];

    // Relationship with Barangay
    public function barangay()
    {
        return $this->belongsTo(Barangay::class);
    }

    // Optional: Add scope for filtering by status
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Optional: Add scope for searching
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('reason', 'like', '%' . $search . '%')
              ->orWhereHas('barangay', function ($barangayQuery) use ($search) {
                  $barangayQuery->where('name', 'like', '%' . $search . '%');
              });
        });
    }
}
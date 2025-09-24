<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'reason',
        'category',
        'barangay_id',
        'details',
        'time',
        'date',
        'status',
        'memo',
        'images',
        'created_by',
        'approved_at',
        'approved_by',
        'rejected_at',
        'rejected_by',
        'rejection_reason'
    ];

    protected $casts = [
        'date' => 'date',
        'time' => 'datetime:H:i',
        'images' => 'array',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime'
    ];

    // Relationship with Barangay
    public function barangay()
    {
        return $this->belongsTo(Barangay::class);
    }

    // Relationship with User (creator)
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relationship with Admin (approver)
    public function approver()
    {
        return $this->belongsTo(\App\Models\Admin::class, 'approved_by');
    }

    // Relationship with Admin (rejecter)
    public function rejecter()
    {
        return $this->belongsTo(\App\Models\Admin::class, 'rejected_by');
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
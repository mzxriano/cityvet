<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnimalArchive extends Model
{
    protected $fillable = [
        'animal_id',
        'user_id',
        'archive_type',
        'reason',
        'notes',
        'archive_date',
        'animal_snapshot',
    ];

    protected $casts = [
        'archive_date' => 'date',
        'animal_snapshot' => 'array',
    ];

    /**
     * Get the animal that was archived
     */
    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }

    /**
     * Get the user who archived the animal
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for deceased animals only
     */
    public function scopeDeceased($query)
    {
        return $query->where('archive_type', 'deceased');
    }

    /**
     * Scope for deleted animals only
     */
    public function scopeDeleted($query)
    {
        return $query->where('archive_type', 'deleted');
    }

    /**
     * Get the original animal data from snapshot
     */
    public function getOriginalAnimalData()
    {
        return $this->animal_snapshot;
    }
}

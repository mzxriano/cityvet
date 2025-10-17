<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnimalBreed extends Model
{
    use HasFactory;

    protected $fillable = [
        'animal_type_id',
        'name',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the animal type that owns this breed
     */
    public function animalType()
    {
        return $this->belongsTo(AnimalType::class);
    }

    /**
     * Get animals of this breed
     */
    public function animals()
    {
        return $this->hasMany(Animal::class);
    }

    /**
     * Scope to get only active breeds
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}

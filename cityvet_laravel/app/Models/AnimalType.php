<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnimalType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'category',
        'icon',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the breeds for this animal type
     */
    public function breeds()
    {
        return $this->hasMany(AnimalBreed::class)->orderBy('sort_order');
    }

    /**
     * Get active breeds for this animal type
     */
    public function activeBreeds()
    {
        return $this->hasMany(AnimalBreed::class)->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * Get animals of this type
     */
    public function animals()
    {
        return $this->hasMany(Animal::class);
    }

    /**
     * Get animals of this type
     */
    public function affectedProduct()
    {
        return $this->hasMany(VaccineProduct::class, 'affected_id');
    }

    /**
     * Scope to get only active types
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get types by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to order by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}

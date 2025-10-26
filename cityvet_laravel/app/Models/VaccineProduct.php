<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VaccineProduct extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * Corresponds to the 'vaccine_products' migration.
     *
     * @var string
     */
    protected $table = 'vaccine_products';

    /**
     * The attributes that are mass assignable.
     * These fields are the static product details.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'brand',
        'category',
        'storage_temp',
        'withdrawal_days',
        'unit_of_measure',
        'protect_against',
        'affected_id',
        'image_url',
        'image_public_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        // Ensure integer fields are cast correctly
        'withdrawal_days' => 'integer', 
    ];

    /**
     * Define the one-to-many relationship with VaccineLot.
     * A single VaccineProduct can have many different Lots (shipments/batches).
     *
     * @return HasMany
     */
    public function lots(): HasMany
    {
        return $this->hasMany(VaccineLot::class);
    }

    public function affectedAnimal()
    {
        return $this->belongsTo(AnimalType::class, 'affected_id');
    }
}
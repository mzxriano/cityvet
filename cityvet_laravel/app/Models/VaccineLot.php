<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VaccineLot extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'vaccine_lots';

    /**
     * The attributes that are mass assignable.
     * These fields are unique to each incoming shipment/batch.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'vaccine_product_id',
        'lot_number',
        'expiration_date',
        'initial_stock',
        'current_stock',
        'received_date',
        'storage_location',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expiration_date' => 'date',
        'received_date' => 'date',
        'initial_stock' => 'integer',
        'current_stock' => 'integer',
    ];

    /**
     * Define the inverse one-to-many relationship with VaccineProduct.
     * This Lot belongs to one generic Vaccine Product (e.g., "Rabies Vaccine").
     *
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(VaccineProduct::class, 'vaccine_product_id');
    }

    /**
     * Define the one-to-many relationship with Administration records.
     * This Lot can be used for many different animal administrations.
     *
     * @return HasMany
     */
    public function administrations(): HasMany
    {
        // Links this Lot to all entries in the 'animal_vaccine_administrations' table
        return $this->hasMany(AnimalVaccineAdministration::class);
    }
}
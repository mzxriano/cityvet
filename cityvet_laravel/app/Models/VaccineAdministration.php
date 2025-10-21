<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VaccineAdministration extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * Use the new, robust table name.
     * @var string
     */
    protected $table = 'animal_vaccine_administrations';

    /**
     * The attributes that are mass assignable.
     * This list must match the columns in your database table.
     * @var array
     */
    protected $fillable = [
        'animal_id',
        'vaccine_lot_id',
        'activity_id', // For campaign tracking
        'doses_given',
        'date_given',
        'administrator',
        'route_of_admin',
        'site_of_admin',
        'adverse_reaction',
        'next_due_date',
        'withdrawal_end_date',
    ];

    /**
     * The attributes that should be cast to native types.
     * @var array
     */
    protected $casts = [
        'date_given' => 'date',
        'next_due_date' => 'date',
        'withdrawal_end_date' => 'date',
        'adverse_reaction' => 'boolean',
        'doses_given' => 'float', // Use float/double as dose amount might not be a whole number
    ];

    // ----------------------
    // RELATIONSHIPS
    // ----------------------

    /**
     * Get the animal that received the vaccination.
     */
    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }

    /**
     * Get the specific lot (inventory record) that was consumed.
     */
    public function lot()
    {
        return $this->belongsTo(VaccineLot::class, 'vaccine_lot_id');
    }

    /**
     * Get the activity (campaign) this vaccination was part of.
     */
    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }
}
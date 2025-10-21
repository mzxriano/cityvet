<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VaccineStockAdjustment extends Model
{
    use HasFactory;

    // The fields that can be mass-assigned
    protected $fillable = [
        'vaccine_lot_id',
        'adjustment_type',
        'quantity',
        'reason',
        'administrator',
    ];

    // Optional: Define relationship back to the lot
    public function lot()
    {
        return $this->belongsTo(VaccineLot::class, 'vaccine_lot_id');
    }
}

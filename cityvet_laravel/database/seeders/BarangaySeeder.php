<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Barangay;

class BarangaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $barangays = [
            'Anonas',
            'Bactad East',
            'Bayaoas',
            'Bolaoen',
            'Cabaruan',
            'Cabuloan',
            'Camanang',
            'Camantiles',
            'Casantaan',
            'Catablan',
            'Cayambanan',
            'Consolacion',
            'Dilan-Paurido',
            'Labit Proper',
            'Labit West',
            'Mabanogbog',
            'Macalong',
            'Nancalobasaan',
            'Nancamaliran East',
            'Nancamaliran West',
            'Nancayasan',
            'Oltama',
            'Palina East',
            'Palina West',
            'Pedro T. Orata',
            'Pinmaludpod',
            'Poblacion',
            'San Jose',
            'San Vicente',
            'Santa Lucia',
            'Santo Domingo',
            'Sugcong',
            'Tipuso',
            'Tulong',
        ];

        foreach ($barangays as $barangay) {
            Barangay::firstOrCreate(['name' => $barangay]);
        }
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Models\Vaccine;
use App\Models\User;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class VaccineController extends Controller
{
    public function index()
    {
        $vaccines = Vaccine::all();
        return response()->json($vaccines);
    }

    public function getAllVaccinationRecords()
    {
        $records = DB::table('animal_vaccine')
            ->join('animals', 'animal_vaccine.animal_id', '=', 'animals.id')
            ->join('vaccines', 'animal_vaccine.vaccine_id', '=', 'vaccines.id')
            ->join('users', 'animals.user_id', '=', 'users.id')
            ->select(
                'animal_vaccine.id',
                'animal_vaccine.dose',
                'animal_vaccine.date_given',
                'animal_vaccine.administrator',
                'animals.name as animal_name',
                'animals.type as animal_type',
                'vaccines.name as vaccine_name',
                DB::raw("CONCAT(users.first_name, ' ', users.last_name) as owner_name"),
                'users.phone_number as owner_phone'
            )
            ->orderBy('animal_vaccine.date_given', 'desc')
            ->get();

        return response()->json($records, 200);
    }
    
    public function fetchVeterinarians()
    {
        $vets = User::whereHas('role', function ($query) {
            $query->where('name', 'veterinarian');
        })->with('role')->get();

        return response()->json($vets);
    }

} 
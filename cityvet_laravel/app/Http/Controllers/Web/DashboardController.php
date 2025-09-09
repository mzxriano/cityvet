<?php

namespace App\Http\Controllers\Web;

use App\Models\Animal;
use App\Models\Barangay;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $totalUsers = User::count();
        $totalAnimals = Animal::count();
        $totalVaccinatedAnimals = Animal::whereHas('vaccines')->distinct()->count('id');
        $userTypeCounts = DB::table('user_roles')
            ->join('roles', 'user_roles.role_id', '=', 'roles.id')
            ->select('roles.name', DB::raw('COUNT(user_roles.user_id) as count'))
            ->groupBy('roles.name')
            ->pluck('count', 'roles.name');
        $animalsPerCategory = Animal::select('type', DB::raw('COUNT(*) as total'))
            ->groupBy('type')
            ->get();
        $barangays = Barangay::withCount(['users as vaccinated_animals_count' => function ($query) {
            $query->join('animals', 'users.id', '=', 'animals.user_id')
                ->join('animal_vaccine', 'animals.id', '=', 'animal_vaccine.animal_id')
                ->select(DB::raw('count(distinct animals.id)'));
        }])->get();

        return view(
            "admin.dashboard", compact("totalUsers","totalAnimals", "totalVaccinatedAnimals", "animalsPerCategory", "barangays", "userTypeCounts"));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

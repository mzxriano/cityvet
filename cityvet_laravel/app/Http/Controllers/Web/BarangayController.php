<?php

namespace App\Http\Controllers\Web;

use App\Models\Barangay;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class BarangayController extends Controller
{
    public function index(Request $request)
    {
        $query = Barangay::query();
        
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
                
        $barangays = $query->with('activities')
            ->withCount(['users as vaccinated_animals_count' => function ($query) {
                $query->join('animals', 'users.id', '=', 'animals.user_id')
                    ->join('animal_vaccine', 'animals.id', '=', 'animal_vaccine.animal_id')
                    ->select(DB::raw('count(distinct animals.id)'));
            }])
            ->orderBy('name', 'asc')
            ->paginate(10);
        
        return view("admin.barangay", compact("barangays"));
    }
}
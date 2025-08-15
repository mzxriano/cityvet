<?php

namespace App\Http\Controllers\Web;

use App\Models\Barangay;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class BarangayController extends Controller
{
    public function index(Request $request)
    {
        $query = Barangay::query();
        
        // Search functionality
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        // Load barangays with activities and apply ordering and pagination
        $barangays = $query->with('activities')
                          ->orderBy('name', 'asc')
                          ->paginate(10);
        
        return view("admin.barangay", compact("barangays"));
    }
}
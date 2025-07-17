<?php

namespace App\Http\Controllers\Web;

use App\Models\Barangay;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class BarangayController extends Controller
{
    public function index(){
        $barangays = Barangay::with("activities")->orderBy('name', 'asc' )->paginate(10);
        return view("admin.barangay",compact("barangays"));
    }
}

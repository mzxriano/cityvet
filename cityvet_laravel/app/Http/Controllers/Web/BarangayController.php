<?php

namespace App\Http\Controllers\Web;

use App\Models\Barangay;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class BarangayController extends Controller
{
    public function index(){
        $barangays = Barangay::with("activities")->get();
        return view("barangay",compact("barangays"));
    }
}

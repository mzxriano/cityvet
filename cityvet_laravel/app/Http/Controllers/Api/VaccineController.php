<?php

namespace App\Http\Controllers\Api;

use App\Models\Vaccine;
use Illuminate\Routing\Controller;

class VaccineController extends Controller
{
    public function index()
    {
        $vaccines = Vaccine::all();
        return response()->json($vaccines);
    }
} 
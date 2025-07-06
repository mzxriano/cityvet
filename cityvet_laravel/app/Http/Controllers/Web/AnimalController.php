<?php

namespace App\Http\Controllers\Web;

use App\Models\Animal;
use DB;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Validator;

class AnimalController extends Controller
{
    /**
     * Display a list of animals through json response.
     */
    public function index()
    {
                
        $animals = DB::table("animals")->get();

        return view("animals", compact("animals"));
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

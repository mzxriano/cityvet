<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use Validator;

class AnimalController extends Controller
{
    /**
     * Display a list of animals through json response.
     */
    public function index()
    {
        
        $animals = DB::table("animals")->get();

        return response()->json($animals);
    }

    /**
     * Display a list of animals in the animals view.
     */
    public function indexWeb() {

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
        //
        $validate = Validator::make($request->all(), [
            'type' => 'required|string',
            'name' => 'nullable|string',
            'breed' => 'required|string',
            'birth_date' => 'nullable|date',
            'gender' => 'required|string',
            'weight' => 'nullable|numeric',
            'height' => 'nullable|numeric',
            'color' => 'required|string',
        ]);

        if($validate->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validate->errors(),
            ], 422);
        }

        $validated = $validate->validated();

        DB::table("animals")->insert($validated);

        return response()->json(['message' => 'Animal successfully created.']);
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

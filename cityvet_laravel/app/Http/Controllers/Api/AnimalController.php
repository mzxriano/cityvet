<?php

namespace App\Http\Controllers\Api;

use App\Models\Animal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AnimalController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $animals = Animal::where("user_id", auth()->id())->get();

        return response()->json($animals);
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

        $user_id = auth()->user()->id;

        Animal::create([
            ...$validated,
            'user_id' => $user_id,
        ]);

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
     * Edit the specified resource.
     */
    public function edit(Request $request,$id)
    {

        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $animal = $user->animals()->find($id);

        if (!$animal) {
            return response()->json([
                'message' => 'No animal found for the authenticated user.'
            ], 404);
        }

        $validate = $request->validate([
            'type' => 'sometimes|string',
            'name' => 'sometimes|string',
            'breed' => 'sometimes|string',
            'birth_date' => 'sometimes|date',
            'gender' => 'sometimes|string',
            'weight' => 'sometimes|numeric',
            'height' => 'sometimes|numeric',
            'color' => 'sometimes|string',
        ]);

        $animal->update($validate);

        return response()->json([
            'message' => 'Animal details, successfully changed.',
            'animal' => $animal,
        ]);
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

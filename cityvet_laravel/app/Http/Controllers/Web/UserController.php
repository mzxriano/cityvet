<?php

namespace App\Http\Controllers\Web;

use App\Models\Barangay;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::with(["role", "barangay"]);

        if($request->filled('role')){
            $query->where('role_id', $request->role);
        }

        if($request->filled('search')){
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like',"%{$search}%");
            });
        }

        $users = $query->get();
        $roles = Role::all();
        $barangays = Barangay::all();

        return view("admin.users", compact([
            "users",
            "roles",
            "barangays",
        ]));

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
        $validate = Validator::make($request->all(), [
            "first_name" => "required|string|max:255",
            "last_name" => "required|string|max:255",
            "birth_date" => "required|date",
            "email" => "required|email|unique:users,email",
            "phone_number" => "required|string|unique:users,phone_number",
            "barangay_id" => "required|integer|exists:barangays,id",
            "street" => "required|string|max:255",
            "role_id" => "required|integer|exists:roles,id",
            "password" => "required|string|min:8|confirmed"
        ]);

        if($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        $validated = $validate->validated();
        $validated["password"] = Hash::make($validated["password"]);

        User::create($validated);

        return redirect()->route("admin.users")->with("success", "User Successfully Created.");

    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        
        // Start with the user's animals relationship
        $animalsQuery = $user->animals();
        
        // Apply animal type filter if provided
        if ($request->filled('animal_type')) {
            $animalsQuery->where('type', $request->animal_type);
        }
        
        // Apply search filter if provided
        if ($request->filled('animal_search')) {
            $search = $request->animal_search;
            $animalsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('breed', 'like', "%{$search}%")
                  ->orWhere('registration_number', 'like', "%{$search}%");
            });
        }
        
        $animals = $animalsQuery->get();
        
        // Get distinct animal types for the filter dropdown
        $animalTypes = $user->animals()->distinct()->pluck('type')->filter()->sort();

        return view('admin.users_view', compact([
            'user', 
            'animals', 
            'animalTypes'
        ]));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate(rules: [
            'first_name'   => 'sometimes|string|max:255',
            'last_name'    => 'sometimes|string|max:255',
            'email'        => 'sometimes|email|unique:users,email,' . $user->id,
            'phone_number' => 'sometimes|string|unique:users,phone_number,' . $user->id,
            'birth_date'   => 'sometimes|date',
            'barangay_id'  => 'sometimes|integer|exists:barangays,id',
            'street'       => 'sometimes|nullable|string',
            'role_id'      => 'sometimes|integer|exists:roles,id',
        ]);

        $user->update($validated);

        return redirect()->route('admin.users')->with('success', 'User successfully updated.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function edit(Request $request, string $id)
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
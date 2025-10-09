<?php

namespace App\Http\Controllers\Web;

use App\Models\Barangay;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Notifications\NewUserCredentials;

class UserController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::with(["roles", "barangay"]);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        } else {
            // For "All" tab, exclude pending users - only show approved/active users
            $query->whereIn('status', ['active', 'inactive']);
        }

        // Filter by role
        if ($request->filled('role')) {
            $roleId = $request->input('role');
            $query->whereHas('roles', function ($q) use ($roleId) {
                $q->where('id', $roleId);
            });
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Get per_page value, default to 10
        $perPage = $request->input('per_page', 10);
        
        // Paginate results
        $users = $query->paginate($perPage);

        // Get counts for tabs
        $allCount = User::whereIn('status', ['active', 'inactive'])->count();
        $pendingCount = User::where('status', 'pending')->count();
        $rejectedCount = User::where('status', 'rejected')->count();

        $roles = Role::all();
        $barangays = Barangay::all();

        return view("admin.users", compact([
            "users",
            "roles",
            "barangays",
            "allCount",
            "pendingCount",
            "rejectedCount"
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
            "role_ids" => "required|array|min:1",
            "role_ids.*" => "exists:roles,id",
            "status" => "nullable|in:active,inactive,pending,rejected,banned",
        ]);

        if($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        $validated = $validate->validated();
        $validated["status"] = $validated["status"] ?? "pending";

        // Generate a random password for the user
        $password = Str::random(10);
        $validated["password"] = Hash::make($password);
        $validated["force_password_change"] = true;

        $user = User::create($validated);
        $user->roles()->attach($validated['role_ids']);

        $user->notify(new NewUserCredentials($password));

        return redirect()->route("admin.users")->with("success", "User Successfully Created.");

    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        
        // Start with the user's animals relationship (only alive animals)
        $animalsQuery = $user->animals()->where('status', 'alive');
        
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
        
        // Get distinct animal types for the filter dropdown (only alive animals)
        $animalTypes = $user->animals()->where('status', 'alive')->distinct()->pluck('type')->filter()->sort();

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
            'role_ids' => 'sometimes|array',
            'role_ids.*' => 'exists:roles,id',
            'status' => 'sometimes|in:active,inactive,pending,rejected,banned',
        ]);

        $user->update($validated);

        if ($request->filled('role_ids')) {
            $user->roles()->sync($validated['role_ids']);
        }

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

    /**
     * Approve a pending user
     */
    public function approve(string $id)
    {
        $user = User::findOrFail($id);
        
        if ($user->status !== 'pending') {
            return redirect()->back()->with('error', 'Only pending users can be approved.');
        }

        $user->update(['status' => 'active']);

        // Send approval email
        $user->notify(new \App\Notifications\UserApproved());

        return redirect()->route('admin.users')->with('success', 'User has been approved successfully and notified via email.');
    }

    /**
     * Reject a pending user
     */
    public function reject(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        
        if ($user->status !== 'pending') {
            return redirect()->back()->with('error', 'Only pending users can be rejected.');
        }

        $validated = $request->validate([
            'rejection_message' => 'required|string|max:1000'
        ]);

        $user->update(['status' => 'rejected']);

        // Send rejection email with message
        $user->notify(new \App\Notifications\UserRejected($validated['rejection_message']));

        return redirect()->route('admin.users')->with('success', 'User has been rejected and notified via email.');
    }
}
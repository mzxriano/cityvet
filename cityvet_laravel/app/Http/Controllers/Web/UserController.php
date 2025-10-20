<?php

namespace App\Http\Controllers\Web;

use App\Models\Barangay;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Notifications\NewUserCredentials;
use App\Notifications\UserBanned;
use App\Services\NotificationService;

class UserController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Define Role Groups (assuming role names are snake_case)
        $ownerRoles = ['pet_owner', 'livestock_owner', 'poultry_owner'];
        $adminRoles = ['aew', 'veterinarian', 'staff', 'sub_admin', 'barangay_personnel'];
        $allApprovedStatuses = ['active', 'inactive'];

        // --- Base Query Setup and Filtering ---

        $query = User::with(["roles", "barangay"]);

        // --- Tab Filtering Logic ---
        $currentRoleGroup = $request->input('role_group');
        $currentStatus = $request->input('status');

        // Determine the effective role_group for the query.
        // If no status or role_group is specified, default to 'animal_owners'.
        $effectiveRoleGroup = $currentRoleGroup ?: ($currentStatus ? null : 'animal_owners');

        if ($currentStatus === 'pending') {
            $query->where('status', 'pending');
        } elseif ($currentStatus === 'rejected') {
            $query->where('status', 'rejected');
        } elseif ($effectiveRoleGroup) {
            // Role Group Tabs (only apply to approved users)
            $query->whereIn('status', $allApprovedStatuses);
            
            $roleNames = [];
            if ($effectiveRoleGroup === 'animal_owners') {
                $roleNames = $ownerRoles;
            } elseif ($effectiveRoleGroup === 'administrative') {
                $roleNames = $adminRoles;
            }

            if (!empty($roleNames)) {
                 // Find role IDs for the names
                $roleIds = Role::whereIn('name', $roleNames)->pluck('id')->toArray();

                $query->whereHas('roles', function ($q) use ($roleIds) {
                    $q->whereIn('id', $roleIds);
                });
            }
        }
        
        // --- Existing Role/Search Filters (Applied to the current query) ---
        
        // Filter by single role (from filter form)
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

        // --- Get counts for tabs ---
        
        $rolesModel = Role::whereIn('name', array_merge($ownerRoles, $adminRoles))->get()->keyBy('name');

        // Get role IDs for groups
        $ownerRoleIds = $rolesModel->whereIn('name', $ownerRoles)->pluck('id')->toArray();
        $adminRoleIds = $rolesModel->whereIn('name', $adminRoles)->pluck('id')->toArray();

        // Counts for status tabs
        $pendingCount = User::where('status', 'pending')->count();
        $rejectedCount = User::where('status', 'rejected')->count();

        // Combined Owner Count
        $ownerCount = User::whereIn('status', $allApprovedStatuses)->whereHas('roles', function ($q) use ($ownerRoleIds) {
            $q->whereIn('id', $ownerRoleIds);
        })->count();
        
        // Administrative Count
        $administrativeCount = User::whereIn('status', $allApprovedStatuses)->whereHas('roles', function ($q) use ($adminRoleIds) {
            $q->whereIn('id', $adminRoleIds);
        })->count();
        
        // --- Existing data for view ---

        $roles = Role::all();
        $barangays = Barangay::all();

        // Fetch pending role requests for admin UI
        $roleRequests = \App\Models\RoleRequest::with(['user', 'requestedRole'])
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->get();

        return view("admin.users", compact([
            "users",
            "roles",
            "barangays",
            // Updated Counts
            "ownerCount",
            "administrativeCount",
            "pendingCount",
            "rejectedCount",
            "roleRequests"
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
            "email" => ($request->has('has_no_email') ? 'nullable' : 'required') . "|email|unique:users,email",
            "phone_number" => ($request->has('has_no_phone') ? 'nullable' : 'required') . "|string|unique:users,phone_number",
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

        // Handle no email
        $validated['has_no_email'] = $request->has('has_no_email');
        if ($validated['has_no_email']) {
            $validated['email'] = 'no-email-' . uniqid() . '@cityvet.local';
        }
        // Handle no phone
        $validated['has_no_phone'] = $request->has('has_no_phone');
        if ($validated['has_no_phone']) {
            $validated['phone_number'] = '0000' . str_pad(rand(1, 9999999), 7, '0');
        }

        // Generate a random password for the user
        $password = Str::random(10);
        $validated["password"] = Hash::make($password);
        $validated["force_password_change"] = true;

        $user = User::create($validated);
        $user->roles()->attach($validated['role_ids']);

        NotificationService::newUserRegistration($user);

        // Only notify if user has email
        if (!$request->has('has_no_email')) {
            $user->notify(new NewUserCredentials($password));
        } else {
            // Attach random code to session for display or notification
            session()->flash('user_code', $randomCode);
        }

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
            'ban_reason' => 'nullable|string|max:1000',
        ]);

        // Handle ban status change
        if ($request->filled('status') && $request->status === 'banned' && $user->status !== 'banned') {
            $banReason = $request->ban_reason ?? 'No reason provided';
            
            // Update user status
            $user->update([
                'status' => 'banned',
                'banned_at' => now(),
                'ban_reason' => $banReason
            ]);
            
            // Send ban notification email
            $user->notify(new UserBanned($banReason));
            
            // Invalidate user sessions (force logout)
            \DB::table('sessions')->where('user_id', $user->id)->delete();
            
            $message = 'User has been banned and notified via email.';
        } else {
            $user->update($validated);
            $message = 'User successfully updated.';
        }

        if ($request->filled('role_ids')) {
            $user->roles()->sync($validated['role_ids']);

            // Also reject any approved RoleRequest for roles that were removed
            $currentRoleIds = $user->roles()->pluck('roles.id')->toArray();
            $approvedRequests = \App\Models\RoleRequest::where('user_id', $user->id)
                ->where('status', 'approved')
                ->get();
            foreach ($approvedRequests as $roleRequest) {
                if (!in_array($roleRequest->requested_role_id, $currentRoleIds)) {
                    $roleRequest->status = 'rejected';
                    $roleRequest->rejection_reason = 'Role removed by admin.';
                    $roleRequest->rejected_at = now();
                    $roleRequest->save();
                }
            }

            // Also approve any pending or rejected RoleRequest for roles that were newly added
            $roleRequestsToApprove = \App\Models\RoleRequest::where('user_id', $user->id)
                ->whereIn('requested_role_id', $currentRoleIds)
                ->whereIn('status', ['pending', 'rejected'])
                ->get();
            foreach ($roleRequestsToApprove as $roleRequest) {
                $roleRequest->status = 'approved';
                $roleRequest->approved_at = now();
                $roleRequest->rejection_reason = null;
                $roleRequest->rejected_at = null;
                $roleRequest->reviewed_by = auth()->id();
                $roleRequest->save();
            }
        }

        return redirect()->route('admin.users')->with('success', $message);
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
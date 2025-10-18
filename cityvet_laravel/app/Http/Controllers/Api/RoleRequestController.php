<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class RoleRequestController extends Controller
{
    // User requests a new role
    public function requestRole(Request $request)
    {
        $user = auth()->user();
        $roleId = $request->input('role_id');
        $reason = $request->input('reason');

        // Get user's current role from relationship
        $currentRole = $user->roles()->first();
        
        // Prevent requesting current role or admin
        if (!$currentRole || $roleId == $currentRole->id || $this->isAdminRole($roleId)) {
            return response()->json(['error' => 'Invalid role request.'], 422);
        }

        // Prevent duplicate pending requests
        $existing = \App\Models\RoleRequest::where('user_id', $user->id)
            ->where('requested_role_id', $roleId)
            ->where('status', 'pending')
            ->first();
        if ($existing) {
            return response()->json(['error' => 'You already have a pending request for this role.'], 409);
        }

        $roleRequest = \App\Models\RoleRequest::create([
            'user_id' => $user->id,
            'requested_role_id' => $roleId,
            'reason' => $reason,
            'status' => 'pending',
        ]);

        return response()->json(['success' => true, 'request' => $roleRequest]);
    }

    // Get available roles for request
    public function availableRoles(Request $request)
    {
        $user = auth()->user();
        
        $currentRole = $user->roles()->first();
        
        if (!$currentRole) {
            return response()->json(['roles' => []]);
        }
        
        $ownerRoles = ['pet_owner', 'livestock_owner', 'poultry_owner'];
        
        if (in_array($currentRole->name, $ownerRoles)) {
            $roles = \App\Models\Role::whereIn('name', $ownerRoles)
                ->where('id', '!=', $currentRole->id)
                ->get();
        } else {
            $roles = \App\Models\Role::where('id', '!=', $currentRole->id)
                ->where('name', '!=', 'admin')
                ->whereNotIn('name', $ownerRoles)
                ->get();
        }
        
        return response()->json(['roles' => $roles]);
    }

    // Switch to an approved role
    public function switchRole(Request $request)
    {
        $user = auth()->user();
        $roleId = $request->input('role_id');

        $request->validate([
            'role_id' => 'required|exists:roles,id'
        ]);

        if (! $user->roles()->where('roles.id', $roleId)->exists()) {
            return response()->json(['error' => 'You do not have this role.'], 403);
        }

        $approved = \App\Models\RoleRequest::where('user_id', $user->id)
            ->where('requested_role_id', $roleId)
            ->where('status', 'approved')
            ->first();

        if (! $approved) {
            return response()->json(['error' => 'Role not approved for switching.'], 403);
        }

        $user->update(['current_role_id' => $roleId]);

        \Log::info("User ID {$user->id} switched to role ID {$roleId}");
        \Log::info("Current role id {$roleId}");

        return response()->json([
            'success' => true,
            'role_id' => $roleId,
            'message' => 'Role switched successfully.'
        ]);
    }


    // Get all approved roles for the current user
    public function approvedRoles(Request $request)
    {
        $user = auth()->user();

        // Fetch approved role requests
        $approved = \App\Models\RoleRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->with('requestedRole')
            ->get();

        // Attach approved roles if not already attached
        foreach ($approved as $req) {
            if (! $user->roles()->where('role_id', $req->requested_role_id)->exists()) {
                $user->roles()->attach($req->requested_role_id);
            }
        }

        // Return all approved roles (now guaranteed to be attached)
        $roles = $user->roles()->get(['roles.id', 'roles.name']);

        return response()->json(['roles' => $roles]);
    }


    // Helper: Check if role is admin
    private function isAdminRole($roleId)
    {
        $role = \App\Models\Role::find($roleId);
        return $role && $role->name === 'admin';
    }
}
<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RoleRequestController extends Controller
{
    // Admin: Reject a request
    public function adminReject($id, Request $request)
    {
        $admin = auth()->user();
        $roleRequest = \App\Models\RoleRequest::findOrFail($id);
        if ($roleRequest->status !== 'pending') {
            return response()->json(['error' => 'Request already processed.'], 409);
        }
        $roleRequest->status = 'rejected';
        $roleRequest->rejection_reason = $request->input('rejection_reason');
        $roleRequest->rejected_at = now();
        $roleRequest->reviewed_by = $admin->id;
        $roleRequest->save();
        return redirect()->route('admin.role_requests.index')->with('success', 'Role request rejected successfully.');
    }

    // Admin: Approve a request
    public function adminApprove($id)
    {
        $admin = auth()->user(); // Should be admin
        $request = \App\Models\RoleRequest::findOrFail($id);
        if ($request->status !== 'pending') {
            return response()->json(['error' => 'Request already processed.'], 409);
        }
        $request->status = 'approved';
        $request->approved_at = now();
        $request->reviewed_by = $admin->id;
        $request->save();
        return redirect()->route('admin.role_requests.index')->with('success', 'Role request approved successfully.');
    }

    // Admin: List all pending requests
    public function adminListRequests()
    {
        $roleRequests = \App\Models\RoleRequest::with(['user', 'requestedRole'])
            ->where('status', 'pending')
            ->get();

        return view('admin.role_requests.index', compact('roleRequests'));
    }
}

<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Mail\RoleRequestRejected;
use Illuminate\Support\Facades\Mail;

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
        $roleRequest->rejection_reason = $request->input('rejection_message');
        $roleRequest->rejected_at = now();
        $roleRequest->reviewed_by = $admin->id;
        $roleRequest->save();

        // Send rejection email
        $user = $roleRequest->user;
        $role = $roleRequest->requestedRole;
        $messageText = $roleRequest->rejection_reason;
        Mail::to($user->email)->send(new RoleRequestRejected($user, $role, $messageText));

        return redirect()->route('admin.users')->with('success', 'Role request rejected and user notified.');
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
        return redirect()->route('admin.users')->with('success', 'Role request approved successfully.');
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

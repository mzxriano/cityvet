<?php

namespace App\Http\Controllers\Web;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ApiController extends Controller
{
    /**
     * Search users for autocomplete functionality.
     */
    public function searchUsers(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $users = User::where(function($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                  ->orWhere('last_name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%")
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) like ?", ["%{$query}%"]);
            })
            ->where('status', 'active')
            ->select('id', 'first_name', 'last_name', 'email')
            ->limit(10)
            ->get();

        return response()->json($users);
    }
}

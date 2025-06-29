<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Register newly created user
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'birth_date' => 'required|date',
            'phone_number' => 'required|string|size:11|unique:users,phone_number',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8'
        ]);

        // Return all validation errors as JSON
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors() 
            ], 422);
        }

        $validated = $validator->validated();

        $roleId = DB::table('roles')->where('name', 'Owner')->value('id');

        DB::table('users')->insert([
            'first_name'=> $validated['first_name'],
            'last_name'=> $validated['last_name'],
            'birth_date'=> $validated['birth_date'],
            'phone_number'=> $validated['phone_number'],
            'email'=> $validated['email'],
            'password'=> Hash::make($validated['password']),
            'role_id'=> $roleId,
            'created_at'=> now(),
            'updated_at'=> now(),
        ]);

        return response()->json(['message' => 'User successfully registered!']);
    }

    /**
     * Register newly created user
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Return all validation errors as JSON
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors() 
            ], 422);
        }

        // Attempt to log in the user
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            // Authentication passed
            $user = Auth::user();
            return response()->json([
                'message' => 'Login successful.',
                'user' => $user,
                //'token' => $user->createToken('YourAppName')->plainTextToken, 
            ], 200);
        }

        // Authentication failed
        return response()->json([
            'message' => 'Invalid credentials.',
        ], 401);
    }

}

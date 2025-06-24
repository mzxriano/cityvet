<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone_number' => 'required|string|unique:users,phone_number|max:11',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8'
        ]);

        $roleId = DB::table('roles')->where('name', 'Owner')->value('id');

        DB::table('users')->insert([
            'first_name'=> $validate['first_name'],
            'last_name'=> $validate['last_name'],
            'phone_number'=> $validate['phone_number'],
            'email'=> $validate['email'],
            'password'=> Hash::make($validate['password']),
            'role_id'=> $roleId,
            'created_at'=> now(),
            'updated_at'=> now(),
        ]);

        return response()->json(['message' => 'User successfully registered!']);

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

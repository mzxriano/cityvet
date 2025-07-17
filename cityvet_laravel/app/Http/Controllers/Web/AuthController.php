<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;

class AuthController
{
    public function loginForm() {
        return view('login');
    }

    public function registerForm() {
        return view('register');
    }
}

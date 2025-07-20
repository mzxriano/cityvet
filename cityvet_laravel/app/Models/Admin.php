<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class Admin extends Authenticatable implements MustVerifyEmail
{
    protected $fillable = ['name', 'email', 'role_id', 'password'];
    protected $hidden = ['password'];
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
} 
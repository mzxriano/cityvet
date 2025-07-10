<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CommunityController
{
    public function index(){
        return view("community");
    }
}

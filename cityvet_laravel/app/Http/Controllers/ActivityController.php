<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    //

    public function index(){
        $activities = Activity::orderBy("created_at","desc")->paginate(10);

        return view("activities",compact("activities"));
    }
}

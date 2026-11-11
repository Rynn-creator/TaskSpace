<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LandingPageController extends Controller
{
    public function index(Request $request, TaskController $tasks)
    {
        if (! $request->user()) {
            return view('welcome');
        }

        return $tasks->index($request);
    }
}

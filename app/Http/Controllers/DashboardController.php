<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $name = $user->name;
        $tasks = $user->tasks;
        $count = $tasks->count();
        return view('dashboard', compact('name','tasks','count'));
    }
}
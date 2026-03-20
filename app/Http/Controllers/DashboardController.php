<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function index()
    {
        $role = session('user')['role']['name'] ?? '';
        return view('dashboard.index', compact('role'));
    }
}
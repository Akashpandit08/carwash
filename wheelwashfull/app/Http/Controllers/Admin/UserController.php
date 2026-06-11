<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function customers()
    {
        $users = User::where('role', 'customer')->latest()->paginate(15);
        $title = 'Customers';
        return view('admin.users.index', compact('users', 'title'));
    }

    public function partners()
    {
        $users = User::where('role', 'partner')->latest()->paginate(15);
        $title = 'Partners';
        return view('admin.users.index', compact('users', 'title'));
    }
}

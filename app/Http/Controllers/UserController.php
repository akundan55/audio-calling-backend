<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // List other users
    public function index() {
        return User::where('id', '!=', auth()->id())->get();
    }

    // Search by name
    public function search(Request $request) {
        return User::where('name', 'like', "%{$request->name}%")
                ->where('id', '!=', auth()->id())->get();
    }

}

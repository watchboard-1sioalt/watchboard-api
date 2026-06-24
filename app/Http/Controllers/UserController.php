<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Utilisateurs;

class UserController extends Controller
{
    //
    public function index():View
    {
        $users = Utilisateurs::all();
        return view('user.index', compact('users'));
    }

}

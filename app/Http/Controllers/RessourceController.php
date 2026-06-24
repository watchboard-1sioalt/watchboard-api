<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Ressources;

class RessourceController extends Controller
{
    //
    public function index():View
    {
        $ressources = Ressources::all();
        return view('ressource.index', compact('ressources'));
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tags;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class TagsController extends Controller
{
    //
    public function public(Request $request)
    {
        $tags = Tags::where('public', true)->get();

        return $tags;
    }

    public function create(Request $request){
        Tags::create([
            'name'=> $request->name,
            'public' => false
        ]);


    }


    public function createPublic(Request $request){
        $user = JWTAuth::parseToken()->authenticate();

        if (!$user->admin) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        Tags::create([
            'name'=> $request->name,
            'public' => true
        ]);

        return response()->json(['ok'=> true, 'message' => 'Tag public créé avec  succès!'],200);
    }
}

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TagsController;

Route::prefix('auth')->controller(AuthController::class)->group(function () {
    Route::post('register', 'register');
    Route::post('login', 'login');

    Route::middleware('auth:api')->group(function () {
        Route::get('me', 'me');
        Route::post('refresh', 'refresh');
        Route::post('logout', 'logout');
    });
});

Route::prefix('tags')->controller(TagsController::class)->group(function () {
    Route::get('public', 'public');
    
    Route::middleware('auth:api')->group(function () {
        Route::get('list', 'listUserTags');
        Route::post('create', 'create');
        Route::patch('edit', 'edit');
        Route::delete('delete', 'delete');

        Route::post('createpublic', 'createPublic');
        Route::patch('editPublic', 'editPublic');
        Route::delete('deletepublic', 'deletePublic');
    });
});
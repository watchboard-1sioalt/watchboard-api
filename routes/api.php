<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RessourceController;
use App\Http\Controllers\TagsController;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:api')->prefix('ressources')->controller(RessourceController::class)->group(function () {
    Route::get('/', 'index');
    Route::post('/create', 'store');
    Route::post('/from-rss', 'storeFromRss');
    Route::get('/{id}', 'show');
    Route::post('/{id}/update', 'update');
    Route::post('/{id}/delete', 'destroy');
    Route::post('/{id}/tags', 'attachTag');
    Route::delete('/{id}/tags/{tagId}', 'detachTag');
});

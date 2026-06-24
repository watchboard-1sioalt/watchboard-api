<?php

<<<<<<< Updated upstream
=======
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RessourceController;
>>>>>>> Stashed changes
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

<<<<<<< Updated upstream
Route::prefix('tags')->controller(TagsController::class)->group(function () {
    Route::get('public', 'public');
    
    Route::middleware('auth:api')->group(function () {
        Route::get('list', 'listUserTags');
        Route::patch('edit', 'edit');
        Route::delete('delete', 'delete');

        Route::post('createpublic', 'createPublic');
        Route::patch('editPublic', 'editPublic');
        Route::delete('deletepublic', 'deletePublic');
    });
=======
Route::middleware('auth:api')->prefix('ressources')->controller(RessourceController::class)->group(function () {
    Route::get('/', 'index');
    Route::get('/creer', 'create');
    Route::post('/creer', 'store');
    Route::post('/depuis-rss', 'storeFromRss');
    Route::get('/{id}', 'show');
    Route::get('/{id}/modifier', 'edit');
    Route::post('/{id}/modifier', 'update');
    Route::post('/{id}/supprimer', 'destroy');
>>>>>>> Stashed changes
});
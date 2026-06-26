<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\RessourceController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TagsController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->controller(AuthController::class)->group(function () {
    Route::post('register', 'register');
    Route::post('login', 'login');

    Route::middleware('auth:api')->group(function () {
        Route::get('me', 'me');
        Route::put('me', 'updateMe');
        Route::put('password', 'updatePassword');
        Route::post('refresh', 'refresh');
        Route::post('logout', 'logout');
    });
});

Route::prefix('tags')->controller(TagsController::class)->group(function () {
    Route::get('public', 'public');

    Route::middleware('auth:api')->group(function () {
        Route::get('autocomplete', 'autocomplete');
        Route::get('list', 'listUserTags');
        Route::post('create', 'create');
        Route::patch('edit', 'edit');
        Route::delete('delete', 'delete');

        Route::post('createpublic', 'createPublic');
        Route::patch('editPublic', 'editPublic');
        Route::delete('deletepublic', 'deletePublic');
    });
});

Route::middleware('auth:api')->prefix('feeds')->controller(FeedController::class)->group(function () {
    Route::get('/', 'index');
    Route::post('/', 'store');
    Route::get('/articles', 'allArticles');
    Route::get('/{id}', 'show');
    Route::put('/{id}', 'update');
    Route::delete('/{id}', 'destroy');
    Route::get('/{id}/articles', 'articles');
    Route::post('/{id}/tags', 'attachTag');
    Route::delete('/{id}/tags/{tagId}', 'detachTag');
});

Route::middleware('auth:api')->prefix('admin')->controller(AdminController::class)->group(function () {
    Route::get('users', 'users');
    Route::get('users/{id}', 'user');
    Route::put('users/{id}/validate', 'validate');
    Route::put('users/{id}/disable', 'disable');
});

Route::middleware('auth:api')->prefix('ressources')->controller(RessourceController::class)->group(function () {
    Route::get('/', 'index');
    Route::post('/create', 'store');
    Route::post('/from-rss', 'storeFromRss');
    Route::post('/from-file', 'storeFromFile');
    Route::post('/from-youtube', 'storeFromYoutube');
    Route::get('/{id}', 'show');
    Route::post('/{id}/update', 'update');
    Route::post('/{id}/delete', 'destroy');
    Route::post('/{id}/tags', 'attachTag');
    Route::delete('/{id}/tags/{tagId}', 'detachTag');
    Route::post('/{id}/resume/generate', 'generateResume');
});

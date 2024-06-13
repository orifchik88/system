<?php

use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserTypeController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\ClientTypeController;
use App\Http\Controllers\Api\LoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::post('login', [LoginController::class, 'login']);


Route::group([
    'middleware' => 'auth:api'
], function() {
    Route::get('profile', [ProfileController::class, 'profile']);
    Route::post('logout', [LoginController::class, 'logout']);
    Route::get('/user-type', [UserTypeController::class, 'index']);
    Route::post('/user-type/create', [UserTypeController::class, 'create']);
    Route::post('/user-type/edit/{id}', [UserTypeController::class, 'edit']);

    Route::get('/client-type', [ClientTypeController::class, 'index']);
    Route::post('/client-type/create', [ClientTypeController::class, 'create']);
    Route::post('/client-type/edit/{id}', [ClientTypeController::class, 'edit']);

    Route::get('/regions', [RegionController::class, 'regions']);
    Route::get('/districts/{id}', [RegionController::class, 'districts']);

    Route::get('/permissions', [PermissionController::class, 'permissions']);
    Route::get('/roles', [PermissionController::class, 'roles']);
    Route::post('/roles/create', [PermissionController::class, 'create']);

    Route::get('/users', [UserController::class, 'users']);
    Route::post('/users/create', [UserController::class, 'create']);
    Route::get('/users/status', [UserController::class, 'status']);

    Route::get('/registers', [\App\Http\Controllers\Api\RegisterController::class, 'registers']);
});







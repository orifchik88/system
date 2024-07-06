<?php

use App\Http\Controllers\Api\BlockController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserTypeController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\ClientTypeController;
use App\Http\Controllers\Api\LoginController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\ObjectController;
use App\Http\Controllers\Api\QuestionController;


Route::post('login', [LoginController::class, 'login']);


Route::group([
    'middleware' => 'auth:api'
], function() {
    Route::get('profile', [ProfileController::class, 'profile']);
    Route::post('logout', [LoginController::class, 'logout']);
    Route::post('refresh', [LoginController::class, 'refresh']);
    Route::get('user-type', [UserTypeController::class, 'index']);
    Route::post('user-type/create', [UserTypeController::class, 'create']);
    Route::post('user-type/edit/{id}', [UserTypeController::class, 'edit']);

    Route::get('client-type', [ClientTypeController::class, 'index']);
    Route::post('client-type/create', [ClientTypeController::class, 'create']);
    Route::post('client-type/edit/{id}', [ClientTypeController::class, 'edit']);

    Route::get('regions', [RegionController::class, 'regions']);
    Route::get('districts/{id}', [RegionController::class, 'districts']);

    Route::get('permissions', [PermissionController::class, 'permissions']);
    Route::get('roles', [PermissionController::class, 'roles']);
    Route::post('roles/create', [PermissionController::class, 'create']);

    Route::get('users', [UserController::class, 'users']);
    Route::post('users/create', [UserController::class, 'create']);
    Route::get('users/status', [UserController::class, 'status']);

    Route::get('registers', [RegisterController::class, 'registers']);
    Route::get('register-status', [RegisterController::class, 'status']);
    Route::get('register-get-pdf', [RegisterController::class, 'getPDF']);

    Route::post('send-inspector', [RegisterController::class, 'sendInspector']);
    Route::post('send-register', [RegisterController::class, 'sendRegister']);
    Route::post('reject-register', [RegisterController::class, 'rejectRegister']);

    Route::get('object-types', [ObjectController::class, 'objectTypes']);
    Route::get('funding-sources', [ObjectController::class, 'fundingSource']);
    Route::get('object-sectors/{id}', [ObjectController::class, 'objectSectors']);
    Route::post('object-create', [ObjectController::class, 'create']);

    Route::get('blocks/{id}', [BlockController::class, 'index']);
    Route::post('block-create', [BlockController::class, 'create']);

    Route::get('question-users', [QuestionController::class, 'questionUsers']);
});







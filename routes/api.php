<?php

use App\Http\Controllers\Api\BlockController;
use App\Http\Controllers\Api\EGovController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\RegulationController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserTypeController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\ClientTypeController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\ViolationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\ObjectController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\MonitoringController;


Route::post('login', [LoginController::class, 'login']);

Route::post('test-api', [EGovController::class, 'getPassportInfo']);

Route::group([
    'middleware' => 'auth:api'
], function() {
    Route::get('profile', [ProfileController::class, 'profile']);
    Route::post('profile-edit', [ProfileController::class, 'edit']);
    Route::post('logout', [LoginController::class, 'logout']);
    Route::post('refresh', [LoginController::class, 'refresh']);
    Route::get('user-type', [UserTypeController::class, 'index']);
    Route::post('user-type/create', [UserTypeController::class, 'create']);
    Route::post('user-type/edit/{id}', [UserTypeController::class, 'edit']);
    Route::get('users-count', [UserTypeController::class, 'count']);

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
    Route::get('users/edit/{id}', [UserController::class, 'edit']);
    Route::post('passport-info', [UserController::class, 'getPassportInfo']);

    Route::get('registers', [RegisterController::class, 'registers']);
    Route::get('register/{id}', [RegisterController::class, 'getRegister']);
    Route::get('register-status', [RegisterController::class, 'status']);
    Route::get('register-get-pdf', [RegisterController::class, 'getPDF']);

    Route::post('send-inspector', [RegisterController::class, 'sendInspector']);
    Route::post('send-register', [RegisterController::class, 'sendRegister']);
    Route::post('reject-register', [RegisterController::class, 'rejectRegister']);

    Route::get('objects', [ObjectController::class, 'index']);
    Route::get('object/{id}', [ObjectController::class, 'getObject']);
    Route::get('object-status', [ObjectController::class, 'status']);
    Route::get('object-types', [ObjectController::class, 'objectTypes']);
    Route::get('object-count', [ObjectController::class, 'objectCount']);
    Route::get('funding-sources', [ObjectController::class, 'fundingSource']);
    Route::get('object-sectors/{id}', [ObjectController::class, 'objectSectors']);
    Route::post('object-create', [ObjectController::class, 'create']);
    Route::post('check-object', [ObjectController::class, 'checkObject']);
    Route::post('change-object-status', [ObjectController::class, 'changeObjectStatus']);

    Route::get('blocks/{id}', [BlockController::class, 'index']);
    Route::post('block-create', [BlockController::class, 'create']);
    Route::post('block-delete', [BlockController::class, 'delete']);
    Route::post('block-edit', [BlockController::class, 'edit']);

    Route::get('question-users', [QuestionController::class, 'questionUsers']);
    Route::post('send-question', [QuestionController::class, 'sendQuestion']);
    Route::post('send-answer', [QuestionController::class, 'sendAnswer']);
    Route::get('levels', [QuestionController::class, 'levels']);

    Route::get('monitoring', [MonitoringController::class, 'monitoring']);
    Route::post('monitoring-create', [MonitoringController::class, 'create']);
    Route::get('regulations', [RegulationController::class, 'regulations']);
    Route::get('regulation-owner', [RegulationController::class, 'regulationOwner']);
    Route::post('accept-answer', [RegulationController::class, 'acceptAnswer']);
    Route::post('accept-deed', [RegulationController::class, 'acceptDeed']);
    Route::post('accept-date', [RegulationController::class, 'acceptDate']);
    Route::post('send-deed', [RegulationController::class, 'sendDeed']);
    Route::post('ask-date', [RegulationController::class, 'askDate']);
    Route::post('reject-date', [RegulationController::class, 'rejectDate']);
    Route::post('reject-deed', [RegulationController::class, 'rejectDeed']);
    Route::post('reject-answer', [RegulationController::class, 'rejectAnswer']);

    Route::get('act-violations', [ViolationController::class, 'actVioleations']);
    Route::get('violations', [ViolationController::class, 'violations']);

    Route::post('test', [RegulationController::class, 'test']);

});







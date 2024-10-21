<?php

use App\Http\Controllers\Api\BlockController;
use App\Http\Controllers\Api\ChecklistAnswerController;
use App\Http\Controllers\Api\ClaimController;
use App\Http\Controllers\Api\PdfController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\RegulationController;
use App\Http\Controllers\Api\ResponseController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserTypeController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\ViolationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\ObjectController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\MonitoringController;
use App\Http\Controllers\Api\InformationController;
use App\Http\Controllers\Api\SphereController;
use App\Http\Controllers\Api\ProgramController;


Route::post('login', [LoginController::class, 'login']);
Route::post('auth', [LoginController::class, 'auth']);
Route::post('check-user', [InformationController::class, 'checkUser']);


Route::get('{module}/{api}/responses', [ResponseController::class, 'receive']);

Route::get('pdf-generation', [PdfController::class, 'generation']);
Route::get('organization-pdf/{id}', [PdfController::class, 'pdfOrganization']);
Route::get('organization-pdf/{id}/download', [PdfController::class, 'pdfOrganizationDownload']);



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

    Route::get('monitoring-objects', [InformationController::class, 'monitoringObjects']);
    Route::get('monitoring-gnk', [InformationController::class, 'monitoringGNK']);
    Route::get('monitoring-customer', [InformationController::class, 'monitoringCustomer']);
    Route::get('reestr', [InformationController::class, 'reestr']);
    Route::get('rating', [InformationController::class, 'rating']);
    Route::get('conference', [InformationController::class, 'conference']);
    Route::get('expertise-files', [InformationController::class, 'expertiseFiles']);
    Route::get('tender', [InformationController::class, 'tender']);
    Route::get('time', [InformationController::class, 'time']);
    Route::get('get-pdf-repo', [InformationController::class, 'getRepoPDF']);
    Route::get('normative-documents', [InformationController::class, 'normativeDocs']);
    Route::get('topics', [InformationController::class, 'topics']);
    Route::get('basis', [InformationController::class, 'basis']);


    Route::get('sphere', [SphereController::class, 'spheres']);
    Route::get('programs', [ProgramController::class, 'programs']);


    Route::get('regions', [RegionController::class, 'regions']);
    Route::get('districts/{id}', [RegionController::class, 'districts']);

    Route::get('permissions', [PermissionController::class, 'permissions']);
    Route::get('roles', [PermissionController::class, 'roles']);
    Route::post('roles/create', [PermissionController::class, 'create']);

    Route::get('users', [UserController::class, 'users']);
    Route::get('inspectors', [UserController::class, 'getInspector']);
    Route::post('users/create', [UserController::class, 'create']);
    Route::get('users/status', [UserController::class, 'status']);
    Route::get('users/edit/{id}', [UserController::class, 'edit']);
    Route::post('passport-info', [UserController::class, 'getPassportInfo']);
    Route::get('employees', [UserController::class, 'getEmployees']);

    Route::get('registers', [RegisterController::class, 'registers']);
    Route::get('re-registers', [RegisterController::class, 'reRegister']);
    Route::get('register-count', [RegisterController::class, 'registerCount']);
    Route::get('re-register-count', [RegisterController::class, 'reRegisterCount']);
    Route::get('register/{id}', [RegisterController::class, 'getRegister']);
    Route::get('register-status', [RegisterController::class, 'status']);
    Route::get('register-get-pdf', [RegisterController::class, 'getPDF']);
    Route::get('total-count', [RegisterController::class, 'totalCount']);

    Route::post('send-inspector', [RegisterController::class, 'sendInspector']);
    Route::post('send-register', [RegisterController::class, 'sendRegister']);
    Route::post('reject-register', [RegisterController::class, 'rejectRegister']);

    Route::get('objects', [ObjectController::class, 'index']);
    Route::get('object/{id}', [ObjectController::class, 'getObject']);
    Route::get('object-count', [ObjectController::class, 'objectCount']);
    Route::get('get-object', [ObjectController::class, 'objectByParams']);
    Route::get('user-objects', [ObjectController::class, 'userObjects']);
    Route::get('funding-sources', [ObjectController::class, 'fundingSource']);
    Route::post('object-create', [ObjectController::class, 'create']);
    Route::post('check-object', [ObjectController::class, 'checkObject']);
    Route::post('change-object-status', [ObjectController::class, 'changeObjectStatus']);
    Route::post('payment', [ObjectController::class, 'payment']);
    Route::get('total-payment', [ObjectController::class, 'totalPayment']);
    Route::get('payment-statistics', [ObjectController::class, 'paymentStatistics']);
    Route::get('accountant-objects', [ObjectController::class, 'accountObjects']);
    Route::get('rotation', [ObjectController::class, 'rotation']);

    Route::get('blocks/{id}', [BlockController::class, 'index']);
    Route::get('block/{id}', [BlockController::class, 'getBlock']);
    Route::get('response-blocks/{id}', [BlockController::class, 'responseBlock']);
    Route::get('block-modes', [BlockController::class, 'blockModes']);
    Route::get('block-types', [BlockController::class, 'blockTypes']);
    Route::post('block-create', [BlockController::class, 'create']);
    Route::post('block-delete', [BlockController::class, 'delete']);
    Route::post('block-edit', [BlockController::class, 'edit']);

    Route::post('send-answer', [QuestionController::class, 'sendAnswer']);
    Route::get('levels', [QuestionController::class, 'levels']);
    Route::post('create-regulation', [QuestionController::class, 'createRegulation']);

    Route::get('monitoring', [MonitoringController::class, 'monitoring']);
    Route::post('monitoring-create', [MonitoringController::class, 'create']);
    Route::get('regulations', [RegulationController::class, 'regulations']);
    Route::get('regulation/{id}', [RegulationController::class, 'getRegulation']);
    Route::post('accept-answer', [RegulationController::class, 'acceptAnswer']);
    Route::post('accept-deed', [RegulationController::class, 'acceptDeed']);
    Route::post('accept-deed-cmr', [RegulationController::class, 'acceptDeedCmr']);
    Route::post('accept-date', [RegulationController::class, 'acceptDate']);
    Route::post('send-deed', [RegulationController::class, 'sendDeed']);
    Route::post('ask-date', [RegulationController::class, 'askDate']);
    Route::post('reject-date', [RegulationController::class, 'rejectDate']);
    Route::post('reject-deed', [RegulationController::class, 'rejectDeed']);
    Route::post('reject-deed-cmr', [RegulationController::class, 'rejectDeedCmr']);
    Route::post('reject-answer', [RegulationController::class, 'rejectAnswer']);
    Route::post('create-fine', [RegulationController::class, 'fine']);
    Route::post('send-court', [RegulationController::class, 'sendCourt']);
    Route::get('regulation-count', [RegulationController::class, 'regulationCount']);
    Route::get('author-regulation', [RegulationController::class, 'getAuthorRegulations']);
    Route::post('send-answer-author-regulation', [RegulationController::class, 'sendAnswerAuthorRegulation']);

    Route::get('act-violations', [ViolationController::class, 'actViolations']);
    Route::get('violations', [ViolationController::class, 'violations']);

    Route::get('checklist', [MonitoringController::class, 'getChecklist']);
    Route::get('checklist-answer', [MonitoringController::class, 'getChecklistAnswer']);
    Route::get('checklist-regular', [MonitoringController::class, 'getChecklistRegular']);
    Route::post('checklist-file-send', [MonitoringController::class, 'sendCheckListFile']);
    Route::post('accept-work-type', [MonitoringController::class, 'sendCheckListFile']);

    Route::get('check-list-answer', [CheckListAnswerController::class, 'index']);


    Route::post('test', [RegulationController::class, 'test']);

    Route::group(['prefix' => 'claim'], function () {
        Route::get('tasks', [ClaimController::class, 'tasksList']);
        Route::get('statistics', [ClaimController::class, 'statisticsQuantity']);
        Route::get('organization-statistics', [ClaimController::class, 'organizationStatisticsQuantity']);
        Route::get('task/{id}', [ClaimController::class, 'showTask']);
        Route::get('objects/{id}', [ClaimController::class, 'getObjects']);
        Route::get('get-pdf', [ClaimController::class, 'getPDF']);
        Route::get('get-conclusion-pdf', [ClaimController::class, 'getConclusionPDF']);
        Route::get('task-histories/{id}', [ClaimController::class, "tasksHistories"]);
        Route::post('send-to-minstroy', [ClaimController::class, "sendToMinstroy"]);
        Route::post('accept-task', [ClaimController::class, "acceptTask"]);
        Route::post('attach-object', [ClaimController::class, "attachObject"]);
        Route::post('attach-blocks', [ClaimController::class, "attachBlockAndOrganization"]);
        Route::post('conclusion-organization', [ClaimController::class, "conclusionOrganization"]);
        Route::post('conclusion-by-inspector', [ClaimController::class, "conclusionClaimByInspector"]);
        Route::post('conclusion-by-director', [ClaimController::class, "conclusionClaimByDirector"]);
        Route::post('reject-from-director', [ClaimController::class, "rejectFromDirector"]);
        Route::post('send-to-director', [ClaimController::class, "sendToDirector"]);
    });

});







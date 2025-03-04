<?php

use App\Http\Controllers\API\V1\AttributeController;
use App\Http\Controllers\API\V1\AuthController;
use App\Http\Controllers\API\V1\ProjectController;
use App\Http\Controllers\API\V1\UserController;
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

Route::group(['prefix' => 'v1'], function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::group(['prefix' => 'v1', 'middleware' => ['verifytoken']], function () {
    //User API
    Route::put('/update-profile', [UserController::class, 'updateProfile']); //Only allow to update our own profile
    Route::get('/user/{id}', [UserController::class, 'getDetailUser']);
    Route::get('/users', [UserController::class, 'getAllUsers']);
    Route::delete('/delete-account', [AuthController::class, 'deleteAccount']); //Only allow to delete our own account

    //Project API
    Route::get('/projects', [ProjectController::class, 'getProjects']);
    Route::get('/project/{id}', [ProjectController::class, 'getDetailProject']);
    Route::post('/create-project', [ProjectController::class, 'createProject']);
    Route::put('/update-project/{id}', [ProjectController::class, 'updateProject']);
    Route::delete('/delete-project/{id}', [ProjectController::class, 'deleteProject']);

    Route::post('/project/{id}/assign-users', [ProjectController::class, 'assignUsers']);
    Route::delete('/projects/{projectId}/users/{userId}/unassign', [ProjectController::class, 'unassignUser']);
    //Timesheet API
    Route::post('/timesheets/log', [ProjectController::class, 'logTimesheet']);
    Route::get('/timesheets', [ProjectController::class, 'getAllTimesheets']);
    Route::put('/update-timesheet/{timesheet_id}', [ProjectController::class, 'updateTimesheet']);
    Route::delete('/delete-timesheet/{timesheet_id}', [ProjectController::class, 'deleteTimesheet']);
    //Attributes API
    Route::get('/attributes', [AttributeController::class, 'getAttributes']);
    Route::get('/attribute/{id}', [AttributeController::class, 'getDetailAttribute']);
    Route::post('/create-attribute', [AttributeController::class, 'createAttribute']);
    Route::put('/update-attribute/{id}', [AttributeController::class, 'updateAttribute']);
    Route::delete('/delete-attribute/{id}', [AttributeController::class, 'deleteAttribute']);

    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

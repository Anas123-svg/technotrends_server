<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\HeadController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectAssignedToUserController;
use App\Http\Controllers\ComplaintAssignedToUserController;
Route::post('/login', [AuthController::class, 'login']); // Create admin
Route::post('/register', [AuthController::class, 'register']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/get/by-token', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
Route::get('/dashboard/admin', [AdminController::class, 'dashboard']); 
Route::get('/admin', [AdminController::class, 'index']); // Get all admins

Route::post('/admin', [AdminController::class, 'store']); // Create admin
Route::get('/admin/{id}', [AdminController::class, 'show']); // Get admin by ID
Route::put('/admin/{id}', [AdminController::class, 'update']); // Update admin
Route::delete('/admin/{id}', [AdminController::class, 'destroy']); // Delete admin
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/admin-by-token', [AdminController::class, 'getAdminByToken']); // Get admin by token
});


Route::prefix('head')->group(function () {
    Route::get('/', [HeadController::class, 'index']); // Get all heads
    Route::post('/', [HeadController::class, 'store']); // Create head
    Route::get('/{id}', [HeadController::class, 'show']); // Get head by ID
    Route::put('/{id}', [HeadController::class, 'update']); // Update head
    Route::delete('/{id}', [HeadController::class, 'destroy']); // Delete head
});

Route::prefix('user')->group(function () {
    Route::get('/', [UserController::class, 'index']); // Get all users
    Route::post('/', [UserController::class, 'store']); // Create user
    Route::get('/{id}', [UserController::class, 'show']); // Get user by ID
    Route::put('/{id}', [UserController::class, 'update']); // Update user
    Route::delete('/{id}', [UserController::class, 'destroy']); // Delete user
});

Route::put('/complaint-assigned-users/update/{id}', [ComplaintAssignedToUserController::class, 'updateComplaintAssignedToUser']); //update assigned users to complaint
Route::put('/project-assigned-users/update/{id}', [ProjectAssignedToUserController::class, 'updateProjectAssignedToUser']);//update assigned users to project
Route::put('update/assigned-user/complaint/{id}', [ComplaintAssignedToUserController::class, 'updateAssignedUser']);
Route::put('update/assigned-user/project/{id}', [ProjectAssignedToUserController::class, 'updateAssignedUser']);

Route::post('projects/{id}/assign-head', [ProjectController::class, 'assignToHead']);
Route::post('projects/{id}/assign-workers', [ProjectController::class, 'assignToWorkers']);

Route::get('/projects', [ProjectController::class, 'index']); // List all projects
Route::post('/projects', [ProjectController::class, 'store']); // Create a new project
Route::get('/projects/{id}', [ProjectController::class, 'show']); // Get a specific project
Route::put('/projects/{id}', [ProjectController::class, 'update']); // Update a project
Route::delete('/projects/{id}', [ProjectController::class, 'destroy']); // Delete a project
Route::get('assigned/projects/user/{id}', [ProjectAssignedToUserController::class, 'getProjectsAssignedToUser']); // get user assigned projects
Route::get('assigned/complaints/user/{id}', [ComplaintAssignedToUserController::class, 'getComplaintsAssignedToUser']); // get user assigned complaints



Route::post('complaints/{id}/assign-head', [ComplaintController::class, 'assignToHead']);
Route::post('complaints/{id}/assign-workers', [ComplaintController::class, 'assignToWorkers']);


Route::get('complaints', [ComplaintController::class, 'index']); // List all complaints
Route::post('complaints', [ComplaintController::class, 'store']); // Create a new complaint
Route::get('complaints/{id}', [ComplaintController::class, 'show']); // Show a single complaint by ID
Route::put('complaints/{id}', [ComplaintController::class, 'update']); // Update a specific complaint
Route::delete('complaints/{id}', [ComplaintController::class, 'destroy']); // Delete a specific complaint

Route::get('/invoices', [InvoiceController::class, 'index']);
Route::post('/invoices', [InvoiceController::class, 'store']);
Route::get('/invoices/{id}', [InvoiceController::class, 'show']);
Route::put('/invoices/{id}', [InvoiceController::class, 'update']);
Route::delete('/invoices/{id}', [InvoiceController::class, 'destroy']);

Route::get('pending/requests', [AdminController::class, 'getPendingHeadAdminsAndUsers']);


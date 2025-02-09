<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\{
    AuthController,
    TaskController,
    CommentController,
    TaskAttachmentController,
    UserController
};

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

Route::prefix('v1')->group(function () {
    // Auth routes
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);
    
    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        // User routes
        Route::get('user/profile', [UserController::class, 'profile']);
        Route::put('user/profile', [UserController::class, 'update']);
        Route::get('users', [UserController::class, 'index']);
        
        // Task routes
        Route::apiResource('tasks', TaskController::class);
        Route::put('tasks/{task}/status', [TaskController::class, 'updateStatus']);
        
        // Task comments
        Route::get('tasks/{task}/comments', [CommentController::class, 'index']);
        Route::post('tasks/{task}/comments', [CommentController::class, 'store']);
        Route::put('tasks/comments/{comment}', [CommentController::class, 'update']);
        Route::delete('tasks/comments/{comment}', [CommentController::class, 'destroy']);
        
        // Task attachments
        Route::get('tasks/{task}/attachments', [TaskAttachmentController::class, 'index']);
        Route::post('tasks/{task}/attachments', [TaskAttachmentController::class, 'store']);
        Route::get('tasks/attachments/{attachment}/download', [TaskAttachmentController::class, 'download']);
        Route::delete('tasks/attachments/{attachment}', [TaskAttachmentController::class, 'destroy']);
        
        // Logout
        Route::post('auth/logout', [AuthController::class, 'logout']);
    });
});

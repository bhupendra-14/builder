<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:6,1');
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\AssetController;
use App\Http\Controllers\Api\SectionController;
use App\Http\Controllers\Api\PublishController;
use App\Http\Controllers\Api\PublicContentController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\AuditLogController;

// Public Content Routes
Route::get('/public/live', [PublicContentController::class, 'live']);
Route::get('/public/preview', [PublicContentController::class, 'preview']);
Route::get('/public/settings', [PublicContentController::class, 'settings']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    Route::get('/dashboard', [DashboardController::class, 'index']);
    
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar']);
    
    Route::get('/assets/folders', [AssetController::class, 'folders']);
    Route::apiResource('assets', AssetController::class)->except(['show']);
    Route::apiResource('users', UserController::class)->except(['show']);
    
    Route::get('/settings', [SettingController::class, 'index']);
    Route::post('/settings', [SettingController::class, 'update']);
    
    Route::get('/audit', [AuditLogController::class, 'index']);
    
    Route::post('/sections/reorder', [SectionController::class, 'reorder']);
    Route::post('/sections/{id}/duplicate', [SectionController::class, 'duplicate'])
        ->where('id', '[0-9]+');
    Route::get('/sections/{id}/history', [SectionController::class, 'history'])
        ->where('id', '[0-9]+');
    Route::post('/sections/{id}/history/{versionId}/rollback', [SectionController::class, 'rollback'])
        ->where(['id' => '[0-9]+', 'versionId' => '[0-9]+']);
    Route::apiResource('sections', SectionController::class)
        ->where(['section' => '[0-9]+']);
    
    Route::post('/publish', [PublishController::class, 'publish']);
    Route::get('/publish/history', [PublishController::class, 'history']);
});

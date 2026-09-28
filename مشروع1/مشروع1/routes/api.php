<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CarController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\RatingController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\AiController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\AuctionController;
use Illuminate\Support\Facades\Route;

// ==========================================
// مسارات عامة (بدون تسجيل دخول)
// ==========================================

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/admin/login', [AuthController::class, 'adminLogin']);
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink']);
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);
Route::get('/cars', [CarController::class, 'index']);
Route::get('/cars/{id}', [CarController::class, 'show']);
Route::get('/cars/{carId}/ratings', [RatingController::class, 'index']);
Route::post('/ai/chat', [AiController::class, 'chat']);

// عرض المزادات النشطة
Route::get('/auctions', [AuctionController::class, 'index']);

// ==========================================
// مسارات محمية (تتطلب تسجيل دخول)
// ==========================================

Route::middleware('auth:sanctum')->group(function () {
    
    // المصادقة والبروفايل
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::delete('/account', [AuthController::class, 'deleteAccount']);
    
    // السيارات
    Route::get('/my-cars', [CarController::class, 'myCars']);
    Route::post('/cars', [CarController::class, 'store']);
    Route::put('/cars/{id}', [CarController::class, 'update']);
    Route::delete('/cars/{id}', [CarController::class, 'destroy']);
    Route::put('/cars/{id}/sold', [CarController::class, 'markAsSold']);
    Route::put('/cars/{id}/available', [CarController::class, 'markAsAvailable']);
    
    // المفضلة
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites/{carId}', [FavoriteController::class, 'store']);
    Route::delete('/favorites/{carId}', [FavoriteController::class, 'destroy']);
    Route::get('/favorites/check/{carId}', [FavoriteController::class, 'check']);
    
    // التقييم
    Route::post('/cars/{carId}/ratings', [RatingController::class, 'store']);
    Route::delete('/cars/{carId}/ratings', [RatingController::class, 'destroy']);
    Route::get('/cars/{carId}/my-rating', [RatingController::class, 'show']);
    
    // الإشعارات
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
    Route::delete('/notifications', [NotificationController::class, 'destroyAll']);
    
    // الدردشة (وحدة بين شخصين)
    Route::post('/conversations/start/{userId}', [ChatController::class, 'startOrGet']);
    Route::get('/conversations', [ChatController::class, 'index']);
    Route::get('/conversations/{id}', [ChatController::class, 'show']);
    Route::post('/conversations/{id}/messages', [ChatController::class, 'sendMessage']);
    Route::post('/chat-test', [ChatController::class, 'test']);

    // المزادات
    Route::post('/auctions', [AuctionController::class, 'store']);
    Route::post('/auctions/{auctionId}/bid', [AuctionController::class, 'bid']);
    Route::get('/auctions/{auctionId}/bids', [AuctionController::class, 'bids']);
    Route::post('/auctions/{auctionId}/finish', [AuctionController::class, 'finishExpired']);
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/users', [AdminController::class, 'users']);
    Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);
    Route::get('/cars', [AdminController::class, 'cars']);
    Route::post('/cars', [AdminController::class, 'storeCar']);
    Route::delete('/cars/{id}', [AdminController::class, 'deleteCar']);
    Route::put('/cars/{id}/sold', [AdminController::class, 'markCarAsSold']);
    Route::put('/cars/{id}/available', [AdminController::class, 'markCarAsAvailable']);
    Route::get('/stats', [AdminController::class, 'stats']);
    
    // التقييمات
    Route::get('/ratings/pending', [AdminController::class, 'pendingRatings']);
    Route::put('/ratings/{id}/approve', [AdminController::class, 'approveRating']);
    Route::put('/ratings/{id}/reject', [AdminController::class, 'rejectRating']);
});
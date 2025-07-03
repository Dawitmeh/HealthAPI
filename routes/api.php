<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\StaffController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\SubscriptionPlanController;
use App\Http\Controllers\Api\TagController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/adminlogin', [AuthController::class, 'adminLogin']);



Route::middleware('auth:sanctum')->group(function () {
    // Main routes

    Route::resource('/category', CategoryController::class);
    Route::resource('/contents', ContentController::class);
    Route::resource('/tags', TagController::class);
    Route::resource('/subscriptionplan', SubscriptionPlanController::class);
    Route::resource('/subscriptions', SubscriptionController::class);
    Route::resource('/payments', PaymentController::class);
    Route::resource('/customers', CustomerController::class);
    Route::resource('/staffs', StaffController::class);
});


<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\NotificationController;
use App\Http\Middleware\CheckLoginStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication & User Routes
|--------------------------------------------------------------------------
*/

// Public Routes (No Auth Required)
Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login'])->middleware(CheckLoginStatus::class);

// Protected Routes (Require Login)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('logout', [UserController::class, 'logout']);

    // ✅ FIX: تم نقله هنا ليعرف السيرفر من هو المستخدم الحالي
    Route::post('user/fcm-token', [UserController::class, 'updateFcmToken']);
});

/*
|--------------------------------------------------------------------------
| Apartment Routes
|--------------------------------------------------------------------------
*/

// 1. Specific Public Routes (Must come BEFORE {id})
Route::get('apartment', [ApartmentController::class, 'index']); // Search/List
Route::get('apartments/filter', [ApartmentController::class, 'filter']);

// 2. Specific Owner Routes (Must come BEFORE {id})
Route::middleware(['auth:sanctum', 'isOwner'])->group(function () {
    Route::post('apartment', [ApartmentController::class, 'store']);

    // [CRITICAL] This MUST be defined before apartment/{id}
    Route::get('apartment/my', [ApartmentController::class, 'myApartments']);

    Route::post('apartment/{id}', [ApartmentController::class, 'update']);
    Route::delete('apartment/{id}', [ApartmentController::class, 'destroy']);
    Route::put('apartment/{id}/activate', [ApartmentController::class, 'activate']);
    Route::delete('apartment/{id}/force', [ApartmentController::class, 'forceDelete']);
});

// 3. Dynamic/Wildcard Public Routes (Must be LAST)
Route::get('apartment/{id}', [ApartmentController::class, 'show']);
Route::get('apartments/{id}/reviews', [ReviewController::class, 'index'])->name('apartments.reviews.index');

Route::post('apartments/{id}/categories', [ApartmentController::class, 'addCatergoriesToTask']);
Route::get('apartments/{apartment_id}/categories', [ApartmentController::class, 'getApartmentCategory']);


/*
|--------------------------------------------------------------------------
| Booking Routes (Tenant)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'canbook'])->group(function () {
    Route::post('bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::put('bookings/{id}/checkout', [BookingController::class, 'checkout'])->name('bookings.checkout');
    Route::post('bookings/{id}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::post('bookings/{id}/request-update', [BookingController::class, 'requestUpdate'])->name('bookings.request_update');
    Route::get('bookings/my/all', [BookingController::class, 'myAllBookings'])->name('bookings.my.all');
    Route::post('bookings/{id}/review', [ReviewController::class, 'store'])->name('bookings.review.store');
});

/*
|--------------------------------------------------------------------------
| Booking Routes (Owner)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'isOwner'])->group(function () {
    Route::get('bookings/owner/requests', [BookingController::class, 'ownerRequests']);
    Route::get('bookings/owner/earnings', [BookingController::class, 'getEarnings']);
    Route::put('bookings/confirm/{id}', [BookingController::class, 'confirm'])->name('bookings.confirm');
    Route::put('/bookings/{id}/reject', [BookingController::class, 'reject']);
    Route::post('bookings/updates/{id}/approve', [BookingController::class, 'approveUpdate'])->name('bookings.update.approve');
    Route::post('bookings/updates/{id}/reject', [BookingController::class, 'rejectUpdate'])->name('bookings.update.reject');
});

/*
|--------------------------------------------------------------------------
| Admin Panel Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'isAdmin'])->prefix('admin')->group(function () {
    Route::get('stats', [AdminController::class, 'getStats']);
    Route::get('users', [AdminController::class, 'getUsers']);
    Route::put('users/{id}/approve', [AdminController::class, 'approveUser']);
    Route::put('users/{id}/reject', [AdminController::class, 'rejectUser']);
    Route::delete('users/{id}', [AdminController::class, 'deleteUser']);
});

/*
|--------------------------------------------------------------------------
| Notification Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::put('notifications/{id}/read', [NotificationController::class, 'markAsRead']);
});

<?php

declare(strict_types=1);

use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\DanceStudio\AttendanceController;
use App\Http\Controllers\DanceStudio\DashboardController;
use App\Http\Controllers\DanceStudio\PrivateBookingController;
use App\Http\Controllers\DanceStudio\StudentPackageController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect('/dashboard'));
Route::get('/dashboard', DashboardController::class);
Route::get('/availability', AvailabilityController::class);
Route::get('/attendance', [AttendanceController::class, 'index']);
Route::post('/attendance/private-bookings/{privateBooking}/mark', [AttendanceController::class, 'mark']);
Route::get('/private-bookings', [PrivateBookingController::class, 'index']);
Route::get('/private-bookings/create', [PrivateBookingController::class, 'create']);
Route::post('/private-bookings', [PrivateBookingController::class, 'store']);
Route::get('/student-packages', [StudentPackageController::class, 'index']);

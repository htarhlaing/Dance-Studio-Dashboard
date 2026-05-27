<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\DanceStudio\AttendanceController;
use App\Http\Controllers\DanceStudio\ClassTypeController;
use App\Http\Controllers\DanceStudio\DashboardController;
use App\Http\Controllers\DanceStudio\PackageTypeController;
use App\Http\Controllers\DanceStudio\PrivateBookingController;
use App\Http\Controllers\DanceStudio\RegularClassController;
use App\Http\Controllers\DanceStudio\RoomController;
use App\Http\Controllers\DanceStudio\StudentController;
use App\Http\Controllers\DanceStudio\StudentPackageController;
use App\Http\Controllers\DanceStudio\StudioSettingsController;
use App\Http\Controllers\DanceStudio\TeacherController;
use App\Http\Controllers\DanceStudio\WeeklyScheduleController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth');

Route::middleware(['auth'])->group(function () {
    Route::get('/', fn () => redirect('/dashboard'));

    Route::middleware(['role:admin,front_desk,teacher'])->group(function () {
        Route::get('/dashboard', DashboardController::class);
        Route::get('/availability', AvailabilityController::class);
        Route::get('/private-bookings', [PrivateBookingController::class, 'index']);
        Route::get('/private-bookings/create', [PrivateBookingController::class, 'create']);
        Route::post('/private-bookings', [PrivateBookingController::class, 'store']);
        Route::get('/private-bookings/{privateBooking}/reschedule', [PrivateBookingController::class, 'rescheduleForm']);
        Route::post('/private-bookings/{privateBooking}/reschedule', [PrivateBookingController::class, 'reschedule']);
        Route::post('/private-bookings/{privateBooking}/cancel', [PrivateBookingController::class, 'cancel']);
        Route::get('/attendance', [AttendanceController::class, 'index']);
        Route::post('/attendance/private-bookings/{privateBooking}/mark', [AttendanceController::class, 'mark']);
    });

    Route::middleware(['role:admin,front_desk'])->group(function () {
        Route::get('/weekly-schedule', WeeklyScheduleController::class);

        Route::get('/regular-classes', [RegularClassController::class, 'index']);
        Route::get('/regular-classes/create', [RegularClassController::class, 'create']);
        Route::post('/regular-classes', [RegularClassController::class, 'store']);
        Route::get('/regular-classes/{regularClass}/edit', [RegularClassController::class, 'edit']);
        Route::put('/regular-classes/{regularClass}', [RegularClassController::class, 'update']);
        Route::delete('/regular-classes/{regularClass}', [RegularClassController::class, 'destroy']);

        Route::get('/student-packages', [StudentPackageController::class, 'index']);
        Route::get('/student-packages/create', [StudentPackageController::class, 'create']);
        Route::post('/student-packages', [StudentPackageController::class, 'store']);

        Route::get('/students', [StudentController::class, 'index']);
        Route::get('/students/create', [StudentController::class, 'create']);
        Route::post('/students', [StudentController::class, 'store']);
        Route::get('/students/{student}/edit', [StudentController::class, 'edit']);
        Route::put('/students/{student}', [StudentController::class, 'update']);
        Route::delete('/students/{student}', [StudentController::class, 'destroy']);

        Route::get('/teachers', [TeacherController::class, 'index']);
        Route::get('/teachers/create', [TeacherController::class, 'create']);
        Route::post('/teachers', [TeacherController::class, 'store']);
        Route::get('/teachers/{teacher}/edit', [TeacherController::class, 'edit']);
        Route::put('/teachers/{teacher}', [TeacherController::class, 'update']);
        Route::delete('/teachers/{teacher}', [TeacherController::class, 'destroy']);

        Route::get('/rooms', [RoomController::class, 'index']);
        Route::get('/rooms/create', [RoomController::class, 'create']);
        Route::post('/rooms', [RoomController::class, 'store']);
        Route::get('/rooms/{room}/edit', [RoomController::class, 'edit']);
        Route::put('/rooms/{room}', [RoomController::class, 'update']);
        Route::delete('/rooms/{room}', [RoomController::class, 'destroy']);
    });

    Route::middleware(['role:admin'])->group(function () {
        Route::get('/studio-settings', [StudioSettingsController::class, 'index']);
        Route::get('/studio-settings/edit', [StudioSettingsController::class, 'edit']);
        Route::post('/studio-settings', [StudioSettingsController::class, 'update']);

        Route::get('/package-types', [PackageTypeController::class, 'index']);
        Route::get('/package-types/create', [PackageTypeController::class, 'create']);
        Route::post('/package-types', [PackageTypeController::class, 'store']);
        Route::get('/package-types/{packageType}/edit', [PackageTypeController::class, 'edit']);
        Route::put('/package-types/{packageType}', [PackageTypeController::class, 'update']);
        Route::delete('/package-types/{packageType}', [PackageTypeController::class, 'destroy']);

        Route::get('/class-types', [ClassTypeController::class, 'index']);
        Route::get('/class-types/create', [ClassTypeController::class, 'create']);
        Route::post('/class-types', [ClassTypeController::class, 'store']);
        Route::get('/class-types/{classType}/edit', [ClassTypeController::class, 'edit']);
        Route::put('/class-types/{classType}', [ClassTypeController::class, 'update']);
        Route::delete('/class-types/{classType}', [ClassTypeController::class, 'destroy']);
    });
});

<?php

namespace App\Http\Controllers\DanceStudio;

use App\Http\Controllers\Controller;
use App\Models\DanceStudio\PrivateBooking;
use App\Models\DanceStudio\StudentPackage;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $studioId = 1;
        $today = CarbonImmutable::today();

        $todayPrivateBookingsCount = PrivateBooking::query()
            ->where('studio_id', $studioId)
            ->whereDate('start_at', $today->toDateString())
            ->count();

        $pendingBookingsCount = PrivateBooking::query()
            ->where('studio_id', $studioId)
            ->where('status', 'pending')
            ->count();

        $completedTodayCount = PrivateBooking::query()
            ->where('studio_id', $studioId)
            ->where('status', 'completed')
            ->whereDate('start_at', $today->toDateString())
            ->count();

        $lowPackagesCount = StudentPackage::query()
            ->where('studio_id', $studioId)
            ->where('remaining_units', '<=', 3)
            ->count();

        $todayPrivateBookings = PrivateBooking::query()
            ->where('studio_id', $studioId)
            ->whereDate('start_at', $today->toDateString())
            ->with(['room', 'student', 'teacher'])
            ->orderBy('start_at')
            ->get();

        $pendingBookings = PrivateBooking::query()
            ->where('studio_id', $studioId)
            ->where('status', 'pending')
            ->with(['room', 'student', 'teacher'])
            ->orderBy('start_at')
            ->limit(20)
            ->get();

        $lowRemainingPackages = StudentPackage::query()
            ->where('studio_id', $studioId)
            ->where('remaining_units', '<=', 3)
            ->with(['student', 'packageType'])
            ->orderBy('remaining_units')
            ->limit(20)
            ->get();

        return view('dance-studio.dashboard', [
            'today' => $today,
            'todayPrivateBookingsCount' => $todayPrivateBookingsCount,
            'pendingBookingsCount' => $pendingBookingsCount,
            'completedTodayCount' => $completedTodayCount,
            'lowPackagesCount' => $lowPackagesCount,
            'todayPrivateBookings' => $todayPrivateBookings,
            'pendingBookings' => $pendingBookings,
            'lowRemainingPackages' => $lowRemainingPackages,
        ]);
    }
}

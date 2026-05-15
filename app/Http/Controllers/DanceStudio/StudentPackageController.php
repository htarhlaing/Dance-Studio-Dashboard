<?php

namespace App\Http\Controllers\DanceStudio;

use App\Http\Controllers\Controller;
use App\Models\DanceStudio\Student;
use App\Models\DanceStudio\StudentPackage;
use Illuminate\Http\Request;

class StudentPackageController extends Controller
{
    public function index(Request $request)
    {
        $studioId = 1;

        $studentId = $request->query('student_id');
        $studentId = is_numeric($studentId) ? (int) $studentId : null;

        $status = $request->query('status');
        $status = is_string($status) && $status !== '' ? $status : null;

        $query = StudentPackage::query()
            ->where('studio_id', $studioId)
            ->with(['student', 'packageType'])
            ->orderBy('purchased_at', 'desc')
            ->orderBy('id', 'desc');

        if ($studentId !== null) {
            $query->where('student_id', $studentId);
        }

        if ($status !== null) {
            $query->where('status', $status);
        }

        $studentPackages = $query->get();

        $students = Student::query()
            ->where('studio_id', $studioId)
            ->orderBy('id')
            ->get(['id', 'name']);

        $availableStatuses = StudentPackage::query()
            ->where('studio_id', $studioId)
            ->select('status')
            ->distinct()
            ->orderBy('status')
            ->pluck('status')
            ->filter()
            ->values();

        return view('dance-studio.student-packages.index', [
            'studentPackages' => $studentPackages,
            'students' => $students,
            'studentId' => $studentId,
            'status' => $status,
            'availableStatuses' => $availableStatuses,
        ]);
    }
}

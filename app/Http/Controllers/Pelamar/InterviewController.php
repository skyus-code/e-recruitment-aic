<?php

namespace App\Http\Controllers\Pelamar;

use App\Http\Controllers\Controller;
use App\Models\InterviewSchedule;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InterviewController extends Controller
{
    /**
     * Jadwal wawancara & microteaching milik pelamar yang bersangkutan.
     */
    public function index(Request $request): View
    {
        $schedules = InterviewSchedule::with(['application.job.department', 'application.user'])
            ->whereHas('application', fn ($query) => $query->where('user_id', $request->user()->id))
            ->orderByDesc('interview_date')
            ->get();

        return view('pelamar.interviews.index', compact('schedules'));
    }
}

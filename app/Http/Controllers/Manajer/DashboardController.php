<?php

namespace App\Http\Controllers\Manajer;

use App\Enums\ApplicationStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\InterviewSchedule;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Portal keputusan manajer: kandidat menunggu keputusan, jadwal terdekat, dan rekap.
     */
    public function index(Request $request): View
    {
        $awaiting = Application::with(['user.detailPerson', 'job.department', 'interviewSchedule'])
            ->where('status', ApplicationStatusEnum::Interview)
            ->orderBy('applied_at')
            ->get();

        $upcoming = InterviewSchedule::with(['application.job.department', 'application.user'])
            ->whereHas('application', fn ($query) => $query->where('status', ApplicationStatusEnum::Interview))
            ->whereDate('interview_date', '>=', today())
            ->orderBy('interview_date')
            ->get();

        $stats = [
            'menunggu' => $awaiting->count(),
            'hired' => Application::where('status', ApplicationStatusEnum::Hired)->count(),
            'rejected' => Application::where('status', ApplicationStatusEnum::Rejected)->count(),
            'total' => Application::count(),
        ];

        $recentDecisions = Application::with(['user', 'job.department'])
            ->whereIn('status', [ApplicationStatusEnum::Hired, ApplicationStatusEnum::Rejected])
            ->whereNotNull('score')
            ->orderByDesc('updated_at')
            ->take(6)
            ->get();

        return view('manajer.dashboard', compact('awaiting', 'upcoming', 'stats', 'recentDecisions'));
    }
}

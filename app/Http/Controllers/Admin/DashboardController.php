<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicationStatusEnum;
use App\Enums\JobStatusEnum;
use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Overview eksekutif: metrik pipeline, chart donut status, dan aktivitas terbaru.
     */
    public function index(): View
    {
        $statusCounts = Application::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $stats = [
            'total' => (int) $statusCounts->sum(),
            'applied' => (int) $statusCounts->get(ApplicationStatusEnum::Applied->value, 0),
            'screening' => (int) $statusCounts->get(ApplicationStatusEnum::Screening->value, 0),
            'passed' => (int) $statusCounts->get(ApplicationStatusEnum::Passed->value, 0),
            'interview' => (int) $statusCounts->get(ApplicationStatusEnum::Interview->value, 0),
            'hired' => (int) $statusCounts->get(ApplicationStatusEnum::Hired->value, 0),
            'rejected' => (int) $statusCounts->get(ApplicationStatusEnum::Rejected->value, 0),
            'formasi' => JobPosting::where('status', JobStatusEnum::Open)->count(),
            'pelamar' => User::where('role', RoleEnum::Pelamar)->count(),
        ];

        $screened = Application::whereNotNull('score')->count();
        $averageScore = (float) Application::whereNotNull('score')->avg('score');

        $chartData = [
            'labels' => collect(ApplicationStatusEnum::cases())->map->label()->values()->all(),
            'values' => collect(ApplicationStatusEnum::cases())
                ->map(fn (ApplicationStatusEnum $status): int => (int) $statusCounts->get($status->value, 0))
                ->all(),
        ];

        $topJobs = JobPosting::withCount(['applications', 'keywords'])
            ->with('department')
            ->orderByDesc('applications_count')
            ->take(5)
            ->get();

        $pendingScreening = Application::with(['user', 'job'])
            ->where('status', ApplicationStatusEnum::Applied)
            ->orderByRaw('admin_seen_at IS NOT NULL')
            ->orderByDesc('applied_at')
            ->take(6)
            ->get();

        $topCandidates = Application::with(['user', 'job'])
            ->whereNotNull('score')
            ->orderByDesc('score')
            ->take(6)
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'chartData',
            'topJobs',
            'pendingScreening',
            'topCandidates',
            'screened',
            'averageScore',
        ));
    }
}

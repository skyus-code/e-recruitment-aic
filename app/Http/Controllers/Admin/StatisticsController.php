<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicationStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Department;
use App\Models\JobKeyword;
use App\Models\ScoreDetail;
use Illuminate\View\View;

class StatisticsController extends Controller
{
    /**
     * Statistik rekrutmen: funnel, distribusi skor, tren bulanan, dan analisis kata kunci.
     */
    public function index(): View
    {
        $statusCounts = Application::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $funnel = collect(ApplicationStatusEnum::cases())->map(fn (ApplicationStatusEnum $status) => [
            'label' => $status->label(),
            'value' => (int) $statusCounts->get($status->value, 0),
        ])->values();

        $buckets = [
            '0–19' => 0,
            '20–39' => 0,
            '40–59' => 0,
            '60–79' => 0,
            '80–100' => 0,
        ];

        Application::whereNotNull('score')->pluck('score')->each(function (float $score) use (&$buckets): void {
            $key = match (true) {
                $score < 20 => '0–19',
                $score < 40 => '20–39',
                $score < 60 => '40–59',
                $score < 80 => '60–79',
                default => '80–100',
            };

            $buckets[$key]++;
        });

        $monthly = collect(range(5, 0))->map(function (int $offset): array {
            $date = now()->subMonths($offset);

            return [
                'label' => $date->translatedFormat('M y'),
                'value' => Application::whereBetween('applied_at', [
                    $date->copy()->startOfMonth(),
                    $date->copy()->endOfMonth(),
                ])->count(),
            ];
        })->values();

        $perDepartment = Department::query()
            ->get()
            ->map(function (Department $department): array {
                $jobIds = $department->jobs()->pluck('id');

                return [
                    'name' => $department->name,
                    'jobs' => $jobIds->count(),
                    'applications' => Application::whereIn('job_id', $jobIds)->count(),
                ];
            })
            ->sortByDesc('applications')
            ->values();

        $keywordAnalysis = JobKeyword::query()
            ->selectRaw('keyword, category, COUNT(*) as uses')
            ->groupBy('keyword', 'category')
            ->orderByDesc('uses')
            ->limit(8)
            ->get()
            ->map(function ($keyword) {
                $keywordIds = JobKeyword::where('keyword', $keyword->keyword)->pluck('id');

                $total = ScoreDetail::whereIn('job_keyword_id', $keywordIds)->count();
                $matched = ScoreDetail::whereIn('job_keyword_id', $keywordIds)->where('is_matched', true)->count();

                return [
                    'keyword' => $keyword->keyword,
                    'category' => $keyword->category->label(),
                    'uses' => (int) $keyword->uses,
                    'rate' => $total > 0 ? (int) round(($matched / $total) * 100) : 0,
                ];
            });

        $scored = Application::whereNotNull('score');
        $summary = [
            'total' => Application::count(),
            'scored' => (clone $scored)->count(),
            'average' => round((float) (clone $scored)->avg('score'), 2),
            'highest' => round((float) (clone $scored)->max('score'), 2),
            'lowest' => round((float) (clone $scored)->min('score'), 2),
            'keywords' => JobKeyword::count(),
        ];

        return view('admin.statistics', [
            'funnel' => $funnel,
            'buckets' => $buckets,
            'monthly' => $monthly,
            'perDepartment' => $perDepartment,
            'keywordAnalysis' => $keywordAnalysis,
            'summary' => $summary,
        ]);
    }
}

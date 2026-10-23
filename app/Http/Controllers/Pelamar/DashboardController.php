<?php

namespace App\Http\Controllers\Pelamar;

use App\Enums\ApplicationStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Dashboard pelamar: ringkasan status, timeline lamaran, dan jadwal terdekat.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $applications = Application::with(['job.department', 'interviewSchedule'])
            ->where('user_id', $user->id)
            ->orderByDesc('applied_at')
            ->get();

        $activeApplication = $applications
            ->where('status', '!=', ApplicationStatusEnum::Rejected)
            ->first()
            ?? $applications->first();

        $upcomingInterview = $applications
            ->filter(fn (Application $application): bool => $application->interviewSchedule !== null)
            ->map(fn (Application $application) => $application->interviewSchedule->setRelation('application', $application))
            ->sortBy('interview_date')
            ->first();

        $summary = [
            'total' => $applications->count(),
            'aktif' => $applications->whereIn('status', [
                ApplicationStatusEnum::Applied,
                ApplicationStatusEnum::Screening,
                ApplicationStatusEnum::Passed,
            ])->count(),
            'wawancara' => $applications->whereIn('status', [
                ApplicationStatusEnum::Interview,
            ])->count(),
            'diterima' => $applications->where('status', ApplicationStatusEnum::Hired)->count(),
        ];

        $completeness = $this->profileCompleteness($user);

        $latestApplication = $activeApplication;
        if ($latestApplication) {
            $latestApplication->loadMissing('job.department', 'interviewSchedule');
        }

        return view('pelamar.dashboard', compact(
            'applications',
            'activeApplication',
            'upcomingInterview',
            'summary',
            'completeness',
        ));
    }

    /**
     * @return array{percent: int, missing: array<int, string>}
     */
    private function profileCompleteness($user): array
    {
        $checks = [
            'data diri (jenis kelamin, tanggal lahir, alamat, telepon)' => (bool) $user->phone
                && (bool) $user->detailPerson?->birth_date
                && (bool) $user->detailPerson?->full_address,
            'riwayat pendidikan' => $user->educations()->exists(),
            'pengalaman kerja' => $user->experiences()->exists(),
            'daftar keahlian' => $user->skills()->exists(),
        ];

        $passed = array_filter($checks, fn (bool $passed): bool => $passed);
        $missing = array_keys($checks);
        foreach ($checks as $key => $val) {
            if ($val) {
                unset($missing[array_search($key, $missing)]);
            }
        }
        $missing = array_values($missing);
        $total = count($checks);

        return [
            'percent' => $total > 0 ? (int) round((count($passed) / $total) * 100) : 0,
            'missing' => $missing,
        ];
    }
}

<?php

namespace App\Http\Controllers\Manajer;

use App\Enums\ApplicationStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\InterviewAssessment;
use App\Models\InterviewSchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InterviewController extends Controller
{
    /**
     * Daftar jadwal wawancara beserta skor NLP tiap kandidat.
     */
    public function index(): View
    {
        $schedules = InterviewSchedule::with(['application.job.department', 'application.user.detailPerson'])
            ->orderBy('interview_date')
            ->get()
            ->groupBy(fn (InterviewSchedule $schedule) => $schedule->interview_date->format('Y-m-d'));

        $stats = [
            'terjadwal' => InterviewSchedule::count(),
            'hari_ini' => InterviewSchedule::whereDate('interview_date', today())->count(),
            'selesai' => Application::whereIn('status', [
                ApplicationStatusEnum::Hired,
                ApplicationStatusEnum::Rejected,
            ])->count(),
        ];

        return view('manajer.interviews.index', compact('schedules', 'stats'));
    }

    /**
     * Lembar penilaian & halaman keputusan untuk satu kandidat.
     */
    public function show(Request $request, Application $application): View
    {
        $application->load([
            'user.detailPerson',
            'user.educations',
            'user.experiences',
            'user.skills',
            'job.department',
            'job.keywords',
            'cvFile',
            'degreeCertificationFile',
            'parsedData',
            'scoreDetails',
            'interviewSchedule',
            'assessment.items',
        ]);

        $keywords = $application->job->keywords
            ->sortByDesc('weight')
            ->values()
            ->map(function ($keyword) use ($application) {
                $detail = $application->scoreDetails->firstWhere('job_keyword_id', $keyword->id);

                return [
                    'keyword' => $keyword->keyword,
                    'category' => $keyword->category,
                    'weight' => (float) $keyword->weight,
                    'is_matched' => (bool) ($detail?->is_matched),
                    'found_in_section' => $detail?->found_in_section,
                ];
            });

        $matchedWeight = (float) $application->scoreDetails->where('is_matched', true)->sum('matched_score');
        $totalWeight = (float) $application->job->keywords->sum('weight');

        $decided = in_array($application->status, [
            ApplicationStatusEnum::Hired,
            ApplicationStatusEnum::Rejected,
        ], true);

        return view('manajer.interviews.show', compact(
            'application',
            'keywords',
            'matchedWeight',
            'totalWeight',
            'decided',
        ));
    }

    /**
     * Simpan lembar penilaian aspek pelamar (Form Penilaian Pelamar).
     */
    public function storeAssessment(Request $request, Application $application): RedirectResponse
    {
        $validated = $request->validate([
            'assessor_name' => ['nullable', 'string', 'max:255'],
            'assessed_at' => ['nullable', 'date'],
            'scores' => ['nullable', 'array'],
            'scores.*' => ['nullable', 'integer', 'min:1', 'max:5'],
            'notes' => ['nullable', 'array'],
            'notes.*' => ['nullable', 'string', 'max:255'],
            'decision' => ['nullable', 'in:lulus,dipertimbangkan,tidak_lulus'],
            'conclusion' => ['nullable', 'string', 'max:3000'],
        ], [
            'scores.*.integer' => 'Skor tiap aspek harus berupa angka.',
            'scores.*.min' => 'Skor minimal 1.',
            'scores.*.max' => 'Skor maksimal 5.',
            'decision.in' => 'Keputusan tidak dikenali.',
        ]);

        $items = [];
        $finalScore = 0.0;

        foreach (InterviewAssessment::ASPECTS as $aspect) {
            $rawScore = $validated['scores'][$aspect['code']] ?? null;
            $score = ($rawScore === null || $rawScore === '') ? null : (int) $rawScore;
            $value = $score !== null ? round(($score / 5) * $aspect['weight'], 2) : null;

            if ($value !== null) {
                $finalScore += $value;
            }

            $items[] = [
                'aspect' => $aspect['code'],
                'weight' => $aspect['weight'],
                'score' => $score,
                'value' => $value,
                'note' => $validated['notes'][$aspect['code']] ?? null,
            ];
        }

        DB::transaction(function () use ($application, $validated, $items, $finalScore): void {
            $assessment = $application->assessment()->updateOrCreate([], [
                'assessor_name' => $validated['assessor_name'] ?? null,
                'assessed_at' => $validated['assessed_at'] ?? null,
                'final_score' => round($finalScore, 2),
                'decision' => $validated['decision'] ?? null,
                'conclusion' => $validated['conclusion'] ?? null,
            ]);

            $assessment->items()->delete();
            $assessment->items()->createMany($items);
        });

        return redirect()->route('manajer.interviews.show', $application)
            ->with('success', 'Penilaian pelamar berhasil disimpan.')
            ->with('message', 'Nilai akhir: '.number_format(round($finalScore, 2), 2).' · Batas lulus '.number_format(InterviewAssessment::PASSING_SCORE, 0).'.');
    }
}

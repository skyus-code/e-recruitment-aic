<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicationStatusEnum;
use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Mail\ApplicationStatusMail;
use App\Models\Application;
use App\Models\JobPosting;
use App\Models\User;
use App\Services\NlpScoringService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ScreeningController extends Controller
{
    /**
     * Daftar lamaran untuk screening, diurutkan berdasarkan skor NLP tertinggi.
     */
    public function index(Request $request): View
    {
        $status = (string) $request->query('status', '');
        $jobId = (int) $request->query('job', 0);
        $keyword = trim((string) $request->query('q'));

        $applications = Application::with(['user.detailPerson', 'job.department', 'cvFile'])
            ->withCount('scoreDetails')
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($jobId > 0, fn ($query) => $query->where('job_id', $jobId))
            ->when($keyword !== '', function ($query) use ($keyword): void {
                $query->where(function ($builder) use ($keyword): void {
                    $builder->whereHas('user', fn ($q) => $q->where('name', 'like', "%{$keyword}%"))
                        ->orWhereHas('job', fn ($q) => $q->where('title', 'like', "%{$keyword}%"));
                });
            })
            ->orderByRaw('score IS NULL, score DESC')
            ->orderByDesc('applied_at')
            ->paginate(12)
            ->withQueryString();

        $jobs = JobPosting::orderBy('title')->get(['id', 'title']);

        return view('admin.applications.index', [
            'applications' => $applications,
            'jobs' => $jobs,
            'filters' => ['status' => $status, 'job' => $jobId, 'q' => $keyword],
        ]);
    }

    /**
     * Menjalankan pipeline NLP penuh untuk satu lamaran.
     */
    public function startNlp(Request $request, Application $application, NlpScoringService $scoring): RedirectResponse
    {
        $result = $scoring->screen($application);

        if ($result['parsing_status'] === 'failed') {
            return redirect()->route('admin.screening.show', $application)->with([
                'error' => 'Parsing CV gagal.',
                'message' => $result['parsing_message'] ?? 'Berkas tidak dapat dibaca.',
            ]);
        }

        return redirect()->route('admin.screening.show', $application)->with([
            'success' => 'Screening NLP selesai.',
            'message' => "Skor kesesuaian {$result['score']} dari {$result['total_keywords']} kata kunci ({$result['matched_count']} cocok).",
        ]);
    }

    /**
     * Detail screening split-screen: data pelamar & CV di kiri, hasil NLP di kanan.
     */
    public function show(Application $application): View
    {
        $application->forceFill(['admin_seen_at' => now()])->save();

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
        ]);

        $keywords = $application->job->keywords
            ->sortByDesc('weight')
            ->values()
            ->map(function ($keyword) use ($application) {
                $detail = $application->scoreDetails->firstWhere('job_keyword_id', $keyword->id);

                return [
                    'id' => $keyword->id,
                    'keyword' => $keyword->keyword,
                    'category' => $keyword->category,
                    'weight' => (float) $keyword->weight,
                    'is_required' => (bool) $keyword->is_required,
                    'is_matched' => (bool) ($detail?->is_matched),
                    'found_in_section' => $detail?->found_in_section,
                ];
            });

        $matchedWeight = (float) $application->scoreDetails->where('is_matched', true)->sum('matched_score');
        $totalWeight = (float) $application->job->keywords->sum('weight');

        $stemmerTop = $application->stemmerData()
            ->orderByDesc('frequency_in_cv')
            ->limit(40)
            ->get();

        $totalStemmed = $application->stemmerData()->count();
        $uniqueStems = $application->stemmerData()->distinct('stemmed_word')->count('stemmed_word');

        $interviewers = User::where('role', RoleEnum::Manajer)
            ->get(['name', 'username'])
            ->sortBy(fn (User $user): string => mb_strtolower($user->username ?: $user->name))
            ->mapWithKeys(fn (User $user): array => [($user->username ?: $user->name) => ($user->username ?: $user->name)]);

        return view('admin.screening.show', compact(
            'application',
            'keywords',
            'matchedWeight',
            'totalWeight',
            'stemmerTop',
            'totalStemmed',
            'uniqueStems',
            'interviewers',
        ));
    }

    /**
     * Keputusan akhir seleksi berkas: lolos ke wawancara atau ditolak.
     */
    public function decide(Request $request, Application $application): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', 'in:passed,rejected'],
            'decision_note' => ['nullable', 'string', 'max:1000'],
        ], [
            'decision.required' => 'Keputusan wajib dipilih.',
            'decision.in' => 'Keputusan tidak dikenali.',
        ]);

        $status = $validated['decision'] === 'passed'
            ? ApplicationStatusEnum::Passed
            : ApplicationStatusEnum::Rejected;

        $application->update(['status' => $status]);

        // Kirim email pemberitahuan hasil seleksi berkas ke pelamar.
        $applicant = $application->user;
        Mail::to($applicant->email)->send(
            new ApplicationStatusMail($applicant->name, $validated['decision']),
        );

        $message = $status === ApplicationStatusEnum::Passed
            ? 'Kandidat dinyatakan lolos seleksi berkas.'
            : 'Kandidat ditolak pada tahap seleksi berkas.';

        return redirect()
            ->back()
            ->with('success', $message)
            ->with('message', $validated['decision_note'] ?? null);
    }
}

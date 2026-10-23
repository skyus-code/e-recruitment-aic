<?php

namespace App\Http\Controllers\Manajer;

use App\Enums\ApplicationStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DossierController extends Controller
{
    /**
     * Daftar berkas kandidat yang siap ditinjau manajer.
     */
    public function index(Request $request): View
    {
        $applications = Application::with(['user.detailPerson', 'job.department', 'cvFile', 'degreeCertificationFile'])
            ->withCount(['scoreDetails', 'stemmerData'])
            ->whereIn('status', [
                ApplicationStatusEnum::Passed,
                ApplicationStatusEnum::Interview,
                ApplicationStatusEnum::Hired,
            ])
            ->orderByDesc('score')
            ->paginate(10);

        return view('manajer.dossier.index', compact('applications'));
    }

    /**
     * Tinjauan lengkap berkas satu kandidat: CV, ijazah, teks hasil parse, dan data stemming.
     */
    public function show(Application $application): View
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
        ]);

        $keywords = $application->job->keywords
            ->sortByDesc('weight')
            ->map(function ($keyword) use ($application) {
                $detail = $application->scoreDetails->firstWhere('job_keyword_id', $keyword->id);

                return [
                    'keyword' => $keyword->keyword,
                    'category' => $keyword->category,
                    'weight' => (float) $keyword->weight,
                    'is_matched' => (bool) ($detail?->is_matched),
                ];
            });

        $stemmer = $application->stemmerData()
            ->orderByDesc('frequency_in_cv')
            ->limit(120)
            ->get();

        return view('manajer.dossier.show', compact('application', 'keywords', 'stemmer'));
    }
}

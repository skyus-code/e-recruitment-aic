<?php

namespace App\Http\Controllers;

use App\Enums\JobStatusEnum;
use App\Models\JobPosting;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobPostingController extends Controller
{
    /**
     * Daftar seluruh lowongan yang dapat dilihat publik.
     */
    public function index(Request $request): View
    {
        $keyword = trim((string) $request->query('q'));

        $jobs = JobPosting::with('department')
            ->withCount(['applications', 'keywords'])
            ->where('status', JobStatusEnum::Open)
            ->when($keyword !== '', function ($query) use ($keyword): void {
                $query->where(function ($builder) use ($keyword): void {
                    $builder->where('title', 'like', "%{$keyword}%")
                        ->orWhere('location', 'like', "%{$keyword}%")
                        ->orWhere('job_type', 'like', "%{$keyword}%");
                });
            })
            ->latest()
            ->paginate(9)
            ->withQueryString();

        return view('jobs.index', [
            'jobs' => $jobs,
            'keyword' => $keyword,
        ]);
    }

    /**
     * Detail satu lowongan beserta bobot kata kunci NLP-nya.
     */
    public function show(JobPosting $job): View
    {
        $job->load(['department', 'keywords' => fn ($query) => $query->orderByDesc('weight')]);

        $totalWeight = (float) $job->keywords->sum('weight');
        $alreadyApplied = auth()->check()
            ? $job->applications()->where('user_id', auth()->id())->exists()
            : false;

        // Pelamar yang emailnya belum terverifikasi belum boleh melamar.
        $emailVerified = auth()->check()
            ? auth()->user()->hasVerifiedEmail()
            : true;

        return view('jobs.show', compact('job', 'totalWeight', 'alreadyApplied', 'emailVerified'));
    }
}

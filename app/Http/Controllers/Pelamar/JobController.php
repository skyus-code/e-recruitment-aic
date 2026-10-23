<?php

namespace App\Http\Controllers\Pelamar;

use App\Enums\JobStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\JobPosting;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobController extends Controller
{
    /**
     * Katalog lowongan yang tersedia untuk dilamar pelamar.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $keyword = trim((string) $request->query('q'));

        $jobs = JobPosting::with('department')
            ->withCount(['applications', 'keywords'])
            ->where('status', JobStatusEnum::Open)
            ->when($keyword !== '', function ($query) use ($keyword): void {
                $query->where(function ($builder) use ($keyword): void {
                    $builder->where('title', 'like', "%{$keyword}%")
                        ->orWhere('location', 'like', "%{$keyword}%");
                });
            })
            ->latest()
            ->paginate(8)
            ->withQueryString();

        $appliedJobIds = $user->applications()->pluck('job_id')->flip();

        return view('pelamar.jobs.index', compact('jobs', 'keyword', 'appliedJobIds', 'user'));
    }
}

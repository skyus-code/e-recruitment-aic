<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatusEnum;
use App\Enums\JobStatusEnum;
use App\Enums\RoleEnum;
use App\Models\Application;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Halaman publik: profil yayasan, statistik, dan lowongan terbuka.
     */
    public function index(): View
    {
        $jobs = JobPosting::with('department')
            ->withCount(['applications', 'keywords'])
            ->where('status', JobStatusEnum::Open)
            ->latest()
            ->take(6)
            ->get();

        $stats = [
            'formasi' => JobPosting::where('status', JobStatusEnum::Open)->count(),
            'pelamar' => User::where('role', RoleEnum::Pelamar)->count(),
            'lamaran' => Application::count(),
            'diterima' => Application::where('status', ApplicationStatusEnum::Hired)->count(),
        ];

        return view('home', compact('jobs', 'stats'));
    }
}

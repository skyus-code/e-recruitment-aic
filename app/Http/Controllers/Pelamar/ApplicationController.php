<?php

namespace App\Http\Controllers\Pelamar;

use App\Enums\ApplicationStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\CvFile;
use App\Models\DegreeCertificationFile;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    /**
     * Formulir lamaran multi-step untuk satu lowongan.
     */
    public function create(Request $request, JobPosting $job): View
    {
        abort_unless($job->isOpen(), 404, 'Lowongan ini sudah ditutup.');

        $user = $request->user()->load(['detailPerson', 'educations', 'experiences', 'skills']);

        $alreadyApplied = Application::where('user_id', $user->id)
            ->where('job_id', $job->id)
            ->exists();

        if ($alreadyApplied) {
            return redirect()->route('pelamar.applications.index')
                ->with('error', 'Anda sudah melamar formasi ini sebelumnya.');
        }

        $job->load('department');

        $completeness = [
            'identitas' => (bool) $user->phone,
            'pendidikan' => $user->educations()->exists(),
            'pengalaman' => $user->experiences()->exists(),
            'keahlian' => $user->skills()->exists(),
        ];

        $defaultCv = $this->defaultCvFor($user);
        $defaultDegree = $this->defaultDegreeFor($user);

        return view('pelamar.apply.create', compact('job', 'user', 'completeness', 'defaultCv', 'defaultDegree'));
    }

    /**
     * Simpan lamaran beserta berkas CV dan ijazah.
     */
    public function store(Request $request, JobPosting $job): RedirectResponse
    {
        abort_unless($job->isOpen(), 404, 'Lowongan ini sudah ditutup.');

        $exists = Application::where('user_id', $request->user()->id)
            ->where('job_id', $job->id)
            ->exists();

        if ($exists) {
            return redirect()->route('pelamar.applications.index')
                ->with('error', 'Anda sudah melamar formasi ini sebelumnya.');
        }

        $defaultCv = $this->defaultCvFor($request->user());
        $defaultDegree = $this->defaultDegreeFor($request->user());

        $validated = $request->validate([
            'cv_file' => $defaultCv
                ? ['nullable', 'file', 'mimetypes:application/pdf', 'max:3072']
                : ['required', 'file', 'mimetypes:application/pdf', 'max:3072'],
            'ijazah_file' => [
                $defaultDegree ? 'nullable' : 'required',
                'file',
                'mimetypes:application/pdf,image/jpeg,image/png',
                'max:3072',
            ],
            'agreement' => ['accepted'],
        ], [
            'cv_file.required' => 'Berkas CV wajib diunggah.',
            'cv_file.mimetypes' => 'CV harus berupa berkas PDF.',
            'cv_file.max' => 'Ukuran CV maksimal 3 MB.',
            'ijazah_file.required' => 'Berkas ijazah wajib diunggah.',
            'ijazah_file.max' => 'Ukuran ijazah maksimal 3 MB.',
            'agreement.accepted' => 'Anda harus menyetujui pernyataan kelengkapan data.',
        ]);

        $application = DB::transaction(function () use ($request, $job, $defaultCv, $defaultDegree): Application {
            $user = $request->user();

            $application = Application::create([
                'user_id' => $user->id,
                'job_id' => $job->id,
                'status' => ApplicationStatusEnum::Applied,
                'applied_at' => now(),
            ]);

            if ($request->hasFile('cv_file')) {
                $cv = $request->file('cv_file');
                $cvPath = $cv->store('cv/'.$user->id, 'local');

                $cvMeta = [
                    'file_name' => $cv->getClientOriginalName(),
                    'file_path' => $cvPath,
                    'file_size' => $cv->getSize(),
                    'mime_type' => $cv->getMimeType(),
                    'file_hash' => hash_file('sha256', $cv->getRealPath()) ?: hash('sha256', $cvPath),
                ];
            } else {
                $cvPath = 'cv/'.$user->id.'/'.sha1($application->id.'-'.uniqid()).'.pdf';
                Storage::disk('local')->copy($defaultCv->file_path, $cvPath);

                $cvMeta = [
                    'file_name' => $defaultCv->file_name,
                    'file_path' => $cvPath,
                    'file_size' => $defaultCv->file_size,
                    'mime_type' => $defaultCv->mime_type,
                    'file_hash' => $defaultCv->file_hash,
                ];
            }

            $application->update(['cv_path' => $cvPath]);

            CvFile::create([
                'application_id' => $application->id,
                'uploaded_at' => now(),
                ...$cvMeta,
            ]);

            $ijazah = $request->file('ijazah_file');
            if ($ijazah) {
                $ijazahPath = $ijazah->store('ijazah/'.$user->id, 'local');
                $ijazahMeta = [
                    'file_name' => $ijazah->getClientOriginalName(),
                    'file_path' => $ijazahPath,
                    'file_size' => $ijazah->getSize(),
                    'mime_type' => $ijazah->getMimeType(),
                    'file_hash' => hash_file('sha256', $ijazah->getRealPath()) ?: hash('sha256', $ijazahPath),
                ];
            } else {
                abort_unless($defaultDegree, 422, 'Berkas ijazah wajib diunggah.');
                $extension = match ($defaultDegree->mime_type) {
                    'application/pdf' => 'pdf',
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    default => abort(422, 'Format ijazah tersimpan tidak didukung.'),
                };
                $ijazahPath = 'ijazah/'.$user->id.'/'.Str::uuid().'.'.$extension;
                abort_unless(Storage::disk('local')->copy($defaultDegree->file_path, $ijazahPath), 500, 'Berkas ijazah tidak dapat disalin.');
                $ijazahMeta = [
                    'file_name' => $defaultDegree->file_name,
                    'file_path' => $ijazahPath,
                    'file_size' => $defaultDegree->file_size,
                    'mime_type' => $defaultDegree->mime_type,
                    'file_hash' => $defaultDegree->file_hash,
                ];
            }

            DegreeCertificationFile::create([
                'application_id' => $application->id,
                'uploaded_at' => now(),
                ...$ijazahMeta,
            ]);

            return $application;
        });

        return redirect()->route('pelamar.applications.show', $application)
            ->with('success', 'Lamaran berhasil dikirim. Tim HRD akan meninjau berkas Anda.');
    }

    /**
     * Daftar seluruh lamaran milik pelamar.
     */
    public function index(Request $request): View
    {
        $applications = Application::with([
            'job.department',
            'job.keywords',
            'cvFile',
            'interviewSchedule',
            'parsedData',
            'stemmerData',
            'scoreDetails',
            'assessment.items',
        ])
            ->where('user_id', $request->user()->id)
            ->orderByDesc('applied_at')
            ->paginate(10);

        return view('pelamar.applications.index', compact('applications'));
    }

    /**
     * Detail status satu lamaran beserta timeline prosesnya.
     */
    public function show(Request $request, Application $application): View
    {
        $this->authorizeApplication($request, $application);

        $application->load([
            'job.department',
            'job.keywords',
            'cvFile',
            'degreeCertificationFile',
            'interviewSchedule',
            'parsedData',
            'stemmerData',
            'scoreDetails',
            'assessment.items',
        ]);

        return view('pelamar.applications.show', compact('application'));
    }

    /**
     * Halaman "Bedah CV": viewer PDF di kiri, hasil analisis NLP di kanan.
     */
    public function showAnalysis(Request $request, Application $application): View
    {
        $this->authorizeApplication($request, $application);

        $application->load([
            'job.keywords',
            'parsedData',
            'scoreDetails',
            'cvFile',
        ]);

        $keywords = $application->job->keywords
            ->sortByDesc('weight')
            ->values();

        $matchedIds = $application->scoreDetails
            ->where('is_matched', true)
            ->pluck('job_keyword_id');

        $keywords = $keywords->map(function ($keyword) use ($application, $matchedIds) {
            $detail = $application->scoreDetails
                ->firstWhere('job_keyword_id', $keyword->id);

            return [
                'keyword' => $keyword->keyword,
                'category' => $keyword->category,
                'weight' => (float) $keyword->weight,
                'is_required' => (bool) $keyword->is_required,
                'is_matched' => $matchedIds->contains($keyword->id),
                'found_in_section' => $detail?->found_in_section,
                'matched_score' => (float) ($detail?->matched_score ?? 0),
            ];
        });

        $matchedWeight = $application->scoreDetails->where('is_matched', true)->sum('matched_score');
        $totalWeight = (float) $keywords->sum('weight');

        $stemmerSample = $application->stemmerData()
            ->orderByDesc('frequency_in_cv')
            ->limit(60)
            ->get();

        return view('pelamar.applications.analysis', [
            'application' => $application,
            'keywords' => $keywords,
            'matchedWeight' => round((float) $matchedWeight, 2),
            'totalWeight' => round($totalWeight, 2),
            'stemmerSample' => $stemmerSample,
            'isScreened' => $application->scoreDetails->isNotEmpty(),
        ]);
    }

    /**
     * CV default terbaru milik pelamar: berkas dari halaman Kelola Berkas
     * atau CV lamaran sebelumnya yang masih ada di disk.
     */
    private function defaultCvFor(User $user): ?CvFile
    {
        return CvFile::where(function ($query) use ($user) {
            $query->whereHas('application', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->orWhere(function ($q) use ($user) {
                $q->whereNull('application_id')
                    ->where('file_path', 'like', 'cv/'.$user->id.'/%');
            });
        })
            ->latest('uploaded_at')
            ->get()
            ->first(fn (CvFile $cv) => Storage::disk('local')->exists($cv->file_path));
    }

    private function defaultDegreeFor(User $user): ?DegreeCertificationFile
    {
        return DegreeCertificationFile::with('application')
            ->whereHas('application', fn ($query) => $query->where('user_id', $user->id))
            ->latest('uploaded_at')
            ->get()
            ->first(fn (DegreeCertificationFile $degree): bool => Storage::disk('local')->exists($degree->file_path));
    }

    private function authorizeApplication(Request $request, Application $application): void
    {
        abort_unless($application->user_id === $request->user()->id, 403, 'Lamaran bukan milik Anda.');
    }
}

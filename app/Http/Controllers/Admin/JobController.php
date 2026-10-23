<?php

namespace App\Http\Controllers\Admin;

use App\Enums\JobStatusEnum;
use App\Enums\JobTypeEnum;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\JobKeyword;
use App\Models\JobPosting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobController extends Controller
{
    /**
     * Daftar seluruh lowongan beserta jumlah pelamar dan kata kuncinya.
     */
    public function index(Request $request): View
    {
        $status = (string) $request->query('status', '');

        $jobs = JobPosting::with('department')
            ->withCount(['applications', 'keywords'])
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $counts = [
            'total' => JobPosting::count(),
            'open' => JobPosting::where('status', JobStatusEnum::Open)->count(),
            'closed' => JobPosting::where('status', JobStatusEnum::Closed)->count(),
            'keywords' => JobKeyword::count(),
        ];

        return view('admin.jobs.index', compact('jobs', 'status', 'counts'));
    }

    /**
     * Formulir pembuatan lowongan baru.
     */
    public function create(): View
    {
        return view('admin.jobs.create', [
            'departments' => Department::orderBy('name')->get(),
            'jobTypes' => collect(JobTypeEnum::cases())->mapWithKeys(
                fn (JobTypeEnum $type): array => [$type->value => $type->label()],
            ),
            'statuses' => collect(JobStatusEnum::cases())->mapWithKeys(
                fn (JobStatusEnum $value): array => [$value->value => $value->label()],
            ),
        ]);
    }

    /**
     * Simpan lowongan baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $job = JobPosting::create($this->validated($request));

        return redirect()->route('admin.jobs.edit', $job)
            ->with('success', 'Lowongan dibuat. Lanjutkan dengan mengatur kata kunci NLP-nya.');
    }

    /**
     * Detail lowongan, pelamarnya, dan konfigurasi kata kunci NLP.
     */
    public function show(JobPosting $job): View
    {
        $job->load([
            'department',
            'keywords' => fn ($query) => $query->orderByDesc('weight'),
            'applications.user',
        ]);

        return view('admin.jobs.show', compact('job'));
    }

    /**
     * Formulir edit lowongan sekaligus pengelolaan kata kunci.
     */
    public function edit(JobPosting $job): View
    {
        $job->load(['department', 'keywords' => fn ($query) => $query->orderByDesc('weight')]);

        return view('admin.jobs.edit', [
            'job' => $job,
            'departments' => Department::orderBy('name')->get(),
            'jobTypes' => collect(JobTypeEnum::cases())->mapWithKeys(
                fn (JobTypeEnum $type): array => [$type->value => $type->label()],
            ),
            'statuses' => collect(JobStatusEnum::cases())->mapWithKeys(
                fn (JobStatusEnum $value): array => [$value->value => $value->label()],
            ),
            'totalWeight' => round((float) $job->keywords->sum('weight'), 2),
        ]);
    }

    /**
     * Perbarui lowongan.
     */
    public function update(Request $request, JobPosting $job): RedirectResponse
    {
        $job->update($this->validated($request, $job));

        return redirect()->route('admin.jobs.edit', $job)
            ->with('success', 'Lowongan berhasil diperbarui.');
    }

    /**
     * Hapus lowongan beserta relasinya.
     */
    public function destroy(JobPosting $job): RedirectResponse
    {
        $job->delete();

        return redirect()->route('admin.jobs.index')
            ->with('success', 'Lowongan dihapus.');
    }

    /**
     * Tambah satu kata kunci NLP untuk lowongan ini.
     */
    public function storeKeyword(Request $request, JobPosting $job): RedirectResponse
    {
        $validated = $request->validate([
            'keyword' => ['required', 'string', 'max:100'],
            'category' => ['required', 'in:hard_skill,soft_skill'],
            'weight' => ['required', 'numeric', 'min:0.01', 'max:99.99'],
            'is_required' => ['nullable', 'boolean'],
        ], [
            'keyword.required' => 'Kata kunci wajib diisi.',
            'weight.max' => 'Bobot maksimal 99.99.',
        ]);

        $duplicate = $job->keywords()
            ->whereRaw('LOWER(keyword) = ?', [mb_strtolower($validated['keyword'])])
            ->exists();

        if ($duplicate) {
            return redirect()->route('admin.jobs.edit', $job)
                ->with('error', 'Kata kunci tersebut sudah terdaftar pada lowongan ini.');
        }

        $job->keywords()->create([
            'keyword' => $validated['keyword'],
            'category' => $validated['category'],
            'weight' => $validated['weight'],
            'is_required' => (bool) ($validated['is_required'] ?? false),
        ]);

        return redirect()->route('admin.jobs.edit', $job)
            ->with('success', 'Kata kunci ditambahkan.');
    }

    /**
     * Perbarui satu kata kunci NLP.
     */
    public function updateKeyword(Request $request, JobPosting $job, JobKeyword $keyword): RedirectResponse
    {
        abort_unless($keyword->job_id === $job->id, 404);

        $validated = $request->validate([
            'keyword' => ['required', 'string', 'max:100'],
            'category' => ['required', 'in:hard_skill,soft_skill'],
            'weight' => ['required', 'numeric', 'min:0.01', 'max:99.99'],
            'is_required' => ['nullable', 'boolean'],
        ]);

        $keyword->update([
            'keyword' => $validated['keyword'],
            'category' => $validated['category'],
            'weight' => $validated['weight'],
            'is_required' => (bool) ($validated['is_required'] ?? false),
        ]);

        return redirect()->route('admin.jobs.edit', $job)
            ->with('success', 'Kata kunci diperbarui.');
    }

    /**
     * Hapus satu kata kunci NLP.
     */
    public function destroyKeyword(JobPosting $job, JobKeyword $keyword): RedirectResponse
    {
        abort_unless($keyword->job_id === $job->id, 404);

        $keyword->delete();

        return redirect()->route('admin.jobs.edit', $job)
            ->with('success', 'Kata kunci dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?JobPosting $job = null): array
    {
        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'requirements' => ['required', 'string', 'max:5000'],
            'salary_min' => ['nullable', 'integer', 'min:0', 'max:2000000000'],
            'salary_max' => ['nullable', 'integer', 'min:0', 'max:2000000000', 'gte:salary_min'],
            'location' => ['required', 'string', 'max:255'],
            'job_type' => ['required', 'in:full-time,part-time,contract,internship'],
            'status' => ['required', 'in:open,closed'],
        ], [
            'salary_max.gte' => 'Gaji maksimal tidak boleh lebih kecil dari gaji minimal.',
            'title.required' => 'Judul lowongan wajib diisi.',
            'description.required' => 'Deskripsi lowongan wajib diisi.',
            'requirements.required' => 'Persyaratan lowongan wajib diisi.',
        ]);

        $validated['salary_min'] = $validated['salary_min'] ?: null;
        $validated['salary_max'] = $validated['salary_max'] ?: null;

        return $validated;
    }
}

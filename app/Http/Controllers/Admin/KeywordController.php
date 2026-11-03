<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobKeyword;
use App\Models\JobPosting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KeywordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $keywords = JobKeyword::with('job')->latest()->paginate(20);

        return view('admin.keywords.index', compact('keywords'));
    }

    public function create()
    {
        $jobs = JobPosting::all();

        return view('admin.keywords.create', compact('jobs'));
    }

    public function store(Request $request)
    {
        if ($request->has('keywords')) {
            $keywordRows = collect($request->input('keywords', []))
                ->filter(fn (array $keyword): bool => trim((string) ($keyword['keyword'] ?? '')) !== '')
                ->values();
            $request->merge(['keywords' => $keywordRows->all()]);

            $validated = $request->validate([
                'job_id' => ['required', 'integer', 'exists:job_postings,id'],
                'keywords' => ['nullable', 'array'],
                'keywords.*.keyword' => ['required', 'string', 'max:255'],
                'keywords.*.weight' => ['required', 'integer', 'min:1', 'max:100'],
                'keywords.*.category' => ['nullable', 'string', 'max:120'],
            ]);

            $keywords = collect($validated['keywords'] ?? [])
                ->map(fn (array $keyword): array => [
                    'keyword' => trim((string) ($keyword['keyword'] ?? '')),
                    'weight' => $keyword['weight'] ?? null,
                    'category' => $keyword['category'] ?? null,
                ])
                ->filter(fn (array $keyword): bool => $keyword['keyword'] !== '')
                ->unique(fn (array $keyword): string => mb_strtolower($keyword['keyword']))
                ->values();

            $existingKeywords = JobKeyword::where('job_id', $validated['job_id'])->pluck('keyword')
                ->map(fn (string $keyword): string => mb_strtolower($keyword));

            $rows = $keywords
                ->reject(fn (array $keyword): bool => $existingKeywords->contains(mb_strtolower($keyword['keyword'])))
                ->map(fn (array $keyword): array => [
                    ...$keyword,
                    'job_id' => $validated['job_id'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            if ($rows->isNotEmpty()) {
                DB::transaction(fn () => JobKeyword::insert($rows->all()));
            }

            return redirect()->route('admin.settings.index')->with('success', $rows->isNotEmpty()
                ? 'Keyword berhasil disimpan.'
                : 'Tidak ada keyword baru untuk disimpan.');
        }

        $validated = $request->validate([
            'job_id' => ['required', 'integer', 'exists:job_postings,id'],
            'keyword' => ['required', 'string', 'max:255'],
            'weight' => ['required', 'integer', 'min:1', 'max:100'],
            'category' => ['nullable', 'string', 'max:120'],
        ]);

        JobKeyword::create($validated);

        return redirect()->route('admin.keywords.index')->with('success', 'Keyword berhasil disimpan');
    }

    public function edit(JobKeyword $keyword)
    {
        $jobs = JobPosting::all();

        return view('admin.keywords.edit', compact('keyword', 'jobs'));
    }

    public function update(Request $request, JobKeyword $keyword)
    {
        $validated = $request->validate([
            'job_id' => ['required', 'integer', 'exists:job_postings,id'],
            'keyword' => ['required', 'string', 'max:255'],
            'weight' => ['required', 'integer', 'min:1', 'max:100'],
            'category' => ['nullable', 'string', 'max:120'],
        ]);

        $keyword->update($validated);

        return redirect()->route('admin.keywords.index')->with('success', 'Keyword berhasil diperbarui');
    }

    public function destroy(JobKeyword $keyword)
    {
        $keyword->delete();

        return redirect()->route('admin.keywords.index')->with('success', 'Keyword berhasil dihapus');
    }
}

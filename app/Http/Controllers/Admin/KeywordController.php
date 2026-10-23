<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobKeyword;
use App\Models\JobPosting;
use Illuminate\Http\Request;

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

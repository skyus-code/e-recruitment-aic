<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Master data departemen dan ringkasan konfigurasi sistem.
     */
    public function index(): View
    {
        $departments = Department::withCount('jobs')
            ->orderBy('name')
            ->get();

        $overview = [
            'users' => User::count(),
            'admins' => User::where('role', RoleEnum::Admin)->count(),
            'manajer' => User::where('role', RoleEnum::Manajer)->count(),
            'pelamar' => User::where('role', RoleEnum::Pelamar)->count(),
            'jobs' => JobPosting::count(),
            'departments' => $departments->count(),
        ];

        $jobs = JobPosting::orderBy('title')->get(['id', 'title']);

        return view('admin.settings', compact('departments', 'overview', 'jobs'));
    }

    /**
     * Tambah departemen baru.
     */
    public function storeDepartment(Request $request): RedirectResponse
    {
        $departmentRows = collect($request->input('departments', []))
            ->filter(fn (array $department): bool => trim((string) ($department['name'] ?? '')) !== '')
            ->values();
        $request->merge(['departments' => $departmentRows->all()]);

        $validated = $request->validate([
            'departments' => ['nullable', 'array'],
            'departments.*.name' => ['nullable', 'string', 'max:255'],
            'departments.*.address' => ['nullable', 'string', 'max:500'],
            'departments.*.email' => ['nullable', 'email', 'max:255'],
        ]);

        $departments = collect($validated['departments'])
            ->map(function (array $department): array {
                return [
                    'name' => trim((string) ($department['name'] ?? '')),
                    'address' => $department['address'] ?? null,
                    'email' => $department['email'] ?? null,
                ];
            })
            ->filter(fn (array $department): bool => $department['name'] !== '')
            ->unique(fn (array $department): string => mb_strtolower($department['name']))
            ->values();

        $existingNames = Department::pluck('name')
            ->map(fn (string $name): string => mb_strtolower($name));

        $departments = $departments
            ->reject(fn (array $department): bool => $existingNames->contains(mb_strtolower($department['name'])))
            ->map(fn (array $department): array => [
                ...$department,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        if ($departments->isNotEmpty()) {
            DB::transaction(fn () => Department::insert($departments->all()));
        }

        return redirect()->route('admin.settings.index')
            ->with('success', $departments->isNotEmpty()
                ? 'Departemen berhasil ditambahkan.'
                : 'Tidak ada departemen baru untuk ditambahkan.');
    }

    /**
     * Perbarui departemen.
     */
    public function updateDepartment(Request $request, Department $department): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:departments,name,'.$department->id],
            'address' => ['nullable', 'string', 'max:500'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        $department->update($validated);

        return redirect()->route('admin.settings.index')
            ->with('success', 'Departemen berhasil diperbarui.');
    }

    /**
     * Hapus departemen yang belum memiliki lowongan.
     */
    public function destroyDepartment(Department $department): RedirectResponse
    {
        if ($department->jobs()->exists()) {
            return redirect()->route('admin.settings.index')
                ->with('error', 'Departemen masih memiliki lowongan terkait dan tidak dapat dihapus.');
        }

        $department->delete();

        return redirect()->route('admin.settings.index')
            ->with('success', 'Departemen dihapus.');
    }
}

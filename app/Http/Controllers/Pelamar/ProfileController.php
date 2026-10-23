<?php

namespace App\Http\Controllers\Pelamar;

use App\Http\Controllers\Controller;
use App\Http\Requests\SyncProfileSkillsRequest;
use App\Models\PersonEducation;
use App\Models\PersonExperience;
use App\Models\PersonSkill;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Halaman Profil & Master Data milik pelamar.
     */
    public function edit(Request $request): View
    {
        $user = $request->user()->load([
            'detailPerson',
            'educations' => fn ($query) => $query->orderByDesc('start_year'),
            'experiences' => fn ($query) => $query->orderByDesc('start_date'),
            'skills' => fn ($query) => $query->orderBy('skill_name'),
        ]);

        return view('pelamar.profile.edit', compact('user'));
    }

    /**
     * Simpan identitas utama dan data diri pelamar.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'indonesian_mobile'],
            'gender' => ['nullable', 'in:Laki-laki,Perempuan'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'full_address' => ['nullable', 'string', 'max:1000'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'birth_date.before' => 'Tanggal lahir harus berada di masa lalu.',
        ]);

        $user = $request->user();

        $user->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
        ]);

        $person = $user->person()->firstOrCreate([]);

        $person->detailPerson()->updateOrCreate([], [
            'gender' => $validated['gender'] ?? null,
            'birth_place' => $validated['birth_place'] ?? null,
            'birth_date' => $validated['birth_date'] ?? null,
            'full_address' => $validated['full_address'] ?? null,
        ]);

        return redirect()->route('pelamar.profile.edit')
            ->with('success', 'Data diri berhasil diperbarui.');
    }

    /**
     * Tambah riwayat pendidikan.
     */
    public function storeEducation(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'institution' => ['required', 'string', 'max:255'],
            'degree' => ['nullable', 'string', 'max:100'],
            'major' => ['nullable', 'string', 'max:100'],
            'start_year' => ['nullable', 'integer', 'min:1950', 'max:2100'],
            'end_year' => ['nullable', 'integer', 'min:1950', 'max:2100', 'gte:start_year'],
            'gpa' => ['nullable', 'numeric', 'min:0', 'max:4'],
        ], [
            'end_year.gte' => 'Tahun selesai tidak boleh lebih kecil dari tahun mulai.',
            'gpa.max' => 'IPK maksimal 4.00.',
        ]);

        $request->user()->person()->firstOrCreate([])->educations()->create($validated);

        return redirect()->route('pelamar.profile.edit')
            ->with('success', 'Riwayat pendidikan ditambahkan.');
    }

    public function destroyEducation(Request $request, PersonEducation $education): RedirectResponse
    {
        $this->authorizeOwner($request, $education->person_id);

        $education->delete();

        return redirect()->route('pelamar.profile.edit')
            ->with('success', 'Riwayat pendidikan dihapus.');
    }

    /**
     * Tambah pengalaman kerja.
     */
    public function storeExperience(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company' => ['required', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'description' => ['nullable', 'string', 'max:2000'],
        ], [
            'end_date.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
        ]);

        $request->user()->person()->firstOrCreate([])->experiences()->create($validated);

        return redirect()->route('pelamar.profile.edit')
            ->with('success', 'Pengalaman kerja ditambahkan.');
    }

    public function destroyExperience(Request $request, PersonExperience $experience): RedirectResponse
    {
        $this->authorizeOwner($request, $experience->person_id);

        $experience->delete();

        return redirect()->route('pelamar.profile.edit')
            ->with('success', 'Pengalaman kerja dihapus.');
    }

    /**
     * Tambah keahlian.
     */
    public function storeSkill(SyncProfileSkillsRequest $request): RedirectResponse
    {
        $skills = (array) $request->validated('skills', []);
        $person = $request->user()->person()->firstOrCreate([]);
        $existing = $person->skills()->get()->keyBy(fn (PersonSkill $skill): string => mb_strtolower($skill->skill_name));
        $keys = array_map(static fn (string $skill): string => mb_strtolower($skill), $skills);

        DB::transaction(function () use ($person, $existing, $keys, $skills): void {
            foreach ($existing as $key => $storedSkill) {
                if (! in_array($key, $keys, true)) {
                    $storedSkill->delete();
                }
            }

            foreach ($skills as $skillName) {
                if (! $existing->has(mb_strtolower($skillName))) {
                    $person->skills()->create(['skill_name' => $skillName, 'level' => 'Menengah']);
                }
            }
        });

        return redirect()->route('pelamar.profile.edit')
            ->with('success', 'Daftar keahlian berhasil disimpan.');
    }

    /**
     * Memastikan baris yang dihapus memang milik pelamar yang sedang masuk.
     */
    private function authorizeOwner(Request $request, int $ownerId): void
    {
        $user = $request->user();
        $personId = $user->person->id ?? null;

        if ($personId !== $ownerId) {
            abort(403, 'Data bukan milik Anda.');
        }
    }
}

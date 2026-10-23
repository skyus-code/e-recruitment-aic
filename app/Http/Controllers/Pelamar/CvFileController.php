<?php

namespace App\Http\Controllers\Pelamar;

use App\Http\Controllers\Controller;
use App\Models\CvFile;
use App\Models\DegreeCertificationFile;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CvFileController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $cvFiles = CvFile::where(function ($q) use ($user) {
            $q->whereHas('application', function ($q2) use ($user) {
                $q2->where('user_id', $user->id);
            })->orWhere(function ($q2) use ($user) {
                $q2->whereNull('application_id')->where('file_path', 'like', 'cv/'.$user->id.'/%');
            });
        })->orderByDesc('uploaded_at')->orderByDesc('id')->get();
        $degreeFiles = DegreeCertificationFile::with('application')
            ->whereHas('application', fn ($query) => $query->where('user_id', $user->id))
            ->orderByDesc('uploaded_at')
            ->orderByDesc('id')
            ->get();
        $cvFiles = $this->uniqueFilesByContent($cvFiles);
        $degreeFiles = $this->uniqueFilesByContent($degreeFiles);

        return view('pelamar.cv-files.index', compact('cvFiles', 'degreeFiles'));
    }

    /**
     * Tampilkan satu entri untuk setiap isi berkas yang sama.
     *
     * @param  Collection<int, CvFile|DegreeCertificationFile>  $files
     * @return Collection<int, CvFile|DegreeCertificationFile>
     */
    private function uniqueFilesByContent(Collection $files): Collection
    {
        return $files->unique(fn (CvFile|DegreeCertificationFile $file): string => $file->file_hash ? strtolower($file->file_hash) : 'record-'.$file->getKey()
        )->values();
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'cv_file' => ['required', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        $user = $request->user();
        $file = $validated['cv_file'];
        $path = $file->store('cv/'.$user->id, 'local');

        CvFile::create([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'file_hash' => hash_file('sha256', $file->getPathname()),
            'uploaded_at' => now(),
        ]);

        return redirect()->route('pelamar.cv-files.index')->with('success', 'Berkas berhasil diunggah');
    }

    public function destroy(Request $request, CvFile $cvFile): RedirectResponse
    {
        $this->authorizeOwnership($request, $cvFile);

        if ($cvFile->application_id !== null) {
            return redirect()->route('pelamar.cv-files.index')
                ->with('error', 'Berkas ini sudah digunakan pada lamaran terkirim sehingga tidak dapat dihapus.');
        }

        $path = $this->safeOwnedPath($request, $cvFile);
        abort_unless(Storage::disk('local')->delete($path), 404, 'Berkas tidak ditemukan.');
        $cvFile->delete();

        return redirect()->route('pelamar.cv-files.index')->with('success', 'Berkas berhasil dihapus.');
    }

    public function destroyDegree(Request $request, DegreeCertificationFile $degreeCertificationFile): RedirectResponse
    {
        abort_unless($degreeCertificationFile->application?->user_id === $request->user()->id, 403);

        return redirect()->route('pelamar.cv-files.index')
            ->with('error', 'Ijazah ini sudah digunakan pada lamaran terkirim sehingga tidak dapat dihapus.');
    }

    public function preview(Request $request, CvFile $cvFile): StreamedResponse
    {
        $this->authorizeOwnership($request, $cvFile);
        $path = $this->safeOwnedPath($request, $cvFile);

        return Storage::disk('local')->response(
            $path,
            $cvFile->file_name,
            ['Content-Type' => 'application/pdf', 'X-Content-Type-Options' => 'nosniff'],
            'inline',
        );
    }

    public function download(Request $request, CvFile $cvFile): StreamedResponse
    {
        $this->authorizeOwnership($request, $cvFile);
        $path = $this->safeOwnedPath($request, $cvFile);

        return Storage::disk('local')->download($path, $cvFile->file_name, ['X-Content-Type-Options' => 'nosniff']);
    }

    private function authorizeOwnership(Request $request, CvFile $cvFile): void
    {
        $ownerId = $cvFile->application?->user_id;
        if ($ownerId === null && str_starts_with((string) $cvFile->file_path, 'cv/')) {
            $ownerId = (int) explode('/', $cvFile->file_path)[1];
        }

        abort_unless($ownerId === $request->user()->id, 403, 'Anda tidak berwenang mengakses berkas ini.');
    }

    private function safeOwnedPath(Request $request, CvFile $cvFile): string
    {
        $relativePath = (string) $cvFile->file_path;
        $segments = explode('/', str_replace('\\', '/', $relativePath));
        abort_if($relativePath === '' || str_contains($relativePath, "\0") || str_contains($relativePath, '\\') || str_starts_with($relativePath, '/') || in_array('..', $segments, true) || in_array('.', $segments, true), 404);
        abort_unless(str_starts_with($relativePath, 'cv/'.$request->user()->id.'/'), 403);

        $disk = Storage::disk('local');
        $root = realpath($disk->path(''));
        $target = realpath($disk->path($relativePath));
        abort_unless($root && $target && str_starts_with($target, $root.DIRECTORY_SEPARATOR) && is_file($target), 404, 'Berkas tidak ditemukan.');

        return $relativePath;
    }
}

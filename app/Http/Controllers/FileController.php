<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    /**
     * Menyajikan berkas CV milik sebuah lamaran (unduhan).
     */
    public function cv(Request $request, Application $application): StreamedResponse
    {
        $this->authorize($request, $application);

        $file = $application->cvFile;

        abort_unless($file, 404, 'Berkas CV tidak ditemukan.');

        abort_unless(Storage::disk('local')->exists($file->file_path), 404, 'Berkas CV tidak ditemukan.');

        return Storage::disk('local')->download(
            $file->file_path,
            'CV-'.$application->user->name.'.pdf',
        );
    }

    /**
     * Menyajikan berkas CV milik sebuah lamaran (pratinjau inline, tanpa unduhan otomatis).
     */
    public function previewCv(Request $request, Application $application): StreamedResponse
    {
        $this->authorize($request, $application);

        $file = $application->cvFile;

        abort_unless($file, 404, 'Berkas CV tidak ditemukan.');

        abort_unless(Storage::disk('local')->exists($file->file_path), 404, 'Berkas CV tidak ditemukan.');

        return Storage::disk('local')->response(
            $file->file_path,
            $file->file_name,
            ['Content-Type' => $file->mime_type],
            'inline',
        );
    }

    /**
     * Menyajikan berkas ijazah milik sebuah lamaran.
     */
    public function ijazah(Request $request, Application $application): StreamedResponse
    {
        $this->authorize($request, $application);

        $file = $application->degreeCertificationFile;

        abort_unless($file, 404, 'Berkas ijazah tidak ditemukan.');

        abort_unless(Storage::disk('local')->exists($file->file_path), 404, 'Berkas ijazah tidak ditemukan.');

        return Storage::disk('local')->download(
            $file->file_path,
            'Ijazah-'.$application->user->name.'.'.pathinfo($file->file_name, PATHINFO_EXTENSION),
        );
    }

    public function previewIjazah(Request $request, Application $application): StreamedResponse
    {
        $this->authorize($request, $application);

        $file = $application->degreeCertificationFile;
        abort_unless($file && Storage::disk('local')->exists($file->file_path), 404, 'Berkas ijazah tidak ditemukan.');

        return Storage::disk('local')->response(
            $file->file_path,
            $file->file_name,
            ['Content-Type' => $file->mime_type],
            'inline',
        );
    }

    /**
     * Pemilik lamaran atau tim HRD/manajer yang berwenang.
     */
    private function authorize(Request $request, Application $application): void
    {
        $user = $request->user();

        $isOwner = $user !== null && $user->id === $application->user_id;
        $isStaff = $user !== null && in_array($user->role, [RoleEnum::Admin, RoleEnum::Manajer], true);

        abort_unless($isOwner || $isStaff, 403, 'Anda tidak berwenang mengakses berkas ini.');
    }
}

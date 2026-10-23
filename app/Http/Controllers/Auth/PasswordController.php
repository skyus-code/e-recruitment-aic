<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class PasswordController extends Controller
{
    /**
     * Simpan kata sandi baru milik pengguna yang sedang masuk.
     */
    public function update(UpdatePasswordRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => $request->validated('password'),
            'password_set_at' => now(),
        ]);

        // Perbarui ID sesi agar sesi lama tidak ikut memakai kredensial baru.
        $request->session()->regenerate();

        return redirect()
            ->route($this->successRoute($request))
            ->with('success', 'Kata sandi berhasil diperbarui.');
    }

    /**
     * Halaman tujuan setelah kata sandi tersimpan: kembali ke tempat asal
     * (mis. profil pelamar) bila masih berada di dalam area terautentikasi.
     */
    private function successRoute(Request $request): string
    {
        $fallback = 'password.edit';
        $target = $request->input('redirect_to');

        if (! is_string($target) || ! Route::has($target)) {
            return $fallback;
        }

        $allowedPrefixes = ['admin.', 'manajer.', 'pelamar.'];

        return Str::startsWith($target, $allowedPrefixes) ? $target : $fallback;
    }
}

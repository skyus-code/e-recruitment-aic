<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Penjaga terpusat untuk pembuatan lamaran.
 *
 * Pelamar wajib memiliki email yang sudah terverifikasi sebelum dapat
 * melamar pekerjaan. Penjagaan dilakukan di server (bukan sekadar
 * menyembunyikan tombol di antarmuka) sehingga tidak dapat dilewati lewat
 * POST manual, API, atau pengulangan form.
 *
 * Middleware ini dipasang pada SETIAP titik masuk pembuatan lamaran.
 */
class EnsureVerifiedEmailForApplication
{
    /**
     * Pesan yang sama untuk=web maupun JSON agar perilakunya konsisten.
     */
    public const MESSAGE = 'Email Anda belum terverifikasi. Verifikasi email di Pengaturan Akun untuk dapat melamar pekerjaan.';

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Hanya pelamar yang dibatasi. Admin & Manajer tidak melamar.
        if (! $user || $user->role->value !== 'pelamar' || $user->hasVerifiedEmail()) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => self::MESSAGE,
            ], 403);
        }

        return redirect()->back()->with('error', self::MESSAGE);
    }
}

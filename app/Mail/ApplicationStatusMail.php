<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email pemberitahuan perubahan status lamaran pelamar.
 *
 * Status yang didukung: 'passed', 'rejected', 'hired'.
 */
class ApplicationStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  string  $userName  Nama pelamar yang akan disapa pada email.
     * @param  string  $status  Status lamaran: 'passed', 'rejected', atau 'hired'.
     */
    public function __construct(
        public string $userName,
        public string $status,
    ) {}

    /**
     * Subjek email dinamis mengikuti status lamaran.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine(),
        );
    }

    /**
     * Isi email memakai template dengan inline CSS agar aman di klien email.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.application_status',
            with: [
                'userName' => $this->userName,
                'status' => $this->status,
                'statusLabel' => $this->statusLabel(),
                'isPositive' => $this->isPositive(),
                'dashboardUrl' => route('login'),
            ],
        );
    }

    /**
     * Subjek menyesuaikan status agar pelamar langsung memahami konteksnya.
     */
    private function subjectLine(): string
    {
        return match ($this->status) {
            'hired' => 'Selamat! Lamaran Anda Diterima - Ar-Rahman Islamic Center',
            'passed' => 'Anda Lolos Seleksi Berkas - Ar-Rahman Islamic Center',
            'rejected' => 'Pemberitahuan Hasil Seleksi - Ar-Rahman Islamic Center',
            default => 'Pemberitahuan Hasil Seleksi - Ar-Rahman Islamic Center',
        };
    }

    /**
     * Label status yang dibaca manusia, dipakai di dalam template.
     */
    private function statusLabel(): string
    {
        return match ($this->status) {
            'passed' => 'Lolos Seleksi Berkas',
            'hired' => 'Diterima',
            'rejected' => 'Belum Lolos',
            default => ucfirst($this->status),
        };
    }

    /**
     * Menentukan warna aksen: hijau untuk kabar baik, merah untuk penolakan.
     */
    private function isPositive(): bool
    {
        return in_array($this->status, ['passed', 'hired'], true);
    }
}

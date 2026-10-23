<?php

namespace App\Mail;

use App\Enums\VerificationPurposeEnum;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email berisi kode OTP 6 digit. Dipakai untuk verifikasi email maupun
 * atur ulang kata sandi; subjek dan isi menyesuaikan tujuan kode.
 */
class VerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  string  $userName  Nama pengguna penerima.
     * @param  string  $code  Kode OTP 6 digit.
     * @param  VerificationPurposeEnum  $purpose  Tujuan kode.
     * @param  int  $expiresInMinutes  Masa berlaku kode dalam menit.
     */
    public function __construct(
        public string $userName,
        public string $code,
        public VerificationPurposeEnum $purpose,
        public int $expiresInMinutes,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->purpose->mailSubject(),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.verification_code',
            with: [
                'userName' => $this->userName,
                'code' => $this->code,
                'purpose' => $this->purpose,
                'expiresInMinutes' => $this->expiresInMinutes,
            ],
        );
    }
}

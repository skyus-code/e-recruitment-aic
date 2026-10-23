<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Konfigurasi Kode Verifikasi (OTP)
    |--------------------------------------------------------------------------
    |
    | Mengatur perilaku kode 6 digit untuk verifikasi email dan atur ulang
    | kata sandi. Nilai default sudah aman; sesuaikan lewat .env bila perlu.
    |
    */

    'expires_in_minutes' => (int) env('VERIFICATION_CODE_EXPIRES_MINUTES', 10),

    'resend_cooldown_seconds' => (int) env('VERIFICATION_CODE_RESEND_COOLDOWN', 60),

    'max_attempts' => (int) env('VERIFICATION_CODE_MAX_ATTEMPTS', 5),

    'send_per_hour' => (int) env('VERIFICATION_CODE_SEND_PER_HOUR', 5),

];

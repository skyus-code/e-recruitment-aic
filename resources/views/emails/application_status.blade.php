@php
    // Aksen & salinan pesan menyesuaikan status lamaran.
    $accent = $isPositive ? '#059669' : '#DC2626';
    $accentSoft = $isPositive ? '#ECFDF5' : '#FEF2F2';
    $headline = match ($status) {
        'hired' => 'Selamat, lamaran Anda diterima!',
        'passed' => 'Anda lolos seleksi berkas.',
        'rejected' => 'Terima kasih atas partisipasi Anda.',
        default => 'Pembaruan status lamaran Anda.',
    };
    $intro = match ($status) {
        'hired' => 'Kami dengan senang hati menginformasikan bahwa Anda dinyatakan DITERIMA pada proses rekrutmen Ar-Rahman Islamic Center. Tim kami akan menghubungi Anda untuk langkah selanjutnya.',
        'passed' => 'Kabar baik! Berkas lamaran Anda dinyatakan LOLOS seleksi administrasi dan akan dilanjutkan ke tahap wawancara. Mohon pantau email dan dasbor Anda untuk jadwal wawancara.',
        'rejected' => 'Setelah melalui proses seleksi yang cermat, kami belum dapat melanjutkan lamaran Anda pada kesempatan kali ini. Kami sangat menghargai minat dan waktu yang Anda berikan.',
        default => 'Status lamaran Anda telah diperbarui. Silakan buka dasbor untuk informasi lebih lengkap.',
    };
    $statusColor = $accent;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <title>Pemberitahuan Hasil Seleksi</title>
</head>
<body style="margin:0; padding:0; background-color:#F1F5F9; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#F1F5F9; padding:24px 12px;">
        <tr>
            <td align="center">
                {{-- Kartu utama, lebar maksimum 600px agar rapi di desktop & mobile --}}
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:100%; max-width:600px; background-color:#FFFFFF; border-radius:16px; overflow:hidden; font-family:Arial, Helvetica, sans-serif;">

                    {{-- Header biru tua tema aplikasi --}}
                    <tr>
                        <td style="background-color:#1E3A8A; padding:28px 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="font-size:18px; font-weight:bold; color:#FFFFFF; letter-spacing:0.5px;">
                                        Ar-Rahman Islamic Center
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-size:11px; color:#BFDBFE; letter-spacing:2px; text-transform:uppercase; padding-top:4px;">
                                        E-Recruitment System
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Badge status --}}
                    <tr>
                        <td style="padding:32px 32px 0 32px;">
                            <span style="display:inline-block; background-color:{{ $accentSoft }}; color:{{ $statusColor }}; border:1px solid {{ $statusColor }}; border-radius:999px; padding:6px 16px; font-size:12px; font-weight:bold; letter-spacing:1px; text-transform:uppercase;">
                                {{ $statusLabel }}
                            </span>
                        </td>
                    </tr>

                    {{-- Judul --}}
                    <tr>
                        <td style="padding:18px 32px 0 32px;">
                            <h1 style="margin:0; font-size:22px; line-height:1.35; color:#0F172A;">
                                {{ $headline }}
                            </h1>
                        </td>
                    </tr>

                    {{-- Sapaan & isi --}}
                    <tr>
                        <td style="padding:16px 32px 0 32px; font-size:15px; line-height:1.7; color:#334155;">
                            <p style="margin:0 0 14px 0;">Assalamu&rsquo;alaikum, <strong>{{ $userName }}</strong>,</p>
                            <p style="margin:0 0 14px 0;">{{ $intro }}</p>
                            <p style="margin:0;">
                                Anda dapat melihat detail lengkap proses lamaran Anda melalui dasbor pelamar.
                            </p>
                        </td>
                    </tr>

                    {{-- Tombol CTA --}}
                    <tr>
                        <td style="padding:26px 32px 8px 32px;">
                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="border-radius:10px; background-color:{{ $accent }};">
                                        <a href="{{ $dashboardUrl }}" target="_blank" style="display:inline-block; padding:14px 28px; font-size:14px; font-weight:bold; color:#FFFFFF; text-decoration:none; border-radius:10px;">
                                            Lihat Dashboard
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Catatan --}}
                    <tr>
                        <td style="padding:20px 32px 8px 32px; font-size:13px; line-height:1.6; color:#64748B;">
                            Jika tombol di atas tidak berfungsi, salin dan tempel tautan berikut ke peramban Anda:<br>
                            <a href="{{ $dashboardUrl }}" target="_blank" style="color:#1E3A8A; word-break:break-all;">{{ $dashboardUrl }}</a>
                        </td>
                    </tr>

                    {{-- Pembatas --}}
                    <tr>
                        <td style="padding:24px 32px 0 32px;">
                            <div style="height:1px; background-color:#E2E8F0;"></div>
                        </td>
                    </tr>

                    {{-- Footer biru tua --}}
                    <tr>
                        <td style="background-color:#1E3A8A; padding:22px 32px; margin-top:24px;">
                            <p style="margin:0; font-size:12px; line-height:1.7; color:#BFDBFE;">
                                Email ini dikirim otomatis oleh Sistem Informasi Rekrutmen
                                Ar-Rahman Islamic Center. Mohon tidak membalas email ini.
                            </p>
                            <p style="margin:8px 0 0 0; font-size:11px; color:#93C5FD;">
                                &copy; {{ date('Y') }} Yayasan Generasi Cerdas Akhlak Mulia
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <title>{{ $purpose->label() }}</title>
</head>
<body style="margin:0; padding:0; background-color:#F1F5F9; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#F1F5F9; padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:100%; max-width:600px; background-color:#FFFFFF; border-radius:16px; overflow:hidden; font-family:Arial, Helvetica, sans-serif;">

                    {{-- Header biru tua --}}
                    <tr>
                        <td style="background-color:#1E3A8A; padding:28px 32px;">
                            <div style="font-size:18px; font-weight:bold; color:#FFFFFF; letter-spacing:0.5px;">Ar-Rahman Islamic Center</div>
                            <div style="font-size:11px; color:#BFDBFE; letter-spacing:2px; text-transform:uppercase; padding-top:4px;">E-Recruitment System</div>
                        </td>
                    </tr>

                    {{-- Judul --}}
                    <tr>
                        <td style="padding:32px 32px 0 32px;">
                            <h1 style="margin:0; font-size:22px; line-height:1.35; color:#0F172A;">
                                @if ($purpose->value === 'password_reset')
                                    Atur ulang kata sandi Anda
                                @else
                                    Verifikasi alamat email Anda
                                @endif
                            </h1>
                        </td>
                    </tr>

                    {{-- Sapaan & isi --}}
                    <tr>
                        <td style="padding:16px 32px 0 32px; font-size:15px; line-height:1.7; color:#334155;">
                            <p style="margin:0 0 14px 0;">Halo, <strong>{{ $userName }}</strong>,</p>
                            <p style="margin:0;">
                                @if ($purpose->value === 'password_reset')
                                    Kami menerima permintaan untuk mengatur ulang kata sandi akun Anda.
                                    Masukkan kode berikut untuk melanjutkan. Jika Anda tidak meminta ini,
                                    abaikan saja email ini dan kata sandi Anda tetap aman.
                                @else
                                    Masukkan kode verifikasi berikut untuk mengaktifkan akun dan
                                    mengakses dasbor Anda.
                                @endif
                            </p>
                        </td>
                    </tr>

                    {{-- Kotak kode --}}
                    <tr>
                        <td style="padding:24px 32px 8px 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="background-color:#EFF6FF; border:1px dashed #1E3A8A; border-radius:12px; padding:22px 16px;">
                                        <div style="font-size:12px; color:#1E3A8A; letter-spacing:2px; text-transform:uppercase; padding-bottom:8px;">{{ $purpose->label() }}</div>
                                        <div style="font-family:'Courier New', Courier, monospace; font-size:34px; font-weight:bold; letter-spacing:10px; color:#1E3A8A;">
                                            {{ $code }}
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Catatan masa berlaku --}}
                    <tr>
                        <td style="padding:18px 32px 8px 32px; font-size:13px; line-height:1.6; color:#64748B;">
                            Kode ini berlaku selama <strong>{{ $expiresInMinutes }} menit</strong>. Demi keamanan,
                            jangan bagikan kode ini kepada siapa pun. Jika Anda tidak merasa meminta kode ini,
                            abaikan saja email ini.
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

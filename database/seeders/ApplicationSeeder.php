<?php

namespace Database\Seeders;

use App\Enums\ApplicationStatusEnum;
use App\Models\Application;
use App\Models\CvFile;
use App\Models\CvParsedData;
use App\Models\DegreeCertificationFile;
use App\Models\JobPosting;
use App\Models\User;
use App\Services\NlpScoringService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ApplicationSeeder extends Seeder
{
    /**
     * Lamaran contoh. Setiap lamaran memiliki berkas PDF nyata sehingga
     * pipeline NLP benar-benar dapat dijalankan ulang dari halaman Admin.
     *
     * @var array<int, array<string, mixed>>
     */
    private const APPLICATIONS = [
        [
            'email' => 'fadhil@arrahman.sch.id',
            'job' => 'Guru Pendidikan Agama Islam (PAI)',
            'status' => 'interview',
            'applied_offset' => 21,
            'schedule' => ['offset' => 4, 'time' => '09:00'],
            'cv' => [
                'AHMAD FADHIL RAMADHAN',
                'Ciputat, Tangerang Selatan | 081234567801 | fadhil@arrahman.sch.id',
                '',
                'PROFIL',
                'Lulusan S-1 Pendidikan Agama Islam dan S-2 Manajemen Pendidikan Islam.',
                'Menguasai fikih, aqidah, akhlak, tafsir, hadis, dan sirah nabawiyah.',
                'Terbiasa menerapkan metode pembelajaran aktif dan menyusun kurikulum.',
                '',
                'PENDIDIKAN',
                '2014 - 2018  S-1 Pendidikan Agama Islam, UIN Syarif Hidayatullah Jakarta, IPK 3.71',
                '2020 - 2022  S-2 Manajemen Pendidikan Islam, Universitas Islam Nusantara, IPK 3.84',
                '',
                'PENGALAMAN KERJA',
                '2018 - 2023  Guru Pendidikan Agama Islam, SMP IT Al-Hikmah',
                '- Mengampu fikih, aqidah, akhlak, tafsir dan hadis',
                '- Menyusun modul ajar dan perangkat penilaian sesuai kurikulum',
                '- Menerapkan metode pembelajaran aktif di kelas',
                '- Membimbing ekstrakurikuler tahfidz alquran 30 juz',
                '- Murojaah mingguan dan persiapan lomba hafalan',
                '',
                '2019 - 2021  Pengasuh Kamar, Pesantren Darul Ilmi',
                '- Membina hafalan dan murojaah 120 santri setiap pekan',
                '- Ceramah pagi dan pembinaan akhlak keseharian',
                '- Membina tarikh dan sirah nabawiyah para santri',
                '',
                'KEAHLIAN',
                'Tafsir, Hadis, Fikih, Aqidah, Akhlak, Bahasa Arab, Metode Pembelajaran,',
                'Kurikulum, Sirah Nabawiyah, Murojaah, Ceramah, Tarikh',
                '',
                'SOFT SKILL',
                'Disiplin, tanggung jawab, sabar, kerjasama, dan leadership dalam tim guru.',
            ],
        ],
        [
            'email' => 'siti@arrahman.sch.id',
            'job' => 'Guru Bahasa Arab',
            'status' => 'passed',
            'applied_offset' => 17,
            'schedule' => null,
            'cv' => [
                'SITI MARYAM HALIMAH',
                'Bogor Timur | 081234567802 | siti@arrahman.sch.id',
                '',
                'PROFIL',
                'S-1 Bahasa dan Sastra Arab. Mengajar nahwu, sharaf, dan balaghah',
                'untuk jenjang SMA dan Ma had Aly.',
                '',
                'PENDIDIKAN',
                '2016 - 2020  S-1 Bahasa dan Sastra Arab, IAIN Pontianak, IPK 3.68',
                '',
                'PENGALAMAN KERJA',
                '2020 - sekarang  Guru Bahasa Arab, MA Al-Muttaqin',
                '- Mengampu nahwu dan sharaf kelas XI dan XII',
                '- Menyusun silabus bahan ajar kitab kuning',
                '- Membaca dan menjelaskan turats dengan qawaid',
                '- Melatih maharah: menyimak, berbicara, membaca, menulis',
                '- Menyusun mufrodat dan latihan istimthol mingguan',
                '- Mengampu balaghah dan pembinaan bahasa arab',
                '',
                'KEAHLIAN',
                'Nahwu, Sharaf, Balaghah, Qawaid, Kitab Kuning, Turats, Mufrodat, Maharah',
                '',
                'SOFT SKILL',
                'Komunikasi yang baik, sabar, disiplin, dan kerjasama dengan tim.',
            ],
        ],
        [
            'email' => 'rizky@arrahman.sch.id',
            'job' => 'Staf Administrasi & Tata Usaha',
            'status' => 'applied',
            'applied_offset' => 2,
            'schedule' => null,
            'cv' => [
                'RIZKY PRATAMA SAKTI',
                'Ciputat, Tangerang Selatan | 081234567803 | rizky@arrahman.sch.id',
                '',
                'PROFIL',
                'Lulusan D-3 Administrasi Bisnis dengan pengalaman empat tahun',
                'menjadi staff administrasi dan pengelola arsip.',
                '',
                'PENDIDIKAN',
                '2017 - 2020  D-3 Administrasi Bisnis, Politeknik Negeri Jakarta, IPK 3.42',
                '',
                'PENGALAMAN KERJA',
                '2021 - 2024  Staff Administrasi, PT Sinar Nusantara',
                '- Pengelolaan arsip fisik dan digital',
                '- Entry data pelanggan menggunakan microsoft excel',
                '- Penyusunan laporan bulanan untuk divisi',
                '- Pengarsipan dokumen surat masuk dan surat keluar',
                '- Membuat data entry basis pelanggan dengan microsoft word',
                '',
                'KEAHLIAN',
                'Microsoft Excel, Microsoft Word, pengarsipan, data entry, laporan, organisasi',
                '',
                'SOFT SKILL',
                'Ketelitian tinggi, tanggung jawab, komunikasi, kerjasama, dan disiplin kerja.',
            ],
        ],
        [
            'email' => 'nurul@arrahman.sch.id',
            'job' => 'Guru Bimbingan Konseling',
            'status' => 'hired',
            'applied_offset' => 34,
            'schedule' => ['offset' => -3, 'time' => '13:00'],
            'cv' => [
                'NURUL AINI SAFITRI',
                'Depok, Jawa Barat | 081234567804 | nurul@arrahman.sch.id',
                '',
                'PROFIL',
                'S-1 Bimbingan Konseling dan S-2 Psikologi Pendidikan.',
                'Menangani konseling individual dan kelompok serta penyelesaian konflik siswa.',
                '',
                'PENDIDIKAN',
                '2013 - 2017  S-1 Bimbingan dan Konseling, UIN Sunan Gunung Djati Bandung, IPK 3.77',
                '2019 - 2021  S-2 Psikologi Pendidikan, Universitas Indonesia, IPK 3.89',
                '',
                'PENGALAMAN KERJA',
                '2017 - sekarang  Guru Bimbingan Konseling, SMA Negeri 5 Depok',
                '- Konseling individual dan kelompok siswa',
                '- Program penanganan penyelesaian konflik antar teman sebaya',
                '- Pendampingan prestasi belajar dan perencanaan karier siswa',
                '- Penerapan positif dan problem solving kasus siswa',
                '- Penyusunan motivasi belajar dan target prestasi belajar',
                '',
                'KEAHLIAN',
                'Bimbingan Konseling, Konseling, Psikologi, Problem Solving',
                '',
                'SOFT SKILL',
                'Empati, komunikasi, sabar, tanggung jawab, dan kasih sayang kepada siswa.',
            ],
        ],
        [
            'email' => 'dimas@arrahman.sch.id',
            'job' => 'Guru Pendidikan Agama Islam (PAI)',
            'status' => 'screening',
            'applied_offset' => 6,
            'schedule' => null,
            'cv' => [
                'DIMAS SAPUTRA WIJAYA',
                'Bekasi Timur | 081234567805 | dimas@arrahman.sch.id',
                '',
                'PROFIL',
                'Lulusan S-1 Tarbiyah dengan pengalaman mengajar baca tulis alquran',
                'untuk anak usia dini. Siap bekerja sama dengan tim guru.',
                '',
                'PENDIDIKAN',
                '2018 - 2022  S-1 Tarbiyah, STAI Miftahul Ulum, IPK 3.15',
                '',
                'PENGALAMAN KERJA',
                '2022 - sekarang  Pengajar, Rumah Tahfidz Bina Umat',
                '- Mengajar baca tulis alquran anak usia dini',
                '- Mengelola kegiatan harian kelas dan administrasi kelas',
                '- Menyusun laporan perkembangan siswa setiap bulan',
                '',
                'KEAHLIAN',
                'Mengajar, administrasi kelas, penyusunan laporan',
                '',
                'SOFT SKILL',
                'Sabar, disiplin, tanggung jawab, dan kerjasama.',
            ],
        ],
        [
            'email' => 'fatimah@arrahman.sch.id',
            'job' => 'Guru Bahasa Arab',
            'status' => 'rejected',
            'applied_offset' => 26,
            'schedule' => null,
            'cv' => [
                'FATIMAH ZAHRA AULIA',
                'Bandung | 081234567806 | fatimah@arrahman.sch.id',
                '',
                'PROFIL',
                'Lulusan Pendidikan Guru Sekolah Dasar. Berpengalaman mengajar',
                'kelas 3 dan 4 serta menyusun perangkat pembelajaran harian.',
                '',
                'PENDIDIKAN',
                '2019 - 2023  S-1 Pendidikan Guru Sekolah Dasar, Universitas Pendidikan Indonesia, IPK 3.52',
                '',
                'PENGALAMAN KERJA',
                '2023 - sekarang  Guru Kelas, SD Islam Al-Amin',
                '- Mengajar kelas 3 dan kelas 4',
                '- Menyusun perangkat pembelajaran dan administrasi kelas',
                '- Melaporkan perkembangan siswa kepada wali murid',
                '',
                'SOFT SKILL',
                'Komunikasi, kerjasama, disiplin, dan sabar.',
            ],
        ],
    ];

    /**
     * Jalankan seeder.
     */
    public function run(): void
    {
        $scoring = app(NlpScoringService::class);

        foreach (self::APPLICATIONS as $data) {
            $user = User::where('email', $data['email'])->firstOrFail();
            $job = JobPosting::where('title', $data['job'])->firstOrFail();

            $application = Application::firstOrNew([
                'user_id' => $user->id,
                'job_id' => $job->id,
            ]);

            $application->status = ApplicationStatusEnum::Applied;
            $application->applied_at = now()->subDays($data['applied_offset']);
            $application->score = null;
            $application->save();

            $this->storeBerkas($application, $data['cv']);

            CvParsedData::where('cv_file_id', $application->cvFile?->id)->delete();

            // Lamaran Rizky sengaja dibiarkan belum discoring agar tombol
            // "Mulai Screening" pada panel Admin dapat dicoba.
            if ($data['status'] !== 'applied') {
                $scoring->screen($application);
            }

            $application->update(['status' => ApplicationStatusEnum::from($data['status'])]);

            if ($data['schedule'] !== null) {
                $application->interviewSchedules()->firstOrCreate([], [
                    'interview_date' => now()->addDays($data['schedule']['offset'])->toDateString(),
                    'interview_time' => $data['schedule']['time'],
                    'interview_type' => 'online',
                    'interview_location' => 'https://meet.google.com/arr-rahman-wawancara',
                    'interviewer_name' => 'Drs. Abdul Qadir',
                    'status' => 'scheduled',
                    'notes' => $data['status'] === 'hired'
                        ? 'Sangat memahami materi, sikap profesional, dan cocok dengan budaya kerja unit.'
                        : null,
                ]);
            }
        }
    }

    /**
     * Menulis berkas CV dan ijazah PDF lalu mencatatnya ke basis data.
     *
     * @param  array<int, string>  $cvLines
     */
    private function storeBerkas(Application $application, array $cvLines): void
    {
        $disk = Storage::disk('local');

        $cvPath = 'cv/seed-'.$application->user_id.'-'.$application->job_id.'.pdf';
        $cvContent = $this->makePdf($cvLines);
        $disk->put($cvPath, $cvContent);

        $application->update(['cv_path' => $cvPath]);

        CvFile::updateOrCreate(
            ['application_id' => $application->id],
            [
                'file_name' => 'CV-'.$application->user->name.'.pdf',
                'file_path' => $cvPath,
                'file_size' => strlen($cvContent),
                'mime_type' => 'application/pdf',
                'file_hash' => hash('sha256', $cvContent),
                'uploaded_at' => now(),
            ],
        );

        $ijazahPath = 'ijazah/seed-'.$application->user_id.'-'.$application->job_id.'.pdf';
        $ijazahContent = $this->makePdf([
            'IJAZAH / TRANSKRIP AKADEMIK',
            $application->user->name,
            '',
            'Dokumen ini adalah berkas contoh yang digunakan untuk keperluan',
            'demonstrasi sistem rekrutmen Ar-Rahman Islamic Center.',
            '',
            'Diterbitkan oleh Yayasan Generasi Cerdas Akhlak Mulia.',
        ]);
        $disk->put($ijazahPath, $ijazahContent);

        DegreeCertificationFile::updateOrCreate(
            ['application_id' => $application->id],
            [
                'file_name' => 'Ijazah-'.$application->user->name.'.pdf',
                'file_path' => $ijazahPath,
                'file_size' => strlen($ijazahContent),
                'mime_type' => 'application/pdf',
                'file_hash' => hash('sha256', $ijazahContent),
                'uploaded_at' => now(),
            ],
        );
    }

    /**
     * Membangun berkas PDF satu halaman yang valid tanpa pustaka eksternal.
     *
     * @param  array<int, string>  $lines
     */
    private function makePdf(array $lines): string
    {
        $content = "BT\n/F1 11 Tf\n14 TL\n46 795 Td\n";

        foreach ($lines as $line) {
            $escaped = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $line);
            $content .= "({$escaped}) Tj T*\n";
        }

        $content .= "ET\n";

        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            2 => '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            3 => '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>',
            4 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>',
            5 => '<< /Length '.strlen($content)." >>\nstream\n".$content.'endstream',
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $number => $body) {
            $offsets[$number] = strlen($pdf);
            $pdf .= $number." 0 obj\n".$body."\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 6\n0000000000 65535 f \n";

        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        $pdf .= "trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n{$xrefOffset}\n%%EOF\n";

        return $pdf;
    }
}

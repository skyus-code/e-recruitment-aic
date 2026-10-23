<?php

namespace Database\Seeders;

use App\Enums\JobStatusEnum;
use App\Enums\JobTypeEnum;
use App\Models\Department;
use App\Models\JobPosting;
use Illuminate\Database\Seeder;

class JobSeeder extends Seeder
{
    /**
     * Lowongan formasi beserta kata kunci NLP dan bobotnya.
     *
     * Setiap entri: [keyword, category, weight, is_required].
     */
    public function run(): void
    {
        $jobs = [
            [
                'department' => 'Pendidikan Islam',
                'title' => 'Guru Pendidikan Agama Islam (PAI)',
                'job_type' => JobTypeEnum::FullTime->value,
                'status' => JobStatusEnum::Open->value,
                'salary_min' => 6_500_000,
                'salary_max' => 9_000_000,
                'location' => 'Ciputat, Tangerang Selatan',
                'description' => "Ar-Rahman Islamic Center membuka formasi Guru Pendidikan Agama Islam untuk jenjang MTs dan MA.\n\nBertanggung jawab merancang dan melaksanakan pembelajaran fikih, aqidah, akhlak, serta tafsir dan hadis; menyusun modul ajar sesuai kurikulum; serta membimbing ekstrakurikuler tahfidz Al-Qur'an.\n\nKandidat diharapkan mampu menerapkan metode pembelajaran yang aktif dan menyiapkan penilaian yang objektif bagi setiap siswa.",
                'requirements' => "S-1 Pendidikan Agama Islam, Tarbiyah, atau bidang terkait\nMampu membaca dan mengajar Al-Qur'an serta memahami hadis\nMenguasai fikih, aqidah, akhlak, dan sirah nabawiyah\nPernah mengajar minimal satu tahun (diutamakan)\nMampu menyusun modul ajar dan perangkat penilaian\nJujur, disiplin, dan memiliki tanggung jawab yang tinggi",
                'keywords' => [
                    ['pendidikan agama islam', 'hard_skill', 8, true],
                    ['alquran', 'hard_skill', 7, true],
                    ['hadis', 'hard_skill', 7, true],
                    ['fikih', 'hard_skill', 7, false],
                    ['aqidah', 'hard_skill', 6, false],
                    ['akhlak', 'hard_skill', 5, false],
                    ['bahasa arab', 'hard_skill', 5, false],
                    ['metode pembelajaran', 'hard_skill', 6, false],
                    ['kurikulum', 'hard_skill', 5, false],
                    ['sirah nabawiyah', 'hard_skill', 5, false],
                    ['murojaah', 'hard_skill', 4, false],
                    ['ceramah', 'hard_skill', 3, false],
                    ['tarikh', 'hard_skill', 4, false],
                    ['komunikasi', 'soft_skill', 6, false],
                    ['tanggung jawab', 'soft_skill', 5, false],
                    ['disiplin', 'soft_skill', 4, false],
                    ['sabar', 'soft_skill', 4, false],
                    ['kerjasama', 'soft_skill', 4, false],
                    ['leadership', 'soft_skill', 3, false],
                ],
            ],
            [
                'department' => 'Bahasa',
                'title' => 'Guru Bahasa Arab',
                'job_type' => JobTypeEnum::Contract->value,
                'status' => JobStatusEnum::Open->value,
                'salary_min' => 5_000_000,
                'salary_max' => 7_500_000,
                'location' => 'Ciputat, Tangerang Selatan',
                'description' => "Formasi Guru Bahasa Arab untuk jenjang SMA dan Ma'had Aly.\n\nTugas utama meliputi pengajaran nahwu, sharaf, balaghah, serta pembinaan mufrodat dan kemampuan berbahasa Arab maharah. Kandidat juga diharapkan terbiasa mengajar dengan metode kitab kuning dan turats.",
                'requirements' => "S-1 Bahasa Arab, Sastra Arab, atau bidang terkait\nMenguasai nahwu dan sharaf hingga tingkat lanjut\nMemahami balaghah dan qawaid\nPernah mengajar atau mendampingi halaqah\nJujur, sabar, dan mampu bekerja sama dalam tim",
                'keywords' => [
                    ['nahwu', 'hard_skill', 9, true],
                    ['sharaf', 'hard_skill', 9, true],
                    ['bahasa arab', 'hard_skill', 8, true],
                    ['balaghah', 'hard_skill', 7, false],
                    ['mufrodat', 'hard_skill', 7, false],
                    ['qawaid', 'hard_skill', 6, false],
                    ['kitab kuning', 'hard_skill', 6, false],
                    ['turats', 'hard_skill', 5, false],
                    ['maharah', 'hard_skill', 5, false],
                    ['istimthol', 'hard_skill', 4, false],
                    ['komunikasi', 'soft_skill', 6, false],
                    ['sabar', 'soft_skill', 4, false],
                    ['kreatif', 'soft_skill', 4, false],
                    ['disiplin', 'soft_skill', 4, false],
                    ['kerjasama', 'soft_skill', 4, false],
                ],
            ],
            [
                'department' => 'Tata Usaha & Administrasi',
                'title' => 'Staf Administrasi & Tata Usaha',
                'job_type' => JobTypeEnum::FullTime->value,
                'status' => JobStatusEnum::Open->value,
                'salary_min' => 4_000_000,
                'salary_max' => 5_500_000,
                'location' => 'Ciputat, Tangerang Selatan',
                'description' => "Mengelola arsip fisik dan digital unit kerja, menyusun laporan berkala, serta mendukung kegiatan operasional harian yayasan.\n\nBertanggung jawab atas pengarsipan, entry data, penerimaan dan pendistribusian dokumen, serta penyusunan laporan administrasi bulanan.",
                'requirements' => "D-3/S-1 Administrasi, Manajemen, atau bidang terkait\nMampu mengoperasikan Microsoft Word dan Excel\nTeliti dalam pengarsipan dan entry data\nMampu menyusun laporan secara terstruktur\nBertanggung jawab dan mampu bekerja secara tim",
                'keywords' => [
                    ['administrasi', 'hard_skill', 9, true],
                    ['microsoft excel', 'hard_skill', 9, true],
                    ['microsoft word', 'hard_skill', 7, false],
                    ['pengarsipan', 'hard_skill', 7, false],
                    ['arsip', 'hard_skill', 6, false],
                    ['data entry', 'hard_skill', 6, false],
                    ['laporan', 'hard_skill', 5, false],
                    ['organisasi', 'hard_skill', 4, false],
                    ['ketelitian', 'soft_skill', 8, false],
                    ['tanggung jawab', 'soft_skill', 6, false],
                    ['komunikasi', 'soft_skill', 6, false],
                    ['kerjasama', 'soft_skill', 5, false],
                    ['disiplin', 'soft_skill', 4, false],
                    ['sop', 'soft_skill', 4, false],
                ],
            ],
            [
                'department' => 'Pendidikan Islam',
                'title' => 'Guru Bimbingan Konseling',
                'job_type' => JobTypeEnum::FullTime->value,
                'status' => JobStatusEnum::Open->value,
                'salary_min' => 6_000_000,
                'salary_max' => 8_500_000,
                'location' => 'Ciputat, Tangerang Selatan',
                'description' => "Menangani bimbingan konseling individual dan kelompok siswa, penanganan konflik antar teman sebaya, serta pendampingan prestasi belajar dan perencanaan karier.\n\nKandidat diharapkan memahami perkembangan remaja dan mampu menjadi pendamping yang empatik dan menjaga kerahasiaan.",
                'requirements' => "S-1 Bimbingan Konseling, Psikologi, atau bidang terkait\nMemahami teknik konseling individual dan kelompok\nMampu menangani masalah disiplin dan prestasi belajar\nBerempatik, sabar, dan menjaga kerahasiaan\nPernah menjadi guru BK atau konselor sekolah (diutamakan)",
                'keywords' => [
                    ['bimbingan konseling', 'hard_skill', 9, true],
                    ['konseling', 'hard_skill', 8, true],
                    ['psikologi', 'hard_skill', 7, false],
                    ['disiplin', 'hard_skill', 7, false],
                    ['motivasi', 'hard_skill', 6, false],
                    ['penyelesaian konflik', 'hard_skill', 6, false],
                    ['prestasi belajar', 'hard_skill', 5, false],
                    ['problem solving', 'hard_skill', 5, false],
                    ['empati', 'soft_skill', 7, false],
                    ['komunikasi', 'soft_skill', 7, false],
                    ['kasih sayang', 'soft_skill', 5, false],
                    ['sabar', 'soft_skill', 4, false],
                    ['tanggung jawab', 'soft_skill', 5, false],
                ],
            ],
            [
                'department' => 'Pengembangan Islam (DKM)',
                'title' => 'Pengurus Dakwah & Kegiatan Islam',
                'job_type' => JobTypeEnum::PartTime->value,
                'status' => JobStatusEnum::Closed->value,
                'salary_min' => 3_000_000,
                'salary_max' => 4_500_000,
                'location' => 'Ciputat, Tangerang Selatan',
                'description' => "Merancang dan menjalankan program dakwah serta kegiatan Islam rutin masjid: kajian, agenda Ramadan, dan program komunitas.\n\nBertanggung jawab atas publikasi, dokumentasi, dan koordinasi relawan setiap kegiatan.",
                'requirements' => "Minimal D-3 semua bidang\nMemahami pengelolaan kegiatan dakwah dan event\nMampu mengelola media sosial dan dokumentasi\nKreatif, koordinatif, dan bertanggung jawab",
                'keywords' => [
                    ['dakwah', 'hard_skill', 9, true],
                    ['kegiatan islam', 'hard_skill', 7, false],
                    ['event', 'hard_skill', 6, false],
                    ['media sosial', 'hard_skill', 6, false],
                    ['publikasi', 'hard_skill', 5, false],
                    ['documentation', 'hard_skill', 5, false],
                    ['koordinasi', 'soft_skill', 6, false],
                    ['kerjasama', 'soft_skill', 6, false],
                    ['tanggung jawab', 'soft_skill', 5, false],
                    ['kreatif', 'soft_skill', 5, false],
                ],
            ],
        ];

        foreach ($jobs as $data) {
            $department = Department::where('name', $data['department'])->firstOrFail();

            $job = JobPosting::firstOrNew([
                'department_id' => $department->id,
                'title' => $data['title'],
            ]);

            $job->fill([
                'description' => $data['description'],
                'requirements' => $data['requirements'],
                'salary_min' => $data['salary_min'],
                'salary_max' => $data['salary_max'],
                'location' => $data['location'],
                'job_type' => $data['job_type'],
                'status' => $data['status'],
            ]);

            $job->save();

            foreach ($data['keywords'] as [$keyword, $category, $weight, $required]) {
                $job->keywords()->firstOrCreate(
                    ['keyword' => $keyword],
                    ['category' => $category, 'weight' => $weight, 'is_required' => $required],
                );
            }
        }
    }
}

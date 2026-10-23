<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Akun sistem (Admin HRD & Manajer) plus pelamar contoh lengkap dengan
     * master data profilnya.
     */
    public function run(): void
    {
        $staff = [
            ['name' => 'Hafshah Rahman', 'email' => 'admin@arrahman.sch.id', 'role' => RoleEnum::Admin],
            ['name' => 'Drs. Abdul Qadir', 'email' => 'manajer@arrahman.sch.id', 'role' => RoleEnum::Manajer],
        ];

        foreach ($staff as $member) {
            $user = User::firstOrNew(['email' => $member['email']]);
            $user->name = $member['name'];
            $user->password = 'password';
            $user->password_set_at = now();
            $user->role = $member['role'];
            $user->forceFill(['email_verified_at' => null])->save();
        }

        $pelamar = [
            [
                'name' => 'Ahmad Fadhil Ramadhan',
                'email' => 'fadhil@arrahman.sch.id',
                'detail' => [
                    'gender' => 'Laki-laki',
                    'birth_place' => 'Jakarta',
                    'birth_date' => '1996-04-12',
                    'full_address' => 'Jl. Melati No. 8, Ciputat, Tangerang Selatan',
                    'phone' => '081234567801',
                ],
                'educations' => [
                    ['institution' => 'UIN Syarif Hidayatullah Jakarta', 'degree' => 'S-1', 'major' => 'Pendidikan Agama Islam', 'start_year' => 2014, 'end_year' => 2018, 'gpa' => 3.71],
                    ['institution' => 'Universitas Islam Nusantara', 'degree' => 'S-2', 'major' => 'Manajemen Pendidikan Islam', 'start_year' => 2020, 'end_year' => 2022, 'gpa' => 3.84],
                ],
                'experiences' => [
                    ['company' => 'SMP IT Al-Hikmah', 'position' => 'Guru Pendidikan Agama Islam', 'start_date' => '2018-07-01', 'end_date' => '2023-06-30', 'description' => "Mengampu mata pelajaran Fikih, Aqidah, dan Akhlak.\nMenyusun modul ajar berbasis metode pembelajaran aktif.\nMembimbing ekstrakurikuler Tahfidz Al-Qur'an 30 juz."],
                    ['company' => 'Pesantren Darul Ilmi', 'position' => 'Pengasuh Kamar & Pembina Santri', 'start_date' => '2019-01-01', 'end_date' => '2021-12-31', 'description' => 'Membina hafalan dan murojaah mingguan 120 santri.'],
                ],
                'skills' => [
                    ['skill_name' => 'Tafsir & Hadis', 'level' => 'Mahir'],
                    ['skill_name' => 'Metode Pembelajaran', 'level' => 'Mahir'],
                    ['skill_name' => 'Tahfidz Al-Qur\'an', 'level' => 'Ahli'],
                    ['skill_name' => 'Public Speaking', 'level' => 'Mahir'],
                ],
            ],
            [
                'name' => 'Siti Maryam Halimah',
                'email' => 'siti@arrahman.sch.id',
                'detail' => [
                    'gender' => 'Perempuan',
                    'birth_place' => 'Bogor',
                    'birth_date' => '1998-09-03',
                    'full_address' => 'Jl. Anggrek Raya No. 21, Bogor Timur',
                    'phone' => '081234567802',
                ],
                'educations' => [
                    ['institution' => 'IAIN Pontianak', 'degree' => 'S-1', 'major' => 'Bahasa dan Sastra Arab', 'start_year' => 2016, 'end_year' => 2020, 'gpa' => 3.68],
                ],
                'experiences' => [
                    ['company' => 'MA Al-Muttaqin', 'position' => 'Guru Bahasa Arab', 'start_date' => '2020-08-01', 'end_date' => null, 'description' => "Mengampu Nahwu, Sharaf, dan Balaghah kelas XI-XII.\nMenyusun silabus dan bahan ajar kitab kuning.\nMelatih kemampuan maharah (menyimak, berbicara, membaca, menulis)."],
                ],
                'skills' => [
                    ['skill_name' => 'Nahwu & Sharaf', 'level' => 'Ahli'],
                    ['skill_name' => 'Balaghah', 'level' => 'Mahir'],
                    ['skill_name' => 'Mufrodat', 'level' => 'Mahir'],
                    ['skill_name' => 'Curriculum Design', 'level' => 'Menengah'],
                ],
            ],
            [
                'name' => 'Rizky Pratama Sakti',
                'email' => 'rizky@arrahman.sch.id',
                'detail' => [
                    'gender' => 'Laki-laki',
                    'birth_place' => 'Tangerang',
                    'birth_date' => '1999-01-25',
                    'full_address' => 'Jl. Kenanga No. 3, Ciputat, Tangerang Selatan',
                    'phone' => '081234567803',
                ],
                'educations' => [
                    ['institution' => 'Politeknik Negeri Jakarta', 'degree' => 'D-3', 'major' => 'Administrasi Bisnis', 'start_year' => 2017, 'end_year' => 2020, 'gpa' => 3.42],
                ],
                'experiences' => [
                    ['company' => 'PT Sinar Nusantara', 'position' => 'Staff Administrasi', 'start_date' => '2021-03-01', 'end_date' => '2024-02-29', 'description' => 'Pengelolaan arsip fisik dan digital, penyusunan laporan bulanan, dan entry data pelanggan.'],
                ],
                'skills' => [
                    ['skill_name' => 'Microsoft Excel', 'level' => 'Mahir'],
                    ['skill_name' => 'Pengarsipan', 'level' => 'Mahir'],
                    ['skill_name' => 'Data Entry', 'level' => 'Menengah'],
                ],
            ],
            [
                'name' => 'Nurul Aini Safitri',
                'email' => 'nurul@arrahman.sch.id',
                'detail' => [
                    'gender' => 'Perempuan',
                    'birth_place' => 'Depok',
                    'birth_date' => '1995-11-17',
                    'full_address' => 'Jl. Mawar No. 15, Depok, Jawa Barat',
                    'phone' => '081234567804',
                ],
                'educations' => [
                    ['institution' => 'UIN Sunan Gunung Djati Bandung', 'degree' => 'S-1', 'major' => 'Bimbingan dan Konseling', 'start_year' => 2013, 'end_year' => 2017, 'gpa' => 3.77],
                    ['institution' => 'Universitas Indonesia', 'degree' => 'S-2', 'major' => 'Psikologi Pendidikan', 'start_year' => 2019, 'end_year' => 2021, 'gpa' => 3.89],
                ],
                'experiences' => [
                    ['company' => 'SMA Negeri 5 Depok', 'position' => 'Guru Bimbingan Konseling', 'start_date' => '2017-08-01', 'end_date' => null, 'description' => "Konseling individual dan kelompok siswa.\nProgram penanganan konflik antar teman sebaya.\nPendampingan prestasi belajar dan karier siswa."],
                ],
                'skills' => [
                    ['skill_name' => 'Konseling Individual', 'level' => 'Ahli'],
                    ['skill_name' => 'Problem Solving', 'level' => 'Mahir'],
                    ['skill_name' => 'Empati', 'level' => 'Ahli'],
                    ['skill_name' => 'Psikodiagnostik', 'level' => 'Menengah'],
                ],
            ],
            [
                'name' => 'Dimas Saputra Wijaya',
                'email' => 'dimas@arrahman.sch.id',
                'detail' => [
                    'gender' => 'Laki-laki',
                    'birth_place' => 'Bekasi',
                    'birth_date' => '2000-06-30',
                    'full_address' => 'Jl. Dahlia No. 4, Bekasi Timur',
                    'phone' => '081234567805',
                ],
                'educations' => [
                    ['institution' => 'STAI Miftahul Ulum', 'degree' => 'S-1', 'major' => 'Tarbiyah', 'start_year' => 2018, 'end_year' => 2022, 'gpa' => 3.15],
                ],
                'experiences' => [
                    ['company' => 'Rumah Tahfidz Bina Umat', 'position' => 'Pengajar', 'start_date' => '2022-09-01', 'end_date' => null, 'description' => 'Mengajar baca tulis Al-Qur\'an untuk anak usia dini dan mengelola kegiatan harian kelas.'],
                ],
                'skills' => [
                    ['skill_name' => 'Mengajar', 'level' => 'Menengah'],
                    ['skill_name' => 'Administrasi Kelas', 'level' => 'Menengah'],
                ],
            ],
            [
                'name' => 'Fatimah Zahra Aulia',
                'email' => 'fatimah@arrahman.sch.id',
                'detail' => [
                    'gender' => 'Perempuan',
                    'birth_place' => 'Bandung',
                    'birth_date' => '2001-02-14',
                    'full_address' => 'Jl. Cempaka No. 9, Bandung, Jawa Barat',
                    'phone' => '081234567806',
                ],
                'educations' => [
                    ['institution' => 'Universitas Pendidikan Indonesia', 'degree' => 'S-1', 'major' => 'Pendidikan Guru Sekolah Dasar', 'start_year' => 2019, 'end_year' => 2023, 'gpa' => 3.52],
                ],
                'experiences' => [
                    ['company' => 'SD Islam Al-Amin', 'position' => 'Guru Kelas', 'start_date' => '2023-07-01', 'end_date' => null, 'description' => 'Mengajar kelas 3-4 dan menyusun perangkat pembelajaran harian.'],
                ],
                'skills' => [
                    ['skill_name' => 'Mengajar', 'level' => 'Menengah'],
                    ['skill_name' => 'Lesson Planning', 'level' => 'Menengah'],
                ],
            ],
        ];

        foreach ($pelamar as $data) {
            $user = User::firstOrNew(['email' => $data['email']]);
            $user->name = $data['name'];
            $user->password = 'password';
            $user->password_set_at = now();
            $user->role = RoleEnum::Pelamar;
            $user->phone = $data['detail']['phone'] ?? null;

            // Data contoh untuk pengembangan: akun demo sengaja dibuat sudah
            // terverifikasi agar alur lamaran lama bisa langsung dicoba.
            // Pada aplikasi nyata, verifikasi hanya bisa terjadi lewat kode OTP
            // yang sah (lihat VerificationCodeService), bukan lewat seeder.
            $user->forceFill(['email_verified_at' => null])->save();

            $person = $user->person()->firstOrCreate([]);

            $detail = $data['detail'];
            unset($detail['phone']);
            $person->detailPerson()->updateOrCreate([], $detail);

            $person->educations()->delete();
            foreach ($data['educations'] as $education) {
                $person->educations()->create($education);
            }

            $person->experiences()->delete();
            foreach ($data['experiences'] as $experience) {
                $person->experiences()->create($experience);
            }

            $person->skills()->delete();
            foreach ($data['skills'] as $skill) {
                $person->skills()->create($skill);
            }
        }
    }
}

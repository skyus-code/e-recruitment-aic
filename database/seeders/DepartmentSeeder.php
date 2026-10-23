<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Master data departemen unit kerja Ar-Rahman Islamic Center.
     */
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Pendidikan Islam',
                'address' => 'Jl. Ar-Rahman Raya No. 12, Ciputat, Tangerang Selatan',
                'email' => 'pendidikan@arrahman.sch.id',
                'phone' => '021-7412001',
            ],
            [
                'name' => 'Bahasa',
                'address' => 'Jl. Ar-Rahman Raya No. 12, Ciputat, Tangerang Selatan',
                'email' => 'bahasa@arrahman.sch.id',
                'phone' => '021-7412002',
            ],
            [
                'name' => 'Tata Usaha & Administrasi',
                'address' => 'Gedung Rektorat Lt. 2, Ciputat, Tangerang Selatan',
                'email' => 'tu@arrahman.sch.id',
                'phone' => '021-7412003',
            ],
            [
                'name' => 'Pengembangan Islam (DKM)',
                'address' => 'Masjid Ar-Rahman, Ciputat, Tangerang Selatan',
                'email' => 'dkm@arrahman.sch.id',
                'phone' => '021-7412004',
            ],
        ];

        foreach ($departments as $department) {
            Department::updateOrCreate(['name' => $department['name']], $department);
        }
    }
}

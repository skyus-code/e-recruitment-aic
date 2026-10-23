<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InterviewAssessment extends Model
{
    protected $guarded = [];

    /**
     * Aspek penilaian mengikuti Form Penilaian Pelamar (bobot total 100%).
     *
     * @var array<int, array{code: string, label: string, detail: string, weight: float}>
     */
    public const ASPECTS = [
        ['code' => 'administrasi', 'label' => 'Administrasi / Kelengkapan Berkas', 'detail' => 'CV, ijazah, transkrip, KTP, sertifikat, pas foto lengkap dan valid', 'weight' => 10.0],
        ['code' => 'pendidikan', 'label' => 'Pendidikan', 'detail' => 'Jenjang, jurusan sesuai formasi, IPK, reputasi institusi', 'weight' => 15.0],
        ['code' => 'pengalaman', 'label' => 'Pengalaman Kerja', 'detail' => 'Lama kerja, relevansi bidang, jabatan, pencapaian', 'weight' => 20.0],
        ['code' => 'hard_skill', 'label' => 'Keahlian Teknis (Hard Skill)', 'detail' => 'Kecocokan dengan kata kunci formasi, tingkat penguasaan, sertifikasi', 'weight' => 25.0],
        ['code' => 'soft_skill', 'label' => 'Keahlian Non-Teknis (Soft Skill)', 'detail' => 'Komunikasi, kerja sama tim, problem solving, kepemimpinan', 'weight' => 10.0],
        ['code' => 'tes_kemampuan', 'label' => 'Tes Kemampuan', 'detail' => 'Tes teknis, tes logika/psikotes, studi kasus', 'weight' => 10.0],
        ['code' => 'wawancara', 'label' => 'Wawancara', 'detail' => 'Motivasi, sikap, kesesuaian budaya kerja, kejelasan jawaban', 'weight' => 10.0],
    ];

    public const PASSING_SCORE = 70.0;

    protected function casts(): array
    {
        return [
            'assessed_at' => 'date',
            'final_score' => 'decimal:2',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function decisionOptions(): array
    {
        return [
            'lulus' => 'Lulus',
            'dipertimbangkan' => 'Dipertimbangkan',
            'tidak_lulus' => 'Tidak Lulus',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InterviewAssessmentItem::class);
    }
}

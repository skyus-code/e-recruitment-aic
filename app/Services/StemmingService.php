<?php

namespace App\Services;

use Wamania\Snowball\Stemmer\Stemmer;
use Wamania\Snowball\StemmerFactory;

/**
 * Stemming (penghilangan imbuhan).
 *
 * `wamania/php-stemmer` hanya membawa stemmer Snowball untuk bahasa Eropa
 * (ca, da, nl, en, fi, fr, de, it, no, pt, ro, ru, es, sv) dan tidak menyediakan
 * stemmer Bahasa Indonesia. Karena teks CV mayoritas berbahasa Indonesia,
 * layanan ini menerapkan aturan imbuhan Nazief–Adriani yang disederhanakan,
 * lalu memanfaatkan stemmer Snowball Inggris milik paket tersebut sebagai
 * varian pendukung agar istilah berbahasa Inggris ikut ter-normalisasi.
 *
 * Bentuk utama (`stem`) disimpan ke tabel `data_stemmer`, sementara seluruh
 * varian dipakai oleh NlpScoringService untuk mencocokkan kata kunci lowongan.
 */
class StemmingService
{
    /**
     * Akar kata umum agar pemilihan kandidat imbuhan lebih akurat.
     *
     * @var array<int, string>
     */
    private const ROOT_WORDS = [
        // Pendidikan & pengajaran
        'ajar', 'guru', 'murid', 'siswa', 'didik', 'pendidikan', 'sekolah', 'kelas', 'mapel',
        'pelajaran', 'kurikulum', 'merdeka', 'soal', 'ujian', 'nilai', 'rapor', 'presensi',
        'absen', 'bimbingan', 'konseling', 'perpustakaan', 'laboratorium', 'ekstrakurikuler',
        'kepala', 'wakil', 'walikelas', 'tata', 'usaha', 'tu', 'ekonomi', 'koperasi',
        // Keagamaan
        'quran', 'qur', 'tahfidz', 'tahsin', 'tartil', 'fikih', 'fiqih', 'nahwu', 'sharaf',
        'balagah', 'hadis', 'ushul', 'akhlak', 'adab', 'tarikh', 'sejarah', 'sirah',
        'doa', 'dzikir', 'salat', 'puasa', 'zakat', 'sedekah', 'khatam', 'hafal', 'hafalan',
        // Mata pelajaran
        'matematika', 'fisika', 'kimia', 'biologi', 'akuntansi', 'geografi', 'sosiologi',
        'psikologi', 'agama', 'bahasa', 'inggris', 'arab', 'indonesia', 'jepang', 'melayu',
        'sastra', 'komputer', 'informatika', 'teknologi', 'elektronika',
        // Administrasi & kepegawaian
        'administrasi', 'akademik', 'kesiswaan', 'kepegawaian', 'keuangan', 'kas', 'gaji',
        'tunjangan', 'kontrak', 'karyawan', 'pegawai', 'personel', 'rekrutmen', 'seleksi',
        'wawancara', 'pengembangan', 'pelatihan', 'sertifikat', 'sertifikasi', 'kompetensi',
        'keahlian', 'keterampilan', 'pengalaman', 'referensi', 'berkas', 'dokumen', 'arsip',
        'surat', 'rapat', 'notulen', 'agenda', 'jadwal', 'laporan', 'rencana', 'anggaran',
        'pengadaan', 'vendor', 'proyek', 'kegiatan', 'peserta', 'undangan', 'inventaris',
        'aset', 'gedung', 'kantor', 'ruang', 'operasional', 'umhum', 'humas', 'publikasi',
        'dokumentasi', 'kemitraan', 'kerjasama', 'moU', 'perjanjian', 'piagam', 'penghargaan',
        // Keahlian umum
        'program', 'sistem', 'data', 'basis', 'jaringan', 'server', 'keamanan', 'perawatan',
        'perbaikan', 'instalasi', 'konfigurasi', 'layanan', 'dukungan', 'komunikasi',
        'koordinasi', 'pengawasan', 'pengelolaan', 'pimpin', 'kepemimpinan', 'tim', 'kerja',
        'kreatif', 'inovatif', 'disiplin', 'jujur', 'ramah', 'sabar', 'teliti', 'rapat',
        'desain', 'grafis', 'foto', 'video', 'editing', 'produksi', 'konten', 'media',
        'sosial', 'website', 'halaman', 'aplikasi', 'mobile', 'android', 'cloud', 'backup',
        'pemasaran', 'penjualan', 'promosi', 'logistik', 'gudang', 'persediaan', 'kualitas',
        'standar', 'prosedur', 'evaluasi', 'analisis', 'riset', 'observasi', 'penelitian',
        'karya', 'ilmiah', 'tulis', 'baca', 'hitung', 'bahas', 'dengar', 'bicara', 'paham',
        'pikir', 'kerjakan', 'selesai', 'capai', 'hasil', 'tujuan', 'target', 'strategi',
    ];

    /** @var array<string, true> */
    private array $dictionary;

    private ?Stemmer $englishStemmer = null;

    private bool $englishLoaded = false;

    public function __construct()
    {
        $this->dictionary = array_flip(self::ROOT_WORDS);
    }

    /**
     * Bentuk utama sebuah kata setelah stemming (disimpan ke `data_stemmer`).
     */
    public function stem(string $word): string
    {
        $word = mb_strtolower(trim($word), 'UTF-8');

        if (mb_strlen($word) < 4) {
            return $word;
        }

        $candidates = $this->candidates($word);
        $best = $this->pickBest($word, $candidates);

        return $this->englishStem($best) ?: $best;
    }

    /**
     * Seluruh bentuk normalisasi yang dipakai untuk pencocokan kata kunci.
     *
     * @return array<int, string>
     */
    public function variants(string $word): array
    {
        $word = mb_strtolower(trim($word), 'UTF-8');

        if ($word === '') {
            return [];
        }

        $variants = [$word, ...$this->candidates($word)];

        if ($stemmed = $this->englishStem($word)) {
            $variants[] = $stemmed;
        }

        $variants = array_map(fn (string $value): string => mb_strtolower($value, 'UTF-8'), $variants);

        return array_values(array_unique(array_filter($variants, fn (string $value): bool => mb_strlen($value) >= 3)));
    }

    /**
     * @param  array<int, string>  $words
     * @return array<int, string>
     */
    public function stemMany(array $words): array
    {
        return array_map(fn (string $word): string => $this->stem($word), $words);
    }

    /**
     * Membangun kandidat akar kata lewat penghapusan imbuhan berulang.
     *
     * @return array<int, string>
     */
    private function candidates(string $word): array
    {
        $found = [];

        // Kata berimbuhan ulang, mis. "buku-buku" -> "buku".
        if (str_contains($word, '-')) {
            [$first] = array_slice(explode('-', $word), 0, 1);
            if ($first !== '' && $first !== $word) {
                $found[] = $first;
            }
        }

        $found[] = $word;

        $current = $word;
        for ($i = 0; $i < 3; $i++) {
            $next = $this->stripAffixes($current);
            if ($next === $current) {
                break;
            }
            $found[] = $next;
            $current = $next;
        }

        return array_values(array_unique(array_filter($found, fn (string $value): bool => mb_strlen($value) >= 3)));
    }

    /**
     * Satu putaran penghapusan imbuhan akhir lalu imbuhan awal.
     */
    private function stripAffixes(string $word): string
    {
        return $this->stripPrefix($this->stripSuffix($word));
    }

    private function stripSuffix(string $word): string
    {
        if (mb_strlen($word) <= 4) {
            return $word;
        }

        // Partikel: -lah, -tah, -pun
        foreach (['lah', 'tah', 'pun'] as $suffix) {
            if (str_ends_with($word, $suffix) && mb_strlen($word) - 3 >= 4) {
                return mb_substr($word, 0, -3, 'UTF-8');
            }
        }

        // Kepemilikan: -ku, -mu, -nya
        foreach (['nya', 'ku', 'mu'] as $suffix) {
            if (str_ends_with($word, $suffix) && mb_strlen($word) - mb_strlen($suffix) >= 4) {
                return mb_substr($word, 0, -mb_strlen($suffix), 'UTF-8');
            }
        }

        // Imbuhan akhir: -kan, -an, -i
        foreach (['kan', 'an', 'i'] as $suffix) {
            if (str_ends_with($word, $suffix) && mb_strlen($word) - mb_strlen($suffix) >= 4) {
                $stripped = mb_substr($word, 0, -mb_strlen($suffix), 'UTF-8');

                if ($stripped !== '') {
                    return $stripped;
                }
            }
        }

        return $word;
    }

    private function stripPrefix(string $word): string
    {
        if (mb_strlen($word) <= 4) {
            return $word;
        }

        // meN- (me, meng-, meny-, men-, mem-, mend-, menc-)
        if (str_starts_with($word, 'me')) {
            $rest = mb_substr($word, 2, null, 'UTF-8');
            $first = $rest[0] ?? '';

            if (in_array($first, ['l', 'r', 'w', 'y'], true)) {
                return $rest;
            }

            if (str_starts_with($rest, 'ng')) {
                $sub = mb_substr($rest, 2, null, 'UTF-8');

                return $this->startsVowel($sub) ? ($sub !== '' ? $sub : $rest) : $rest;
            }

            if (str_starts_with($rest, 'ny') || str_starts_with($rest, 'ny')) {
                $sub = mb_substr($rest, 2, null, 'UTF-8');

                return $sub !== '' ? $sub : $rest;
            }

            if (in_array($first, ['n', 'm', 'p', 'b', 't', 'd', 's', 'c', 'j', 'k'], true)) {
                $sub = mb_substr($rest, 1, null, 'UTF-8');

                if ($sub !== '' && ! $this->startsVowel($sub) && mb_strlen($sub) >= 4) {
                    return $sub;
                }

                // me + {huruf} + konsonan lain: buang dua huruf pertama ("memprogram" -> "program")
                if (in_array($first, ['m', 'p', 'b'], true) && $sub !== '' && mb_strlen($sub) >= 4) {
                    return $sub;
                }
            }

            if ($this->startsVowel($rest) && mb_strlen($rest) >= 4) {
                return $rest;
            }

            return $word;
        }

        // peN- (pe, peng-, peny-, pen-, pem-, ped-, pec-)
        if (str_starts_with($word, 'pe')) {
            $rest = mb_substr($word, 2, null, 'UTF-8');
            $first = $rest[0] ?? '';

            if (in_array($first, ['l', 'r', 'w', 'y'], true)) {
                return $rest;
            }

            foreach (['ng', 'ny'] as $digraph) {
                if (str_starts_with($rest, $digraph)) {
                    $sub = mb_substr($rest, strlen($digraph), null, 'UTF-8');

                    return $sub !== '' ? $sub : $rest;
                }
            }

            if (in_array($first, ['n', 'm', 'p', 'b', 't', 'd', 's', 'c', 'j', 'k'], true)) {
                $sub = mb_substr($rest, 1, null, 'UTF-8');

                if ($sub !== '' && mb_strlen($sub) >= 4) {
                    return $sub;
                }
            }

            if ($this->startsVowel($rest) && mb_strlen($rest) >= 4) {
                return $rest;
            }

            return $word;
        }

        $simplePrefixes = ['di', 'ter', 'ber', 'per', 'se', 'ke'];

        foreach ($simplePrefixes as $prefix) {
            if (str_starts_with($word, $prefix)) {
                $sub = mb_substr($word, mb_strlen($prefix), null, 'UTF-8');

                if ($sub !== '' && mb_strlen($sub) >= 3) {
                    // "berlari" -> "lari", tetapi "berita" tetap utuh
                    if ($prefix === 'ber' && str_starts_with($sub, 'l')) {
                        return $sub;
                    }

                    if (in_array($prefix, ['se', 'ke']) && mb_strlen($sub) < 4) {
                        return $word;
                    }

                    return $sub;
                }
            }
        }

        return $word;
    }

    /**
     * Memilih kandidat terbaik: akar kata dari kamus lebih diprioritaskan.
     *
     * @param  array<int, string>  $candidates
     */
    private function pickBest(string $original, array $candidates): string
    {
        if (isset($this->dictionary[$original])) {
            return $original;
        }

        foreach ($candidates as $candidate) {
            if (isset($this->dictionary[$candidate])) {
                return $candidate;
            }
        }

        $longest = $original;
        foreach ($candidates as $candidate) {
            if (mb_strlen($candidate) > mb_strlen($longest)) {
                $longest = $candidate;
            }
        }

        return $longest;
    }

    private function startsVowel(string $value): bool
    {
        return $value !== '' && str_contains('aiueo', $value[0]);
    }

    /**
     * Normalisasi ala Snowball Inggris dari `wamania/php-stemmer`.
     * Mengembalikan string kosong bila stemmer tidak tersedia.
     */
    private function englishStem(string $word): string
    {
        if (! $this->englishLoaded) {
            $this->englishLoaded = true;

            try {
                $this->englishStemmer = StemmerFactory::create('en');
            } catch (\Throwable) {
                $this->englishStemmer = null;
            }
        }

        if ($this->englishStemmer === null || mb_strlen($word) < 4) {
            return '';
        }

        try {
            $stemmed = $this->englishStemmer->stem($word);

            return $stemmed === $word ? '' : $stemmed;
        } catch (\Throwable) {
            return '';
        }
    }
}

<?php

namespace App\Services;

/**
 * Tahap preprocessing pada pipeline NLP: case folding, tokenizing,
 * dan stopword removal sebelum teks masuk ke proses stemming.
 */
class TextPreprocessor
{
    /**
     * Kata-kata umum Bahasa Indonesia yang tidak membawa makna kunci.
     *
     * @var array<int, string>
     */
    private const INDONESIAN_STOPWORDS = [
        'yang', 'di', 'ke', 'dari', 'dan', 'atau', 'untuk', 'dengan', 'pada', 'ini', 'itu',
        'adalah', 'sebagai', 'oleh', 'karena', 'agar', 'akan', 'sudah', 'belum', 'bisa', 'dapat',
        'juga', 'saja', 'hanya', 'tidak', 'bukan', 'ada', 'para', 'kami', 'kita', 'anda',
        'dia', 'mereka', 'saya', 'beliau', 'tersebut', 'seperti', 'hingga', 'sejak', 'jika',
        'sehingga', 'maka', 'namun', 'tetapi', 'masih', 'setiap', 'lain', 'sendiri', 'hal',
        'jadi', 'antara', 'dalam', 'luar', 'atas', 'bawah', 'begitu', 'demikian', 'walau',
        'walaupun', 'meski', 'guna', 'berbagai', 'macam', 'jenis', 'yaitu', 'yakni', 'ialah',
        'termasuk', 'sekitar', 'sebanyak', 'sejumlah', 'seluruh', 'sebagian', 'masing', 'hampir',
        'kurang', 'lebih', 'paling', 'sangat', 'amat', 'telah', 'pernah', 'saat', 'ketika',
        'serta', 'bahkan', 'pun', 'lah', 'tah', 'ku', 'mu', 'nya', 'kalian', 'seseorang',
        'suatu', 'apapun', 'manapun', 'dimana', 'kemana', 'mengapa', 'bagaimana', 'berapa',
        'milik', 'baru', 'samping', 'depan', 'belakang', 'sini', 'sana', 'berikut', 'sebelum',
        'sesudah', 'sampai', 'sepanjang', 'semasa', 'selama', 'sementara', 'sedang', 'lagi',
        'pula', 'juga', 'ialah', 'bila', 'bilamana', 'kecuali', 'mengenai', 'tentang',
    ];

    /**
     * Kata-kata umum Bahasa Inggris yang tidak membawa makna kunci.
     *
     * @var array<int, string>
     */
    private const ENGLISH_STOPWORDS = [
        'the', 'a', 'an', 'and', 'or', 'of', 'to', 'in', 'on', 'for', 'with', 'at', 'by',
        'from', 'is', 'are', 'was', 'were', 'be', 'been', 'being', 'this', 'that', 'these',
        'those', 'it', 'its', 'as', 'but', 'not', 'no', 'will', 'would', 'can', 'could',
        'should', 'may', 'might', 'do', 'does', 'did', 'have', 'has', 'had', 'you', 'your',
        'we', 'our', 'they', 'their', 'he', 'she', 'his', 'her', 'i', 'my', 'me', 'him',
        'them', 'us', 'our', 'which', 'what', 'when', 'where', 'who', 'whom', 'how', 'all',
        'any', 'both', 'each', 'few', 'more', 'most', 'other', 'some', 'such', 'than', 'too',
        'very', 'just', 'about', 'above', 'after', 'again', 'also', 'am', 'because', 'before',
        'between', 'into', 'out', 'over', 'under', 'until', 'up', 'down', 'while', 'during',
        'etc', 'eg', 'ie', 'via', 'per', 'one', 'two', 'new', 'use', 'used', 'using',
    ];

    /** @var array<int, string> */
    private array $stopwords;

    public function __construct()
    {
        $this->stopwords = array_flip([
            ...self::INDONESIAN_STOPWORDS,
            ...self::ENGLISH_STOPWORDS,
        ]);
    }

    /**
     * Case folding: seluruh teks menjadi huruf kecil.
     */
    public function caseFolding(string $text): string
    {
        return mb_strtolower(trim($text), 'UTF-8');
    }

    /**
     * Tokenizing: memecah teks menjadi array kata murni (huruf & angka saja).
     *
     * @return array<int, string>
     */
    public function tokenize(string $text): array
    {
        $folded = $this->caseFolding($text);

        $tokens = preg_split('/[^\p{L}\p{N}]+/u', $folded, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_values(array_filter($tokens, fn (string $token): bool => mb_strlen($token) > 1));
    }

    /**
     * Stopword removal: membuang kata yang tidak bermakna.
     *
     * @param  array<int, string>  $tokens
     * @return array<int, string>
     */
    public function removeStopwords(array $tokens): array
    {
        return array_values(array_filter(
            $tokens,
            fn (string $token): bool => ! isset($this->stopwords[$token]),
        ));
    }

    /**
     * Menjalankan seluruh tahap preprocessing sekaligus.
     *
     * @return array<int, string>
     */
    public function process(string $text): array
    {
        return $this->removeStopwords($this->tokenize($text));
    }

    /**
     * Menghitung frekuensi tiap kata asli.
     *
     * @param  array<int, string>  $tokens
     * @return array<string, int>
     */
    public function frequencies(array $tokens): array
    {
        $counts = [];

        foreach ($tokens as $token) {
            $counts[$token] = ($counts[$token] ?? 0) + 1;
        }

        asort($counts);

        return array_reverse($counts, true);
    }

    /**
     * Memecah teks CV menjadi bagian-bagian berdasarkan judul umum.
     *
     * @return array<string, string> nama bagian => isinya
     */
    public function sections(string $rawText): array
    {
        $headings = [
            'pendidikan' => ['pendidikan', 'education', 'riwayat pendidikan', 'educational background'],
            'pengalaman' => ['pengalaman', 'experience', 'riwayat kerja', 'work experience', 'pengalaman kerja'],
            'keahlian' => ['keahlian', 'skill', 'skills', 'kompetensi', 'kompetensi keahlian'],
            'organisasi' => ['organisasi', 'organization', 'pengalaman organisasi'],
            'pelatihan' => ['pelatihan', 'training', 'kursus', 'sertifikat', 'sertifikasi'],
            'tentang' => ['tentang saya', 'profil', 'about me', 'ringkasan', 'summary', 'biodata'],
            'kontak' => ['kontak', 'contact', 'informasi kontak'],
            'referensi' => ['referensi', 'reference', 'referensi kerja'],
            'publikasi' => ['publikasi', 'publication', 'karya', 'portfolio'],
        ];

        $lines = preg_split('/\r\n|\r|\n/', $rawText) ?: [];
        $sections = ['lainnya' => ''];
        $current = 'lainnya';

        foreach ($lines as $line) {
            $trimmed = trim($line);
            $normalized = $this->caseFolding($trimmed);
            $matchedHeading = null;

            if ($trimmed !== '' && mb_strlen($normalized) <= 45) {
                foreach ($headings as $section => $aliases) {
                    foreach ($aliases as $alias) {
                        if ($normalized === $alias || str_starts_with($normalized, $alias.' ') || str_ends_with($normalized, ' '.$alias)) {
                            $matchedHeading = $section;
                            break 2;
                        }
                    }
                }
            }

            if ($matchedHeading !== null) {
                $current = $matchedHeading;
                $sections[$current] ??= '';

                continue;
            }

            $sections[$current] .= $line."\n";
        }

        return array_map(fn (string $value): string => trim($value), array_filter($sections));
    }

    /**
     * Menentukan dari bagian mana sebuah kata ditemukan pada teks CV.
     */
    public function locateSection(?string $needle, array $sections): ?string
    {
        if ($needle === null || $needle === '' || $sections === []) {
            return null;
        }

        $folded = $this->caseFolding($needle);

        foreach ($sections as $name => $content) {
            if ($content !== '' && str_contains($this->caseFolding($content), $folded)) {
                return $name;
            }
        }

        return null;
    }
}

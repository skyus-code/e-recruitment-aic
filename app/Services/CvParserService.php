<?php

namespace App\Services;

use Smalot\PdfParser\Parser;

/**
 * Ekstraksi teks mentah dari berkas PDF CV menggunakan `smalot/pdfparser`.
 */
class CvParserService
{
    /**
     * Versi parser yang dicatat pada kolom `parser_version`.
     */
    public const PARSER_VERSION = 'smalot/pdfparser v2.12.5';

    public function __construct(private readonly Parser $parser) {}

    /**
     * @return array{raw_text: string, total_words: int, parsing_status: 'success' | 'failed', message: ?string}
     */
    public function parse(string $absolutePath): array
    {
        if (! is_file($absolutePath)) {
            return [
                'raw_text' => '',
                'total_words' => 0,
                'parsing_status' => 'failed',
                'message' => 'Berkas CV tidak ditemukan pada penyimpanan.',
            ];
        }

        if (strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION)) !== 'pdf') {
            return [
                'raw_text' => '',
                'total_words' => 0,
                'parsing_status' => 'failed',
                'message' => 'Hanya berkas PDF yang dapat diparse.',
            ];
        }

        try {
            $document = $this->parser->parseFile($absolutePath);
            $rawText = trim($document->getText());

            if ($rawText === '') {
                return [
                    'raw_text' => '',
                    'total_words' => 0,
                    'parsing_status' => 'failed',
                    'message' => 'PDF tidak memuat teks yang dapat dibaca (kemungkinan hasil scan).',
                ];
            }

            return [
                'raw_text' => $rawText,
                'total_words' => $this->countWords($rawText),
                'parsing_status' => 'success',
                'message' => null,
            ];
        } catch (\Throwable $exception) {
            report($exception);

            return [
                'raw_text' => '',
                'total_words' => 0,
                'parsing_status' => 'failed',
                'message' => 'Gagal mengekstrak teks dari PDF: '.$exception->getMessage(),
            ];
        }
    }

    private function countWords(string $text): int
    {
        $tokens = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return count($tokens);
    }
}

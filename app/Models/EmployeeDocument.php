<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeDocument extends Model
{
    protected $fillable = [
        'employee_id',
        'document_id',
        'document_value',
        'is_requested',
        'created_by'
    ];

    /**
     * Safely decode document_value.
     * Supports:
     * - Single file string: 'file.jpg'
     * - Comma-separated file strings: 'file1.jpg,file2.jpg'
     * - Text-only string: 'some text'
     * - JSON object: {"text":"...", "file":"file1.jpg", "files":["file1.jpg","file2.jpg"]}
     *
     * @param  string|null  $docType  The document_type from Document model ('file','text','both')
     * @return array{text: string|null, file: string|null, files: array}
     */
    public function getParsedValue(string $docType = 'file'): array
    {
        $raw = $this->document_value;

        if (empty($raw)) {
            return ['text' => null, 'file' => null, 'files' => []];
        }

        $text  = null;
        $files = [];

        if ($raw[0] === '{' || $raw[0] === '[') {
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $text = $decoded['text'] ?? null;

                if (isset($decoded['files']) && is_array($decoded['files'])) {
                    $files = array_values(array_filter($decoded['files']));
                } elseif (isset($decoded['file']) && !empty($decoded['file'])) {
                    $files = [$decoded['file']];
                }
            }
        }

        if (empty($files) && empty($text)) {
            if ($docType === 'text') {
                $text = $raw;
            } else {
                if (str_contains($raw, ',')) {
                    $files = array_map('trim', explode(',', $raw));
                } else {
                    $files = [$raw];
                }
            }
        }

        $primaryFile = !empty($files) ? $files[0] : null;

        return [
            'text'  => $text,
            'file'  => $primaryFile,
            'files' => $files,
        ];
    }
}

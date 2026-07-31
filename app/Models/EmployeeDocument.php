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
     * Old records: plain file path string  → returns ['text' => null, 'file' => 'path/to/file.jpg']
     * New text-only records: plain string  → returns ['text' => 'some text', 'file' => null]
     * New both records: JSON string        → returns ['text' => '...', 'file' => '...']
     *
     * @param  string|null  $docType  The document_type from the Document model ('file','text','both')
     * @return array{text: string|null, file: string|null}
     */
    public function getParsedValue(string $docType = 'file'): array
    {
        $raw = $this->document_value;

        if (empty($raw)) {
            return ['text' => null, 'file' => null];
        }

        // Try JSON decode first (new 'both' records)
        if ($raw[0] === '{') {
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return [
                    'text' => $decoded['text'] ?? null,
                    'file' => $decoded['file'] ?? null,
                ];
            }
        }

        // Plain string — determine meaning from document_type
        if ($docType === 'text') {
            return ['text' => $raw, 'file' => null];
        }

        // 'file' or 'both' with old/plain record — treat as file path
        return ['text' => null, 'file' => $raw];
    }
}

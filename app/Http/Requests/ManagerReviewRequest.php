<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ManagerReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ratings'            => ['required', 'array', 'min:1'],
            'ratings.*.area'     => ['required', 'string', 'max:150'],
            'ratings.*.score'    => ['nullable', 'integer', 'between:1,5'],
            'ratings.*.comments' => ['nullable', 'string', 'max:2000'],
            'manager_summary'    => ['nullable', 'string', 'max:4000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $ratings = collect($this->input('ratings', []))
            ->reject(fn ($row) => blank($row['area'] ?? null))
            ->values()
            ->all();

        $this->merge(['ratings' => $ratings]);
    }

    public function attributes(): array
    {
        return [
            'ratings.*.area'     => 'performance area',
            'ratings.*.score'    => 'rating',
            'ratings.*.comments' => 'comments',
        ];
    }
}

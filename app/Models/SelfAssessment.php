<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SelfAssessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'user_id',
        'reviewed_by',
        'employee_name',
        'designation',
        'department',
        'assessment_month',
        'reporting_manager',
        'status',
        'submitted_at',
        'reviewed_at',
        'manager_summary',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'assessment_month' => 'date',
            'submitted_at'     => 'datetime',
            'reviewed_at'      => 'datetime',
        ];
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(AssessmentTask::class)->orderBy('position');
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(AssessmentRating::class)->orderBy('position');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function averageScore(): ?float
    {
        $scores = $this->ratings->whereNotNull('score');

        return $scores->isEmpty() ? null : round($scores->avg('score'), 1);
    }

    public function isEditable(): bool
    {
        if (\Auth::check() && in_array(\Auth::user()->type, ['company', 'hr', 'super admin', 'admin'])) {
            return true;
        }
        return $this->status === 'draft';
    }

    public function monthLabel(): string
    {
        return $this->assessment_month ? $this->assessment_month->format('F Y') : '-';
    }

    public function seedRatingRows(): void
    {
        if ($this->ratings()->count() == 0) {
            $rows = collect(config('self_assessment.performance_areas'))
                ->values()
                ->map(fn (string $area, int $i) => [
                    'position'  => $i + 1,
                    'area'      => $area,
                    'is_custom' => false,
                ])
                ->all();

            $this->ratings()->createMany($rows);
        }
    }
}

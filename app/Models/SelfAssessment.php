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
        'due_date',
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
            'due_date'         => 'date',
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

    public function isOverdue(): bool
    {
        if (!$this->due_date || $this->status === 'reviewed' || $this->status === 'submitted') {
            return false;
        }

        return $this->due_date->isPast() && !$this->due_date->isToday();
    }

    public function deadlineBadgeHtml(): string
    {
        if (!$this->due_date) {
            return '<span class="badge bg-secondary">-</span>';
        }

        $dateStr = $this->due_date->format('d M Y');

        if ($this->status === 'reviewed' || $this->status === 'submitted') {
            return '<span class="badge bg-light-primary text-primary" data-bs-toggle="tooltip" title="' . __('Due Date') . '"><i class="ti ti-calendar me-1"></i>' . $dateStr . '</span>';
        }

        if ($this->isOverdue()) {
            return '<span class="badge bg-danger" data-bs-toggle="tooltip" title="' . __('Overdue Submission') . '"><i class="ti ti-alert-triangle me-1"></i>' . $dateStr . ' (' . __('Overdue') . ')</span>';
        }

        if ($this->due_date->isToday()) {
            return '<span class="badge bg-warning text-dark" data-bs-toggle="tooltip" title="' . __('Due Today') . '"><i class="ti ti-clock me-1"></i>' . $dateStr . ' (' . __('Due Today') . ')</span>';
        }

        return '<span class="badge bg-info text-white" data-bs-toggle="tooltip" title="' . __('Due Date') . '"><i class="ti ti-calendar me-1"></i>' . $dateStr . '</span>';
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

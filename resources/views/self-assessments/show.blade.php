@extends('layouts.admin')

@section('page-title')
    {{ __('Self Assessment') }} - {{ $assessment->monthLabel() }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('self-assessments.index') }}">{{ __('Self Assessment') }}</a></li>
    <li class="breadcrumb-item">{{ $assessment->monthLabel() }}</li>
@endsection

@section('action-button')
    <div class="d-flex align-items-center gap-2">
        @can('Edit Self Assessment')
            @if ($assessment->isEditable())
                <a href="{{ route('self-assessments.edit', $assessment->id) }}" class="btn btn-sm btn-info shadow-sm me-1" data-bs-toggle="tooltip" title="{{ __('Edit Assessment Sheet') }}">
                    <i class="ti ti-pencil me-1"></i> {{ __('Edit Sheet') }}
                </a>
            @endif
        @endcan

        @if ($assessment->status === 'draft')
            {!! Form::open(['method' => 'POST', 'route' => ['self-assessments.submit', $assessment->id], 'id' => 'submit-assessment-form-' . $assessment->id, 'class' => 'd-inline']) !!}
            <a href="javascript:void(0)"
                class="btn btn-sm btn-success shadow-sm bs-pass-para"
                data-bs-toggle="tooltip"
                title="{{ __('Submit for Review') }}"
                data-confirm="{{ __('Submit Assessment?') . '|' . __('Once submitted, you will no longer be able to edit this assessment sheet. Do you want to continue?') }}"
                data-confirm-yes="document.getElementById('submit-assessment-form-{{ $assessment->id }}').submit();">
                <i class="ti ti-send me-1"></i> {{ __('Submit for Review') }}
            </a>
            {!! Form::close() !!}
        @endif

        <a href="{{ route('self-assessments.index') }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="ti ti-arrow-left me-1"></i> {{ __('Back') }}
        </a>
    </div>
@endsection

@section('content')
    @php
        $scale = config('self_assessment.scale', []);
        $ratingRows = $assessment->ratings->map(fn ($r) => [
            'area' => $r->area, 'score' => $r->score, 'comments' => $r->comments,
        ])->values()->all();

        if ($canReview) {
            $ratingRows[] = ['area' => '', 'score' => null, 'comments' => ''];
        }
    @endphp

    <div class="row">
        <!-- Sheet Header Card -->
        <div class="col-12 mb-4">
            <div class="card sys-assessment-card">
                <div class="card-body">
                    <div class="row align-items-center g-3">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center gap-3 mb-2 flex-wrap">
                                <div class="emp-avatar-box">
                                    {{ strtoupper(substr($assessment->employee_name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                        <h4 class="mb-0 text-dark font-weight-bold fs-4">{{ $assessment->employee_name }}</h4>
                                        @if($assessment->status == 'reviewed')
                                            <span class="badge bg-success p-2 px-3 rounded">{{ __('Reviewed') }}</span>
                                        @elseif($assessment->status == 'submitted')
                                            <span class="badge bg-warning p-2 px-3 rounded">{{ __('Submitted for Review') }}</span>
                                        @else
                                            <span class="badge bg-secondary p-2 px-3 rounded">{{ __('Draft') }}</span>
                                        @endif
                                    </div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap mt-2">
                                        @if($assessment->designation)
                                            <span class="badge bg-light text-dark border p-2 px-3"><i class="ti ti-briefcase me-1 text-primary"></i>{{ $assessment->designation }}</span>
                                        @endif
                                        @if($assessment->department)
                                            <span class="badge bg-light text-dark border p-2 px-3"><i class="ti ti-building me-1 text-primary"></i>{{ $assessment->department }}</span>
                                        @endif
                                        @if($assessment->reporting_manager)
                                            <span class="badge bg-light text-dark border p-2 px-3"><i class="ti ti-user-check me-1 text-primary"></i>{{ __('Reporting Manager:') }} <strong>{{ $assessment->reporting_manager }}</strong></span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-3 mt-3 text-muted small flex-wrap">
                                <span class="badge bg-light text-dark border p-2 px-3"><i class="ti ti-calendar me-1 text-primary"></i>{{ __('Assessment Month:') }} <strong>{{ $assessment->monthLabel() }}</strong></span>
                                @if($assessment->due_date)
                                    <span class="badge bg-light text-dark border p-2 px-3"><i class="ti ti-clock me-1 text-primary"></i>{{ __('Submission Deadline:') }} {!! $assessment->deadlineBadgeHtml() !!}</span>
                                @endif
                                @if($assessment->submitted_at)
                                    <span><i class="ti ti-clock me-1 text-warning"></i>{{ __('Submitted:') }} <strong>{{ \Auth::user()->dateFormat($assessment->submitted_at) }}</strong></span>
                                @endif
                                @if($assessment->reviewed_at)
                                    <span><i class="ti ti-circle-check me-1 text-success"></i>{{ __('Reviewed:') }} <strong>{{ \Auth::user()->dateFormat($assessment->reviewed_at) }}</strong></span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end">
                            @if($assessment->averageScore())
                                <div class="bg-light p-3 rounded d-inline-block border text-center" style="min-width: 170px;">
                                    <small class="text-muted d-block fw-bold text-uppercase fs-7 mb-1">{{ __('Overall Score Average') }}</small>
                                    <div class="text-warning mb-1 d-flex align-items-center justify-content-center gap-1">
                                        @for($s = 1; $s <= 5; $s++)
                                            <span class="star-icon {{ $s <= round($assessment->averageScore()) ? 'active' : '' }}" style="cursor: default; font-size: 1.3rem !important;">★</span>
                                        @endfor
                                    </div>
                                    <span class="fs-3 font-weight-bold text-primary">{{ $assessment->averageScore() }} <small class="fs-6 text-muted">/ 5</small></span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 1: Task Summary -->
        <div class="col-12 mb-4">
            <div class="card sys-assessment-card">
                <div class="task-summary-card-header">
                    <div class="d-flex align-items-center gap-2">
                        <h5 class="mb-0 text-dark fw-bold"><i class="ti ti-list-check me-1 text-primary"></i> 1. {{ __('Task Summary') }}</h5>
                        <span class="badge bg-primary text-white rounded-pill px-3 py-1 fw-semibold" id="task-count-badge">{{ $assessment->tasks->count() }} {{ __('Tasks') }}</span>
                    </div>
                </div>
                <div class="card-body bg-light-subtle p-3 p-md-4">
                    <div class="d-flex flex-column gap-3">
                        @forelse($assessment->tasks as $task)
                            <div class="card border shadow-sm mb-0 rounded-3 overflow-hidden bg-white">
                                <div class="card-header bg-white py-2.5 px-3 d-flex align-items-center justify-content-between border-bottom flex-wrap gap-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-primary text-white rounded-circle p-1" style="width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.82rem;">{{ $task->position }}</span>
                                        <span class="fw-bold text-dark fs-6">{{ $task->title }}</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        @if($task->status == 'completed')
                                            <span class="badge bg-success p-1.5 px-3 rounded">{{ __('Completed') }}</span>
                                        @elseif($task->status == 'in_progress')
                                            <span class="badge bg-warning p-1.5 px-3 rounded">{{ __('In Progress') }}</span>
                                        @else
                                            <span class="badge bg-secondary p-1.5 px-3 rounded">{{ __('Pending') }}</span>
                                        @endif

                                        @if($task->priority == 'high')
                                            <span class="badge bg-danger p-1.5 px-3 rounded">{{ __('High') }}</span>
                                        @elseif($task->priority == 'medium')
                                            <span class="badge bg-info p-1.5 px-3 rounded">{{ __('Medium') }}</span>
                                        @else
                                            <span class="badge bg-secondary p-1.5 px-3 rounded">{{ __('Low') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="card-body p-3">
                                    <div class="text-muted small mb-1 fw-semibold text-uppercase fs-7">{{ __('Key Responsibilities & Details') }}</div>
                                    <p class="text-dark mb-0 fs-6" style="line-height: 1.6; white-space: pre-line;">{{ $task->responsibilities ?: '—' }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-4 bg-white rounded border">
                                <i class="ti ti-notes-off fs-2 text-muted d-block mb-1"></i>
                                {{ __('No task summary entries available.') }}
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Manager Rating -->
        <div class="col-12">
            <div class="card sys-assessment-card">
                <div class="task-summary-card-header">
                    <div class="d-flex align-items-center gap-2">
                        <h5 class="mb-0 text-dark fw-bold"><i class="ti ti-star me-1 text-warning"></i> 2. {{ __('Manager Rating & Performance Evaluation') }}</h5>
                    </div>
                    <div class="d-flex align-items-center gap-1 flex-wrap">
                        <small class="fw-bold text-muted me-1">{{ __('Scale:') }}</small>
                        @foreach($scale as $n => $sLabel)
                            <span class="badge bg-white text-dark border px-2 py-1"><strong class="text-primary">{{ $n }}</strong> = {{ __($sLabel) }}</span>
                        @endforeach
                    </div>
                </div>
                <div class="card-body">
                    @if ($canReview)
                        {{ Form::open(['route' => ['self-assessments.review', $assessment->id], 'method' => 'PUT']) }}
                            <div class="table-responsive mb-4">
                                <table class="table align-middle show-task-table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Performance Area') }}</th>
                                            <th width="220px">{{ __('Score (1 - 5)') }}</th>
                                            <th>{{ __('Comments / Evidence') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($ratingRows as $i => $row)
                                            <tr>
                                                <td>
                                                    @if ($row['area'] === '')
                                                        <input type="text" name="ratings[{{ $i }}][area]" value="{{ old("ratings.$i.area") }}" placeholder="{{ __('Add custom performance area (Optional)...') }}" class="form-control form-control-sm">
                                                    @else
                                                        <input type="hidden" name="ratings[{{ $i }}][area]" value="{{ $row['area'] }}">
                                                        <strong class="text-dark">{{ $row['area'] }}</strong>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="star-rating-widget d-flex flex-column align-items-center justify-content-center text-center gap-1">
                                                        <input type="hidden" name="ratings[{{ $i }}][score]" value="{{ old("ratings.$i.score", $row['score']) }}" class="star-score-input">
                                                        <div class="star-picker star-rating-box d-flex align-items-center justify-content-center gap-1">
                                                            @for($s = 1; $s <= 5; $s++)
                                                                @php
                                                                    $currentVal = (int) old("ratings.$i.score", $row['score']);
                                                                    $isFilled = $currentVal >= $s;
                                                                @endphp
                                                                <span class="star-icon {{ $isFilled ? 'active' : '' }}" data-value="{{ $s }}" title="{{ $s }} / 5">★</span>
                                                            @endfor
                                                        </div>
                                                        <span class="star-rating-text small fw-semibold text-secondary">
                                                            @php $curVal = (int) old("ratings.$i.score", $row['score']); @endphp
                                                            @if($curVal > 0 && isset($scale[$curVal]))
                                                                <span class="text-primary fw-bold" style="color: #4f46e5 !important;">{{ $curVal }} - {{ __($scale[$curVal]) }}</span>
                                                            @else
                                                                <span class="text-muted fw-normal fs-7">{{ __('Click stars to rate') }}</span>
                                                            @endif
                                                        </span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <input type="text" name="ratings[{{ $i }}][comments]" value="{{ old("ratings.$i.comments", $row['comments']) }}" placeholder="{{ __('Add manager feedback / comments...') }}" class="form-control form-control-sm">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="form-group mb-3">
                                {{ Form::label('manager_summary', __('Manager Overall Summary & Feedback'), ['class' => 'form-label font-weight-bold']) }}
                                {{ Form::textarea('manager_summary', old('manager_summary', $assessment->manager_summary), ['class' => 'form-control', 'rows' => '3', 'placeholder' => __('Enter overall evaluation summary and growth points...')]) }}
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i> {{ __('Save Manager Review') }}</button>
                            </div>
                        {{ Form::close() }}
                    @else
                        @if($assessment->ratings->count() > 0 && $assessment->status == 'reviewed')
                            <div class="table-responsive mb-4">
                                <table class="table align-middle show-task-table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Performance Area') }}</th>
                                            <th width="220px" class="text-center">{{ __('Score') }}</th>
                                            <th>{{ __('Comments / Feedback') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($assessment->ratings as $rating)
                                            <tr>
                                                <td class="font-weight-bold text-dark">{{ $rating->area }}</td>
                                                <td class="text-center">
                                                    @if($rating->score)
                                                        <div class="d-flex flex-column align-items-center justify-content-center gap-1">
                                                            <div class="star-rating-box">
                                                                @for($s = 1; $s <= 5; $s++)
                                                                    <span class="star-icon {{ $s <= $rating->score ? 'active' : '' }}" style="cursor: default; font-size: 1.4rem !important;">★</span>
                                                                @endfor
                                                            </div>
                                                            <div class="fw-bold small" style="color: #4f46e5 !important;">{{ $rating->score }} - {{ $scale[$rating->score] ?? '' }}</div>
                                                        </div>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td class="text-secondary">{{ $rating->comments ?: '—' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @if($assessment->manager_summary)
                                <div class="bg-light p-3.5 rounded-3 border mb-3">
                                    <h6 class="font-weight-bold text-dark mb-2"><i class="ti ti-message-dots me-1 text-primary"></i> {{ __('Manager Overall Summary:') }}</h6>
                                    <p class="mb-0 text-dark" style="line-height: 1.6;">{{ $assessment->manager_summary }}</p>
                                </div>
                            @endif
                        @else
                            <div class="text-center text-muted py-5">
                                <div class="mb-3">
                                    <span class="badge bg-light rounded-circle p-3 d-inline-flex align-items-center justify-content-center border">
                                        <i class="ti ti-clock fs-2 text-warning"></i>
                                    </span>
                                </div>
                                <h6 class="fw-semibold text-dark mb-1">{{ __('Pending Manager Review') }}</h6>
                                <p class="small text-muted mb-0">{{ __('Manager evaluation and ratings will appear here once reviewed.') }}</p>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css-page')
<style>
    .sys-assessment-card {
        background: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 10px !important;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04) !important;
        overflow: hidden !important;
        margin-bottom: 1.5rem !important;
    }
    .task-summary-card-header {
        background: #f8fafc !important;
        border-bottom: 1px solid #e2e8f0 !important;
        padding: 0.95rem 1.25rem !important;
        min-height: 54px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        flex-wrap: wrap !important;
        gap: 0.75rem !important;
    }
    .task-summary-card-header h5 {
        margin: 0 !important;
        font-size: 1.05rem !important;
        font-weight: 700 !important;
        color: #1e293b !important;
        line-height: 1.4 !important;
        display: inline-flex !important;
        align-items: center !important;
    }
    .task-summary-card-header .badge {
        font-size: 0.76rem !important;
        font-weight: 600 !important;
        padding: 0.35rem 0.8rem !important;
        line-height: 1.2 !important;
        display: inline-flex !important;
        align-items: center !important;
    }
    .sys-assessment-card .card-body {
        background: #ffffff !important;
    }
    .emp-avatar-box {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: #4f46e5;
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.2rem;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
    }
    .bg-primary-soft {
        background-color: #eef2ff !important;
        color: #4338ca !important;
        border: 1px solid #c7d2fe !important;
    }
    .bg-success-soft {
        background-color: #dcfce7 !important;
        color: #15803d !important;
        border: 1px solid #bbf7d0 !important;
    }
    .bg-warning-soft {
        background-color: #fef3c7 !important;
        color: #b45309 !important;
        border: 1px solid #fde68a !important;
    }
    .bg-danger-soft {
        background-color: #fee2e2 !important;
        color: #b91c1c !important;
        border: 1px solid #fecaca !important;
    }
    .bg-info-soft {
        background-color: #e0f2fe !important;
        color: #0369a1 !important;
        border: 1px solid #bae6fd !important;
    }
    .bg-secondary-soft {
        background-color: #f1f5f9 !important;
        color: #475569 !important;
        border: 1px solid #cbd5e1 !important;
    }
    
    .show-task-table {
        margin-bottom: 0 !important;
        border-collapse: separate !important;
        border-spacing: 0 !important;
        width: 100% !important;
    }
    .show-task-table thead th {
        background-color: #f1f5f9 !important;
        color: #334155 !important;
        font-size: 0.76rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.6px !important;
        padding: 0.85rem 1rem !important;
        border-bottom: 2px solid #e2e8f0 !important;
        border-top: none !important;
    }
    .show-task-table tbody tr {
        transition: background-color 0.15s ease-in-out !important;
        border-bottom: 1px solid #f1f5f9 !important;
    }
    .show-task-table tbody tr:hover {
        background-color: #f8fafc !important;
    }
    .show-task-table td {
        padding: 1rem 1rem !important;
        border-top: none !important;
    }
    .row-index-circle {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 28px !important;
        height: 28px !important;
        border-radius: 50% !important;
        background: #e2e8f0 !important;
        color: #1e293b !important;
        font-weight: 700 !important;
        font-size: 0.82rem !important;
    }

    .star-rating-box {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 6px !important;
        user-select: none !important;
    }
    .star-icon {
        font-size: 1.75rem !important;
        line-height: 1 !important;
        cursor: pointer !important;
        transition: transform 0.15s ease, color 0.15s ease !important;
        color: #cbd5e1 !important; /* Solid Light Gray Star */
        display: inline-block !important;
        font-style: normal !important;
    }
    .star-icon.active {
        color: #ffb703 !important; /* Bright Solid Gold Star */
    }
    .star-icon:hover {
        transform: scale(1.3) !important;
    }
</style>
@endpush

@push('script-page')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var scaleLabels = @json($scale ?? []);

        document.querySelectorAll('.star-rating-widget').forEach(function(widget) {
            var hiddenInput = widget.querySelector('.star-score-input');
            var stars = widget.querySelectorAll('.star-icon');
            var labelText = widget.querySelector('.star-rating-text');
            var starPicker = widget.querySelector('.star-picker');

            function renderStars(val) {
                stars.forEach(function(star) {
                    var sVal = parseInt(star.getAttribute('data-value'), 10);
                    if (sVal <= val) {
                        star.classList.add('active');
                    } else {
                        star.classList.remove('active');
                    }
                });

                if (val > 0 && scaleLabels[val]) {
                    labelText.innerHTML = '<span class="fw-bold" style="color: #4f46e5 !important;">' + val + ' - ' + scaleLabels[val] + '</span>';
                } else if (val > 0) {
                    labelText.innerHTML = '<span class="fw-bold" style="color: #4f46e5 !important;">' + val + ' / 5</span>';
                } else {
                    labelText.innerHTML = '<span class="text-muted fw-normal fs-7">{{ __("Click stars to rate") }}</span>';
                }
            }

            stars.forEach(function(star) {
                star.addEventListener('mouseenter', function() {
                    var hoverVal = parseInt(this.getAttribute('data-value'), 10);
                    stars.forEach(function(s) {
                        var v = parseInt(s.getAttribute('data-value'), 10);
                        if (v <= hoverVal) {
                            s.classList.add('active');
                        } else {
                            s.classList.remove('active');
                        }
                    });
                });

                star.addEventListener('click', function() {
                    var clickVal = parseInt(this.getAttribute('data-value'), 10);
                    hiddenInput.value = clickVal;
                    renderStars(clickVal);
                });
            });

            if (starPicker) {
                starPicker.addEventListener('mouseleave', function() {
                    var currentVal = parseInt(hiddenInput.value || 0, 10);
                    renderStars(currentVal);
                });
            }
        });
    });
</script>
@endpush

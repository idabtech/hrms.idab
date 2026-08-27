@php
    $statuses   = config('self_assessment.statuses', []);
    $priorities = config('self_assessment.priorities', []);
    $maxRows    = config('self_assessment.max_task_rows', 20);

    $oldTasks = old('tasks', isset($assessment) && $assessment->exists
        ? $assessment->tasks->map(fn ($t) => [
            'title'            => $t->title,
            'responsibilities' => $t->responsibilities,
            'status'           => $t->status,
            'priority'         => $t->priority,
        ])->values()->all()
        : []);

    if (empty($oldTasks)) {
        $defaultCount = config('self_assessment.default_task_rows', 5);
        for ($i = 0; $i < $defaultCount; $i++) {
            $oldTasks[] = ['title' => '', 'responsibilities' => '', 'status' => 'pending', 'priority' => 'medium'];
        }
    }

    $monthValue   = old('assessment_month', isset($assessment) && $assessment->assessment_month ? $assessment->assessment_month->format('Y-m') : now()->format('Y-m'));
    $dueDateValue = old('due_date', isset($assessment) && $assessment->due_date ? $assessment->due_date->format('Y-m-d') : '');
@endphp

<div class="row">
    <!-- Header Block -->
    <div class="col-12 mb-4">
        <div class="card border shadow-none">
            <div class="card-header bg-light">
                <h6 class="mb-0 font-weight-bold text-dark"><i class="ti ti-user me-1 text-primary"></i> {{ __('Employee & Period Details') }}</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    @if(\Auth::user()->type == 'employee')
                        @if(isset($emp) && $emp)
                            <input type="hidden" name="employee_id" value="{{ $emp->id }}">
                        @elseif(isset($assessment) && $assessment->employee_id)
                            <input type="hidden" name="employee_id" value="{{ $assessment->employee_id }}">
                        @endif
                    @else
                        <div class="form-group col-md-6">
                            {{ Form::label('employee_id', __('Select Employee'), ['class' => 'form-label']) }}<x-required></x-required>
                            {{ Form::select('employee_id', $employees, old('employee_id', $assessment->employee_id), ['class' => 'form-control select2', 'id' => 'emp_select_box', 'required' => 'required']) }}
                        </div>
                    @endif

                    <div class="form-group col-md-6">
                        {{ Form::label('employee_name', __('Employee Name'), ['class' => 'form-label']) }}<x-required></x-required>
                        {{ Form::text('employee_name', old('employee_name', $assessment->employee_name), ['class' => 'form-control', 'id' => 'employee_name', 'required' => 'required', 'placeholder' => __('Enter Employee Name')]) }}
                    </div>

                    <div class="form-group col-md-6">
                        {{ Form::label('designation', __('Designation'), ['class' => 'form-label']) }}<x-required></x-required>
                        @if(isset($designations) && is_array($designations) && count($designations) > 0)
                            {{ Form::select('designation', $designations, old('designation', $assessment->designation), ['class' => 'form-control', 'id' => 'designation', 'required' => 'required']) }}
                        @else
                            {{ Form::text('designation', old('designation', $assessment->designation), ['class' => 'form-control', 'id' => 'designation', 'required' => 'required', 'placeholder' => __('Enter Designation')]) }}
                        @endif
                    </div>

                    <div class="form-group col-md-6">
                        {{ Form::label('department', __('Department / Team'), ['class' => 'form-label']) }}<x-required></x-required>
                        @if(isset($departments) && is_array($departments) && count($departments) > 0)
                            {{ Form::select('department', $departments, old('department', $assessment->department), ['class' => 'form-control', 'id' => 'department', 'required' => 'required']) }}
                        @else
                            {{ Form::text('department', old('department', $assessment->department), ['class' => 'form-control', 'id' => 'department', 'required' => 'required', 'placeholder' => __('Enter Department')]) }}
                        @endif
                    </div>

                    <div class="form-group col-md-6">
                        {{ Form::label('assessment_month', __('Assessment Month'), ['class' => 'form-label']) }}<x-required></x-required>
                        <input type="month" name="assessment_month" id="assessment_month" value="{{ $monthValue }}" class="form-control" required>
                    </div>

                    <div class="form-group col-md-6">
                        {{ Form::label('reporting_manager', __('Reporting Manager'), ['class' => 'form-label']) }}<x-required></x-required>
                        @if(isset($managers) && is_array($managers) && count($managers) > 0)
                            {{ Form::select('reporting_manager', $managers, old('reporting_manager', $assessment->reporting_manager), ['class' => 'form-control select2', 'id' => 'reporting_manager', 'required' => 'required']) }}
                        @else
                            {{ Form::text('reporting_manager', old('reporting_manager', $assessment->reporting_manager), ['class' => 'form-control', 'id' => 'reporting_manager', 'required' => 'required', 'placeholder' => __('Enter Reporting Manager Name')]) }}
                        @endif
                    </div>

                    <div class="form-group col-md-6">
                        {{ Form::label('due_date', __('Submission Deadline Date'), ['class' => 'form-label']) }}
                        <input type="date" name="due_date" id="due_date" value="{{ $dueDateValue }}" class="form-control">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Task Summary Section -->
    <div class="col-12 mb-4">
        <div class="card border shadow-sm rounded-3">
            <div class="card-header bg-light d-flex align-items-center justify-content-between py-3 px-3">
                <div class="d-flex align-items-center gap-2">
                    <h6 class="mb-0 font-weight-bold text-dark"><i class="ti ti-list-check me-1 text-primary"></i> 1. {{ __('Task Summary') }}</h6>
                    <span class="badge bg-primary text-white rounded-pill px-2.5 py-1 small" id="task-count-badge">{{ count($oldTasks) }} {{ __('Tasks') }}</span>
                </div>
                <button type="button" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1 shadow-sm" id="add-task-row-btn">
                    <i class="ti ti-plus"></i> {{ __('Add Task') }}
                </button>
            </div>
            
            <div class="card-body bg-light-subtle p-3 p-md-4">
                <p class="text-muted small mb-3">{{ __('List key tasks/projects worked on this month. Completely blank rows are ignored.') }}</p>
                
                <div id="tasks-container" class="d-flex flex-column gap-3">
                    @foreach($oldTasks as $index => $tRow)
                        <div class="card border shadow-sm task-card mb-0" data-index="{{ $index }}">
                            <div class="card-header bg-white py-2 px-3 d-flex align-items-center justify-content-between border-bottom">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-primary text-white rounded-circle p-1 row-number" style="width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.8rem;">{{ $index + 1 }}</span>
                                    <span class="fw-bold text-dark small">{{ __('Task') }} #<span class="task-num-text">{{ $index + 1 }}</span></span>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-task-row-btn border-0 py-1 px-2" title="{{ __('Remove Task') }}">
                                    <i class="ti ti-trash me-1"></i>{{ __('Remove') }}
                                </button>
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label text-dark fw-semibold small mb-1">{{ __('Task / Project Title') }}</label>
                                        <input type="text" name="tasks[{{ $index }}][title]" value="{{ $tRow['title'] }}" class="form-control form-control-sm" placeholder="{{ __('e.g. Server Maintenance / Feature API') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label text-dark fw-semibold small mb-1">{{ __('Status') }}</label>
                                        <select name="tasks[{{ $index }}][status]" class="form-select form-select-sm task-status-select fw-medium">
                                            @foreach($statuses as $stVal => $stLabel)
                                                <option value="{{ $stVal }}" {{ ($tRow['status'] ?? 'pending') == $stVal ? 'selected' : '' }}>
                                                    {{ __($stLabel) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label text-dark fw-semibold small mb-1">{{ __('Priority') }}</label>
                                        <select name="tasks[{{ $index }}][priority]" class="form-select form-select-sm task-priority-select fw-medium">
                                            @foreach($priorities as $prVal => $prLabel)
                                                <option value="{{ $prVal }}" {{ ($tRow['priority'] ?? 'medium') == $prVal ? 'selected' : '' }}>
                                                    {{ __($prLabel) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label text-dark fw-semibold small mb-1">{{ __('Key Responsibilities & Details') }}</label>
                                        <textarea name="tasks[{{ $index }}][responsibilities]" rows="2" class="form-control form-control-sm" placeholder="{{ __('Key responsibilities and details...') }}">{{ $tRow['responsibilities'] }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@push('css-page')
<style>
    .task-card {
        border-radius: 8px !important;
        transition: all 0.2s ease-in-out;
    }
    .task-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
    }
    .task-status-select, .task-priority-select {
        cursor: pointer;
    }

    /* Suppress inline green checkmark icons and green borders on form validation */
    .was-validated .form-control:valid,
    .was-validated .form-select:valid,
    .form-control.is-valid,
    .form-select.is-valid,
    .was-validated .select2-container--default .select2-selection--single {
        border-color: #ced4da !important;
        background-image: none !important;
        box-shadow: none !important;
        padding-right: 0.75rem !important;
    }
</style>
@endpush

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var container = document.getElementById('tasks-container');
        var addBtn = document.getElementById('add-task-row-btn');
        var maxRows = {{ $maxRows }};

        function updateRowNumbers() {
            if (!container) return;
            var cards = container.querySelectorAll('.task-card');
            cards.forEach(function(card, idx) {
                card.setAttribute('data-index', idx);
                var numBadge = card.querySelector('.row-number');
                if (numBadge) numBadge.textContent = idx + 1;

                var numText = card.querySelector('.task-num-text');
                if (numText) numText.textContent = idx + 1;

                var titleInput = card.querySelector('input[name*="[title]"]');
                if (titleInput) titleInput.setAttribute('name', 'tasks[' + idx + '][title]');

                var respInput = card.querySelector('textarea[name*="[responsibilities]"]');
                if (respInput) respInput.setAttribute('name', 'tasks[' + idx + '][responsibilities]');

                var statusSelect = card.querySelector('select[name*="[status]"]');
                if (statusSelect) statusSelect.setAttribute('name', 'tasks[' + idx + '][status]');

                var prioritySelect = card.querySelector('select[name*="[priority]"]');
                if (prioritySelect) prioritySelect.setAttribute('name', 'tasks[' + idx + '][priority]');
            });

            var countBadge = document.getElementById('task-count-badge');
            if (countBadge) {
                countBadge.textContent = cards.length + " {{ __('Tasks') }}";
            }
        }

        if (addBtn) {
            addBtn.addEventListener('click', function() {
                var currentCount = container.querySelectorAll('.task-card').length;
                if (currentCount >= maxRows) {
                    alert("{{ __('Maximum rows limit reached') }} (" + maxRows + ")");
                    return;
                }

                var nextIdx = currentCount;
                var card = document.createElement('div');
                card.className = 'card border shadow-sm task-card mb-0';
                card.setAttribute('data-index', nextIdx);
                card.innerHTML = `
                    <div class="card-header bg-white py-2 px-3 d-flex align-items-center justify-content-between border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary text-white rounded-circle p-1 row-number" style="width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.8rem;">${nextIdx + 1}</span>
                            <span class="fw-bold text-dark small">{{ __('Task') }} #<span class="task-num-text">${nextIdx + 1}</span></span>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-task-row-btn border-0 py-1 px-2" title="{{ __('Remove Task') }}">
                            <i class="ti ti-trash me-1"></i>{{ __('Remove') }}
                        </button>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-dark fw-semibold small mb-1">{{ __('Task / Project Title') }}</label>
                                <input type="text" name="tasks[${nextIdx}][title]" class="form-control form-control-sm" placeholder="{{ __('e.g. Server Maintenance / Feature API') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-dark fw-semibold small mb-1">{{ __('Status') }}</label>
                                <select name="tasks[${nextIdx}][status]" class="form-select form-select-sm task-status-select fw-medium">
                                    @foreach($statuses as $stVal => $stLabel)
                                        <option value="{{ $stVal }}" ${'{{ $stVal }}' === 'pending' ? 'selected' : ''}>{{ __($stLabel) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-dark fw-semibold small mb-1">{{ __('Priority') }}</label>
                                <select name="tasks[${nextIdx}][priority]" class="form-select form-select-sm task-priority-select fw-medium">
                                    @foreach($priorities as $prVal => $prLabel)
                                        <option value="{{ $prVal }}" ${'{{ $prVal }}' === 'medium' ? 'selected' : ''}>{{ __($prLabel) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label text-dark fw-semibold small mb-1">{{ __('Key Responsibilities & Details') }}</label>
                                <textarea name="tasks[${nextIdx}][responsibilities]" rows="2" class="form-control form-control-sm" placeholder="{{ __('Key responsibilities and details...') }}"></textarea>
                            </div>
                        </div>
                    </div>
                `;
                container.appendChild(card);
                updateRowNumbers();
            });
        }

        if (container) {
            container.addEventListener('click', function(e) {
                var removeBtn = e.target.closest('.remove-task-row-btn');
                if (removeBtn) {
                    var cards = container.querySelectorAll('.task-card');
                    if (cards.length <= 1) {
                        alert("{{ __('At least one task must remain.') }}");
                        return;
                    }
                    var card = removeBtn.closest('.task-card');
                    if (card) {
                        card.remove();
                        updateRowNumbers();
                    }
                }
            });
        }
    });
</script>

@push('script-page')
<script>
    (function initEmpDetailsFill() {
        var attempts = 0;
        function runFill() {
            if (typeof jQuery === 'undefined') {
                if (attempts < 50) {
                    attempts++;
                    setTimeout(runFill, 100);
                }
                return;
            }

            var $ = jQuery;
            var employeeMap = @json($employeeData ?? []);

            function setFieldValue($field, val) {
                if (!$field || !$field.length) return;
                val = (val || '').toString().trim();

                if ($field.is('select')) {
                    if (val) {
                        var matchedVal = null;
                        $field.find('option').each(function() {
                            var optVal = $(this).val().toString().trim();
                            var optText = $(this).text().toString().trim();
                            if (optVal.toLowerCase() === val.toLowerCase() || optText.toLowerCase() === val.toLowerCase()) {
                                matchedVal = $(this).val();
                                return false;
                            }
                        });

                        if (matchedVal !== null) {
                            $field.val(matchedVal);
                        } else {
                            var newOpt = new Option(val, val, true, true);
                            $field.append(newOpt).val(val);
                        }
                    } else {
                        $field.val('');
                    }
                    $field.trigger('change');
                    if ($field.hasClass('select2-hidden-accessible') || $field.data('select2')) {
                        $field.trigger('change.select2');
                    }
                } else {
                    $field.val(val).trigger('change');
                }
            }

            function fillEmployeeDetails(empId) {
                if (empId && employeeMap[empId]) {
                    var emp = employeeMap[empId];
                    setFieldValue($('#employee_name'), emp.name);
                    setFieldValue($('#designation'), emp.designation);
                    setFieldValue($('#department'), emp.department);
                }
            }

            $(document).on('change select2:select', '#emp_select_box', function() {
                var empId = $(this).val();
                fillEmployeeDetails(empId);
            });

            var initialEmpId = $('#emp_select_box').val();
            if (initialEmpId && employeeMap[initialEmpId]) {
                fillEmployeeDetails(initialEmpId);
            }
        }

        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            runFill();
        } else {
            document.addEventListener('DOMContentLoaded', runFill);
        }
    })();
</script>
@endpush

@php
    $plan = App\Models\Utility::getChatGPTSettings();

    // Pre-build a map of saved day details keyed by date string
    $savedDetails = $leave->dayDetails->keyBy(fn($d) => \Carbon\Carbon::parse($d->date)->format('Y-m-d'));

    // Determine default day_status based on leave type's is_paid flag
    $currentLeaveType = $leavetypes->firstWhere('id', $leave->leave_type_id);
    $defaultDayStatus = ($currentLeaveType && $currentLeaveType->is_paid) ? 'paid' : 'unpaid';

    // Build the list of days in the current leave range
    $editDays = [];
    if ($leave->start_date && $leave->end_date) {
        $period = \Carbon\CarbonPeriod::create($leave->start_date, $leave->end_date);
        foreach ($period as $day) {
            $dk = $day->format('Y-m-d');
            $savedRow = $savedDetails[$dk] ?? null;
            $editDays[] = [
                'date'                    => $dk,
                'day_name'                => $day->format('D'),
                'day_duration'            => $savedRow ? $savedRow->day_duration            : 'full_day',
                'half_day_period'         => $savedRow ? $savedRow->half_day_period         : 'morning',
                'day_status'              => $savedRow ? $savedRow->day_status              : $defaultDayStatus,
                'sandwich_leave_rule_id'  => $savedRow ? $savedRow->sandwich_leave_rule_id  : null,
            ];
        }
    }
@endphp

{{ Form::model($leave, ['route' => ['leave.update', $leave->id], 'method' => 'PUT', 'class' => 'needs-validation', 'novalidate', 'id' => 'leave-edit-form-' . $leave->id]) }}
<div class="modal-body">

    @if ($plan->enable_chatgpt == 'on')
    <div class="card-footer text-end mb-2">
        <a href="javascript:void(0)" class="btn btn-sm btn-primary" data-size="medium"
            data-ajax-popup-over="true" data-url="{{ route('generate', ['leave']) }}"
            data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Generate') }}"
            data-title="{{ __('Generate Content With AI') }}">
            <i class="fas fa-robot"></i>{{ __(' Generate With AI') }}
        </a>
    </div>
    @endif

    {{-- Employee --}}
    @if (\Auth::user()->type != 'employee')
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('employee_id', __('Employee'), ['class' => 'col-form-label']) }}<x-required></x-required>
                {{ Form::select('employee_id', $employees, null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Select Employee')]) }}
            </div>
        </div>
    </div>
    @else
    {!! Form::hidden('employee_id', !empty($employees) ? $employees->id : 0) !!}
    @endif

    {{-- Leave Type --}}
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('leave_type_id', __('Leave Type'), ['class' => 'col-form-label']) }}<x-required></x-required>
                <select name="leave_type_id" class="form-control select" required>
                    @foreach ($leavetypes as $ltype)
                        <option value="{{ $ltype->id }}"
                            {{ $leave->leave_type_id == $ltype->id ? 'selected' : '' }}
                            data-is-paid="{{ $ltype->is_paid ? 1 : 0 }}"
                            data-days="{{ $ltype->days }}">
                            {{ $ltype->title }} ({{ $ltype->days }})
                        </option>
                    @endforeach
                </select>
                <div class="leave-type-info mt-2">
                    @php $curType = $leavetypes->firstWhere('id', $leave->leave_type_id); @endphp
                    @if ($curType)
                        <span class="leave-type-pay-badge">
                            @if ($curType->is_paid)
                                <span class="badge bg-success"><i class="ti ti-circle-check me-1"></i>{{ __('Paid Leave') }}</span>
                            @else
                                <span class="badge bg-danger"><i class="ti ti-circle-x me-1"></i>{{ __('Unpaid Leave') }}</span>
                            @endif
                        </span>
                    @else
                        <span class="leave-type-pay-badge"></span>
                    @endif
                    <span class="leave-type-remaining ms-2 text-muted small"></span>
                </div>
            </div>
        </div>
    </div>

    {{-- Sandwich Leave Notice (Shown ONLY when leave spans across weekend/holiday) --}}
    <div id="sandwich_leave_notice" class="alert alert-warning border-warning align-items-center mb-3" style="display:none;">
        <div class="d-flex align-items-center">
            <i class="ti ti-alert-triangle fs-4 me-2 text-warning"></i>
            <div>
                <strong>{{ __('Sandwich Leave Notice') }}:</strong>
                <span>{{ __('This leave application includes intervening Day Off / Weekend dates. Sandwich Leave rules apply.') }}</span>
            </div>
        </div>
    </div>

    {{-- Date range --}}
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('start_date', __('Start Date'), ['class' => 'col-form-label']) }}<x-required></x-required>
                {{ Form::date('start_date', null, ['class' => 'form-control', 'required' => 'required', 'autocomplete' => 'off']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('end_date', __('End Date'), ['class' => 'col-form-label']) }}<x-required></x-required>
                {{ Form::date('end_date', null, ['class' => 'form-control', 'required' => 'required', 'autocomplete' => 'off']) }}
            </div>
        </div>
    </div>

    {{-- ── Per-day breakdown table ─────────────────────────────────────────── --}}
    <div class="day-breakdown-wrapper mb-2">
        <label class="col-form-label fw-bold">{{ __('Day-wise Leave Details') }}</label>
        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Day') }}</th>
                        <th>{{ __('Duration') }}</th>
                        <th>{{ __('Period') }}</th>
                        <th>{{ __('Status') }}</th>
                        @if (\Auth::user()->type != 'employee')
                            <th>{{ __('Sandwich Rule') }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="day-breakdown-body">
                    @foreach ($editDays as $eDay)
                        @php
                            $dk     = $eDay['date'];
                            $dname  = $eDay['day_name'];
                            $dur    = $eDay['day_duration'];
                            $period = $eDay['half_day_period'] ?? 'morning';
                            $status = $eDay['day_status'];
                            $uid    = str_replace('-', '_', $dk) . '_' . $leave->id;
                        @endphp
                        <tr data-date="{{ $dk }}">
                            <td><small>{{ $dk }}</small></td>
                            <td><small>{{ $dname }}</small></td>
                            <td>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input dur-radio" type="radio"
                                        name="day_duration[{{ $dk }}]" id="full_{{ $uid }}" value="full_day"
                                        {{ $dur === 'full_day' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="full_{{ $uid }}">{{ __('Full') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input dur-radio" type="radio"
                                        name="day_duration[{{ $dk }}]" id="half_{{ $uid }}" value="half_day"
                                        {{ $dur === 'half_day' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="half_{{ $uid }}">{{ __('Half') }}</label>
                                </div>
                            </td>
                            <td class="period-cell">
                                <span class="period-dash" style="{{ $dur === 'half_day' ? 'display:none;' : '' }}">-</span>
                                <select name="half_day_period_day[{{ $dk }}]" class="form-control form-control-sm period-select" style="{{ $dur !== 'half_day' ? 'display:none;' : '' }}">
                                    <option value="morning" {{ $period === 'morning' ? 'selected' : '' }}>{{ __('Morning') }}</option>
                                    <option value="afternoon" {{ $period === 'afternoon' ? 'selected' : '' }}>{{ __('Afternoon') }}</option>
                                </select>
                            </td>
                            <td>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input stat-radio" type="radio"
                                        name="day_status[{{ $dk }}]" id="paid_{{ $uid }}" value="paid"
                                        {{ $status === 'paid' ? 'checked' : '' }}>
                                    <label class="form-check-label text-success" for="paid_{{ $uid }}">{{ __('Paid') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input stat-radio" type="radio"
                                        name="day_status[{{ $dk }}]" id="unpaid_{{ $uid }}" value="unpaid"
                                        {{ $status === 'unpaid' ? 'checked' : '' }}>
                                    <label class="form-check-label text-danger" for="unpaid_{{ $uid }}">{{ __('Unpaid') }}</label>
                                </div>
                            </td>
                            @if (\Auth::user()->type != 'employee')
                                <td>
                                    @php
                                        $curRuleId = $eDay['sandwich_leave_rule_id'] ?? $leave->sandwich_leave_rule_id;
                                    @endphp
                                    <select name="sandwich_leave_rule_id[{{ $dk }}]" class="form-select form-select-sm">
                                        <option value="">{{ __('None') }}</option>
                                        @foreach ($sandwichRules as $srule)
                                            <option value="{{ $srule->id }}" {{ $curRuleId == $srule->id ? 'selected' : '' }}>
                                                {{ $srule->name }} ({{ number_format($srule->deduction_rate, 2) }}{{ $srule->rate_type == 'percentage' ? '%' : 'x' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-1 text-muted small day-summary"></div>
    </div>

    {{-- Leave Note --}}
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('leave_reason', __('Leave Note'), ['class' => 'col-form-label']) }}<x-required></x-required>
                {{ Form::textarea('leave_reason', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Leave Reason'), 'rows' => '3']) }}
            </div>
        </div>
    </div>

    {{-- Remark --}}
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('remark', __('Remark'), ['class' => 'col-form-label']) }}<x-required></x-required>
                @if ($plan->enable_chatgpt == 'on')
                <a href="javascript:void(0)" data-size="md" class="btn btn-primary btn-icon btn-sm"
                    data-ajax-popup-over="true" id="grammarCheck"
                    data-url="{{ route('grammar', ['grammar']) }}" data-bs-placement="top"
                    data-title="{{ __('Grammar check with AI') }}">
                    <i class="ti ti-rotate"></i> <span>{{ __('Grammar check with AI') }}</span>
                </a>
                @endif
                {{ Form::textarea('remark', null, ['class' => 'form-control grammer_textarea', 'required' => 'required', 'placeholder' => __('Leave Remark'), 'rows' => '3']) }}
            </div>
        </div>
    </div>

    {{-- Leave Status (HR/Admin only, not own leave) --}}
    @php
        $canChangeStatus = in_array(strtolower(\Auth::user()->type), ['company', 'hr', 'admin'])
                           || \Auth::user()->can('Edit Leave')
                           || \Auth::user()->hasRole('hr');
        $actorEmpId = optional(\Auth::user()->employee)->id ?? null;
        $isOwnLeave = $actorEmpId && $actorEmpId == $leave->employee_id;
    @endphp
    @if ($canChangeStatus && !$isOwnLeave)
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('status', __('Leave Status'), ['class' => 'col-form-label']) }}
                <select name="status" class="form-control">
                    <option value="Pending" {{ $leave->status == 'Pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                    <option value="Approved" {{ $leave->status == 'Approved' ? 'selected' : '' }}>{{ __('Approved') }}</option>
                    <option value="Reject" {{ $leave->status == 'Reject' ? 'selected' : '' }}>{{ __('Reject') }}</option>
                </select>
                <small class="text-muted">{{ __('Change the leave status directly from here.') }}</small>
            </div>
        </div>
    </div>
    @endif

</div>
<div class="modal-footer">
    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
</div>
{{ Form::close() }}

<script>
(function () {
    'use strict';

    var $form = $('#leave-edit-form-{{ $leave->id }}');
    if (!$form.length) return;

    var phpDateFormat = '{{ \App\Models\Utility::settings()['site_date_format'] ?? 'd-m-Y' }}';
    var dayNames = ['{{ __("Sun") }}','{{ __("Mon") }}','{{ __("Tue") }}','{{ __("Wed") }}','{{ __("Thu") }}','{{ __("Fri") }}','{{ __("Sat") }}'];

    var savedDetails = {
        @foreach ($leave->dayDetails as $det)
        "{{ \Carbon\Carbon::parse($det->date)->format('Y-m-d') }}": {
            day_duration: "{{ $det->day_duration }}",
            half_day_period: "{{ $det->half_day_period ?? 'morning' }}",
            day_status: "{{ $det->day_status }}",
            sandwich_leave_rule_id: "{{ $det->sandwich_leave_rule_id ?? '' }}"
        },
        @endforeach
        @if ($leave->dayDetails->isEmpty() && $leave->start_date && $leave->end_date)
        @php
            $fbStatus = ($currentLeaveType && $currentLeaveType->is_paid) ? 'paid' : 'unpaid';
            $fbDur = ($leave->leave_duration === 'half_day') ? 'half_day' : 'full_day';
            $fbPeriod = $leave->half_day_period ?? 'morning';
            $fbDays = \Carbon\CarbonPeriod::create($leave->start_date, $leave->end_date);
        @endphp
        @foreach ($fbDays as $fbDay)
        "{{ $fbDay->format('Y-m-d') }}": { day_duration: "{{ $fbDur }}", half_day_period: "{{ $fbPeriod }}", day_status: "{{ $fbStatus }}" },
        @endforeach
        @endif
    };

    var leaveTypeData = {};

    // ── Init: load leave type info via AJAX (don't rebuild day table) ───────
    setTimeout(function () {
        var eid = $form.find('[name="employee_id"]').val();
        if (eid) loadLeaveTypes(eid, true);
        updateSummary();
    }, 200);

    // ── Scoped events ──────────────────────────────────────────────────────
    $form.on('change', '[name="start_date"], [name="end_date"]', function () {
        buildBreakdown();
    });

    $form.on('change', '[name="leave_type_id"]', function () {
        if (!$(this).val()) return;
        updateLeaveTypeInfo($(this).val());
        buildBreakdown();
    });

    $form.on('change', '#sandwich_leave_rule_id', function () {
        var $opt = $(this).find('option:selected');
        var rate = $opt.data('rate') || '';
        $form.find('#sandwich_deduction_rate').val(rate);
    });

    $form.on('change', '[name="employee_id"]', function () {
        var eid = $(this).val();
        if (eid) loadLeaveTypes(eid, false);
    });

    $form.on('change', '.dur-radio', function () {
        var isHalf = $(this).val() === 'half_day';
        var $cell = $(this).closest('tr').find('.period-cell');
        $cell.find('.period-dash').toggle(!isHalf);
        $cell.find('.period-select').toggle(isHalf);
        updateSummary();
    });

    $form.on('change', '.stat-radio', updateSummary);

    // ── AJAX: load leave type balances ──────────────────────────────────────
    function loadLeaveTypes(employeeId, isInitial) {
        $.ajax({
            url: '{{ route('leave.jsoncount') }}',
            type: 'POST',
            data: { employee_id: employeeId, _token: '{{ csrf_token() }}' },
            success: function (data) {
                leaveTypeData = {};
                var $select = $form.find('[name="leave_type_id"]');
                var oldval = $select.val();
                $select.empty();

                $.each(data, function (key, value) {
                    leaveTypeData[value.id] = {
                        is_paid: value.is_paid ? 1 : 0,
                        remaining: parseFloat(value.remaining),
                        days: parseFloat(value.days),
                        total_leave: parseFloat(value.total_leave),
                    };
                    var label = value.title + ' (' + value.total_leave + '/' + value.days
                              + ' {{ __("used") }}, {{ __("Remaining") }}: ' + value.remaining + ')';
                    var disabled = (value.remaining <= 0 && value.is_paid && oldval != value.id) ? ' disabled' : '';
                    $select.append('<option value="' + value.id + '"' + disabled
                        + ' data-is-paid="' + (value.is_paid ? 1 : 0) + '"'
                        + ' data-days="' + value.days + '">' + label + '</option>');
                    if (oldval && oldval == value.id) {
                        $select.find('option[value="' + oldval + '"]').prop('selected', true);
                    }
                });

                updateLeaveTypeInfo($select.val());
                // Only rebuild on subsequent changes, NOT on initial load
                if (!isInitial) buildBreakdown();
            }
        });
    }

    // ── Helpers ─────────────────────────────────────────────────────────────
    function formatDate(dateObj) {
        var yyyy = dateObj.getFullYear(), mm = String(dateObj.getMonth()+1).padStart(2,'0'),
            dd = String(dateObj.getDate()).padStart(2,'0');
        return phpDateFormat.replace('Y',yyyy).replace('y',String(yyyy).slice(-2))
            .replace('m',mm).replace('n',String(dateObj.getMonth()+1))
            .replace('d',dd).replace('j',String(dateObj.getDate()));
    }

    function updateLeaveTypeInfo(id) {
        var $info = $form.find('.leave-type-info');
        if (!id) { $info.hide(); return; }
        var d = leaveTypeData[id];
        var opt = $form.find('[name="leave_type_id"] option[value="' + id + '"]');
        var isPaid = d ? d.is_paid : parseInt(opt.data('is-paid') || 1);
        var remaining = d ? d.remaining : parseFloat(opt.data('days') || 0);
        var payBadge = isPaid
            ? '<span class="badge bg-success"><i class="ti ti-circle-check me-1"></i>{{ __("Paid Leave") }}</span>'
            : '<span class="badge bg-danger"><i class="ti ti-circle-x me-1"></i>{{ __("Unpaid Leave") }}</span>';
        var remClass = remaining > 0 ? 'text-success' : 'text-danger';
        $form.find('.leave-type-pay-badge').html(payBadge);
        $form.find('.leave-type-remaining').html('<span class="' + remClass + ' fw-semibold">{{ __("Remaining") }}: ' + remaining + ' {{ __("day(s)") }}</span>');
        $info.show();
    }

    function buildBreakdown() {
        var sd = $form.find('[name="start_date"]').val();
        var ed = $form.find('[name="end_date"]').val();
        var $wrapper = $form.find('.day-breakdown-wrapper');

        if (!sd || !ed) { $wrapper.hide(); return; }
        var start = new Date(sd), end = new Date(ed);
        if (end < start) { $wrapper.hide(); return; }

        var ltId = $form.find('[name="leave_type_id"]').val();
        var ltd = ltId ? leaveTypeData[ltId] : null;
        var opt = ltId ? $form.find('[name="leave_type_id"] option[value="' + ltId + '"]') : null;
        var isPaid = ltd ? ltd.is_paid : (opt ? parseInt(opt.data('is-paid') || 1) : 1);

        var diffDays = Math.round((end - start) / 86400000) + 1;
        var $tbody = $form.find('.day-breakdown-body');
        $tbody.empty();

        for (var i = 0; i < diffDays; i++) {
            var cur = new Date(start); cur.setDate(start.getDate() + i);
            var yyyy = cur.getFullYear(), m = String(cur.getMonth()+1).padStart(2,'0'), d = String(cur.getDate()).padStart(2,'0');
            var dateStr = yyyy + '-' + m + '-' + d;
            var saved = savedDetails[dateStr] || {};
            var dur = saved.day_duration || 'full_day';
            var period = saved.half_day_period || 'morning';
            var status = saved.day_status || (isPaid ? 'paid' : 'unpaid');
            $tbody.append(buildRow(dateStr, formatDate(cur), dayNames[cur.getDay()], dur, period, status));
        }
        checkSandwichCondition(start, end);
        updateSummary();
        $wrapper.show();
    }

    function checkSandwichCondition(start, end) {
        var diffDays = Math.round((end - start) / 86400000) + 1;
        if (diffDays <= 1) {
            $form.find('#sandwich_leave_notice').hide();
            return;
        }

        var hasWeekendInside = false;

        for (var i = 0; i < diffDays; i++) {
            var cur = new Date(start);
            cur.setDate(start.getDate() + i);
            var dow = cur.getDay(); // 0 = Sun, 6 = Sat

            if (dow === 0 || dow === 6) {
                hasWeekendInside = true;
            }
        }

        if (hasWeekendInside && diffDays > 1) {
            $form.find('#sandwich_leave_notice').fadeIn();
        } else {
            $form.find('#sandwich_leave_notice').hide();
        }
    }

    function buildRow(dateStr, dateDisp, dayName, duration, period, status) {
        var uid = dateStr.replace(/-/g, '_') + '_{{ $leave->id }}';
        var showSelect = duration === 'half_day';

        var isNotEmp = {{ \Auth::user()->type !== 'employee' ? 'true' : 'false' }};
        var sRuleCell = '';
        if (isNotEmp) {
            var saved = savedDetails[dateStr] || {};
            var curRuleId = saved.sandwich_leave_rule_id || '';
            var sRuleOptions = '<option value="">{{ __("None") }}</option>';
            @if (isset($sandwichRules) && count($sandwichRules) > 0)
                @foreach ($sandwichRules as $srule)
                    var sel = (curRuleId == '{{ $srule->id }}') ? ' selected' : '';
                    sRuleOptions += '<option value="{{ $srule->id }}"' + sel + '>{{ $srule->name }} ({{ number_format($srule->deduction_rate, 2) }}{{ $srule->rate_type == "percentage" ? "%" : "x" }})</option>';
                @endforeach
            @endif
            sRuleCell = '<td><select name="sandwich_leave_rule_id[' + dateStr + ']" class="form-select form-select-sm">' + sRuleOptions + '</select></td>';
        }

        return '<tr data-date="' + dateStr + '">'
            + '<td><small>' + dateDisp + '</small></td>'
            + '<td><small>' + dayName + '</small></td>'
            + '<td>'
            + '<div class="form-check form-check-inline"><input class="form-check-input dur-radio" type="radio" name="day_duration[' + dateStr + ']" id="full_' + uid + '" value="full_day"' + (duration !== 'half_day' ? ' checked' : '') + '><label class="form-check-label" for="full_' + uid + '">{{ __("Full") }}</label></div>'
            + '<div class="form-check form-check-inline"><input class="form-check-input dur-radio" type="radio" name="day_duration[' + dateStr + ']" id="half_' + uid + '" value="half_day"' + (duration === 'half_day' ? ' checked' : '') + '><label class="form-check-label" for="half_' + uid + '">{{ __("Half") }}</label></div>'
            + '</td>'
            + '<td class="period-cell">'
            + '<span class="period-dash"' + (showSelect ? ' style="display:none;"' : '') + '>-</span>'
            + '<select name="half_day_period_day[' + dateStr + ']" class="form-control form-control-sm period-select"' + (!showSelect ? ' style="display:none;"' : '') + '><option value="morning"' + (period === 'morning' ? ' selected' : '') + '>{{ __("Morning") }}</option><option value="afternoon"' + (period === 'afternoon' ? ' selected' : '') + '>{{ __("Afternoon") }}</option></select>'
            + '</td>'
            + '<td>'
            + '<div class="form-check form-check-inline"><input class="form-check-input stat-radio" type="radio" name="day_status[' + dateStr + ']" id="paid_' + uid + '" value="paid"' + (status === 'paid' ? ' checked' : '') + '><label class="form-check-label text-success" for="paid_' + uid + '">{{ __("Paid") }}</label></div>'
            + '<div class="form-check form-check-inline"><input class="form-check-input stat-radio" type="radio" name="day_status[' + dateStr + ']" id="unpaid_' + uid + '" value="unpaid"' + (status === 'unpaid' ? ' checked' : '') + '><label class="form-check-label text-danger" for="unpaid_' + uid + '">{{ __("Unpaid") }}</label></div>'
            + '</td>' + sRuleCell + '</tr>';
    }

    function updateSummary() {
        var total = 0, paid = 0, unpaid = 0;
        $form.find('.day-breakdown-body tr').each(function () {
            var dur = $(this).find('.dur-radio:checked').val() || 'full_day';
            var days = dur === 'half_day' ? 0.5 : 1.0;
            var stat = $(this).find('.stat-radio:checked').val() || 'paid';
            total += days;
            if (stat === 'paid') paid += days; else unpaid += days;
        });
        $form.find('.day-summary').html(
            '<strong>{{ __("Total") }}:</strong> ' + total + ' &nbsp;|&nbsp; '
            + '<span class="text-success"><strong>{{ __("Paid") }}:</strong> ' + paid + '</span> &nbsp;|&nbsp; '
            + '<span class="text-danger"><strong>{{ __("Unpaid") }}:</strong> ' + unpaid + '</span>'
        );
    }

}());
</script>

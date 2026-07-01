@php
    $plan = App\Models\Utility::getChatGPTSettings();

    // Pre-build a map of saved day details keyed by date string
    $savedDetails = $leave->dayDetails->keyBy(fn($d) => \Carbon\Carbon::parse($d->date)->format('Y-m-d'));

    // Build the list of days in the current leave range
    $editDays = [];
    if ($leave->start_date && $leave->end_date) {
        $period = \Carbon\CarbonPeriod::create($leave->start_date, $leave->end_date);
        foreach ($period as $day) {
            $dk = $day->format('Y-m-d');
            $savedRow = $savedDetails[$dk] ?? null;
            $editDays[] = [
                'date'            => $dk,
                'day_name'        => $day->format('D'),
                'day_duration'    => $savedRow ? $savedRow->day_duration    : 'full_day',
                'half_day_period' => $savedRow ? $savedRow->half_day_period : 'morning',
                'day_status'      => $savedRow ? $savedRow->day_status      : 'paid',
            ];
        }
    }
@endphp

{{ Form::model($leave, ['route' => ['leave.update', $leave->id], 'method' => 'PUT', 'class' => 'needs-validation', 'novalidate']) }}
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
                {{ Form::select('employee_id', $employees, null, ['class' => 'form-control', 'id' => 'employee_id', 'required' => 'required', 'placeholder' => __('Select Employee')]) }}
            </div>
        </div>
    </div>
    @else
    {!! Form::hidden('employee_id', !empty($employees) ? $employees->id : 0, ['id' => 'employee_id']) !!}
    @endif

    {{-- Leave Type --}}
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('leave_type_id', __('Leave Type'), ['class' => 'col-form-label']) }}<x-required></x-required>
                <select name="leave_type_id" id="leave_type_id" class="form-control select" required>
                    @foreach ($leavetypes as $ltype)
                        <option value="{{ $ltype->id }}"
                            {{ $leave->leave_type_id == $ltype->id ? 'selected' : '' }}
                            data-is-paid="{{ $ltype->is_paid ? 1 : 0 }}"
                            data-days="{{ $ltype->days }}">
                            {{ $ltype->title }} ({{ $ltype->days }})
                        </option>
                    @endforeach
                </select>
                {{-- Info bar --}}
                <div id="leave_type_info" class="mt-2">
                    @php $curType = $leavetypes->firstWhere('id', $leave->leave_type_id); @endphp
                    @if ($curType)
                        <span id="leave_type_pay_badge">
                            @if ($curType->is_paid)
                                <span class="badge bg-success"><i class="ti ti-circle-check me-1"></i>{{ __('Paid Leave') }}</span>
                            @else
                                <span class="badge bg-danger"><i class="ti ti-circle-x me-1"></i>{{ __('Unpaid Leave') }}</span>
                            @endif
                        </span>
                    @else
                        <span id="leave_type_pay_badge"></span>
                    @endif
                    <span id="leave_type_remaining" class="ms-2 text-muted small"></span>
                </div>
            </div>
        </div>
    </div>

    {{-- Date range --}}
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('start_date', __('Start Date'), ['class' => 'col-form-label']) }}<x-required></x-required>
                {{ Form::date('start_date', null, ['class' => 'form-control', 'id' => 'start_date', 'required' => 'required', 'autocomplete' => 'off']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('end_date', __('End Date'), ['class' => 'col-form-label']) }}<x-required></x-required>
                {{ Form::date('end_date', null, ['class' => 'form-control', 'id' => 'end_date', 'required' => 'required', 'autocomplete' => 'off']) }}
            </div>
        </div>
    </div>

    {{-- ── Per-day breakdown table ─────────────────────────────────────────── --}}
    <div id="day_breakdown_wrapper" style="{{ count($editDays) > 0 ? '' : 'display:none;' }}" class="mb-2">
        <label class="col-form-label fw-bold">{{ __('Day-wise Leave Details') }}</label>
        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle" id="day_breakdown_table">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Day') }}</th>
                        <th>{{ __('Duration') }}</th>
                        <th>{{ __('Period') }}<small class="text-muted d-block">{{ __('(if half day)') }}</small></th>
                        <th>{{ __('Status') }}</th>
                    </tr>
                </thead>
                <tbody id="day_breakdown_body">
                    @foreach ($editDays as $eDay)
                        @php
                            $dk       = $eDay['date'];
                            $dname    = $eDay['day_name'];
                            $dur      = $eDay['day_duration'];
                            $period   = $eDay['half_day_period'] ?? 'morning';
                            $status   = $eDay['day_status'];
                            $uid      = str_replace('-', '_', $dk);

                            // Lock status for employees when leave type is unpaid OR quota exhausted
                            $curLeaveType = $leavetypes->firstWhere('id', $leave->leave_type_id);
                            $isTypePaid   = $curLeaveType ? $curLeaveType->is_paid : true;
                            $isEmployee   = \Auth::user()->type === 'employee';
                            $locked       = $isEmployee && (!$isTypePaid || $status === 'unpaid');
                        @endphp
                        <tr data-date="{{ $dk }}">
                            <td><small>{{ $dk }}</small></td>
                            <td><small>{{ $dname }}</small></td>

                            {{-- Duration --}}
                            <td>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input dur-radio" type="radio"
                                        name="day_duration[{{ $dk }}]"
                                        id="full_{{ $uid }}" value="full_day"
                                        {{ $dur === 'full_day' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="full_{{ $uid }}">{{ __('Full') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input dur-radio" type="radio"
                                        name="day_duration[{{ $dk }}]"
                                        id="half_{{ $uid }}" value="half_day"
                                        {{ $dur === 'half_day' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="half_{{ $uid }}">{{ __('Half') }}</label>
                                </div>
                            </td>

                            {{-- Period --}}
                            <td class="period-cell" style="{{ $dur !== 'half_day' ? 'display:none;' : '' }}">
                                <select name="half_day_period_day[{{ $dk }}]"
                                    class="form-control form-control-sm period-select"
                                    {{ $locked ? 'disabled' : '' }}>
                                    <option value="morning"   {{ ($period ?? '') === 'morning'   ? 'selected' : '' }}>{{ __('Morning') }}</option>
                                    <option value="afternoon" {{ ($period ?? '') === 'afternoon' ? 'selected' : '' }}>{{ __('Afternoon') }}</option>
                                </select>
                            </td>

                            {{-- Status: locked for employees on unpaid types or exhausted quota --}}
                            <td>
                                @if ($locked)
                                    {{-- Hidden input carries the value; radios are display-only --}}
                                    <input type="hidden" name="day_status[{{ $dk }}]" value="{{ $status }}">
                                @endif
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input stat-radio" type="radio"
                                        name="{{ $locked ? '_day_status_display['.$dk.']' : 'day_status['.$dk.']' }}"
                                        id="paid_{{ $uid }}" value="paid"
                                        {{ $status === 'paid' ? 'checked' : '' }}
                                        {{ $locked ? 'disabled' : '' }}>
                                    <label class="form-check-label text-success" for="paid_{{ $uid }}">{{ __('Paid') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input stat-radio" type="radio"
                                        name="{{ $locked ? '_day_status_display['.$dk.']' : 'day_status['.$dk.']' }}"
                                        id="unpaid_{{ $uid }}" value="unpaid"
                                        {{ $status === 'unpaid' ? 'checked' : '' }}
                                        {{ $locked ? 'disabled' : '' }}>
                                    <label class="form-check-label text-danger" for="unpaid_{{ $uid }}">{{ __('Unpaid') }}</label>
                                </div>
                                @if ($locked)
                                    <span class="badge bg-secondary ms-1" title="{{ __('Auto-set based on leave type or quota') }}">
                                        <i class="ti ti-lock"></i>
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-1 text-muted small" id="day_summary"></div>
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

</div>
<div class="modal-footer">
    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
</div>
{{ Form::close() }}

<script>
(function () {
    'use strict';

    var isEmployee = {{ \Auth::user()->type === 'employee' ? 'true' : 'false' }};

    // PHP date format passed from server for display formatting
    var phpDateFormat = '{{ \App\Models\Utility::settings()['site_date_format'] ?? 'd-m-Y' }}';

    var dayNames = [
        '{{ __("Sun") }}','{{ __("Mon") }}','{{ __("Tue") }}',
        '{{ __("Wed") }}','{{ __("Thu") }}','{{ __("Fri") }}','{{ __("Sat") }}'
    ];

    // Pre-saved details from server for date restore on date change
    var savedDetails = {
        @foreach ($leave->dayDetails as $det)
        "{{ \Carbon\Carbon::parse($det->date)->format('Y-m-d') }}": {
            day_duration:    "{{ $det->day_duration }}",
            half_day_period: "{{ $det->half_day_period ?? 'morning' }}",
            day_status:      "{{ $det->day_status }}"
        },
        @endforeach
    };

    // Populated by jsoncount AJAX
    var leaveTypeData = {};

    $(document).ready(function () {
        setTimeout(function () {
            var eid = $('#employee_id').val();
            if (eid) $('#employee_id').trigger('change');
        }, 100);
        updateSummary();
    });

    // ── Rebuild when dates change ──────────────────────────────────────────
    $(document).on('change', '#start_date, #end_date', buildBreakdown);

    // ── Rebuild when leave type changes ───────────────────────────────────
    $(document).on('change', '#leave_type_id', function () {
        updateLeaveTypeInfo($(this).val());
        buildBreakdown();
    });

    // ── Load leave type balance when employee changes ──────────────────────
    $(document).on('change', '#employee_id', function () {
        var employee_id = $(this).val();
        if (!employee_id) return;

        $.ajax({
            url: '{{ route('leave.jsoncount') }}',
            type: 'POST',
            data: { employee_id: employee_id, _token: '{{ csrf_token() }}' },
            success: function (data) {
                leaveTypeData = {};
                var oldval = $('#leave_type_id').val();

                $('#leave_type_id').empty();

                $.each(data, function (key, value) {
                    leaveTypeData[value.id] = {
                        is_paid:     value.is_paid ? 1 : 0,
                        remaining:   parseFloat(value.remaining),
                        days:        parseFloat(value.days),
                        total_leave: parseFloat(value.total_leave),
                    };

                    var label    = value.title + ' (' + value.total_leave + '/' + value.days
                                 + ' {{ __("used") }}, {{ __("Remaining") }}: ' + value.remaining + ')';
                    var disabled = (value.remaining <= 0 && value.is_paid) ? ' disabled' : '';

                    $('#leave_type_id').append(
                        '<option value="' + value.id + '"' + disabled
                        + ' data-is-paid="' + (value.is_paid ? 1 : 0) + '"'
                        + ' data-remaining="' + value.remaining + '">'
                        + label + '</option>'
                    );

                    if (oldval && oldval == value.id) {
                        $('#leave_type_id option[value="' + oldval + '"]').prop('selected', true);
                    }
                });

                updateLeaveTypeInfo($('#leave_type_id').val());
                buildBreakdown();
            }
        });
    });

    // ── PHP → JS date formatter ────────────────────────────────────────────
    function formatDate(dateObj) {
        var yyyy = dateObj.getFullYear();
        var mm   = String(dateObj.getMonth() + 1).padStart(2, '0');
        var dd   = String(dateObj.getDate()).padStart(2, '0');
        var m1   = String(dateObj.getMonth() + 1);
        var d1   = String(dateObj.getDate());

        return phpDateFormat
            .replace('Y', yyyy)
            .replace('y', String(yyyy).slice(-2))
            .replace('m', mm)
            .replace('n', m1)
            .replace('d', dd)
            .replace('j', d1);
    }

    function updateLeaveTypeInfo(id) {
        if (!id) { $('#leave_type_info').hide(); return; }

        var d         = leaveTypeData[id];
        var opt       = $('#leave_type_id option[value="' + id + '"]');
        var isPaid    = d ? d.is_paid   : parseInt(opt.data('is-paid') || 1);
        var remaining = d ? d.remaining : parseFloat(opt.data('remaining') || 0);

        var payBadge = isPaid
            ? '<span class="badge bg-success"><i class="ti ti-circle-check me-1"></i>{{ __("Paid Leave") }}</span>'
            : '<span class="badge bg-danger"><i class="ti ti-circle-x me-1"></i>{{ __("Unpaid Leave") }}</span>';

        var remClass = remaining > 0 ? 'text-success' : 'text-danger';
        var remText  = '<span class="' + remClass + ' fw-semibold">'
                     + '{{ __("Remaining") }}: ' + remaining + ' {{ __("day(s)") }}</span>';

        $('#leave_type_pay_badge').html(payBadge);
        $('#leave_type_remaining').html(remText);
        $('#leave_type_info').show();
    }

    function buildBreakdown() {
        var sd = $('#start_date').val();
        var ed = $('#end_date').val();

        if (!sd || !ed) { $('#day_breakdown_wrapper').hide(); return; }

        var start = new Date(sd);
        var end   = new Date(ed);
        if (end < start) { $('#day_breakdown_wrapper').hide(); return; }

        var ltId      = $('#leave_type_id').val();
        var ltd       = ltId ? leaveTypeData[ltId] : null;
        var isPaid    = ltd ? ltd.is_paid   : 1;
        var remaining = ltd ? ltd.remaining : 0;

        var diffDays  = Math.round((end - start) / 86400000) + 1;
        var tbody     = $('#day_breakdown_body');
        tbody.empty();

        var paidBudget = remaining;

        for (var i = 0; i < diffDays; i++) {
            var cur = new Date(start);
            cur.setDate(start.getDate() + i);

            var yyyy    = cur.getFullYear();
            var m       = String(cur.getMonth() + 1).padStart(2, '0');
            var d       = String(cur.getDate()).padStart(2, '0');
            var dateStr  = yyyy + '-' + m + '-' + d;   // Y-m-d for form keys
            var dateDisp = formatDate(cur);             // user's configured format for display
            var dayName  = dayNames[cur.getDay()];

            var saved  = savedDetails[dateStr] || {};
            var dur    = saved.day_duration    || 'full_day';
            var period = saved.half_day_period || 'morning';

            var status, locked;
            if (!isPaid) {
                status = 'unpaid';
                locked = isEmployee;
            } else if (paidBudget <= 0) {
                status = 'unpaid';
                locked = isEmployee;
            } else {
                status = saved.day_status || 'paid';
                locked = false;
                paidBudget -= (dur === 'half_day' ? 0.5 : 1.0);
            }

            tbody.append(buildRow(dateStr, dateDisp, dayName, dur, period, status, locked));
        }

        updateSummary();
        $('#day_breakdown_wrapper').show();
    }

    function buildRow(dateStr, dateDisp, dayName, duration, period, status, locked) {
        var periodStyle = duration === 'half_day' ? '' : 'display:none;';
        var uid         = dateStr.replace(/-/g, '_');
        var disAttr     = locked ? ' disabled' : '';
        var lockedBadge = locked
            ? '<span class="badge bg-secondary ms-1" title="{{ __("Auto-set based on leave type or quota") }}">'
              + '<i class="ti ti-lock"></i></span>'
            : '';
        var hiddenStatus = locked
            ? '<input type="hidden" name="day_status[' + dateStr + ']" value="' + status + '">'
            : '';
        var statName = locked ? '_day_status_display[' + dateStr + ']' : 'day_status[' + dateStr + ']';

        return ''
            + '<tr data-date="' + dateStr + '">'
            + '<td><small>' + dateDisp + '</small></td>'
            + '<td><small>' + dayName + '</small></td>'
            + '<td>'
            +   '<div class="form-check form-check-inline">'
            +     '<input class="form-check-input dur-radio" type="radio" name="day_duration[' + dateStr + ']" id="full_' + uid + '" value="full_day"' + (duration !== 'half_day' ? ' checked' : '') + '>'
            +     '<label class="form-check-label" for="full_' + uid + '">{{ __("Full") }}</label>'
            +   '</div>'
            +   '<div class="form-check form-check-inline">'
            +     '<input class="form-check-input dur-radio" type="radio" name="day_duration[' + dateStr + ']" id="half_' + uid + '" value="half_day"' + (duration === 'half_day' ? ' checked' : '') + '>'
            +     '<label class="form-check-label" for="half_' + uid + '">{{ __("Half") }}</label>'
            +   '</div>'
            + '</td>'
            + '<td class="period-cell" style="' + periodStyle + '">'
            +   '<select name="half_day_period_day[' + dateStr + ']" class="form-control form-control-sm period-select"' + disAttr + '>'
            +     '<option value="morning"   ' + (period === 'morning'   ? 'selected' : '') + '>{{ __("Morning") }}</option>'
            +     '<option value="afternoon" ' + (period === 'afternoon' ? 'selected' : '') + '>{{ __("Afternoon") }}</option>'
            +   '</select>'
            + '</td>'
            + '<td>'
            +   hiddenStatus
            +   '<div class="form-check form-check-inline">'
            +     '<input class="form-check-input stat-radio" type="radio" name="' + statName + '" id="paid_' + uid + '" value="paid"' + (status === 'paid' ? ' checked' : '') + disAttr + '>'
            +     '<label class="form-check-label text-success" for="paid_' + uid + '">{{ __("Paid") }}</label>'
            +   '</div>'
            +   '<div class="form-check form-check-inline">'
            +     '<input class="form-check-input stat-radio" type="radio" name="' + statName + '" id="unpaid_' + uid + '" value="unpaid"' + (status === 'unpaid' ? ' checked' : '') + disAttr + '>'
            +     '<label class="form-check-label text-danger" for="unpaid_' + uid + '">{{ __("Unpaid") }}</label>'
            +   '</div>'
            +   lockedBadge
            + '</td>'
            + '</tr>';
    }

    $(document).on('change', '.dur-radio', function () {
        $(this).closest('tr').find('.period-cell').toggle($(this).val() === 'half_day');
        updateSummary();
    });

    $(document).on('change', '.stat-radio', updateSummary);

    function updateSummary() {
        var total = 0, paid = 0, unpaid = 0;

        $('#day_breakdown_body tr').each(function () {
            var dur    = $(this).find('.dur-radio:checked').val() || 'full_day';
            var days   = dur === 'half_day' ? 0.5 : 1.0;

            var hiddenStat = $(this).find('input[type="hidden"][name^="day_status"]');
            var statVal    = hiddenStat.length
                           ? hiddenStat.val()
                           : ($(this).find('.stat-radio:checked').val() || 'paid');

            total += days;
            if (statVal === 'paid') paid += days; else unpaid += days;
        });

        $('#day_summary').html(
            '<strong>{{ __("Total") }}:</strong> '  + total  + ' &nbsp;|&nbsp; '
          + '<span class="text-success"><strong>{{ __("Paid") }}:</strong> '   + paid   + '</span> &nbsp;|&nbsp; '
          + '<span class="text-danger"><strong>{{ __("Unpaid") }}:</strong> '  + unpaid + '</span>'
        );
    }

}());
</script>

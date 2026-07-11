@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Leave') }}
@endsection


@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Leave ') }}</li>
@endsection

@section('action-button')
    <a href="{{ route('leave.export') }}" class="btn btn-sm btn-primary me-1" data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('Export') }}">
        <i class="ti ti-file-export"></i>
    </a>

    <a href="{{ route('leave.calender') }}" class="btn btn-sm btn-primary me-1" data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('Calendar View') }}">
        <i class="ti ti-calendar"></i>
    </a>

    @can('Create Leave')
        <a href="javascript:void(0)" data-url="{{ route('leave.create') }}" data-ajax-popup="true"
            data-title="{{ __('Create New Leave') }}" data-size="lg" data-bs-toggle="tooltip" title=""
            class="btn btn-sm btn-primary" data-bs-original-title="{{ __('Create') }}">
            <i class="ti ti-plus"></i>
        </a>
    @endcan
@endsection

@section('content')
    <div class="row">

        {{-- ══════════════════════════════════════════════════════════════════════
            FILTERS
        ══════════════════════════════════════════════════════════════════════ --}}
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(['route' => ['leave.index'], 'method' => 'get', 'id' => 'leave_filter']) }}
                    <div class="row align-items-center justify-content-end">
                        <div class="col">
                            <div class="row">
                                @if (\Auth::user()->type != 'employee')
                                    <div class="col-xl col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('employee', __('Employee'), ['class' => 'form-label']) }}
                                            {{ Form::select('employee', $employeesList, isset($_GET['employee']) ? $_GET['employee'] : '', ['class' => 'form-control select', 'id' => 'filter_employee_id']) }}
                                        </div>
                                    </div>
                                @endif
                                <div class="col-xl col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('leave_type', __('Leave Type'), ['class' => 'form-label']) }}
                                        {{ Form::select('leave_type', $leaveTypes, isset($_GET['leave_type']) ? $_GET['leave_type'] : '', ['class' => 'form-control select', 'id' => 'filter_leave_type']) }}
                                    </div>
                                </div>
                                <div class="col-xl col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
                                        {{ Form::select('status', ['' => __('All'), 'Pending' => __('Pending'), 'Approved' => __('Approved'), 'Reject' => __('Reject')], isset($_GET['status']) ? $_GET['status'] : '', ['class' => 'form-control select']) }}
                                    </div>
                                </div>
                                <div class="col-xl col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
                                        {{ Form::date('start_date', isset($_GET['start_date']) ? $_GET['start_date'] : null, ['class' => 'form-control w-100', 'autocomplete' => 'off']) }}
                                    </div>
                                </div>
                                <div class="col-xl col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}
                                        {{ Form::date('end_date', isset($_GET['end_date']) ? $_GET['end_date'] : null, ['class' => 'form-control w-100', 'autocomplete' => 'off']) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="row">
                                <div class="col-auto mt-4">
                                    <a href="javascript:void(0)" class="btn btn-sm btn-primary me-1"
                                        onclick="document.getElementById('leave_filter').submit(); return false;"
                                        data-bs-toggle="tooltip" title="" data-bs-original-title="{{ __('Apply') }}">
                                        <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                    </a>
                                    <a href="{{ route('leave.index') }}" class="btn btn-sm btn-danger"
                                        data-bs-toggle="tooltip" title="" data-bs-original-title="{{ __('Reset') }}">
                                        <span class="btn-inner--icon"><i class="ti ti-refresh text-white-off"></i></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════════════
            STAT CARDS — Leave Balance Summary
        ══════════════════════════════════════════════════════════════════════ --}}
        @if ($leaveBalances->isNotEmpty())
            @foreach ($leaveBalances as $balance)
                @php
                    $percentage = $balance->days > 0 ? round(($balance->total_used / $balance->days) * 100) : 0;
                    // Unpaid leave types always show danger badge; otherwise color by usage percentage
                    $bgColor = (!$balance->is_paid) ? 'danger' : ($percentage > 80 ? 'danger' : ($percentage > 50 ? 'warning' : 'primary'));
                @endphp
                <div class="col-lg-4 col-md-6">
                    <div class="card stats-wrapper dash-info-card">
                        <div class="card-body stats">
                            <div class="row align-items-center justify-content-between">
                                <div class="col-auto mb-3 mb-sm-0">
                                    <div class="d-flex align-items-center">
                                        <div class="badge theme-avtar bg-{{ $bgColor }}">
                                            <i class="ti ti-calendar-event"></i>
                                        </div>
                                        <div class="ms-3">
                                            <small class="text-muted">{{ $balance->title }}</small>
                                            <h6 class="m-0">{{ __('Remaining') }}: {{ $balance->remaining }} / {{ $balance->days }}</h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto text-end">
                                    <h4 class="m-0 text-{{ $bgColor }}">{{ $balance->total_used }}</h4>
                                    <small class="text-muted">{{ __('Used') }}</small>
                                </div>
                            </div>
                            <div class="progress mt-3" style="height: 5px;">
                                <div class="progress-bar bg-{{ $bgColor }}"
                                    role="progressbar" style="width: {{ $percentage }}%"
                                    aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif

        {{-- ══════════════════════════════════════════════════════════════════════
            LEAVE TABLE
        ══════════════════════════════════════════════════════════════════════ --}}
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table" id="pc-dt-simple">
                            <thead>
                                <tr>
                                    @if (\Auth::user()->type != 'employee')
                                        <th>{{ __('Employee') }}</th>
                                    @endif
                                    <th>{{ __('Leave Type') }}</th>
                                    <th>{{ __('Applied On') }}</th>
                                    <th>{{ __('Start Date') }}</th>
                                    <th>{{ __('End Date') }}</th>
                                    <th>{{ __('Total Days') }}</th>
                                    <th>{{ __('Leave Note') }}</th>
                                    <th>{{ __('status') }}</th>
                                    <th width="200px">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($leaves as $leave)
                                    @php
                                        // Compute once per row — used in both Status and Action columns
                                        $actorEmpId = optional(\Auth::user()->employee)->id ?? null;
                                        $isOwnLeave = $actorEmpId && $actorEmpId == $leave->employee_id;
                                        $canAction  = (in_array(strtolower(\Auth::user()->type), ['company', 'hr', 'admin'])
                                                      || \Auth::user()->can('Edit Leave')) && \Auth::user()->type != 'employee';
                                    @endphp
                                    <tr>
                                        @if (\Auth::user()->type != 'employee')
                                            <td>{{ !empty($leave->employee_id) && !empty($leave->employees) ? $leave->employees->name : '-' }}
                                            </td>
                                        @endif
                                        <td>{{ !empty($leave->leave_type_id) ? $leave->leaveType->title : '' }}
                                        </td>
                                        <td>{{ \Auth::user()->dateFormat($leave->applied_on) }}</td>
                                        <td>{{ \Auth::user()->dateFormat($leave->start_date) }}</td>
                                        <td>{{ \Auth::user()->dateFormat($leave->end_date) }}</td>

                                        <td>
                                            {{-- Show numeric days --}}
                                            {{ $leave->total_leave_days }}

                                            @php
                                                $halfDayCount   = $leave->dayDetails->where('day_duration', 'half_day')->count();
                                                $fullDayCount   = $leave->dayDetails->where('day_duration', 'full_day')->count();
                                                $unpaidDayCount = $leave->dayDetails->where('day_status', 'unpaid')->count();
                                                // Also catch legacy single-day half-day (no dayDetails rows)
                                                $isLegacyHalf   = $leave->dayDetails->isEmpty() && ($leave->leave_duration ?? '') === 'half_day';
                                            @endphp

                                            {{-- Half day badge --}}
                                            @if ($isLegacyHalf)
                                                <span class="badge bg-info ms-1"
                                                    title="{{ ucfirst($leave->half_day_period ?? '') }}">
                                                    {{ __('Half Day') }}
                                                </span>
                                            @elseif ($halfDayCount > 0 && $fullDayCount === 0)
                                                {{-- All days are half days --}}
                                                <span class="badge bg-info ms-1">{{ __('Half Day') }}</span>
                                            @elseif ($halfDayCount > 0)
                                                {{-- Mixed: some half days --}}
                                                <span class="badge bg-info ms-1"
                                                    title="{{ $halfDayCount . ' ' . __('half day(s)') }}">
                                                    {{ $halfDayCount }} × {{ __('Half') }}
                                                </span>
                                            @endif

                                            {{-- Unpaid badge --}}
                                            @if ($unpaidDayCount > 0)
                                                <span class="badge bg-danger ms-1"
                                                    title="{{ $unpaidDayCount . ' ' . __('unpaid day(s)') }}">
                                                    {{ $unpaidDayCount }} {{ __('Unpaid') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(strlen($leave->leave_reason) > 5)
                                                <span
                                                    data-bs-toggle="tooltip"
                                                    data-bs-placement="top"
                                                    data-bs-custom-class="tooltip-primary"
                                                    title="{{ $leave->leave_reason }}"
                                                >
                                                    {{ \Illuminate\Support\Str::limit($leave->leave_reason, 20) }}
                                                </span>
                                            @else
                                                {{ $leave->leave_reason }}
                                            @endif
                                        </td>
                                        <td>
                                            @if ($canAction)
                                                @if ($isOwnLeave)
                                                    {{-- Cannot change own leave status --}}
                                                    @if ($leave->status == 'Pending')
                                                        <div class="badge bg-warning p-2 px-3 status-badge5">{{ $leave->status }}</div>
                                                    @elseif ($leave->status == 'Approved')
                                                        <div class="badge bg-success p-2 px-3 status-badge5">{{ $leave->status }}</div>
                                                    @elseif ($leave->status == 'Reject')
                                                        <div class="badge bg-danger p-2 px-3 status-badge5">{{ $leave->status }}</div>
                                                    @endif
                                                @else
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm dropdown-toggle
                                                            @if ($leave->status == 'Pending') btn-warning
                                                            @elseif ($leave->status == 'Approved') btn-success
                                                            @elseif ($leave->status == 'Reject') btn-danger
                                                            @endif"
                                                            data-bs-toggle="dropdown">
                                                            {{ $leave->status }}
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li>
                                                                <form method="POST" action="{{ route('leave.changeaction') }}">
                                                                    @csrf
                                                                    <input type="hidden" name="leave_id" value="{{ $leave->id }}">
                                                                    <input type="hidden" name="status" value="Pending">
                                                                    <button class="dropdown-item">{{ __('Pending') }}</button>
                                                                </form>
                                                            </li>
                                                            <li>
                                                                <form method="POST" action="{{ route('leave.changeaction') }}">
                                                                    @csrf
                                                                    <input type="hidden" name="leave_id" value="{{ $leave->id }}">
                                                                    <input type="hidden" name="status" value="Approved">
                                                                    <button class="dropdown-item">{{ __('Approved') }}</button>
                                                                </form>
                                                            </li>
                                                            <li>
                                                                <form method="POST" action="{{ route('leave.changeaction') }}">
                                                                    @csrf
                                                                    <input type="hidden" name="leave_id" value="{{ $leave->id }}">
                                                                    <input type="hidden" name="status" value="Reject">
                                                                    <button class="dropdown-item">{{ __('Reject') }}</button>
                                                                </form>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                @endif
                                            @else
                                                {{-- Employee: static badge only --}}
                                                @if ($leave->status == 'Pending')
                                                    <div class="badge bg-warning p-2 px-3 status-badge5">{{ $leave->status }}</div>
                                                @elseif ($leave->status == 'Approved')
                                                    <div class="badge bg-success p-2 px-3 status-badge5">{{ $leave->status }}</div>
                                                @elseif ($leave->status == 'Reject')
                                                    <div class="badge bg-danger p-2 px-3 status-badge5">{{ $leave->status }}</div>
                                                @endif
                                            @endif
                                        </td>
                                        <td class="Action">
                                            @if ($canAction)
                                                {{-- Detail modal: show for all rows except own --}}
                                                @if (!$isOwnLeave)
                                                    <div class="action-btn me-2">
                                                        <a href="javascript:void(0)"
                                                            class="mx-3 btn btn-sm bg-success align-items-center"
                                                            data-url="{{ URL::to('leave/' . $leave->id . '/action') }}"
                                                            data-ajax-popup="true" data-size="md"
                                                            data-bs-toggle="tooltip" title="{{ __('Manage Leave') }}"
                                                            data-title="{{ __('Leave Action') }}">
                                                            <span class="text-white"><i class="ti ti-caret-right"></i></span>
                                                        </a>
                                                    </div>
                                                @endif

                                                {{-- Edit: show for all admin/company/hr so they can edit employee leaves --}}
                                                <div class="action-btn me-2">
                                                    <a href="javascript:void(0)"
                                                        class="mx-3 btn btn-sm bg-info align-items-center"
                                                        data-url="{{ URL::to('leave/' . $leave->id . '/edit') }}"
                                                        data-ajax-popup="true" data-size="lg"
                                                        data-bs-toggle="tooltip" title="{{ __('Edit') }}"
                                                        data-title="{{ __('Edit Leave') }}">
                                                        <span class="text-white"><i class="ti ti-pencil"></i></span>
                                                    </a>
                                                </div>

                                                @can('Delete Leave')
                                                    <div class="action-btn">
                                                        {!! Form::open([
                                                            'method' => 'DELETE',
                                                            'route'  => ['leave.destroy', $leave->id],
                                                            'id'     => 'delete-form-' . $leave->id,
                                                        ]) !!}
                                                        <a href="javascript:void(0)"
                                                            data-bs-trigger="hover"
                                                            class="btn btn-sm bg-danger align-items-center bs-pass-para"
                                                            data-bs-toggle="tooltip" title="{{ __('Delete') }}"
                                                            aria-label="{{ __('Delete') }}">
                                                            <span class="text-white"><i class="ti ti-trash"></i></span>
                                                        </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                @endcan
                                            @else
                                                {{-- Employee: view detail + edit/delete own pending leaves --}}
                                                <div class="action-btn me-2">
                                                    <a href="javascript:void(0)"
                                                        class="mx-3 btn btn-sm bg-success align-items-center"
                                                        data-url="{{ URL::to('leave/' . $leave->id . '/action') }}"
                                                        data-ajax-popup="true" data-size="md"
                                                        data-bs-toggle="tooltip" title="{{ __('View') }}"
                                                        data-title="{{ __('Leave Detail') }}">
                                                        <span class="text-white"><i class="ti ti-caret-right"></i></span>
                                                    </a>
                                                </div>

                                                @if ($leave->status == 'Pending')
                                                    @can('Edit Leave')
                                                        <div class="action-btn me-2">
                                                            <a href="javascript:void(0)"
                                                                class="mx-3 btn btn-sm bg-info align-items-center"
                                                                data-url="{{ URL::to('leave/' . $leave->id . '/edit') }}"
                                                                data-ajax-popup="true" data-size="lg"
                                                                data-bs-toggle="tooltip" title="{{ __('Edit') }}"
                                                                data-title="{{ __('Edit Leave') }}">
                                                                <span class="text-white"><i class="ti ti-pencil"></i></span>
                                                            </a>
                                                        </div>
                                                    @endcan

                                                    @can('Delete Leave')
                                                        <div class="action-btn">
                                                            {!! Form::open([
                                                                'method' => 'DELETE',
                                                                'route'  => ['leave.destroy', $leave->id],
                                                                'id'     => 'delete-form-' . $leave->id,
                                                            ]) !!}
                                                            <a href="javascript:void(0)"
                                                                data-bs-trigger="hover"
                                                                class="btn btn-sm bg-danger align-items-center bs-pass-para"
                                                                data-bs-toggle="tooltip" title="{{ __('Delete') }}"
                                                                aria-label="{{ __('Delete') }}">
                                                                <span class="text-white"><i class="ti ti-trash"></i></span>
                                                            </a>
                                                            {!! Form::close() !!}
                                                        </div>
                                                    @endcan
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script-page')
    <script>
        $(document).on('change', '#employee_id', function() {
            var employee_id = $(this).val();

            $.ajax({
                url: '{{ route('leave.jsoncount') }}',
                type: 'POST',
                data: {
                    "employee_id": employee_id,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    var oldval = $('#leave_type_id').val();
                    $('#leave_type_id').empty();
                    $('#leave_type_id').append(
                        '<option value="">{{ __('Select Leave Type') }}</option>');

                    $.each(data, function(key, value) {
                        var label    = value.title + ' (' + value.total_leave + '/' + value.days + ' {{ __("used") }}, {{ __("Remaining") }}: ' + value.remaining + ')';
                        var disabled = (value.remaining <= 0) ? ' disabled' : '';
                        $('#leave_type_id').append(
                            '<option value="' + value.id + '"' + disabled
                            + ' data-is-paid="' + (value.is_paid ? 1 : 0) + '">'
                            + label + '</option>'
                        );
                        if (oldval && oldval == value.id) {
                            $('#leave_type_id option[value="' + oldval + '"]').prop('selected', true);
                        }
                    });
                }
            });
        });

        // ── Filter: update Leave Type dropdown when employee filter changes ──
        $(document).on('change', '#filter_employee_id', function() {
            var employee_id = $(this).val();

            if (!employee_id) {
                // Reset to show all leave types
                var allTypes = {!! json_encode($leaveTypes) !!};
                $('#filter_leave_type').empty();
                $('#filter_leave_type').append('<option value="">{{ __("All") }}</option>');
                $.each(allTypes, function(id, title) {
                    if (id !== '') {
                        $('#filter_leave_type').append('<option value="' + id + '">' + title + '</option>');
                    }
                });
                return;
            }

            $.ajax({
                url: '{{ route('leave.jsoncount') }}',
                type: 'POST',
                data: {
                    "employee_id": employee_id,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    var oldval = $('#filter_leave_type').val();
                    $('#filter_leave_type').empty();
                    $('#filter_leave_type').append('<option value="">{{ __("All") }}</option>');

                    $.each(data, function(key, value) {
                        $('#filter_leave_type').append(
                            '<option value="' + value.id + '">' + value.title + '</option>'
                        );
                        if (oldval && oldval == value.id) {
                            $('#filter_leave_type option[value="' + value.id + '"]').prop('selected', true);
                        }
                    });
                }
            });
        });
    </script>
@endpush

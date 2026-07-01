@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Attendance Requests') }}
@endsection

@php
    $company_settings = \App\Models\Utility::settings();
@endphp
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Attendance Requests') }}</li>
@endsection

@section('action-button')
    <div class="row align-items-center m-1">
        @can('Create Attendance Request')
            <a href="javascript:void(0)" data-size="lg" data-url="{{ route('attendance-requests.create') }}" data-ajax-popup="true"
                data-bs-toggle="tooltip" title="{{ __('Create') }}" data-title="{{ __('Request Clock In/Out') }}"
                class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        @endcan
    </div>
@endsection

@section('content')
    <div class="row">
        {{-- Stats Cards --}}
        <div class="col-xl-3">
            <div class="card comp-card">
                <div class="card-body">
                    <h6>{{ __('Total Requests') }}</h6>
                    <h3>{{ $stats['total'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-xl-3">
            <div class="card comp-card">
                <div class="card-body">
                    <h6>{{ __('This Month') }}</h6>
                    <h3>{{ $stats['this_month'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-xl-3">
            <div class="card comp-card">
                <div class="card-body">
                    <h6>{{ __('This Week') }}</h6>
                    <h3>{{ $stats['this_week'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-xl-3">
            <div class="card comp-card">
                <div class="card-body">
                    <h6>{{ __('Last 30 Days') }}</h6>
                    <h3>{{ $stats['last_30days'] }}</h3>
                </div>
            </div>
        </div>

        {{-- Bulk action forms — OUTSIDE the filter form to avoid invalid nested <form> --}}
        @can('Approve Attendance Request')
            <form method="POST" action="{{ route('attendance-requests.bulk-approve') }}" id="bulk-approve-form" class="d-none">
                @csrf
                <input type="hidden" name="type"  value="{{ request('type', 'monthly') }}">
                <input type="hidden" name="month" value="{{ request('month', date('Y-m')) }}">
                <input type="hidden" name="date"  value="{{ request('date', '') }}">
            </form>
        @endcan

        @can('Decline Attendance Request')
            <form method="POST" action="{{ route('attendance-requests.bulk-decline') }}" id="bulk-decline-form" class="d-none">
                @csrf
                <input type="hidden" name="type"  value="{{ request('type', 'monthly') }}">
                <input type="hidden" name="month" value="{{ request('month', date('Y-m')) }}">
                <input type="hidden" name="date"  value="{{ request('date', '') }}">
            </form>
        @endcan

        <div class="col-sm-12">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{-- Filter Form --}}
                        {{ Form::open(['route' => ['attendance-requests.index'], 'method' => 'get', 'id' => 'attendance_request_filter']) }}
                        <div class="row align-items-center justify-content-between">
                            <div class="col-xl-8">
                                <div class="row">

                                    <div class="col-3">
                                        <label class="form-label">{{ __('Type') }}</label> <br>

                                        <div class="form-check form-check-inline form-group">
                                            <input type="radio" id="monthly" value="monthly" name="type"
                                                class="form-check-input"
                                                {{ isset($_GET['type']) && $_GET['type'] == 'monthly' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="monthly">{{ __('Monthly') }}</label>
                                        </div>
                                        <div class="form-check form-check-inline form-group">
                                            <input type="radio" id="daily" value="daily" name="type"
                                                class="form-check-input"
                                                {{ isset($_GET['type']) && $_GET['type'] == 'daily' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="daily">{{ __('Daily') }}</label>
                                        </div>
                                    </div>

                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 month">
                                        <div class="btn-box">
                                            {{ Form::label('month', __('Month'), ['class' => 'form-label']) }}
                                            {{ Form::month('month', isset($_GET['month']) ? $_GET['month'] : date('Y-m'), ['class' => 'month-btn form-control month-btn']) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 date">
                                        <div class="btn-box">
                                            {{ Form::label('date', __('Date'), ['class' => 'form-label']) }}
                                            {{ Form::date('date', isset($_GET['date']) ? $_GET['date'] : '', ['class' => 'form-control month-btn datepicker w-100']) }}
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="col-xl-4 mt-4">
                                <div class="row justify-content-end">
                                    <div class="col-auto d-flex align-items-center gap-1">

                                        {{-- Apply Filter --}}
                                        <a href="javascript:void(0)" class="btn btn-sm btn-primary"
                                            onclick="document.getElementById('attendance_request_filter').submit(); return false;"
                                            data-bs-toggle="tooltip" title="{{ __('Apply') }}">
                                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                        </a>

                                        {{-- Reset Filter --}}
                                        <a href="{{ route('attendance-requests.index') }}"
                                            class="btn btn-sm btn-secondary" data-bs-toggle="tooltip"
                                            title="{{ __('Reset') }}">
                                            <span class="btn-inner--icon"><i class="ti ti-refresh"></i></span>
                                        </a>

                                        @can('Approve Attendance Request')
                                            <button type="button" class="btn btn-sm btn-success"
                                                data-bs-toggle="tooltip"
                                                title="{{ __('Bulk approve all pending requests matching current filter') }}"
                                                onclick="if(confirm('{{ __('Approve all pending requests in the current filter?') }}')) { document.getElementById('bulk-approve-form').submit(); }">
                                                <i class="ti ti-checks"></i> {{ __('Bulk Approve') }}
                                            </button>
                                        @endcan

                                        @can('Decline Attendance Request')
                                            <button type="button" class="btn btn-sm btn-danger"
                                                data-bs-toggle="tooltip"
                                                title="{{ __('Bulk decline all pending requests matching current filter') }}"
                                                onclick="if(confirm('{{ __('Decline all pending requests in the current filter?') }}')) { document.getElementById('bulk-decline-form').submit(); }">
                                                <i class="ti ti-x"></i> {{ __('Bulk Decline') }}
                                            </button>
                                        @endcan

                                    </div>
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="col-xl-12 mt-3">
            <div class="card table-card">
                <div class="card-header card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table mb-0" id="pc-dt-simple">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Employee') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Reason') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Requested Date') }}</th>
                                    <th>{{ __('Requested Time') }}</th>
                                    <th>{{ __('Approved By') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($requests as $req)
                                    @php
                                        $reqDateTime  = \Carbon\Carbon::parse($req->requested_at);
                                        $ownEmpId     = optional(\App\Models\Employee::where('user_id', Auth::id())->first())->id;
                                        $isOwnRequest = $ownEmpId && $req->employee_id === $ownEmpId;
                                        $isPending    = $req->status === 'pending';
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $req->employee?->name }}</td>
                                        <td>{{ Str::title(str_replace('_', ' ', $req->type)) }}</td>
                                        <td data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="{{ $req->reason ?? '-' }}"
                                            style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            {{ $req->reason ?? '-' }}
                                        </td>
                                        <td>
                                            @if ($req->status == 'pending')
                                                <span class="badge bg-warning">{{ __('Pending') }}</span>
                                            @elseif ($req->status == 'approved')
                                                <span class="badge bg-success">{{ __('Approved') }}</span>
                                            @else
                                                <span class="badge bg-danger">{{ __('Declined') }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $reqDateTime->toDateString() }}</td>
                                        <td>{{ $reqDateTime->format('h:i A') }}</td>
                                        <td>{{ $req->approver?->name }}</td>
                                        <td>
                                            @can('Approve Attendance Request')
                                                @if ($isPending)
                                                    @if ($isOwnRequest)
                                                        <span class="badge bg-secondary"
                                                            title="{{ __('You cannot approve or decline your own request') }}">
                                                            {{ __('Your Request') }}
                                                        </span>
                                                    @else
                                                        <form method="POST"
                                                            action="{{ route('attendance-requests.approve', $req) }}"
                                                            style="display:inline;">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-success">
                                                                {{ __('Approve') }}
                                                            </button>
                                                        </form>
                                                        <form method="POST"
                                                            action="{{ route('attendance-requests.decline', $req) }}"
                                                            style="display:inline;">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-danger">
                                                                {{ __('Decline') }}
                                                            </button>
                                                        </form>
                                                    @endif
                                                @else
                                                    -
                                                @endif
                                            @endcan
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
        $('input[name="type"]:radio').on('change', function(e) {
            var type = $(this).val();

            if (type == 'monthly') {
                $('.month').addClass('d-block').removeClass('d-none');
                $('.date').addClass('d-none').removeClass('d-block');
            } else {
                $('.date').addClass('d-block').removeClass('d-none');
                $('.month').addClass('d-none').removeClass('d-block');
            }

            // Keep bulk form filter inputs in sync with the visible filter
            var filterVal = type === 'monthly'
                ? $('input[name="month"]').val()
                : $('input[name="date"]').val();

            $('#bulk-approve-form input[name="type"], #bulk-decline-form input[name="type"]').val(type);
        });

        // Sync month/date inputs into the bulk forms whenever they change
        $('input[name="month"]').on('change', function() {
            $('#bulk-approve-form input[name="month"], #bulk-decline-form input[name="month"]').val($(this).val());
        });
        $('input[name="date"]').on('change', function() {
            $('#bulk-approve-form input[name="date"], #bulk-decline-form input[name="date"]').val($(this).val());
        });

        $('input[name="type"]:radio:checked').trigger('change');
    </script>
@endpush

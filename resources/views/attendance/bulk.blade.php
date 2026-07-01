@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Bulk Attendance') }}
@endsection

@php
    $company_settings = \App\Models\Utility::settings();
@endphp
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Bulk Attendance') }}</li>
@endsection


@push('script-page')
    <script>
        $('#present_all').click(function(event) {
            if (this.checked) {
                $('.present').each(function() {
                    this.checked = true;
                });
                $('.present_check_in').removeClass('d-none').addClass('d-flex');
            } else {
                $('.present').each(function() {
                    this.checked = false;
                });
                $('.present_check_in').removeClass('d-flex').addClass('d-none');
            }
        });

        $('.present').click(function(event) {
            var div = $(this).closest('td').find('.present_check_in');
            if (this.checked) {
                div.removeClass('d-none').addClass('d-flex');
            } else {
                div.removeClass('d-flex').addClass('d-none');
            }
        });
    </script>

    <script>
        $(document).ready(function() {
            var b_id = $('#branch_id').val();
            getDepartment(b_id);
        });
        $(document).on('change', 'select[name=branch]', function() {
            var branch_id = $(this).val();

            getDepartment(branch_id);
        });

        function getDepartment(bid) {

            $.ajax({
                url: '{{ route('monthly.getdepartment') }}',
                type: 'POST',
                data: {
                    "branch_id": bid,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {

                    $('.department_id').empty();
                    var emp_selct = `<select class="form-control department_id" name="department_id" id="choices-multiple"
                                            placeholder="{{ __('Select Department') }}" >
                                            </select>`;
                    $('.department_div').html(emp_selct);

                    $('.department_id').append('<option value="0"> {{ __('All') }} </option>');
                    $.each(data, function(key, value) {
                        $('.department_id').append('<option value="' + key + '">' + value +
                            '</option>');
                    });
                    new Choices('#choices-multiple', {
                        removeItemButton: true,
                    });
                }
            });
        }
    </script>
@endpush

@section('action-button')
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(['route' => ['attendanceemployee.bulkattendance'], 'method' => 'get', 'id' => 'bulkattendance_filter']) }}
                        <div class="row align-items-center justify-content-end">
                            <div class="col-xl-10">
                                <div class="row">
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box"></div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('date', __('Date'), ['class' => 'form-label']) }}
                                            {{ Form::date('date', isset($_GET['date']) ? $_GET['date'] : null, ['class' => 'form-control  w-100', 'autocomplete' => 'off']) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('branch', __('Branch'), ['class' => 'form-label']) }}
                                            {{ Form::select('branch', $branch, isset($_GET['branch']) ? $_GET['branch'] : '', ['class' => 'form-control select branch_id', 'id' => 'branch_id']) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('department', __('Department'), ['class' => 'form-label']) }}
                                            {{ Form::select('department', $department, isset($_GET['department']) ? $_GET['department'] : '', ['class' => 'form-control select department_id', 'id' => 'department_id']) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="row">
                                    <div class="col-auto mt-4">

                                        <a href="javascript:void(0)" class="btn btn-sm btn-primary me-1"
                                            onclick="document.getElementById('bulkattendance_filter').submit(); return false;"
                                            data-bs-toggle="tooltip" title="" data-bs-original-title="apply">
                                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                        </a>
                                        <a href="{{ route('attendanceemployee.bulkattendance') }}"
                                            class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title=""
                                            data-bs-original-title="Reset">
                                            <span class="btn-inner--icon"><i
                                                    class="ti ti-refresh text-white-off "></i></span>
                                        </a>

                                    </div>
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                {{ Form::open(['route' => ['attendanceemployee.bulkattendance'], 'method' => 'post']) }}
                <div class="table-responsive">
                    <table class="table" id="">
                        <thead>
                            <tr>
                                <th width="10%">{{ __('Employee Id') }}</th>
                                <th>{{ __('Employee') }}</th>
                                <th>{{ __('Branch') }}</th>
                                <th>{{ __('Department') }}</th>
                                <th>
                                    <div class="form-group my-auto">
                                        <div class="custom-control ">
                                            <input class="form-check-input" type="checkbox" name="present_all"
                                                id="present_all" {{ old('remember') ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="present_all">
                                                {{ __('Attendance') }}</label>
                                        </div>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($employees as $employee)
                                @php
                                    $attendance = $employee->present_status($employee->id, isset($_GET['date']) ? $_GET['date'] : date('Y-m-d'));
                                @endphp
                                <tr>
                                    <td class="Id">
                                        <input type="hidden" value="{{ $employee->id }}" name="employee_id[]">
                                        <a href="{{ route('employee.show', \Illuminate\Support\Facades\Crypt::encrypt($employee->id)) }}"
                                            class="btn btn-outline-primary">{{ \Auth::user()->employeeIdFormat($employee->employee_id) }}</a>
                                    </td>
                                    <td>{{ $employee->name }}</td>
                                    <td>{{ !empty($employee->branch) ? $employee->branch->name : '' }}</td>
                                    <td>{{ !empty($employee->department) ? $employee->department->name : '' }}</td>
                                    <td>
                                        <div class="d-flex align-items-start gap-2 flex-wrap">

                                            {{-- Checkbox --}}
                                            <div class="d-flex align-items-center pt-1">
                                                <input class="form-check-input present" type="checkbox"
                                                    name="present-{{ $employee->id }}"
                                                    id="present{{ $employee->id }}"
                                                    {{ !empty($attendance) && $attendance->status == 'Present' ? 'checked' : '' }}>
                                                <label class="ms-1 mb-0" for="present{{ $employee->id }}"></label>
                                            </div>

                                            {{-- Time fields --}}
                                            <div class="present_check_in {{ empty($attendance) ? 'd-none' : 'd-flex' }} align-items-center gap-2 flex-wrap">

                                                {{-- Check In --}}
                                                <div class="d-flex align-items-center gap-1">
                                                    <span class="badge bg-success text-white" style="white-space:nowrap;">
                                                        <i class="ti ti-login me-1"></i>{{ __('In') }}
                                                    </span>
                                                    <input type="time" class="form-control timepicker"
                                                        style="min-width:110px; max-width:130px;"
                                                        name="in-{{ $employee->id }}"
                                                        value="{{ !empty($attendance) && $attendance->clock_in != '00:00:00' ? $attendance->clock_in : ($attendance->company_start_time ?? App\Models\Utility::getValByName('company_start_time')) }}">
                                                </div>

                                                {{-- Check Out --}}
                                                <div class="d-flex align-items-center gap-1">
                                                    <span class="badge bg-danger text-white" style="white-space:nowrap;">
                                                        <i class="ti ti-logout me-1"></i>{{ __('Out') }}
                                                    </span>
                                                    <input type="time" class="form-control timepicker"
                                                        style="min-width:110px; max-width:130px;"
                                                        name="out-{{ $employee->id }}"
                                                        value="{{ !empty($attendance) && $attendance->clock_out != '00:00:00' ? $attendance->clock_out : ($attendance->company_end_time ?? App\Models\Utility::getValByName('company_end_time')) }}">
                                                </div>

                                            </div>
                                        </div>
                                    </td>

                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="attendance-btn float-end pt-4">
                    <input type="hidden" value="{{ isset($_GET['date']) ? $_GET['date'] : date('Y-m-d') }}"
                        name="date">
                    <input type="hidden" value="{{ isset($_GET['branch']) ? $_GET['branch'] : '' }}" name="branch">
                    <input type="hidden" value="{{ isset($_GET['department']) ? $_GET['department'] : '' }}"
                        name="department">
                    {{ Form::submit(__('Update'), ['class' => 'btn btn-primary']) }}
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
    </div>
@endsection

@push('script-page')
@include('layouts.dateformat');

    <script>
        // $(document).ready(function() {
        //     if ($('.daterangepicker').length > 0) {
        //         $('.daterangepicker').daterangepicker({
        //             format: 'yyyy-mm-dd',
        //             locale: {
        //                 format: 'YYYY-MM-DD'
        //             },
        //         });
        //     }
        // });
    </script>
@endpush

@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Employee Salary') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Employee Salary') }}</li>
@endsection


@section('content')
    <div class="row">

        <div class="col-md-12 col-lg-12 col-sm-12 col-xl-12">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    {{-- <h5></h5> --}}
                    <div class="table-responsive">
                        <table class="table" id="pc-dt-simple">
                            <thead>
                                <tr>
                                    <th>{{ __('Employee Id') }}</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Payroll Type') }}</th>
                                    <th>{{ __('Gross Salary') }}</th>
                                    <th>{{ __('Basic Salary') }}</th>
                                    {{-- <th>{{ __('Loan/Advance') }}</th> --}}
                                    <th>{{ __('Net Salary') }}</th>
                                    <th width="200px">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($employees as $employee)
                                    @if ($employee?->user)
                                    @php
                                        $_empSalaryType = \App\Models\PayslipType::find($employee->salary_type);
                                        $_empIsHourly   = ($_empSalaryType->salary_basis ?? 'monthly') === 'hourly';
                                    @endphp
                                    <tr>
                                        <td>
                                            <a href="{{ route('setsalary.show', \Illuminate\Support\Facades\Crypt::encrypt($employee->id)) }}"
                                                class="btn btn-outline-primary">
                                                {{ \Auth::user()->employeeIdFormat($employee->employee_id) }}
                                            </a>
                                        </td>

                                        <td>{{ $employee->name }}</td>
                                        <td>{{ !empty($employee->salary_type()) ? $employee->salary_type() : '-' }}</td>

                                        {{-- Gross Salary column --}}
                                        <td>
                                            @if ($_empIsHourly)
                                                {{ \Auth::user()->priceFormat($employee->salary) }}
                                                <small class="text-muted d-block" style="font-size:10px;">{{ __('per hr') }}</small>
                                            @else
                                                {{ \Auth::user()->priceFormat($employee->salary) }}
                                            @endif
                                        </td>

                                        {{-- Basic Salary column (not applicable for hourly) --}}
                                        <td>
                                            @if ($_empIsHourly)
                                                <span class="text-muted">—</span>
                                            @else
                                                {{ \Auth::user()->priceFormat($employee->basic_salary) }}
                                            @endif
                                        </td>

                                        {{-- Net Salary: hourly net varies by hours worked each month --}}
                                        <td>
                                            @if ($_empIsHourly)
                                                <span class="text-muted" title="{{ __('Varies by hours worked') }}">—</span>
                                            @else
                                                {{ !empty($employee->get_net_salary()) ? \Auth::user()->priceFormat($employee->get_net_salary()) : '-' }}
                                            @endif
                                        </td>

                                        <td class="Action">
                                            @if ($employee?->user?->is_active == 1 && $employee?->user?->is_disable == 1)
                                            <div class="dt-buttons">
                                                    <span>
                                                        <div class="action-btn bg-warning">
                                                            <a href="{{ route('setsalary.show', \Illuminate\Support\Facades\Crypt::encrypt($employee->id)) }}"
                                                                class="mx-3 btn btn-sm  align-items-center"
                                                                data-bs-toggle="tooltip" title=""
                                                                data-bs-original-title="{{ __('View') }}">
                                                                <span class="text-white"><i class="ti ti-eye"></i></span>
                                                            </a>
                                                        </div>
                                                    </span>
                                                </div>
                                            @else
                                                <i class="ti ti-lock"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

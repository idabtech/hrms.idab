@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Employee  Salary List') }}
@endsection
@section('content')
    <div class="row">
        <div class="row">
            <div class="col-lg-12">
                <section class="nav-tabs">
                    <div class="col-lg-12 our-system">
                        <div class="row">
                            <ul class="nav nav-tabs my-4">
                                <li>
                                    <a data-toggle="tab" href="#salary" class="active">{{ __('Salary') }}</a>
                                </li>
                                <li>
                                    <a data-toggle="tab" href="#allowance" class="">{{ __('Allowance') }}</a>
                                </li>
                                <li>
                                    <a data-toggle="tab" href="#commission" class="">{{ __('Commission') }}</a>
                                </li>
                                <li>
                                    <a data-toggle="tab" href="#loan" class="">{{ __('Loan') }}</a>
                                </li>
                                <li>
                                    <a data-toggle="tab" href="#saturation-deduction"
                                        class="">{{ __('Statutory Deductions') }}</a>
                                </li>
                                <li>
                                    <a data-toggle="tab" href="#other-payment" class="">{{ __('Other Payment') }}</a>
                                </li>
                                <li>
                                    <a data-toggle="tab" href="#overtime" class="">{{ __('Overtime') }}</a>
                                </li>
                                @if(\App\Models\Utility::isUkRequest())
                                <li>
                                    <a data-toggle="tab" href="#uk-payroll" class="">{{ __('Payroll') }}</a>
                                </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                    <div class="tab-content">
                        <div id="salary" class="tab-pane in active">
                            <div class="card">
                                <div class="card-body">
                                    {{ Form::model($employee, ['route' => ['employee.salary.update', $employee->id], 'method' => 'POST', 'class' => 'needs-validation', 'novalidate']) }}
                                    @php
                                        $payslipTypeBasisMap = \App\Models\PayslipType::whereIn('id', collect($payslip_type)->keys()->toArray())
                                            ->pluck('salary_basis', 'id')->toArray();
                                    @endphp
                                    <script>
                                        var _salaryBasisMapEdit = @json($payslipTypeBasisMap);
                                        function _updateSalaryLabelEdit(selectEl) {
                                            var basis = _salaryBasisMapEdit[parseInt(selectEl.value)] || 'monthly';
                                            var label = document.getElementById('salary-label-edit');
                                            var input = document.getElementById('salary-input-edit');
                                            var hint  = document.getElementById('salary-hint-edit');
                                            if (basis === 'hourly') {
                                                label.textContent  = '{{ __('Hourly Rate (per hour)') }}';
                                                input.placeholder  = '{{ __('Enter rate per hour e.g. 15.50') }}';
                                                hint.style.display = 'block';
                                            } else {
                                                label.textContent  = '{{ __('Monthly Salary') }}';
                                                input.placeholder  = '{{ __('Enter monthly salary amount') }}';
                                                hint.style.display = 'none';
                                            }
                                        }
                                        document.addEventListener('DOMContentLoaded', function () {
                                            var sel = document.getElementById('salary-type-edit');
                                            if (sel) { _updateSalaryLabelEdit(sel); sel.addEventListener('change', function(){ _updateSalaryLabelEdit(this); }); }
                                        });
                                    </script>
                                    <div class="row">
                                        <div class="col-12 col-md-6">
                                            <div class="form-group">
                                                {{ Form::label('salary_type', __('Payslip Type'), ['class' => 'col-form-label']) }}
                                                {{ Form::select('salary_type', $payslip_type, null, ['class' => 'form-control', 'required' => 'required', 'id' => 'salary-type-edit', 'onchange' => '_updateSalaryLabelEdit(this)']) }}
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="form-group">
                                                <label for="salary-input-edit" id="salary-label-edit" class="col-form-label">{{ __('Monthly Salary') }}</label>
                                                {{ Form::number('salary', null, ['class' => 'form-control', 'required' => 'required', 'id' => 'salary-input-edit', 'placeholder' => __('Enter monthly salary amount'), 'step' => '0.01']) }}
                                                <small id="salary-hint-edit" class="text-info" style="display:none;">
                                                    <i class="ti ti-info-circle"></i>
                                                    {{ __('Enter the hourly rate. Gross = hourly rate × actual hours worked.') }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    @can('Create Set Salary')
                                        <div class="row">
                                            <div class="col-12 text-right mt-1">
                                                <input type="submit" value="{{ __('Save Change') }}"
                                                    class="btn-create badge-blue">
                                            </div>
                                        </div>
                                    @endcan
                                    {{ Form::close() }}
                                </div>
                            </div>
                        </div>
                        <div id="allowance" class="tab-pane">
                            <div class="card">
                                <div class="card-body">
                                    {{ Form::open(['url' => 'allowance', 'method' => 'post']) }}
                                    @csrf
                                    {{ Form::hidden('employee_id', $employee->id, []) }}
                                    <div class="row">
                                        <div class="col-12 col-md-4">
                                            <div class="form-group">
                                                {{ Form::label('allowance_option', __('Allowance Options'), ['class' => 'col-form-label']) }}
                                                {{ Form::select('allowance_option', $allowance_options, null, ['class' => 'form-control ', 'required' => 'required']) }}
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-4">
                                            <div class="form-group">
                                                {{ Form::label('title', __('Title'), ['class' => 'col-form-label']) }}
                                                {{ Form::text('title', null, ['class' => 'form-control', 'required' => 'required']) }}
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <div class="form-group">
                                                {{ Form::label('amount', __('Amount'), ['class' => 'col-form-label']) }}
                                                {{ Form::number('amount', null, ['class' => 'form-control ', 'required' => 'required', 'step' => '0.01']) }}
                                            </div>
                                        </div>
                                    </div>
                                    @can('Create Allowance')
                                        <div class="row">
                                            <div class="col-12 text-right mt-1">
                                                <input type="submit" value="{{ __('Save Change') }}"
                                                    class="btn-create badge-blue">
                                            </div>
                                        </div>
                                    @endcan
                                    {{ Form::close() }}
                                    <hr>
                                    <div class="table-responsive">
                                        <table class="table table-striped mb-0" id="allowance-dataTable">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Employee Name') }}</th>
                                                    <th>{{ __('Allownace Option') }}</th>
                                                    <th>{{ __('Title') }}</th>
                                                    <th>{{ __('Amount') }}</th>
                                                    <th>{{ __('Action') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($allowances as $allowance)
                                                    <tr>
                                                        <td>{{ $allowance->employee()->name }}</td>
                                                        <td>{{ $allowance->allowance_option()->name }}</td>
                                                        <td>{{ $allowance->title }}</td>
                                                        <td>{{ \Auth::user()->priceFormat($allowance->amount) }}</td>
                                                        @can('Delete Set Salary')
                                                            <td class="d-flex">
                                                                @can('Edit Allowance')
                                                                    <a href="javascript:void(0)"
                                                                        data-url="{{ URL::to('allowance/' . $allowance->id . '/edit') }}"
                                                                        data-size="lg" data-ajax-popup="true"
                                                                        data-title="{{ __('Edit Allowance') }}"
                                                                        class="action-btn btn-primary me-1 btn btn-sm d-inline-flex align-items-center"
                                                                        data-toggle="tooltip"
                                                                        data-original-title="{{ __('Edit') }}"><i
                                                                            class="ti ti-pencil"></i></a>
                                                                @endcan
                                                                @can('Delete Allowance')
                                                                    {!! Form::open([
                                                                        'method' => 'DELETE',
                                                                        'route' => ['allowance.destroy', $allowance->id],
                                                                        'id' => 'delete-form-' . $allowance->id,
                                                                    ]) !!}
                                                                    <a href="javascript:void(0)"
                                                                        class="action-btn btn-danger me-1 btn btn-sm d-inline-flex align-items-center show_confirm"
                                                                        data-bs-toggle="tooltip" data-bs-placement="bottom"
                                                                        title="{{ __('Delete') }}">
                                                                        <i class="ti ti-trash"></i></a>
                                                                    {!! Form::close() !!}
                                                                @endcan
                                                            </td>
                                                        @endcan
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="commission" class="tab-pane">
                            <div class="card">
                                <div class="card-body">
                                    {{ Form::open(['url' => 'commission', 'method' => 'post']) }}
                                    @csrf
                                    {{ Form::hidden('employee_id', $employee->id, []) }}
                                    <div class="row">
                                        <div class="col-12 col-md-6">
                                            <div class="form-group">
                                                {{ Form::label('title', __('Title'), ['class' => 'col-form-label']) }}
                                                {{ Form::text('title', null, ['class' => 'form-control ', 'required' => 'required']) }}
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="form-group">
                                                {{ Form::label('amount', __('Amount'), ['class' => 'col-form-label']) }}
                                                {{ Form::number('amount', null, ['class' => 'form-control ', 'required' => 'required', 'step' => '0.01']) }}
                                            </div>
                                        </div>
                                    </div>
                                    @can('Create Commission')
                                        <div class="row">
                                            <div class="col-12 text-right mt-1">
                                                <input type="submit" value="{{ __('Save Change') }}"
                                                    class="btn-create badge-blue">
                                            </div>
                                        </div>
                                    @endcan
                                    {{ Form::close() }}
                                    <hr>
                                    <div class="table-responsive">
                                        <table class="table table-striped mb-0" id="commission-dataTable">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Employee Name') }}</th>
                                                    <th>{{ __('Title') }}</th>
                                                    <th>{{ __('Amount') }}</th>
                                                    <th>{{ __('Action') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($commissions as $commission)
                                                    <tr>
                                                        <td>{{ $commission->employee()->name }}</td>
                                                        <td>{{ $commission->title }}</td>
                                                        <td>{{ \Auth::user()->priceFormat($commission->amount) }}</td>

                                                        <td class="d-flex">
                                                            @can('Edit Commission')
                                                                <a href="javascript:void(0)"
                                                                    data-url="{{ URL::to('commission/' . $commission->id . '/edit') }}"
                                                                    data-size="lg" data-ajax-popup="true"
                                                                    data-title="{{ __('Edit Commission') }}"
                                                                    class="action-btn btn-primary me-1 btn btn-sm d-inline-flex align-items-center"
                                                                    data-toggle="tooltip"
                                                                    data-original-title="{{ __('Edit') }}"><i
                                                                        class="ti ti-pencil"></i></a>
                                                            @endcan
                                                            @can('Delete Commission')
                                                                {!! Form::open([
                                                                    'method' => 'DELETE',
                                                                    'route' => ['commission.destroy', $commission->id],
                                                                    'id' => 'delete-form-' . $commission->id,
                                                                ]) !!}
                                                                <a href="javascript:void(0)"
                                                                    class="action-btn btn-danger me-1 btn btn-sm d-inline-flex align-items-center show_confirm"
                                                                    data-bs-toggle="tooltip" data-bs-placement="bottom"
                                                                    title="{{ __('Delete') }}">
                                                                    <i class="ti ti-trash"></i></a>
                                                                {!! Form::close() !!}
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
                        <div id="loan" class="tab-pane">
                            <div class="card">
                                <div class="card-body">
                                    {{ Form::open(['url' => 'loan', 'method' => 'post']) }}
                                    @csrf
                                    {{ Form::hidden('employee_id', $employee->id, []) }}
                                    <div class="row">
                                        <div class="col-12 col-md-4">
                                            <div class="form-group">
                                                {{ Form::label('loan_option', __('Loan Options'), ['class' => 'col-form-label']) }}
                                                {{ Form::select('loan_option', $loan_options, null, ['class' => 'form-control ', 'required' => 'required']) }}
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <div class="form-group">
                                                {{ Form::label('title', __('Title'), ['class' => 'col-form-label']) }}
                                                {{ Form::text('title', null, ['class' => 'form-control ', 'required' => 'required']) }}
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <div class="form-group">
                                                {{ Form::label('amount', __('Loan Amount'), ['class' => 'col-form-label']) }}
                                                {{ Form::number('amount', null, ['class' => 'form-control ', 'required' => 'required', 'step' => '0.01']) }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12 col-md-4">
                                            <div class="form-group">
                                                {{ Form::label('start_date', __('Start Date'), ['class' => 'col-form-label']) }}
                                                {{ Form::text('start_date', null, ['class' => 'form-control ', 'id' => 'data_picker4', 'required' => 'required']) }}
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <div class="form-group">
                                                {{ Form::label('end_date', __('End Date'), ['class' => 'col-form-label']) }}
                                                {{ Form::text('end_date', null, ['class' => 'form-control ', 'id' => 'data_picker3', 'required' => 'required']) }}
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <div class="form-group">
                                                {{ Form::label('reason', __('Reason'), ['class' => 'col-form-label']) }}
                                                {{ Form::textarea('reason', null, ['class' => 'form-control', 'rows' => 3, 'required' => 'required']) }}
                                            </div>
                                        </div>
                                    </div>

                                    @can('Create Loan')
                                        <div class="row">
                                            <div class="col-12 text-right mt-1">
                                                <input type="submit" value="{{ __('Save Change') }}"
                                                    class="btn-create badge-blue">
                                            </div>
                                        </div>
                                    @endcan
                                    {{ Form::close() }}

                                    <hr>

                                    <div class="table-responsive">
                                        <table class="table table-striped mb-0" id="loan-dataTable">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('employee') }}</th>
                                                    <th>{{ __('Loan Options') }}</th>
                                                    <th>{{ __('Title') }}</th>
                                                    <th>{{ __('Loan Amount') }}</th>
                                                    <th>{{ __('Start Date') }}</th>
                                                    <th>{{ __('End Date') }}</th>
                                                    <th>{{ __('Action') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($loans as $loan)
                                                    <tr>
                                                        <td>{{ $loan->employee()->name }}</td>
                                                        <td>{{ $loan->loan_option()->name }}</td>
                                                        <td>{{ $loan->title }}</td>
                                                        <td>{{ \Auth::user()->priceFormat($loan->amount) }}</td>
                                                        <td>{{ \Auth::user()->dateFormat($loan->start_date) }}</td>
                                                        <td>{{ \Auth::user()->dateFormat($loan->end_date) }}</td>

                                                        <td class="d-flex">
                                                            @can('Edit Loan')
                                                                <a href="javascript:void(0)"
                                                                    data-url="{{ URL::to('loan/' . $loan->id . '/edit') }}"
                                                                    data-size="lg" data-ajax-popup="true"
                                                                    data-title="{{ __('Edit Loan') }}"
                                                                    class="action-btn btn-primary me-1 btn btn-sm d-inline-flex align-items-center"
                                                                    data-toggle="tooltip"
                                                                    data-original-title="{{ __('Edit') }}"><i
                                                                        class="ti ti-pencil"></i></a>
                                                            @endcan
                                                            @can('Delete Loan')
                                                                {!! Form::open([
                                                                    'method' => 'DELETE',
                                                                    'route' => ['loan.destroy', $loan->id],
                                                                    'id' => 'delete-form-' . $loan->id,
                                                                ]) !!}
                                                                <a href="javascript:void(0)"
                                                                    class="action-btn btn-danger me-1 btn btn-sm d-inline-flex align-items-center show_confirm"
                                                                    data-bs-toggle="tooltip" data-bs-placement="bottom"
                                                                    title="{{ __('Delete') }}">
                                                                    <i class="ti ti-trash"></i></a>
                                                                {!! Form::close() !!}
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
                        <div id="saturation-deduction" class="tab-pane">
                            <div class="card">
                                <div class="card-body">
                                    {{ Form::open(['url' => 'saturationdeduction', 'method' => 'post']) }}
                                    @csrf
                                    {{ Form::hidden('employee_id', $employee->id, []) }}
                                    <div class="row">
                                        <div class="col-12 col-md-4">
                                            <div class="form-group">
                                                {{ Form::label('deduction_option', __('Deduction Options'), ['class' => 'col-form-label']) }}
                                                {{ Form::select('deduction_option', $deduction_options, null, ['class' => 'form-control ', 'required' => 'required']) }}
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <div class="form-group">
                                                {{ Form::label('title', __('Title'), ['class' => 'col-form-label']) }}
                                                {{ Form::text('title', null, ['class' => 'form-control ', 'required' => 'required']) }}
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <div class="form-group">
                                                {{ Form::label('amount', __('Amount'), ['class' => 'col-form-label']) }}
                                                {{ Form::number('amount', null, ['class' => 'form-control ', 'required' => 'required', 'step' => '0.01']) }}
                                            </div>
                                        </div>
                                    </div>
                                    @can('Create Saturation Deduction')
                                        <div class="row">
                                            <div class="col-12 text-right mt-1">
                                                <input type="submit" value="{{ __('Save Change') }}"
                                                    class="btn-create badge-blue">
                                            </div>
                                        </div>
                                    @endcan
                                    {{ Form::close() }}

                                    <hr>
                                    <div class="table-responsive">
                                        <table class="table table-striped mb-0" id="saturation-deduction-dataTable">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Employee Name') }}</th>
                                                    <th>{{ __('Deduction Option') }}</th>
                                                    <th>{{ __('Title') }}</th>
                                                    <th>{{ __('Amount') }}</th>
                                                    <th>{{ __('Action') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($saturationdeductions as $saturationdeduction)
                                                    <tr>

                                                        <td>{{ $saturationdeduction->employee()->name }}</td>
                                                        <td>{{ $saturationdeduction->deduction_option()->name }}</td>
                                                        <td>{{ $saturationdeduction->title }}</td>
                                                        <td>{{ \Auth::user()->priceFormat($saturationdeduction->amount) }}
                                                        </td>

                                                        <td class="d-flex">
                                                            @can('Edit Saturation Deduction')
                                                                <a href="javascript:void(0)"
                                                                    data-url="{{ URL::to('saturationdeduction/' . $saturationdeduction->id . '/edit') }}"
                                                                    data-size="lg" data-ajax-popup="true"
                                                                    data-title="{{ __('Edit Statutory Deductions') }}"
                                                                    class="action-btn btn-primary me-1 btn btn-sm d-inline-flex align-items-center"
                                                                    data-toggle="tooltip"
                                                                    data-original-title="{{ __('Edit') }}"><i
                                                                        class="ti ti-pencil"></i></a>
                                                            @endcan
                                                            @can('Delete Saturation Deduction')
                                                                {!! Form::open([
                                                                    'method' => 'DELETE',
                                                                    'route' => ['saturationdeduction.destroy', $saturationdeduction->id],
                                                                    'id' => 'delete-form-' . $saturationdeduction->id,
                                                                ]) !!}
                                                                <a href="javascript:void(0)"
                                                                    class="action-btn btn-danger me-1 btn btn-sm d-inline-flex align-items-center show_confirm"
                                                                    data-bs-toggle="tooltip" data-bs-placement="bottom"
                                                                    title="{{ __('Delete') }}">
                                                                    <i class="ti ti-trash"></i></a>
                                                                {!! Form::close() !!}
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
                        <div id="other-payment" class="tab-pane">
                            <div class="card">
                                <div class="card-body">
                                    {{ Form::open(['url' => 'otherpayment', 'method' => 'post']) }}
                                    @csrf
                                    {{ Form::hidden('employee_id', $employee->id, []) }}
                                    <div class="row">
                                        <div class="col-12 col-md-6">
                                            <div class="form-group">
                                                {{ Form::label('title', __('Title'), ['class' => 'col-form-label']) }}
                                                {{ Form::text('title', null, ['class' => 'form-control ', 'required' => 'required']) }}
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="form-group">
                                                {{ Form::label('amount', __('Amount'), ['class' => 'col-form-label']) }}
                                                {{ Form::number('amount', null, ['class' => 'form-control ', 'required' => 'required', 'step' => '0.01']) }}
                                            </div>
                                        </div>
                                    </div>

                                    @can('Create Other Payment')
                                        <div class="row">
                                            <div class="col-12 text-right mt-1">
                                                <input type="submit" value="{{ __('Save Change') }}"
                                                    class="btn-create badge-blue">
                                            </div>
                                        </div>
                                    @endcan
                                    {{ Form::close() }}


                                    <hr>
                                    <div class="table-responsive">
                                        <table class="table table-striped mb-0" id="other-payment-dataTable">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('employee') }}</th>
                                                    <th>{{ __('Title') }}</th>
                                                    <th>{{ __('Amount') }}</th>
                                                    <th>{{ __('Action') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($otherpayments as $otherpayment)
                                                    <tr>
                                                        <td>{{ $otherpayment->employee()->name }}</td>
                                                        <td>{{ $otherpayment->title }}</td>
                                                        <td>{{ \Auth::user()->priceFormat($otherpayment->amount) }}</td>

                                                        <td class="d-flex">
                                                            @can('Edit Other Payment')
                                                                <a href="javascript:void(0)"
                                                                    data-url="{{ URL::to('otherpayment/' . $otherpayment->id . '/edit') }}"
                                                                    data-size="lg" data-ajax-popup="true"
                                                                    data-title="{{ __('Edit Other Payment') }}"
                                                                    class="action-btn btn-primary me-1 btn btn-sm d-inline-flex align-items-center"
                                                                    data-toggle="tooltip"
                                                                    data-original-title="{{ __('Edit') }}"><i
                                                                        class="ti ti-pencil"></i></a>
                                                            @endcan
                                                            @can('Delete Other Payment')
                                                                {!! Form::open([
                                                                    'method' => 'DELETE',
                                                                    'route' => ['otherpayment.destroy', $otherpayment->id],
                                                                    'id' => 'delete-form-' . $otherpayment->id,
                                                                ]) !!}
                                                                <a href="javascript:void(0)"
                                                                    class="action-btn btn-danger me-1 btn btn-sm d-inline-flex align-items-center show_confirm"
                                                                    data-bs-toggle="tooltip" data-bs-placement="bottom"
                                                                    title="{{ __('Delete') }}">
                                                                    <i class="ti ti-trash"></i></a>
                                                                {!! Form::close() !!}
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
                        <div id="overtime" class="tab-pane">
                            <div class="card">
                                <div class="card-body">
                                    {{ Form::open(['url' => 'overtime', 'method' => 'post']) }}
                                    @csrf
                                    {{ Form::hidden('employee_id', $employee->id, []) }}
                                    <div class="row">
                                        <div class="col-12 col-md-6">
                                            <div class="form-group">
                                                {{ Form::label('title', __('Overtime Title'), ['class' => 'col-form-label']) }}
                                                {{ Form::text('title', null, ['class' => 'form-control ', 'required' => 'required']) }}
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="form-group">
                                                {{ Form::label('number_of_days', __('Number of days'), ['class' => 'col-form-label']) }}
                                                {{ Form::number('number_of_days', null, ['class' => 'form-control ', 'required' => 'required', 'step' => '0.01']) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 col-md-6">
                                            <div class="form-group">
                                                {{ Form::label('hours', __('Hours'), ['class' => 'col-form-label']) }}
                                                {{ Form::number('hours', null, ['class' => 'form-control ', 'required' => 'required', 'step' => '0.01']) }}
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="form-group">
                                                {{ Form::label('rate', __('Rate'), ['class' => 'col-form-label']) }}
                                                {{ Form::number('rate', null, ['class' => 'form-control ', 'required' => 'required', 'step' => '0.01']) }}
                                            </div>
                                        </div>
                                    </div>
                                    @can('Create Overtime')
                                        <div class="row">
                                            <div class="col-12 text-right mt-1">
                                                <input type="submit" value="{{ __('Save Change') }}"
                                                    class="btn-create badge-blue">
                                            </div>
                                        </div>
                                    @endcan
                                    {{ Form::close() }}

                                    <hr>
                                    <div class="table-responsive">
                                        <table class="table table-striped mb-0" id="overtime-dataTable">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Employee Name') }}</th>
                                                    <th>{{ __('Overtime Title') }}</th>
                                                    <th>{{ __('Number of days') }}</th>
                                                    <th>{{ __('Hours') }}</th>
                                                    <th>{{ __('Rate') }}</th>

                                                    <th>{{ __('Action') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($overtimes as $overtime)
                                                    <tr>
                                                        <td>{{ $overtime->employee()->name }}</td>
                                                        <td>{{ $overtime->title }}</td>
                                                        <td>{{ $overtime->number_of_days }}</td>
                                                        <td>{{ $overtime->hours }}</td>
                                                        <td>{{ \Auth::user()->priceFormat($overtime->rate) }}</td>

                                                        <td class="d-flex">
                                                            @can('Edit Overtime')
                                                                <a href="javascript:void(0)"
                                                                    data-url="{{ URL::to('overtime/' . $overtime->id . '/edit') }}"
                                                                    data-size="lg" data-ajax-popup="true"
                                                                    data-title="{{ __('Edit OverTime') }}"
                                                                    class="action-btn btn-primary me-1 btn btn-sm d-inline-flex align-items-center"
                                                                    data-toggle="tooltip"
                                                                    data-original-title="{{ __('Edit') }}"><i
                                                                        class="ti ti-pencil"></i></a>
                                                            @endcan
                                                            @can('Delete Overtime')
                                                                {!! Form::open([
                                                                    'method' => 'DELETE',
                                                                    'route' => ['overtime.destroy', $overtime->id],
                                                                    'id' => 'delete-form-' . $overtime->id,
                                                                ]) !!}
                                                                <a href="javascript:void(0)"
                                                                    class="action-btn btn-danger me-1 btn btn-sm d-inline-flex align-items-center show_confirm"
                                                                    data-bs-toggle="tooltip" data-bs-placement="bottom"
                                                                    title="{{ __('Delete') }}">
                                                                    <i class="ti ti-trash"></i></a>
                                                                {!! Form::close() !!}
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

                        {{-- ═══ UK Payroll Tab ═══ --}}
                        @if(\App\Models\Utility::isUkRequest())
                        <div id="uk-payroll" class="tab-pane">
                            <div class="card">
                                <div class="card-body">
                                    {{ Form::model($employee, ['route' => ['employee.uk.payroll.update', $employee->id], 'method' => 'POST', 'class' => 'needs-validation', 'novalidate']) }}
                                    @csrf
                                    <div class="row">
                                        <div class="col-12 col-md-4">
                                            <div class="form-group">
                                                {{ Form::label('tax_payer_id', __('Tax Code'), ['class' => 'col-form-label']) }}
                                                {{ Form::text('tax_payer_id', null, ['class' => 'form-control', 'placeholder' => 'e.g. 1257L']) }}
                                                <small class="text-muted">{{ __('HMRC tax code assigned to this employee') }}</small>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <div class="form-group">
                                                {{ Form::label('ni_number', __('NI Number'), ['class' => 'col-form-label']) }}
                                                <div class="input-group">
                                                    {{ Form::text('ni_number', null, ['class' => 'form-control', 'placeholder' => 'e.g. JS877742C', 'id' => 'ni_number_field']) }}
                                                    <button type="button" class="btn btn-outline-info btn-sm" id="btn-verify-nino" title="{{ __('Verify NI Number') }}">
                                                        <i class="fa fa-check-circle"></i> {{ __('Verify') }}
                                                    </button>
                                                </div>
                                                <small class="text-muted">{{ __('National Insurance Number') }}</small>
                                                <div id="nino-verification-result" class="mt-1" style="display:none;"></div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <div class="form-group">
                                                {{ Form::label('ni_table_letter', __('NI Table Letter'), ['class' => 'col-form-label']) }}
                                                {{ Form::select('ni_table_letter', [
                                                    'A' => 'A — All not under another category',
                                                    'B' => 'B — Married women / widows (reduced rate)',
                                                    'C' => 'C — Employees over state pension age',
                                                    'H' => 'H — Apprentice under 25',
                                                    'J' => 'J — Deferment (second job)',
                                                    'M' => 'M — Under 21',
                                                    'Z' => 'Z — Under 21, deferment',
                                                ], null, ['class' => 'form-control']) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 col-md-4">
                                            <div class="form-group">
                                                {{ Form::label('payment_method', __('Payment Method'), ['class' => 'col-form-label']) }}
                                                {{ Form::select('payment_method', [
                                                    'BACS'   => 'BACS',
                                                    'CHAPS'  => 'CHAPS',
                                                    'Cash'   => 'Cash',
                                                    'Cheque' => 'Cheque',
                                                ], null, ['class' => 'form-control']) }}
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <div class="form-group">
                                                {{ Form::label('bank_name', __('Bank Name'), ['class' => 'col-form-label']) }}
                                                {{ Form::text('bank_name', null, ['class' => 'form-control', 'placeholder' => 'e.g. Barclays']) }}
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <div class="form-group">
                                                {{ Form::label('bank_identifier_code', __('Sort Code'), ['class' => 'col-form-label']) }}
                                                {{ Form::text('bank_identifier_code', null, ['class' => 'form-control', 'placeholder' => 'e.g. 20-00-00']) }}
                                                <small class="text-muted">{{ __('6-digit sort code') }}</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 col-md-6">
                                            <div class="form-group">
                                                {{ Form::label('account_number', __('Account Number'), ['class' => 'col-form-label']) }}
                                                {{ Form::text('account_number', null, ['class' => 'form-control', 'placeholder' => 'e.g. 12345678']) }}
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="form-group">
                                                {{ Form::label('account_holder_name', __('Account Holder Name'), ['class' => 'col-form-label']) }}
                                                {{ Form::text('account_holder_name', null, ['class' => 'form-control']) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 text-right mt-1">
                                            <input type="submit" value="{{ __('Save Changes') }}" class="btn-create badge-blue">
                                        </div>
                                    </div>
                                    {{ Form::close() }}
                                </div>
                            </div>
                        </div>
                        {{-- end UK Payroll Tab --}}
                        @endif

                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection

@push('script-page')
    <script type="text/javascript">
        $(document).ready(function() {
            var d_id = $('#department_id').val();
            var designation_id = '{{ $employee->designation_id }}';
            getDesignation(d_id);


            $("#allowance-dataTable").dataTable({
                "columnDefs": [{
                    "sortable": false,
                    "targets": [1]
                }]
            });

            $("#commission-dataTable").dataTable({
                "columnDefs": [{
                    "sortable": false,
                    "targets": [1]
                }]
            });

            $("#loan-dataTable").dataTable({
                "columnDefs": [{
                    "sortable": false,
                    "targets": [1]
                }]
            });

            $("#saturation-deduction-dataTable").dataTable({
                "columnDefs": [{
                    "sortable": false,
                    "targets": [1]
                }]
            });

            $("#other-payment-dataTable").dataTable({
                "columnDefs": [{
                    "sortable": false,
                    "targets": [1]
                }]
            });

            $("#overtime-dataTable").dataTable({
                "columnDefs": [{
                    "sortable": false,
                    "targets": [1]
                }]
            });


        });

        $(document).on('change', 'select[name=department_id]', function() {
            var department_id = $(this).val();
            getDesignation(department_id);
        });

        function getDesignation(did) {
            $.ajax({
                url: '{{ route('employee.json') }}',
                type: 'POST',
                data: {
                    "department_id": did,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    $('#designation_id').empty();
                    $('#designation_id').append(
                        '<option value="">{{ __('Select any Designation') }}</option>');
                    $.each(data, function(key, value) {
                        var select = '';
                        if (key == '{{ $employee->designation_id }}') {
                            select = 'selected';
                        }

                        $('#designation_id').append('<option value="' + key + '"  ' + select + '>' +
                            value + '</option>');
                    });
                }
            });
        }

        // ── HMRC NI Number Verification ──────────────────────────────────────
        $('#btn-verify-nino').on('click', function() {
            var nino = $('#ni_number_field').val().trim();
            var $btn = $(this);
            var $result = $('#nino-verification-result');

            if (!nino) {
                $result.html('<span class="text-warning"><i class="fa fa-exclamation-triangle"></i> {{ __("Please enter a NI number first.") }}</span>').show();
                return;
            }

            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
            $result.hide();

            $.ajax({
                url: '{{ route("hmrc.verify.nino") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    nino: nino
                },
                success: function(res) {
                    if (res.format_valid) {
                        var icon = res.hmrc_connected ? 'fa-check-circle text-success' : 'fa-check-circle text-info';
                        var msg = '<span class="' + (res.hmrc_connected ? 'text-success' : 'text-info') + '">'
                            + '<i class="fa ' + icon + '"></i> '
                            + '{{ __("Valid format") }}: ' + (res.formatted_nino || res.nino);
                        if (res.hmrc_connected) {
                            msg += ' <small class="text-muted">({{ __("HMRC Connected") }})</small>';
                        }
                        msg += '</span>';
                        $result.html(msg).show();
                    } else {
                        $result.html('<span class="text-danger"><i class="fa fa-times-circle"></i> ' + res.message + '</span>').show();
                    }
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON ? xhr.responseJSON.message : '{{ __("Verification failed.") }}';
                    $result.html('<span class="text-danger"><i class="fa fa-times-circle"></i> ' + msg + '</span>').show();
                },
                complete: function() {
                    $btn.prop('disabled', false).html('<i class="fa fa-check-circle"></i> {{ __("Verify") }}');
                }
            });
        });
    </script>
@endpush

@extends('layouts.admin')
@push('css-page')
<style>
    .emp-basic {
        background-color: aliceblue;
    }
    .deduction {
        background-color: #f2e1e1;
    }
    .earning {
        background-color: #deefdf;
    }
</style>
@endpush
@section('page-title')
    {{ __('Employee Set Salary') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard') }}">{{ __('Home') }}</a>
    </li>
    <li class="breadcrumb-item"><a href="{{ url('setsalary') }}">{{ __('Set Salary') }}</a></li>
    <li class="breadcrumb-item">{{ __('Employee Set Salary') }}</li>
@endsection

@section('content')
    <div class="row">

        <div class="col-12">
            <div class="row">
                <div class="col-xl-6">
                    <div class="card set-card emp-basic">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-11">
                                    <h5>{{ __('Employee Salary') }}</h5>
                                </div>
                                @can('Create Set Salary')
                                    <div class="col-1 text-end">
                                        <a data-url="{{ route('employee.basic.salary', $employee->id) }}" data-ajax-popup="true"
                                            data-title="{{ __('Set Basic Salary') }}" data-bs-toggle="tooltip" title=""
                                            class="btn btn-sm btn-primary" data-bs-original-title="{{ __('Set Salary') }}">
                                            <i class="ti ti-plus"></i>
                                        </a>
                                    </div>
                                @endcan
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="project-info d-flex text-sm mb-2">
                                <div class="project-info-inner flex-grow-1">
                                    <b class="m-0">{{ __('Payslip Type') }}</b>
                                    <div class="project-amnt pt-1">{{ $employee->salary_type() }}</div>
                                </div>
                                <div class="project-info-inner text-end">
                                    <b class="m-0">{{ __('Salary') }}</b>
                                    <div class="project-amnt pt-1">{{ \Auth::user()->priceFormat($employee->salary) }}
                                    </div>
                                </div>
                            </div>

                            <div class="project-info d-flex text-sm mb-2">
                                <div class="project-info-inner flex-grow-1">
                                    <b class="m-0">{{ __('Basic Salary') }}</b>
                                </div>
                                <div class="project-info-inner text-end">
                                    <div class="project-amnt pt-1">
                                        {{ \Auth::user()->priceFormat($employee->basic_salary) }}</div>
                                </div>
                            </div><div class="project-info d-flex text-sm mb-2">
                                <div class="project-info-inner flex-grow-1">
                                    <b class="m-0">{{ __('Net Salary') }}</b>
                                </div>
                                <div class="project-info-inner text-end">
                                    <div class="project-amnt pt-1">
                                        {{ \Auth::user()->priceFormat($employee->get_net_salary()) }}</div>
                                </div>
                            </div>
                            <div class="project-info d-flex text-sm mb-2">
                                <div class="project-info-inner flex-grow-1">
                                    <b class="m-0">{{ __('HRA') }}</b>
                                </div>
                                <div class="project-info-inner text-end">
                                    <div class="project-amnt pt-1">

                                        {{ \Auth::user()->priceFormat($employee->get_net_hra()) }}</div>
                                </div>
                            </div><div class="project-info d-flex text-sm mb-2">
                                <div class="project-info-inner flex-grow-1">
                                    <b class="m-0">{{ __('DA') }}</b>
                                </div>
                                <div class="project-info-inner text-end">
                                    <div class="project-amnt pt-1">
                                        {{ \Auth::user()->priceFormat($employee->get_net_da()) }}</div>
                                </div>
                            </div>

                            <div class="project-info d-flex text-sm">
                                <div class="project-info-inner flex-grow-1">
                                    <b class="m-0">{{ __('Account Type') }}</b>
                                    <div class="project-amnt pt-1">
                                        {{ !empty($employee->account_type()) ? $employee->account_type() : '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- allowance -->
                <div class="col-md-6">
                    <div class="card set-card earning">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-11">
                                    <h5>{{ __('Allowance') }}</h5>
                                </div>
                                @can('Create Allowance')
                                    <div class="col-1 text-end">
                                        <a data-url="{{ route('allowances.create', $employee->id) }}" data-ajax-popup="true"
                                            data-title="{{ __('Create Allowance') }}" data-bs-toggle="tooltip" title=""
                                            class="btn btn-sm btn-primary" data-bs-original-title="{{ __('Create') }}">
                                            <i class="ti ti-plus"></i>
                                        </a>
                                    </div>
                                @endcan
                            </div>
                        </div>
                        <div class=" card-body table-border-style" style=" overflow:auto">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Employee Name') }}</th>
                                            <th>{{ __('Allownace Option') }}</th>
                                            <th>{{ __('Title') }}</th>
                                            <th>{{ __('Type') }}</th>
                                            <th>{{ __('Amount') }}</th>
                                            @can(['Edit Allowance', 'Delete Allowance'])
                                                <th>{{ __('Action') }}</th>
                                            @endcan
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($allowances as $allowance)
                                            <tr>
                                                <td>{{ !empty($allowance->employee()) ? $allowance->employee()->name : '' }}
                                                </td>
                                                <td>{{ !empty($allowance->allowance_option()) ? $allowance->allowance_option()->name : '' }}
                                                </td>
                                                <td>{{ $allowance->title }}</td>

                                                <td>{{ ucfirst($allowance->type) }}</td>
                                                @if ($allowance->type == 'fixed')
                                                    <td>{{ \Auth::user()->priceFormat($allowance->amount) }}</td>
                                                @else
                                                    <td>{{ $allowance->amount }}%
                                                        ({{ \Auth::user()->priceFormat($allowance->tota_allow) }})
                                                    </td>
                                                @endif
                                                @can(['Edit Allowance', 'Delete Allowance'])
                                                    <td class="Action">
                                                        @can('Edit Allowance')
                                                            <div class="action-btn me-2">
                                                                <a class="mx-3 btn btn-sm bg-info align-items-center"
                                                                    data-url="{{ URL::to('allowance/' . $allowance->id . '/edit') }}"
                                                                    data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                                    title="" data-title="{{ __('Edit Allowance') }}"
                                                                    data-bs-original-title="{{ __('Edit') }}">
                                                                    <span class="text-white"><i class="ti ti-pencil"></i></span>
                                                                </a>
                                                            </div>
                                                        @endcan
                                                        @can('Delete Allowance')
                                                            <div class="action-btn">
                                                                {!! Form::open([
                                                                    'method' => 'DELETE',
                                                                    'route' => ['allowance.destroy', $allowance->id],
                                                                    'id' => 'delete-form-' . $allowance->id,
                                                                ]) !!}
                                                                <a class=" btn btn-sm bg-danger align-items-center bs-pass-para"
                                                                    data-bs-toggle="tooltip" title="" data-bs-trigger="hover"
                                                                    data-bs-original-title="Delete" aria-label="Delete"><span
                                                                        class="text-white"><i class="ti ti-trash"></i></span></a>
                                                                </form>
                                                            </div>
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

                <!--pension-->
                <div class="col-md-6">
                    <div class="card set-card deduction">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-6">
                                    <h5>{{ __('Pension') }}</h5>
                                </div>
                                @can('Create Pension')
                                    <div class="col text-end">
                                        <a data-url="{{ route('employee.pension.create', $employee->id) }}"
                                            data-ajax-popup="true" data-size="lg" data-title="{{ __('Create pension') }}"
                                            data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary"
                                            data-bs-original-title="{{ __('Create') }}">
                                            <i class="ti ti-plus"></i>
                                        </a>
                                    </div>
                                @endcan
                            </div>
                        </div>
                        <div class=" card-body table-border-style" style=" overflow:auto">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Employee') }}</th>
                                            <th>{{ __('Ttile') }}</th>
                                            <th>{{ __('Amount') }}</th>
                                            @can(['Edit Pension', 'Delete Pension'])
                                                <th>{{ __('Action') }}</th>
                                            @endcan
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($pension as $data)
                                            <tr>
                                                <td>{{ !empty($data->employee()) ? $data->employee()->name : '' }}</td>
                                                <td>{{ $data->title }}</td>
                                                <td>{{ \Auth::user()->priceFormat($data->amount) }}</td>
                                                @can(['Edit Pension', 'Delete Pension'])
                                                    <td class="Action">
                                                        <span>
                                                            @can('Edit Pension')
                                                                <div class="action-btn bg-info ms-2">
                                                                    <a class="mx-3 btn btn-sm  align-items-center"
                                                                        data-url="{{ URL::to('pension/' . $data->id . '/edit') }}"
                                                                        data-ajax-popup="true" data-size="lg"
                                                                        data-bs-toggle="tooltip" title=""
                                                                        data-title="{{ __('Edit Pension') }}"
                                                                        data-bs-original-title="{{ __('Edit') }}">
                                                                        <i class="ti ti-pencil text-white"></i>
                                                                    </a>
                                                                </div>
                                                            @endcan
                                                            @can('Delete Pension')
                                                                <div class="action-btn bg-danger ms-2">
                                                                    {!! Form::open([
                                                                        'method' => 'DELETE',
                                                                        'route' => ['pension.destroy', $data->id],
                                                                        'id' => 'delete-form-' . $data->id,
                                                                    ]) !!}
                                                                    <a class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                                        data-bs-toggle="tooltip" title=""
                                                                        data-bs-original-title="Delete" aria-label="Delete"><i
                                                                            class="ti ti-trash text-white"></i></a>
                                                                    </form>
                                                                </div>
                                                            @endcan
                                                        </span>
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

                <!-- Commission -->
                <div class="col-md-6">
                    <div class="card set-card earning">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-11">
                                    <h5>{{ __('Commission') }}</h5>
                                </div>
                                @can('Create Commission')
                                    <div class="col-1 text-end">
                                        <a data-url="{{ route('commissions.create', $employee->id) }}" data-ajax-popup="true"
                                            data-title="{{ __('Create Commission') }}" data-bs-toggle="tooltip"
                                            title="" class="btn btn-sm btn-primary"
                                            data-bs-original-title="{{ __('Create') }}">
                                            <i class="ti ti-plus"></i>
                                        </a>

                                    </div>
                                @endcan
                            </div>
                        </div>
                        <div class=" card-body table-border-style" style=" overflow:auto">

                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Employee Name') }}</th>
                                            <th>{{ __('Title') }}</th>
                                            <th>{{ __('Type') }}</th>
                                            <th>{{ __('Amount') }}</th>
                                            @can(['Edit Commission', 'Delete Commission'])
                                                <th>{{ __('Action') }}</th>
                                            @endcan
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($commissions as $commission)
                                            <tr>
                                                <td>{{ !empty($commission->employee()) ? $commission->employee()->name : '' }}
                                                </td>
                                                <td>{{ $commission->title }}</td>

                                                <td>{{ ucfirst($commission->type) }}</td>
                                                @if ($commission->type == 'fixed')
                                                    <td>{{ \Auth::user()->priceFormat($commission->amount) }}</td>
                                                @else
                                                    <td>{{ $commission->amount }}%
                                                        ({{ \Auth::user()->priceFormat($commission->tota_allow) }})
                                                    </td>
                                                @endif
                                                @can(['Edit Commission', 'Delete Commission'])
                                                    <td class="Action">
                                                        @can('Edit Commission')
                                                            <div class="action-btn me-2">
                                                                <a class="mx-3 btn btn-sm bg-info align-items-center"
                                                                    data-url="{{ URL::to('commission/' . $commission->id . '/edit') }}"
                                                                    data-ajax-popup="true" data-size="md"
                                                                    data-bs-toggle="tooltip" title=""
                                                                    data-title="{{ __('Edit Commission') }}"
                                                                    data-bs-original-title="{{ __('Edit') }}">
                                                                    <span class="text-white"><i class="ti ti-pencil"></i></span>
                                                                </a>
                                                            </div>
                                                        @endcan
                                                        @can('Delete Commission')
                                                            <div class="action-btn">
                                                                {!! Form::open([
                                                                    'method' => 'DELETE',
                                                                    'route' => ['commission.destroy', $commission->id],
                                                                    'id' => 'delete-form-' . $commission->id,
                                                                ]) !!}
                                                                <a class=" btn btn-sm bg-danger align-items-center bs-pass-para"
                                                                    data-bs-trigger="hover" data-bs-toggle="tooltip"
                                                                    title="" data-bs-original-title="Delete"
                                                                    aria-label="Delete"><span class="text-white"><i
                                                                            class="ti ti-trash"></i></span></a>
                                                                </form>
                                                            </div>
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

                <!-- loan-->
                <div class="col-md-6">
                    <div class="card set-card deduction">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-11">
                                    <h5>{{ __('Loan/Advance') }}</h5>
                                </div>
                                @can('Create Loan')
                                    <div class="col-1 text-end">
                                        <a data-url="{{ route('loans.create', $employee->id) }}" data-ajax-popup="true"
                                            data-title="{{ __('Create Loan') }}" data-bs-toggle="tooltip" title=""
                                            data-size="lg" class="btn btn-sm btn-primary"
                                            data-bs-original-title="{{ __('Create') }}">
                                            <i class="ti ti-plus"></i>
                                        </a>
                                    </div>
                                @endcan
                            </div>
                        </div>
                        <div class=" card-body table-border-style" style=" overflow:auto">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Employee') }}</th>
                                            <th>{{ __('Loan Options') }}</th>
                                            <th>{{ __('Title') }}</th>
                                            <th>{{ __('Type') }}</th>
                                            <th>{{ __('Loan Amount') }}</th>
                                            @can(['Edit Loan', 'Delete Loan'])
                                                <th>{{ __('Action') }}</th>
                                            @endcan
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($loans as $loan)
                                            <tr>
                                                <td>{{ !empty($loan->employee()) ? $loan->employee()->name : '' }}</td>
                                                <td>{{ !empty($loan->loan_option()) ? $loan->loan_option()->name : '' }}
                                                </td>
                                                <td>{{ $loan->title }}</td>
                                                <td>{{ ucfirst($loan->type) }}</td>
                                                @if ($loan->type == 'fixed')
                                                    <td>{{ \Auth::user()->priceFormat($loan->amount) }}</td>
                                                @else
                                                    <td>{{ $loan->amount }}%
                                                        ({{ \Auth::user()->priceFormat($loan->tota_allow) }})
                                                    </td>
                                                @endif
                                                @can(['Edit Loan', 'Delete Loan'])
                                                    <td class="Action">
                                                        @can('Edit Loan')
                                                            <div class="action-btn me-2">
                                                                <a class="mx-3 btn btn-sm bg-info align-items-center"
                                                                    data-url="{{ URL::to('loan/' . $loan->id . '/edit') }}"
                                                                    data-ajax-popup="true" data-size="lg"
                                                                    data-bs-toggle="tooltip" title=""
                                                                    data-title="{{ __('Edit Loan') }}"
                                                                    data-bs-original-title="{{ __('Edit') }}">
                                                                    <span class="text-white"><i class="ti ti-pencil"></i></span>
                                                                </a>
                                                            </div>
                                                        @endcan
                                                        @can('Delete Loan')
                                                            <div class="action-btn">
                                                                {!! Form::open([
                                                                    'method' => 'DELETE',
                                                                    'route' => ['loan.destroy', $loan->id],
                                                                    'id' => 'delete-form-' . $loan->id,
                                                                ]) !!}
                                                                <a class=" btn btn-sm bg-danger align-items-center bs-pass-para"
                                                                    data-bs-trigger="hover" data-bs-toggle="tooltip"
                                                                    title="" data-bs-original-title="Delete"
                                                                    aria-label="Delete"><span class="text-white"><i
                                                                            class="ti ti-trash"></i></span></a>
                                                                </form>
                                                            </div>
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

                <!-- Saturation -->
                <div class="col-md-6">
                    <div class="card set-card deduction">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-11">
                                    <h5>{{ __('Statutory Deductions') }}</h5>
                                </div>
                                @can('Create Saturation Deduction')
                                    <div class="col-1 text-end">
                                        <a data-url="{{ route('saturationdeductions.create', $employee->id) }}"
                                            data-ajax-popup="true" data-size="lg"
                                            data-title="{{ __('Create Statutory Deductions') }}" data-bs-toggle="tooltip"
                                            title="" class="btn btn-sm btn-primary"
                                            data-bs-original-title="{{ __('Create') }}">
                                            <i class="ti ti-plus"></i>
                                        </a>
                                    </div>
                                @endcan
                            </div>
                        </div>
                        <div class=" card-body table-border-style" style=" overflow:auto">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Employee Name') }}</th>
                                            <th>{{ __('Deduction Option') }}</th>
                                            <th>{{ __('Title') }}</th>
                                            <th>{{ __('Type') }}</th>
                                            <th>{{ __('Amount') }}</th>
                                            @can(['Edit Saturation Deduction', 'Delete Saturation Deduction'])
                                                <th>{{ __('Action') }}</th>
                                            @endcan
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($saturationdeductions as $saturationdeduction)
                                            <tr>
                                                <td>{{ !empty($saturationdeduction->employee()) ? $saturationdeduction->employee()->name : '' }}
                                                </td>
                                                <td>{{ !empty($saturationdeduction->deduction_option()) ? $saturationdeduction->deduction_option()->name : '' }}
                                                </td>
                                                <td>{{ $saturationdeduction->title }}</td>
                                                <td>{{ ucfirst($saturationdeduction->type) }}</td>
                                                @if ($saturationdeduction->type == 'fixed')
                                                    <td>{{ \Auth::user()->priceFormat($saturationdeduction->amount) }}
                                                    </td>
                                                @else
                                                    <td>{{ $saturationdeduction->amount }}%
                                                        ({{ \Auth::user()->priceFormat($saturationdeduction->tota_allow) }})
                                                    </td>
                                                @endif
                                                @can(['Edit Saturation Deduction', 'Delete Saturation Deduction'])
                                                    <td class="Action">
                                                        @can('Edit Saturation Deduction')
                                                            <div class="action-btn me-2">
                                                                <a class="mx-3 btn btn-sm bg-info align-items-center"
                                                                    data-url="{{ URL::to('saturationdeduction/' . $saturationdeduction->id . '/edit') }}"
                                                                    data-ajax-popup="true" data-size="lg"
                                                                    data-bs-toggle="tooltip" title=""
                                                                    data-title="{{ __('Edit Statutory Deductions') }}"
                                                                    data-bs-original-title="{{ __('Edit') }}">
                                                                    <span class="text-white"><i class="ti ti-pencil"></i></span>
                                                                </a>
                                                            </div>
                                                        @endcan
                                                        @can('Delete Saturation Deduction')
                                                            <div class="action-btn">
                                                                {!! Form::open([
                                                                    'method' => 'DELETE',
                                                                    'route' => ['saturationdeduction.destroy', $saturationdeduction->id],
                                                                    'id' => 'delete-form-' . $saturationdeduction->id,
                                                                ]) !!}
                                                                <a class=" btn btn-sm bg-danger align-items-center bs-pass-para"
                                                                    data-bs-trigger="hover" data-bs-toggle="tooltip"
                                                                    title="" data-bs-original-title="Delete"
                                                                    aria-label="Delete"><span class="text-white"><i
                                                                            class="ti ti-trash"></i></span></a>
                                                                </form>
                                                            </div>
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

                <!-- other payment-->
                <div class="col-md-6">
                    <div class="card set-card earning">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-11">
                                    <h5>{{ __('Other Payment') }}</h5>
                                </div>
                                @can('Create Other Payment')
                                    <div class="col-1 text-end">

                                        <a data-url="{{ route('otherpayments.create', $employee->id) }}"
                                            data-ajax-popup="true" data-title="{{ __('Create Other Payment') }}"
                                            data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary"
                                            data-bs-original-title="{{ __('Create') }}">
                                            <i class="ti ti-plus"></i>
                                        </a>
                                    </div>
                                @endcan
                            </div>
                        </div>
                        <div class=" card-body table-border-style" style=" overflow:auto">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Employee') }}</th>
                                            <th>{{ __('Title') }}</th>
                                            <th>{{ __('Type') }}</th>
                                            <th>{{ __('Amount') }}</th>
                                            @can(['Edit Other Payment', 'Delete Other Payment'])
                                                <th>{{ __('Action') }}</th>
                                            @endcan
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($otherpayments as $otherpayment)
                                            <tr>
                                                <td>{{ !empty($otherpayment->employee()) ? $otherpayment->employee()->name : '' }}
                                                </td>
                                                <td>{{ $otherpayment->title }}</td>
                                                <td>{{ ucfirst($otherpayment->type) }}</td>
                                                @if ($otherpayment->type == 'fixed')
                                                    <td>{{ \Auth::user()->priceFormat($otherpayment->amount) }}</td>
                                                @else
                                                    <td>{{ $otherpayment->amount }}%
                                                        ({{ \Auth::user()->priceFormat($otherpayment->tota_allow) }})
                                                    </td>
                                                @endif
                                                @can(['Edit Other Payment', 'Delete Other Payment'])
                                                    <td class="Action">
                                                        @can('Edit Other Payment')
                                                            <div class="action-btn me-2">
                                                                <a class="mx-3 btn btn-sm bg-info align-items-center"
                                                                    data-url="{{ URL::to('otherpayment/' . $otherpayment->id . '/edit') }}"
                                                                    data-ajax-popup="true" data-size="md"
                                                                    data-bs-toggle="tooltip" title=""
                                                                    data-title="{{ __('Edit Other Payment') }}"
                                                                    data-bs-original-title="{{ __('Edit') }}">
                                                                    <span class="text-white"><i class="ti ti-pencil"></i></span>
                                                                </a>
                                                            </div>
                                                        @endcan
                                                        @can('Delete Other Payment')
                                                            <div class="action-btn">
                                                                {!! Form::open([
                                                                    'method' => 'DELETE',
                                                                    'route' => ['otherpayment.destroy', $otherpayment->id],
                                                                    'id' => 'delete-form-' . $otherpayment->id,
                                                                ]) !!}
                                                                <a class=" btn btn-sm bg-danger align-items-center bs-pass-para"
                                                                    data-bs-trigger="hover" data-bs-toggle="tooltip"
                                                                    title="" data-bs-original-title="Delete"
                                                                    aria-label="Delete"><span class="text-white"><i
                                                                            class="ti ti-trash"></i></span></a>
                                                                </form>
                                                            </div>
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

                <!--Perk-->
                <div class="col-md-6">
                    <div class="card set-card earning">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-6">
                                    <h5>{{ __('Perk') }}</h5>
                                </div>
                                @can('Create Perk')
                                    <div class="col text-end">
                                        <a data-url="{{ route('employee.peark.create', $employee->id) }}"
                                            data-ajax-popup="true" data-title="{{ __('Create Perk') }}"
                                            data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary"
                                            data-bs-original-title="{{ __('Create') }}">
                                            <i class="ti ti-plus"></i>
                                        </a>
                                    </div>
                                @endcan
                            </div>
                        </div>
                        <div class=" card-body table-border-style" style=" overflow:auto">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Employee') }}</th>
                                            <th>{{ __('Title') }}</th>
                                            <th>{{ __('Amount') }}</th>
                                            <th>{{ __('Perk Coupon') }}</th>
                                            @can(['Edit Peark', 'Delete Peark'])
                                                <th>{{ __('Action') }}</th>
                                            @endcan
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($peark as $value)
                                            <tr>
                                                <td>
                                                    {{ !empty($value->employee()) ? $value->employee()->name : '' }}
                                                </td>
                                                <td>{{ $value->title }}</td>
                                                <td>{{ \Auth::user()->priceFormat($value->amount) }}</td>
                                                <td>{{ $value->peark_coupon }}</td>
                                                @can(['Edit Peark', 'Delete Peark'])
                                                    <td class="Action">
                                                        <span>
                                                            @can('Edit Peark')
                                                                <div class="action-btn bg-info ms-2">
                                                                    <a class="mx-3 btn btn-sm  align-items-center"
                                                                        data-url="{{ URL::to('peark/' . $value->id . '/edit') }}"
                                                                        data-ajax-popup="true" data-size="md"
                                                                        data-bs-toggle="tooltip" title=""
                                                                        data-title="{{ __('Edit Perk') }}"
                                                                        data-bs-original-title="{{ __('Edit') }}">
                                                                        <i class="ti ti-pencil text-white"></i>
                                                                    </a>
                                                                </div>
                                                            @endcan
                                                            @can('Delete Peark')
                                                                <div class="action-btn bg-danger ms-2">
                                                                    {!! Form::open([
                                                                        'method' => 'DELETE',
                                                                        'route' => ['peark.destroy', $value->id],
                                                                        'id' => 'delete-form-' . $value->id,
                                                                    ]) !!}
                                                                    <a class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                                        data-bs-toggle="tooltip" title=""
                                                                        data-bs-original-title="Delete" aria-label="Delete"><i
                                                                            class="ti ti-trash text-whit"></i></a>
                                                                    </form>
                                                                </div>
                                                            @endcan
                                                        </span>
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

                <!--bonous-->
                <div class="col-md-6">
                    <div class="card set-card earning">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-6">
                                    <h5>{{ __('Bonous') }}</h5>
                                </div>
                                @can('Create Bonous')
                                    <div class="col text-end">

                                        <a data-url="{{ route('employee.bonous.create', $employee->id) }}"
                                            data-ajax-popup="true" data-title="{{ __('Create Bonous') }}"
                                            data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary"
                                            data-bs-original-title="{{ __('Create') }}">
                                            <i class="ti ti-plus"></i>
                                        </a>
                                    </div>
                                @endcan
                            </div>
                        </div>
                        <div class=" card-body table-border-style" style=" overflow:auto">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Employee') }}</th>
                                            <th>{{ __('Title') }}</th>
                                            <th>{{ __('Amount') }}</th>
                                            @can(['Edit Bonous', 'Delete Bonous'])
                                                <th>{{ __('Action') }}</th>
                                            @endcan
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($bonous as $item)
                                            <tr>
                                                <td>{{ !empty($item->employee()) ? $item->employee()->name : '' }}
                                                </td>
                                                <td>{{ $item->title }}</td>
                                                <td>{{ \Auth::user()->priceFormat($item->amount) }}</td>
                                                @can(['Edit Bonous', 'Delete Bonous'])
                                                    <td class="Action">
                                                        <span>
                                                            @can('Edit Bonous')
                                                                <div class="action-btn bg-info ms-2">
                                                                    <a class="mx-3 btn btn-sm  align-items-center"
                                                                        data-url="{{ URL::to('bonous/' . $item->id . '/edit') }}"
                                                                        data-ajax-popup="true" data-size="md"
                                                                        data-bs-toggle="tooltip" title=""
                                                                        data-title="{{ __('Edit Bonous') }}"
                                                                        data-bs-original-title="{{ __('Edit') }}">
                                                                        <i class="ti ti-pencil text-white"></i>
                                                                    </a>
                                                                </div>
                                                            @endcan
                                                            @can('Delete Bonous')
                                                                <div class="action-btn bg-danger ms-2">
                                                                    {!! Form::open([
                                                                        'method' => 'DELETE',
                                                                        'route' => ['bonous.destroy', $item->id],
                                                                        'id' => 'delete-form-' . $item->id,
                                                                    ]) !!}
                                                                    <a class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                                        data-bs-toggle="tooltip" title=""
                                                                        data-bs-original-title="Delete" aria-label="Delete"><i
                                                                            class="ti ti-trash text-white"></i></a>
                                                                    </form>
                                                                </div>
                                                            @endcan
                                                        </span>
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


                <!--overtime-->
                <div class="col-md-6">
                    <div class="card set-card earning">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-11">
                                    <h5>{{ __('Overtime') }}</h5>
                                </div>
                                @can('Create Overtime')
                                    <div class="col-1 text-end">
                                        <a data-url="{{ route('overtimes.create', $employee->id) }}" data-ajax-popup="true"
                                            data-title="{{ __('Create Overtime') }}" data-bs-toggle="tooltip"
                                            title="" class="btn btn-sm btn-primary"
                                            data-bs-original-title="{{ __('Create') }}">
                                            <i class="ti ti-plus"></i>
                                        </a>
                                    </div>
                                @endcan
                            </div>
                        </div>
                        <div class=" card-body table-border-style" style=" overflow:auto">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Employee Name') }}</th>
                                            <th>{{ __('Overtime Title') }}</th>
                                            <th>{{ __('Number of days') }}</th>
                                            <th>{{ __('Hours') }}</th>
                                            <th>{{ __('Rate') }}</th>
                                            @can(['Edit Overtime', 'Delete Overtime'])
                                                <th>{{ __('Action') }}</th>
                                            @endcan

                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($overtimes as $overtime)
                                            <tr>
                                                <td>{{ !empty($overtime->employee()) ? $overtime->employee()->name : '' }}
                                                </td>
                                                <td>{{ $overtime->title }}</td>
                                                <td>{{ $overtime->number_of_days }}</td>
                                                <td>{{ $overtime->hours }}</td>
                                                <td>{{ \Auth::user()->priceFormat($overtime->rate) }}</td>
                                                @can(['Edit Overtime', 'Delete Overtime'])
                                                    <td class="Action">
                                                        @can('Edit Overtime')
                                                            <div class="action-btn me-2">
                                                                <a class="mx-3 btn btn-sm bg-info align-items-center"
                                                                    data-url="{{ URL::to('overtime/' . $overtime->id . '/edit') }}"
                                                                    data-ajax-popup="true" data-size="md"
                                                                    data-bs-toggle="tooltip" title=""
                                                                    data-title="{{ __('Edit OverTime') }}"
                                                                    data-bs-original-title="{{ __('Edit') }}">
                                                                    <span class="text-white"><i class="ti ti-pencil"></i></span>
                                                                </a>
                                                            </div>
                                                        @endcan
                                                        @can('Delete Overtime')
                                                            <div class="action-btn">
                                                                {!! Form::open([
                                                                    'method' => 'DELETE',
                                                                    'route' => ['overtime.destroy', $overtime->id],
                                                                    'id' => 'delete-form-' . $overtime->id,
                                                                ]) !!}
                                                                <a class=" btn btn-sm bg-danger align-items-center bs-pass-para"
                                                                    data-bs-trigger="hover" data-bs-toggle="tooltip"
                                                                    title="" data-bs-original-title="Delete"
                                                                    aria-label="Delete"><span class="text-white"><i
                                                                            class="ti ti-trash"></i></span></a>
                                                                </form>
                                                            </div>
                                                        @endcan
                                                        </span>
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

                @if(\App\Models\Utility::isUkRequest())
                <!-- ── UK Payroll ──────────────────────────────────────────── -->
                <div class="col-12 mb-5">
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-12">
                                    <h5>{{ __('Payroll Details') }}</h5>
                                </div>
                            </div>
                        </div>
                        {{ Form::model($employee, ['route' => ['employee.uk.payroll.update', $employee->id], 'method' => 'POST', 'class' => 'needs-validation', 'novalidate']) }}
                        @csrf
                        <div class="card-body">
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
                                        {{ Form::text('account_holder_name', null, ['class' => 'form-control', 'placeholder' => 'Enter account holder name']) }}
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="card-footer">
                            <div class="row mt-3 pb-2">
                                <div class="col-12 text-right">
                                    <input type="submit" value="{{ __('Save Changes') }}" class="btn btn-sm btn-primary">
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
                <!-- ── /UK Payroll ─────────────────────────────────────────── -->
                @endif

                <!-- ── Salary Revision ─────────────────────────────────────── -->
                <div class="col-12">
                    <div class="card set-card">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h5 class="mb-0">{{ __('Salary Revision') }}</h5>
                                </div>
                                @can('Create Salary Revision')
                                    <div class="col-auto">
                                        <a data-url="{{ route('salary-revision.create', $employee->id) }}"
                                            data-ajax-popup="true"
                                            data-size="lg"
                                            data-title="{{ __('Schedule Salary Revision') }}"
                                            data-bs-toggle="tooltip"
                                            title=""
                                            class="btn btn-sm btn-primary"
                                            data-bs-original-title="{{ __('Add Revision') }}">
                                            <i class="ti ti-plus"></i>
                                        </a>
                                    </div>
                                @endcan
                            </div>
                        </div>
                        <div class="card-body table-border-style" style="overflow:auto">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Current Salary') }}</th>
                                            <th>{{ __('Revised Salary') }}</th>
                                            <th>{{ __('Type') }}</th>
                                            <th>{{ __('Value') }}</th>
                                            <th>{{ __('Cycle') }}</th>
                                            <th>{{ __('Effective From') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th>{{ __('Note') }}</th>
                                            @canany(['Edit Salary Revision', 'Delete Salary Revision'])
                                                <th>{{ __('Action') }}</th>
                                            @endcanany
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($salaryRevisions as $revision)
                                            <tr>
                                                <td>{{ \Auth::user()->priceFormat($revision->current_salary) }}</td>
                                                <td>{{ \Auth::user()->priceFormat($revision->revised_salary) }}</td>
                                                <td>{{ ucfirst($revision->revision_type) }}</td>
                                                <td>
                                                    @if ($revision->revision_type === 'percentage')
                                                        {{ $revision->revision_value }}%
                                                    @else
                                                        {{ \Auth::user()->priceFormat($revision->revision_value) }}
                                                    @endif
                                                </td>
                                                <td>
                                                    @php
                                                        $cycles = [3 => '3 Months', 6 => '6 Months', 12 => '12 Months'];
                                                    @endphp
                                                    {{ $cycles[$revision->cycle_months] ?? $revision->cycle_months . ' Months' }}
                                                </td>
                                                <td>{{ \Auth::user()->dateFormat($revision->effective_from) }}</td>
                                                <td>
                                                    @if ($revision->status === 'applied')
                                                        <span class="badge bg-success">{{ __('Applied') }}</span>
                                                    @elseif ($revision->status === 'approved')
                                                        <span class="badge bg-primary">{{ __('Approved') }}</span>
                                                    @elseif ($revision->status === 'rejected')
                                                        <span class="badge bg-danger">{{ __('Rejected') }}</span>
                                                    @else
                                                        <span class="badge bg-warning text-dark">{{ __('Pending') }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ $revision->note ?? '-' }}</td>
                                                @canany(['Edit Salary Revision', 'Delete Salary Revision'])
                                                    <td class="Action">
                                                        @if ($revision->status === 'pending')
                                                            {{-- Edit: any authorised user can edit a pending revision --}}
                                                            @can('Edit Salary Revision')
                                                                <div class="action-btn me-1">
                                                                    <a class="btn btn-sm bg-info align-items-center"
                                                                        data-url="{{ route('salary-revision.edit', $revision->id) }}"
                                                                        data-ajax-popup="true"
                                                                        data-size="lg"
                                                                        data-bs-toggle="tooltip"
                                                                        title="{{ __('Edit') }}"
                                                                        data-title="{{ __('Edit Salary Revision') }}">
                                                                        <span class="text-white"><i class="ti ti-pencil"></i></span>
                                                                    </a>
                                                                </div>
                                                            @endcan

                                                            {{-- Approve & Reject: only company / super admin / admin users --}}
                                                            @if (in_array(\Auth::user()->type, ['company', 'super admin', 'admin']))
                                                                @can('Edit Salary Revision')
                                                                    {{-- Approve --}}
                                                                    <div class="action-btn me-1">
                                                                        {!! Form::open([
                                                                            'method' => 'POST',
                                                                            'route'  => ['salary-revision.approve', $revision->id],
                                                                            'id'     => 'approve-form-' . $revision->id,
                                                                            'style'  => 'display:inline',
                                                                        ]) !!}
                                                                        <button type="submit"
                                                                            class="btn btn-sm bg-success align-items-center"
                                                                            data-bs-toggle="tooltip"
                                                                            title="{{ __('Approve') }}"
                                                                            onclick="return confirm('{{ __('Approve this salary revision? The new salary will be applied automatically on the effective date.') }}')">
                                                                            <span class="text-white"><i class="ti ti-check"></i></span>
                                                                        </button>
                                                                        {!! Form::close() !!}
                                                                    </div>

                                                                    {{-- Reject --}}
                                                                    <div class="action-btn me-1">
                                                                        {!! Form::open([
                                                                            'method' => 'POST',
                                                                            'route'  => ['salary-revision.reject', $revision->id],
                                                                            'id'     => 'reject-form-' . $revision->id,
                                                                            'style'  => 'display:inline',
                                                                        ]) !!}
                                                                        <button type="submit"
                                                                            class="btn btn-sm bg-warning align-items-center"
                                                                            data-bs-toggle="tooltip"
                                                                            title="{{ __('Reject') }}"
                                                                            onclick="return confirm('{{ __('Reject this salary revision?') }}')">
                                                                            <span class="text-white"><i class="ti ti-x"></i></span>
                                                                        </button>
                                                                        {!! Form::close() !!}
                                                                    </div>
                                                                @endcan
                                                            @endif

                                                            @can('Delete Salary Revision')
                                                                <div class="action-btn">
                                                                    {!! Form::open([
                                                                        'method' => 'DELETE',
                                                                        'route'  => ['salary-revision.destroy', $revision->id],
                                                                        'id'     => 'delete-revision-' . $revision->id,
                                                                        'style'  => 'display:inline',
                                                                    ]) !!}
                                                                    <a class="btn btn-sm bg-danger align-items-center bs-pass-para"
                                                                        data-bs-toggle="tooltip"
                                                                        title="{{ __('Delete') }}"
                                                                        aria-label="{{ __('Delete') }}">
                                                                        <span class="text-white"><i class="ti ti-trash"></i></span>
                                                                    </a>
                                                                    {!! Form::close() !!}
                                                                </div>
                                                            @endcan
                                                        @else
                                                            <span class="text-muted">—</span>
                                                        @endif
                                                    </td>
                                                @endcanany
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center text-muted">
                                                    {{ __('No salary revisions scheduled.') }}
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ── /Salary Revision ────────────────────────────────────── -->

            </div>
        </div>
    </div>
@endsection

@push('script-page')
    <script type="text/javascript">
        $(document).on('change', '.amount_type', function() {

            var val = $(this).val();
            var label_text = 'Amount';
            if (val == 'percentage') {
                var label_text = 'Percentage';
            }
            $('.amount_label').html(label_text);
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

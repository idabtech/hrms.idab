@extends('layouts.admin')

@section('page-title')
    {{ __('Employee') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ url('employee') }}">{{ __('Employee') }}</a></li>
    <li class="breadcrumb-item">{{ __('Manage Employee') }}</li>
@endsection

@section('action-button')
    <div class="float-end">
        @can('edit employee')
            <a href="{{ route('employee.edit', \Illuminate\Support\Facades\Crypt::encrypt($employee->id)) }}"
                data-bs-toggle="tooltip" title="{{ __('Edit') }}"class="btn btn-sm btn-primary">
                <i class="ti ti-pencil"></i>
            </a>
        @endcan
    </div>
    <div class="text-end mb-3">
        <div class="d-flex justify-content-end drp-languages">
            <ul class="list-unstyled mb-0 m-2">
                <li class="dropdown dash-h-item drp-language">
                    <a class="dash-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="javascript:void(0)"
                        role="button" aria-haspopup="false" aria-expanded="false">
                        <span class="drp-text hide-mob text-primary"> {{ __('Joining Letter') }}
                            <i class="ti ti-chevron-down drp-arrow nocolor hide-mob"></i>
                    </a>
                    <div class="dropdown-menu dash-h-dropdown">
                        <a href="{{ route('joiningletter.download.pdf', $employee->id) }}" class=" btn-icon dropdown-item"
                            data-bs-toggle="tooltip" data-bs-placement="top" target="_blanks"><i
                                class="ti ti-download ">&nbsp;</i>{{ __('PDF') }}</a>

                        <a href="{{ route('joininglatter.download.doc', $employee->id) }}" class=" btn-icon dropdown-item"
                            data-bs-toggle="tooltip" data-bs-placement="top" target="_blanks"><i
                                class="ti ti-download ">&nbsp;</i>{{ __('DOC') }}</a>
                    </div>
                </li>
            </ul>
            <ul class="list-unstyled mb-0 m-2">
                <li class="dropdown dash-h-item drp-language">
                    <a class="dash-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="javascript:void(0)"
                        role="button" aria-haspopup="false" aria-expanded="false">
                        <span class="drp-text hide-mob text-primary"> {{ __('Experience Certificate') }}
                            <i class="ti ti-chevron-down drp-arrow nocolor hide-mob"></i>
                    </a>
                    <div class="dropdown-menu dash-h-dropdown">
                        <a href="{{ route('exp.download.pdf', $employee->id) }}" class=" btn-icon dropdown-item"
                            data-bs-toggle="tooltip" data-bs-placement="top" target="_blanks"><i
                                class="ti ti-download ">&nbsp;</i>{{ __('PDF') }}</a>

                        <a href="{{ route('exp.download.doc', $employee->id) }}" class=" btn-icon dropdown-item"
                            data-bs-toggle="tooltip" data-bs-placement="top" target="_blanks"><i
                                class="ti ti-download ">&nbsp;</i>{{ __('DOC') }}</a>
                    </div>
                </li>
            </ul>
            <ul class="list-unstyled mb-0 m-2">
                <li class="dropdown dash-h-item drp-language">
                    <a class="dash-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="javascript:void(0)"
                        role="button" aria-haspopup="false" aria-expanded="false">
                        <span class="drp-text hide-mob text-primary"> {{ __('NOC') }}
                            <i class="ti ti-chevron-down drp-arrow nocolor hide-mob"></i>
                    </a>
                    <div class="dropdown-menu dash-h-dropdown">
                        <a href="{{ route('noc.download.pdf', $employee->id) }}" class=" btn-icon dropdown-item"
                            data-bs-toggle="tooltip" data-bs-placement="top" target="_blanks"><i
                                class="ti ti-download ">&nbsp;</i>{{ __('PDF') }}</a>

                        <a href="{{ route('noc.download.doc', $employee->id) }}" class=" btn-icon dropdown-item"
                            data-bs-toggle="tooltip" data-bs-placement="top" target="_blanks"><i
                                class="ti ti-download ">&nbsp;</i>{{ __('DOC') }}</a>
                    </div>
                </li>
            </ul>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="row g-4">
                {{-- Personal Detail --}}
                <div class="col-sm-12 col-md-6">
                    <div class="card h-100 mb-0">
                        <div class="card-body employee-detail-body fulls-card">
                            <h5>{{ __('Personal Detail') }}</h5>
                            <hr>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="info text-sm">
                                        <strong class="font-bold">{{ __('Employee ID') }} : </strong>
                                        <span>{{ $employeesId }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info text-sm font-style">
                                        <strong class="font-bold">{{ __('Name') }} :</strong>
                                        <span>{{ $employee->name }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info text-sm font-style">
                                        <strong class="font-bold">{{ __('Email') }} :</strong>
                                        <span>{{ $employee->email }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info text-sm">
                                        <strong class="font-bold">{{ __('Date of Birth') }} :</strong>
                                        <span>{{ \Auth::user()->dateFormat($employee->dob) }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info text-sm">
                                        <strong class="font-bold">{{ __('Phone') }} :</strong>
                                        <span>{{ $employee->phone }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info text-sm">
                                        <strong class="font-bold">{{ __('Address') }} :</strong>
                                        <span>{{ $employee->address }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info text-sm">
                                        <strong class="font-bold">{{ __('Salary Type') }} :</strong>
                                        <span>{{ !empty($employee->salaryType) ? $employee->salaryType->name : '' }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info text-sm">
                                        <strong class="font-bold">{{ __('Basic Salary') }} :</strong>
                                        <span>{{ \Auth::user()->priceFormat($employee->salary) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Company Detail --}}
                <div class="col-sm-12 col-md-6">
                    <div class="card h-100 mb-0">
                        <div class="card-body employee-detail-body fulls-card">
                            <h5>{{ __('Company Detail') }}</h5>
                            <hr>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="info text-sm">
                                        <strong class="font-bold">{{ __('Branch') }} : </strong>
                                        <span>{{ !empty($employee->branch) ? $employee->branch->name : '' }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info text-sm font-style">
                                        <strong class="font-bold">{{ __('Department') }} :</strong>
                                        <span>{{ !empty($employee->department) ? $employee->department->name : '' }}</span>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="info text-sm">
                                        <strong class="font-bold">{{ __('Designation') }} :</strong>
                                        <span>{{ !empty($employee->designation) ? $employee->designation->name : '' }}</span>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="info text-sm">
                                        <strong class="font-bold">{{ __('Date Of Joining') }} :</strong>
                                        <span>{{ \Auth::user()->dateFormat($employee->company_doj) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Document Detail --}}
                <div class="col-sm-12 col-md-6">
                    <div class="card h-100 mb-0">
                        <div class="card-body employee-detail-body fulls-card">
                            <h5>{{ __('Document Detail') }}</h5>
                            <hr>
                            <div class="row g-3">
                                @php
                                    $employeedoc = $employee->documents()->pluck('document_value', 'document_id');
                                    $logo = \App\Models\Utility::get_file('uploads/document');
                                @endphp
                                @if (!$documents->isEmpty())
                                    @foreach ($documents as $key => $document)
                                    @php
                                        $docType  = $document->document_type ?? 'file';
                                        $rawValue = $employeedoc[$document->id] ?? null;
                                        $parsed   = null;
                                        if ($rawValue && $rawValue[0] === '{') {
                                            $dec = json_decode($rawValue, true);
                                            if (json_last_error() === JSON_ERROR_NONE) { $parsed = $dec; }
                                        }
                                        $textValue = $docType === 'text'  ? ($parsed['text'] ?? $rawValue) : ($parsed['text'] ?? null);
                                        $fileValue = $docType === 'file'  ? ($parsed['file'] ?? $rawValue) : ($parsed['file'] ?? ($parsed === null && $docType === 'both' ? $rawValue : null));
                                    @endphp
                                        <div class="col-md-6 col-12">
                                            <div class="info text-sm text-break" style="word-break: break-all; overflow-wrap: anywhere;">
                                                <strong class="font-bold d-block mb-1">{{ $document->name }} : </strong>
                                                @if ($docType === 'text')
                                                    <span class="text-muted">{{ $textValue }}</span>
                                                @elseif ($docType === 'both')
                                                    @if ($textValue) <span class="d-block text-muted">{{ $textValue }}</span> @endif
                                                    @if ($fileValue)
                                                        <span class="d-block text-break">
                                                            <a href="{{ $logo . '/' . $fileValue }}" target="_blank" class="text-primary text-decoration-underline" style="word-break: break-all;">
                                                                <i class="ti ti-file me-1"></i>{{ $fileValue }}
                                                            </a>
                                                        </span>
                                                    @endif
                                                @else
                                                    <span class="d-block text-break">
                                                        @if(!empty($fileValue))
                                                            <a href="{{ $logo . '/' . $fileValue }}" target="_blank" class="text-primary text-decoration-underline" style="word-break: break-all;">
                                                                <i class="ti ti-file me-1"></i>{{ $fileValue }}
                                                            </a>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="text-center text-muted">
                                        No Document Type Added.!
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bank Account Detail --}}
                <div class="col-sm-12 col-md-6">
                    <div class="card h-100 mb-0">
                        <div class="card-body employee-detail-body fulls-card">
                            <h5>{{ __('Bank Account Detail') }}</h5>
                            <hr>
                            <div class="row g-3">
                                <div class="col-md-6 col-12">
                                    <div class="info text-sm text-break" style="word-break: break-word;">
                                        <strong class="font-bold d-block mb-1">{{ __('Account Holder Name') }} : </strong>
                                        <span class="text-muted">{{ $employee->account_holder_name ?: '-' }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="info text-sm font-style text-break" style="word-break: break-word;">
                                        <strong class="font-bold d-block mb-1">{{ __('Account Number') }} :</strong>
                                        <span class="text-muted">{{ $employee->account_number ?: '-' }}</span>
                                    </div>
                                </div>

                                <div class="col-md-6 col-12">
                                    <div class="info text-sm text-break" style="word-break: break-word;">
                                        <strong class="font-bold d-block mb-1">{{ __('Bank Name') }} :</strong>
                                        <span class="text-muted">{{ $employee->bank_name ?: '-' }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="info text-sm text-break" style="word-break: break-word;">
                                        <strong class="font-bold d-block mb-1">{{ __(\App\Models\Utility::bankCodeLabel()) }} :</strong>
                                        <span class="text-muted">{{ $employee->bank_identifier_code ?: '-' }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="info text-sm text-break" style="word-break: break-word;">
                                        <strong class="font-bold d-block mb-1">{{ __('Tax Payer Id') }} :</strong>
                                        <span class="text-muted">{{ $employee->tax_payer_id ?: '-' }}</span>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="info text-sm text-break" style="word-break: break-word;">
                                        <strong class="font-bold d-block mb-1">{{ __('Branch Location') }} :</strong>
                                        <span class="text-muted">{{ $employee->branch_location ?: '-' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

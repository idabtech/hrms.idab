<style>
    .addBtn {
        float: right;
    }

    /* Document preview styles */
    .doc-preview-area {
        min-height: 30px;
    }
    .doc-preview-img {
        border-radius: 6px;
        border: 1px solid #dee2e6;
    }
    .doc-file-icon-link {
        text-decoration: none;
    }
    .doc-file-icon {
        line-height: 1;
    }
    .doc-file-icon-link:hover .doc-file-icon {
        opacity: 0.8;
    }
</style>
@extends('layouts.admin')
@section('page-title')
{{ __('Create Employee') }}
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
<li class="breadcrumb-item"><a href="{{ url('employee') }}">{{ __('Employee') }}</a></li>
<li class="breadcrumb-item">{{ __('Create Employee') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="">
        <div class="">
            <div class="row">

            </div>
            {{ Form::open(['route' => ['employee.store'], 'method' => 'post', 'enctype' => 'multipart/form-data', 'class' => 'employee-create-form']) }}
            <div class="row">
                <div class="col-md-6">
                    <div class="card em-card">
                        <div class="card-header">
                            <h5>{{ __('Personal Detail') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-6">
                                    {!! Form::label('name', __('First Name'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    {!! Form::text('fname', old('name'), [
                                    'class' => 'form-control',
                                    'required' => 'required',
                                    'placeholder' => 'Enter Employee First Name',
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('name', __('Last Name'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    {!! Form::text('lname', old('name'), [
                                    'class' => 'form-control',
                                    'required' => 'required',
                                    'placeholder' => 'Enter Employee Last Name',
                                    ]) !!}
                                </div>
                                <x-mobile divClass="col-md-6" name="phone" label="{{ __('Phone') }}"
                                    placeholder="{{ __('Enter employee phone') }}" id="phone" required="true">
                                </x-mobile>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {!! Form::label('dob', __('Date of Birth'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>

                                        {{ Form::date('dob', null,
                                            [
                                                'class' => 'form-control w-100',
                                                'required',
                                                'autocomplete' => 'off',
                                                'placeholder' => 'Select Date of Birth',
                                            ])
                                        }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {!! Form::label('gender', __('Gender'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                        <div class="d-flex radio-check">
                                            <div class="custom-control custom-radio custom-control-inline">
                                                <input type="radio" id="g_male" value="Male" name="gender"
                                                    class="form-check-input" required>
                                                <label class="form-check-label"
                                                    for="g_male">{{ __('Male') }}</label>
                                            </div>
                                            <div class="custom-control custom-radio ms-1 custom-control-inline">
                                                <input type="radio" id="g_female" value="Female" name="gender"
                                                    class="form-check-input">
                                                <label class="form-check-label"
                                                    for="g_female">{{ __('Female') }}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('email', __('Email'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    {!! Form::email('email', old('email'), [
                                    'class' => 'form-control',
                                    'required' => 'required',
                                    'placeholder' => 'Enter employee email',
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('password', __('Password'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    {!! Form::password('password', [
                                    'class' => 'form-control',
                                    'required' => 'required',
                                    'placeholder' => 'Enter employee new password',
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-12">
                                    {!! Form::label('address', __('Address'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    {!! Form::textarea('address', old('address'), [
                                    'class' => 'form-control',
                                    'rows' => 2,
                                    'required' => 'required',
                                    'placeholder' => 'Enter employee address',
                                    ]) !!}
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card em-card">
                        <div class="card-header">
                            <h5>{{ __('Company Detail') }}</h5>
                        </div>
                        <div class="card-body employee-detail-create-body">
                            <div class="row">
                                @csrf
                                <div class="form-group col-md-6">
                                    {!! Form::label('employee_id', __('Employee ID'), ['class' => 'form-label']) !!}
                                    {!! Form::text('employee_id', $employeesId, ['class' => 'form-control', 'disabled' => 'disabled']) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('company_doj', __('Date Of Joining'), ['class'=> 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    {{ Form::date('company_doj', null, [
                                        'class' => 'form-control w-100',
                                        'required',
                                        'autocomplete' => 'off',
                                        'placeholder' => 'Select company date of joining',
                                    ]) }}
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('branch_id', __('Select Branch'), ['class' => 'form-label']) }}<span
                                        class="text-danger pl-1">*</span>
                                    <a href="javascript:void(0)" data-title="{{ __('Create New Branch') }}"
                                        onclick="modalShow([{'name' : ''}], 'create-branch', 'Create Branch','branch')"
                                        data-bs-toggle="tooltip" title="{{ __('Create New Branch') }}"
                                        class="btn btn-sm btn-primary addBtn"
                                        data-bs-original-title="{{ __('Create') }}">
                                        <i class="ti ti-plus"></i>
                                    </a>
                                    <div class="form-icon-user">
                                        {{ Form::select('branch_id', $branches, null, ['class' => 'form-control branch_id', 'required' => 'required', 'placeholder' => 'Select Branch']) }}
                                    </div>
                                </div>

                                <div class="form-group col-md-6">
                                    {{ Form::label('department_id', __('Select Department'), ['class' => 'form-label']) }}<span
                                        class="text-danger pl-1">*</span>
                                    <a href="javascript:void(0)" data-title="{{ __('Create New Department') }}"
                                        onclick="modalShow([{'branch': '{{ $branches }}'}, {'name' : ''}, {'slug' : ''}], 'create-department','Create Department','department')"
                                        data-bs-toggle="tooltip" title="{{ __('Create New Department') }}"
                                        class="btn btn-sm btn-primary addBtn "
                                        data-bs-original-title="{{ __('Create') }}">
                                        <i class="ti ti-plus"></i>
                                    </a>
                                    <div class="form-icon-user">
                                        {{ Form::select('department_id', $departments, null, ['class' => 'form-control department_id', 'id' => 'department_id', 'required' => 'required', 'placeholder' => 'Select Department']) }}
                                    </div>
                                </div>

                                <div class="form-group col-md-6">
                                    {{ Form::label('subdepartment_id', __('Select Sub Department'), ['class' => 'form-label']) }}<span
                                        class="text-danger pl-1">*</span>
                                    <a href="javascript:void(0)" data-title="{{ __('Create New Sub Department') }}"
                                        onclick="modalShow([{'department': '{{ $departments }}'}, {'name' : ''}], 'create-subDepartment','Create Sub Department','subdepartment')"
                                        data-bs-toggle="tooltip" title="{{ __('Create New Sub Department') }}"
                                        class="btn btn-sm btn-primary addBtn "
                                        data-bs-original-title="{{ __('Create') }}">
                                        <i class="ti ti-plus"></i>
                                    </a>
                                    <div class="form-icon-user">
                                        {{ Form::select('subdepartment_id', $subdepartments, null, ['class' => 'form-control subdepartment_id', 'id' => 'subdepartment_id', 'required' => 'required', 'placeholder' => 'Select Sub Department']) }}
                                    </div>
                                </div>

                                <div class="form-group col-md-6 ">
                                    {{ Form::label('designation_id', __('Select Designation'), ['class' => 'form-label']) }}
                                    <a href="javascript:void(0)" data-title="{{ __('Create New Designation') }}"
                                        onclick="modalShow([{'department': '{{ $departments }}'}, {'name' : ''}], 'create-designation','Create Designation','designation')"
                                        data-bs-toggle="tooltip" title="{{ __('Create New Designation') }}"
                                        class="btn btn-sm btn-primary addBtn"
                                        data-bs-original-title="{{ __('Create') }}">
                                        <i class="ti ti-plus"></i>
                                    </a>
                                    <div class="form-icon-user">
                                        {{ Form::select('designation_id', $designations, null, ['class' => 'form-control designation_id', 'id' => 'designation_id', 'required' => 'required', 'placeholder' => 'Select Designation']) }}

                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('employment_type_id', __('Employment Type'), ['class' => 'form-label']) }}
                                    <a href="javascript:void(0)" data-title="{{ __('Create New Employment Type') }}"
                                        onclick="modalShow([{'name' : ''}], 'create-employmenttype', 'Create Employment Type','employmenttype')"
                                        data-bs-toggle="tooltip" title="{{ __('Create New Employment Type') }}"
                                        class="btn btn-sm btn-primary addBtn"
                                        data-bs-original-title="{{ __('Create') }}">
                                        <i class="ti ti-plus"></i>
                                    </a>
                                    <div class="form-icon-user">
                                        <div class="employmenttype_div">
                                            {{ Form::select('employment_type_id', $employmentTypes ?? [], old('employment_type_id'), ['class' => 'form-control employment_type_id', 'placeholder' => 'Select Employment Type']) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('shift_id', __('Select Shift'), ['class' => 'form-label']) }}<span
                                        class="text-danger pl-1">*</span>
                                    <a href="javascript:void(0)" data-title="{{ __('Create New  Shift') }}"
                                        onclick="modalShow([{'name' : ''}], 'create-shift', 'Create Shift','shift')"
                                        data-bs-toggle="tooltip" title="{{ __('Create New  Shift') }}"
                                        class="btn btn-sm btn-primary addBtn"
                                        data-bs-original-title="{{ __('Create') }}">
                                        <i class="ti ti-plus"></i>
                                    </a>

                                    <div class="form-icon-user">
                                        {{ Form::select('shift_id', $shift, null, ['class' => 'form-control shift_id', 'id' => 'shift_id', 'required' => 'required', 'placeholder' => 'Select Shift']) }}

                                    </div>
                                </div>

                                <div class="form-group col-md-6">
                                    {{ Form::label('company_shift_time', __('Select Shift Time'), ['class' => 'form-label']) }}
                                    <span class="text-danger pl-1">*</span>

                                    <div class="form-icon-user">
                                        @php
                                            $shiftOptions = [];

                                            if (!empty($company_shifts)) {
                                                foreach ($company_shifts as $index => $shift) {
                                                    $label = ($shift['start'] ?? '') . ' - ' . ($shift['end'] ?? '');
                                                    $shiftOptions[$index] = $label;
                                                }
                                            }

                                            $selectedShiftIndex = old('company_shift_time', null);
                                        @endphp

                                        @if(!empty($shiftOptions))
                                            {{ Form::select(
                                                'company_shift_time',
                                                $shiftOptions,
                                                $selectedShiftIndex,
                                                [
                                                    'class' => 'form-control',
                                                    'id' => 'company_shift_time',
                                                    'placeholder' => __('Select Shift Time'),
                                                    'onchange' => 'loadShiftBreakTimes()',
                                                    'required' => 'required'
                                                ]
                                            ) }}
                                        @else
                                            <div class="alert alert-info mt-2">
                                                {{ __('Please set company shifts in Company Settings first.') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>


                                <!--- Refresh Break Time --->
                                <div class="form-group col-md-6">
                                    {{ Form::label('refresh_type', __('Refresh Break Time'), ['class' => 'form-label']) }}
                                    {{ Form::select('refresh_type', ['fixed' => 'Fixed Time', 'flexible' => 'Flexible Time'], null, ['class' => 'form-control', 'id' => 'refresh_break_type', 'placeholder' => 'Select Refresh Break Time', 'onchange' => 'toggleShiftBreakType()', 'required' => 'required']) }}
                                </div>

                                <!-- Refresh Break Fixed Time Fields -->
                                <div class="form-group col-md-6" id="refreshStartDiv" style="display:none;">
                                    {{ Form::label('refresh_start', __('Refresh Start Time'), ['class' => 'form-label']) }}
                                    {{ Form::time('refresh_start', old('refresh_start', null), ['class' => 'form-control', 'id' => 'refresh_start']) }}
                                </div>

                                <div class="form-group col-md-6" id="refreshEndDiv" style="display:none;">
                                    {{ Form::label('refresh_end', __('Refresh End Time'), ['class' => 'form-label']) }}
                                    {{ Form::time('refresh_end', old('refresh_end', null), ['class' => 'form-control', 'id' => 'refresh_end']) }}
                                </div>

                                <!-- Lunch Time Fields -->
                                <div class="form-group col-md-6" id="lunchTimeDiv" style="display:none;">
                                    {{ Form::label('lunch_start', __('Lunch Start Time'), ['class' => 'form-label']) }}
                                    {{ Form::time('lunch_start', old('lunch_start',null), ['class' => 'form-control', 'id' => 'lunch_start']) }}
                                </div>

                                <div class="form-group col-md-6" id="lunchEndDiv" style="display:none;">
                                    {{ Form::label('lunch_end', __('Lunch End Time'), ['class' => 'form-label']) }}
                                    {{ Form::time('lunch_end', old('lunch_end', null), ['class' => 'form-control', 'id' => 'lunch_end']) }}
                                </div>

                                <div class="form-group col-md-6" id="lunchMinDiv" style="display:none;">
                                    {{ Form::label('lunch_minutes', __('Lunch Minutes'), ['class' => 'form-label']) }}
                                    {{ Form::number('lunch_minutes', null, ['class' => 'form-control', 'id' => 'lunch_minutes', 'min' => 0]) }}
                                </div>

                                <!-- Tea Time Fields -->
                                <div class="form-group col-md-6" id="teaTimeDiv" style="display:none;">
                                    {{ Form::label('tea_start', __('Tea Start Time'), ['class' => 'form-label']) }}
                                    {{ Form::time('tea_start', old('tea_start', null), ['class' => 'form-control', 'id' => 'tea_start']) }}
                                </div>

                                <div class="form-group col-md-6" id="teaEndDiv" style="display:none;">
                                    {{ Form::label('tea_end', __('Tea End Time'), ['class' => 'form-label']) }}
                                    {{ Form::time('tea_end', old('tea_end', null), ['class' => 'form-control', 'id' => 'tea_end']) }}
                                </div>

                                <div class="form-group col-md-6" id="teaMinDiv" style="display:none;">
                                    {{ Form::label('tea_minutes', __('Tea Minutes'), ['class' => 'form-label']) }}
                                    {{ Form::number('tea_minutes', null, ['class' => 'form-control', 'id' => 'tea_minutes', 'min' => 0]) }}
                                </div>


                                <div class="form-group col-md-12">
                                    <div class="row">

                                        <!-- @php
                                            $refreshType = $company_settings['refreshtime'] ?? 'fixed';
                                        @endphp
                                        @if($refreshType === 'fixed')
                                            <div class="col-md-3 p-2">
                                                <div class="form-group">
                                                    {{ Form::label('refresh_start', __('Refresh Start'), ['class' => 'form-label']) }}
                                                    {{ Form::time('refresh_start', old('refresh_start'), ['class' => 'form-control', 'required']) }}
                                                </div>
                                            </div>
                                            <div class="col-md-3 p-2">
                                                <div class="form-group">
                                                    {{ Form::label('refresh_end', __('Refresh End'), ['class' => 'form-label']) }}
                                                    {{ Form::time('refresh_end', old('refresh_end'), ['class' => 'form-control', 'required']) }}
                                                </div>
                                            </div>
                                        @else
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {{ Form::label('refresh_minutes', __('Refresh Minutes'), ['class' => 'form-label']) }}
                                                    {{ Form::number('refresh_minutes', old('refresh_minutes', 0), ['class' => 'form-control', 'required', 'min' => 0]) }}
                                                </div>
                                            </div>
                                        @endif -->
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 ">
                    <div class="card em-card">
                        <div class="card-header">
                            <h5>{{ __('Document') }}</h6>
                        </div>
                        <div class="card-body employee-detail-create-body">
                            @foreach ($documents as $key => $document)
                            @php
                                $previewId = 'prev_' . $document->id . '_' . $key;
                                $docType   = $document->document_type ?? 'file';
                            @endphp
                            <div class="row mb-2">
                                <div class="form-group col-12">
                                    <label class="form-label">
                                        {{ $document->name }}
                                        @if ($document->is_required == 1)
                                            <span class="text-danger">*</span>
                                        @endif
                                    </label>
                                    <input type="hidden" name="emp_doc_id[{{ $document->id }}]" value="{{ $document->id }}">

                                    {{-- TEXT field --}}
                                    @if ($docType === 'text' || $docType === 'both')
                                        <input type="text"
                                            class="form-control mb-2"
                                            name="document_text[{{ $document->id }}]"
                                            id="document_text_{{ $document->id }}"
                                            placeholder="{{ __('Enter') }} {{ $document->name }}"
                                            @if ($document->is_required == 1 && $docType === 'text') required @endif>
                                    @endif

                                    {{-- FILE field --}}
                                    @if ($docType === 'file' || $docType === 'both')
                                        <div class="choose-files">
                                            <label for="document[{{ $document->id }}]">
                                                <div class="bg-primary document">
                                                    <i class="ti ti-upload"></i>{{ __('Choose file here') }}
                                                </div>
                                                <input type="file"
                                                    class="form-control file d-none @error('document') is-invalid @enderror"
                                                    name="document[{{ $document->id }}]"
                                                    id="document[{{ $document->id }}]"
                                                    data-filename="{{ $document->id . '_filename' }}"
                                                    data-preview-id="{{ $previewId }}"
                                                    onchange="handleDocPreview(this)">
                                            </label>
                                            <div id="{{ $previewId }}" class="doc-preview-area mt-2"></div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="col-md-6 ">
                    <div class="card em-card">
                        <div class="card-header">
                            <h5>{{ __('Bank Account Detail') }}</h5>
                        </div>
                        <div class="card-body employee-detail-create-body">
                            <div class="row">
                                <div class="form-group col-md-6">
                                    {!! Form::label('account_holder_name', __('Account Holder Name'), ['class' => 'form-label']) !!}
                                    {!! Form::text('account_holder_name', old('account_holder_name'), [
                                    'class' => 'form-control',
                                    'placeholder' => 'Enter account holder name',
                                    ]) !!}

                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('account_number', __('Account Number'), ['class' => 'form-label']) !!}
                                    {!! Form::number('account_number', old('account_number'), [
                                    'class' => 'form-control',
                                    'placeholder' => 'Enter account number',
                                    ]) !!}

                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('bank_name', __('Bank Name'), ['class' => 'form-label']) !!}
                                    {!! Form::text('bank_name', old('bank_name'), ['class' => 'form-control', 'placeholder' => 'Enter bank name']) !!}

                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('bank_identifier_code', __(\App\Models\Utility::bankCodeLabel()), ['class' => 'form-label']) !!}
                                    {!! Form::text('bank_identifier_code', old('bank_identifier_code'), [
                                    'class' => 'form-control',
                                    'placeholder' => 'Enter ' . \App\Models\Utility::bankCodeLabel(),
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('branch_location', __('Branch Location'), ['class' => 'form-label']) !!}
                                    {!! Form::text('branch_location', old('branch_location'), [
                                    'class' => 'form-control',
                                    'placeholder' => 'Enter branch location',
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('tax_payer_id', __('Tax Payer/PAN Id'), ['class' => 'form-label']) !!}
                                    {!! Form::text('tax_payer_id', old('tax_payer_id'), [
                                    'class' => 'form-control',
                                    'placeholder' => 'Enter tax payer id',
                                    ]) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Leave Type Assignment ─────────────────────────────────────────── --}}
            <div class="row">
                <div class="col-md-12">
                    <div class="card em-card">
                        <div class="card-header">
                            <h5>{{ __('Assign Leave Types') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-12">
                                    {!! Form::label('leave_types', __('Leave Types'), ['class' => 'form-label']) !!}
                                    <small class="text-muted d-block mb-2">{{ __('Select the leave types this employee can apply for. If none selected, all leave types will be available.') }}</small>
                                    <div class="row">
                                        @foreach ($leaveTypes as $leaveType)
                                            <div class="col-md-4 col-sm-6 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="leave_types[]"
                                                        value="{{ $leaveType->id }}"
                                                        id="leave_type_{{ $leaveType->id }}"
                                                        {{ in_array($leaveType->id, old('leave_types', [])) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="leave_type_{{ $leaveType->id }}">
                                                        {{ $leaveType->title }}
                                                        <small class="text-muted">({{ $leaveType->days }} {{ __('days') }}{{ $leaveType->is_paid ? ', ' . __('Paid') : ', ' . __('Unpaid') }})</small>
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="float-end">
                <button type="submit" class="btn  btn-primary">{{ 'Create' }}</button>
            </div>
        </div>

        </form>
    </div>
</div>
<div class="modal fade" id="staticBackdrop" data-backdrop="static" data-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel"></h5>
                {{-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button> --}}
            </div>
            <form id="myForm">
                <div class="modal-body" id="newModalBody">

                </div>
            </form>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Close</button>
                <button type="button" class="btn btn-primary" onclick="submitForm()">Submit</button>
            </div>
        </div>
    </div>
</div>
@endsection
@push('script-page')
@include('layouts.dateformat')
<script>
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            const modal = document.querySelector('.modal.show');

            if (modal) {
                submitForm();
            } else {
                const mainForm = document.querySelector('.employee-create-form');
                if (mainForm) {
                    mainForm.querySelector('button[type="submit"]')?.click();
                }
            }
        }
    });

    $('input[type="file"]').change(function(e) {
        var file = e.target.files[0].name;
        var file_name = $(this).attr('data-filename');
        $('.' + file_name).append(file);
    });
</script>
<script>
    $(document).ready(function() {
        var d_id = $('.department_id').val();
        getDesignation(d_id);
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

                $('.designation_id').empty();
                var emp_selct = ` <select class="form-control  designation_id" name="designation_id" id="choices-multiple"
                                            placeholder="Select Designation" >
                                            </select>`;
                $('.designation_div').html(emp_selct);

                $('.designation_id').append('<option value="0"> {{ __('All') }} </option>');
                $.each(data, function(key, value) {
                    $('.designation_id').append('<option value="' + key + '">' + value +
                        '</option>');
                });
                new Choices('#choices-multiple', {
                    removeItemButton: true,
                });


            }
        });
    }
</script>
<script>
    $('#department_id').change(function() {
        var department_id = $(this).val();
        $.ajax({
            url: '{{ route('employee.sub') }}',
            type: "post",
            data: {
                '_token': '{{ csrf_token() }}',
                'department_id': department_id
            },
            success: function(response) {
                $('#subdepartment_id').html(response)
            }
        })
    })
      // Load shift break times based on selected shift
        const companyShifts = @json($company_shifts ?? []);

        function loadShiftBreakTimes() {
            const selectElement = document.getElementById('company_shift_time');
            const selectedIndex = selectElement.value;

            if (selectedIndex === '' || !companyShifts[selectedIndex]) {
                // Hide all break time fields
                document.getElementById('lunchTimeDiv').style.display = 'none';
                document.getElementById('lunchEndDiv').style.display = 'none';
                document.getElementById('lunchMinDiv').style.display = 'none';
                document.getElementById('teaTimeDiv').style.display = 'none';
                document.getElementById('teaEndDiv').style.display = 'none';
                document.getElementById('teaMinDiv').style.display = 'none';
                // Don't hide refresh break fields - they're independent
                return;
            }

            const shift = companyShifts[selectedIndex];

            // // Set hidden fields with shift times
            // document.getElementById('company_start_time').value = shift.start || '';
            // document.getElementById('company_end_time').value = shift.end || '';

            document.getElementById('refresh_break_type').value = shift.type;
            // Load lunch times
            if (shift.type === 'fixed') {
                document.getElementById('lunchTimeDiv').style.display = 'block';
                document.getElementById('lunchEndDiv').style.display = 'block';
                document.getElementById('lunchMinDiv').style.display = 'none';

                document.getElementById('lunch_start').value = shift.lunch_start || '';
                document.getElementById('lunch_end').value = shift.lunch_end || '';
            } else if (shift.type === 'flexible') {
                document.getElementById('lunchTimeDiv').style.display = 'none';
                document.getElementById('lunchEndDiv').style.display = 'none';
                document.getElementById('lunchMinDiv').style.display = 'block';

                document.getElementById('lunch_minutes').value = shift.lunch_minutes || 0;
            } else {
                document.getElementById('lunchTimeDiv').style.display = 'none';
                document.getElementById('lunchEndDiv').style.display = 'none';
                document.getElementById('lunchMinDiv').style.display = 'none';
            }

            // Load tea times
            if (shift.type === 'fixed') {
                document.getElementById('teaTimeDiv').style.display = 'block';
                document.getElementById('teaEndDiv').style.display = 'block';
                document.getElementById('teaMinDiv').style.display = 'none';

                document.getElementById('tea_start').value = shift.tea_start || '';
                document.getElementById('tea_end').value = shift.tea_end || '';
            } else if (shift.type === 'flexible') {
                document.getElementById('teaTimeDiv').style.display = 'none';
                document.getElementById('teaEndDiv').style.display = 'none';
                document.getElementById('teaMinDiv').style.display = 'block';

                document.getElementById('tea_minutes').value = shift.tea_minutes || 0;
            } else {
                document.getElementById('teaTimeDiv').style.display = 'none';
                document.getElementById('teaEndDiv').style.display = 'none';
                document.getElementById('teaMinDiv').style.display = 'none';
            }
        }

        // Toggle shift break type fields (lunch and tea)
        function toggleShiftBreakType() {
            const breakType = document.getElementById('refresh_break_type').value;

            if (breakType === 'fixed') {
                // Show time fields, hide minute fields
                document.getElementById('lunchTimeDiv').style.display = 'block';
                document.getElementById('lunchEndDiv').style.display = 'block';
                document.getElementById('lunchMinDiv').style.display = 'none';
                document.getElementById('teaTimeDiv').style.display = 'block';
                document.getElementById('teaEndDiv').style.display = 'block';
                document.getElementById('teaMinDiv').style.display = 'none';
            } else if (breakType === 'flexible') {
                // Show minute fields, hide time fields
                document.getElementById('lunchTimeDiv').style.display = 'none';
                document.getElementById('lunchEndDiv').style.display = 'none';
                document.getElementById('lunchMinDiv').style.display = 'block';
                document.getElementById('teaTimeDiv').style.display = 'none';
                document.getElementById('teaEndDiv').style.display = 'none';
                document.getElementById('teaMinDiv').style.display = 'block';
            } else {
                // Hide all if not set
                document.getElementById('lunchTimeDiv').style.display = 'none';
                document.getElementById('lunchEndDiv').style.display = 'none';
                document.getElementById('lunchMinDiv').style.display = 'none';
                document.getElementById('teaTimeDiv').style.display = 'none';
                document.getElementById('teaEndDiv').style.display = 'none';
                document.getElementById('teaMinDiv').style.display = 'none';
            }
        }

        // Toggle refresh break type fields
        function toggleRefreshBreakType() {
            const refreshType = document.getElementById('refresh_break_type').value;

            if (refreshType === 'fixed') {
                document.getElementById('refreshStartDiv').style.display = 'block';
                document.getElementById('refreshEndDiv').style.display = 'block';
                document.getElementById('refreshMinDiv').style.display = 'none';
            } else if (refreshType === 'flexible') {
                document.getElementById('refreshStartDiv').style.display = 'none';
                document.getElementById('refreshEndDiv').style.display = 'none';
                document.getElementById('refreshMinDiv').style.display = 'block';
            } else {
                document.getElementById('refreshStartDiv').style.display = 'none';
                document.getElementById('refreshEndDiv').style.display = 'none';
                document.getElementById('refreshMinDiv').style.display = 'none';
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadShiftBreakTimes();
            // toggleShiftBreakType();
            // toggleRefreshBreakType();
        });
    // }
</script>
<script>
    var globalRoute = '';
    var html = "";
    var globalBranch = '';
    var globalDepartment = '';
    var globalSubDepartment = '';
    var globalDesignation = '';
    var globalShift = '';
    var newData = "";

    function modalShow(data, route, title, section) {
        html = "";
        globalRoute = route;
        newData = "";
        switch (section) {
            case 'department':
                getData('get-branch');
                break;
            case 'subdepartment':
                getData('get-department');
                break;
            case 'designation':
                getData('get-department');
                break;
            default:
                break;
        }
        $('#staticBackdropLabel').text(title);

        html += '<div class="row">';
        $.each(data, function(key, value) {
            $.each(value, function(key1, value1) {
                html += '<div class="col-lg-12 col-md-12 col-sm-12">\
                                                    <div class="form-group">\
                                                        <label class="form-label">' + key1.toUpperCase() + '</label>';

                if (value1 != '') {
                    html += '<div class="form-icon-user">\
                                                        <select class="form-control" name="' + key1 + '">';
                    value1 = JSON.parse(value1);
                    if (newData != '') {
                        value1 = newData;
                    }
                    $.each(value1, function(key2, value2) {
                        html += '<option value="' + key2 + '">' + value2 + '</option>';
                    })
                    html += '</select>\
                                                    </div>';
                } else {
                    html += '<div class="form-icon-user">\
                                                            <input name="' + key1 + '" id="' + key1 + '" placeholder="' +
                        key1
                        .toUpperCase() + '" class="form-control">\
                                                        </div>';
                }

                html += '   </div>\
                                                    </div>';
            })
        });
        html += '</div>';
        $('#newModalBody').html(html);
        $('#staticBackdrop').modal('show');
    }

    function submitForm() {
        var input = [];
        $("#myForm :input").each(function() {
            var value = $(this).val();
            var name = $(this).attr('name');
            input.push({
                'name': name,
                'value': value
            });
        });
        var url = "<?= url('') ?>/" + globalRoute;

        $.ajax({
            type: "post",
            url: url,
            data: input,
            success: function(response) {
                closeModal();
                response = JSON.parse(response);
                switch (response.section) {
                    case 'branch':
                        $('.branch_id').html(response.output);
                        break;
                    case 'department':
                        $('.department_id').html(response.output);
                        break;
                    case 'subdepartment':
                        $('.subdepartment_id').html(response.output);
                        break;
                    case 'designation':
                        $('.designation_id').html(response.output);
                        break;
                    case 'shift':
                        $('.shift_id').html(response.output);
                        break;
                    case 'employmenttype':
                        $('.employment_type_id').html(response.output);
                        break;
                    default:
                        break;
                }
            }
        });
    }

    function closeModal() {
        $('#staticBackdrop').modal('hide');
        html = "";
    }

    function getData(url) {
        var url = "<?= url('') ?>/" + url;
        $.ajax({
            type: "get",
            url: url,
            async: false,
            success: function(response) {
                newData = JSON.parse(response);
            }
        });
    }

    // Document preview handler — shows image preview or file-type icon on new file selection
    function handleDocPreview(input) {
        var previewId = input.getAttribute('data-preview-id');
        var previewArea = document.getElementById(previewId);
        if (!input.files || !input.files[0]) return;

        var file = input.files[0];
        var fileName = file.name;
        var ext = fileName.split('.').pop().toLowerCase();
        var imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'];

        var fileIconMap = {
            'pdf':  { icon: 'ti ti-file-type-pdf',    color: '#e74c3c', label: 'PDF'  },
            'doc':  { icon: 'ti ti-file-type-doc',    color: '#2980b9', label: 'DOC'  },
            'docx': { icon: 'ti ti-file-type-docx',   color: '#2980b9', label: 'DOCX' },
            'xls':  { icon: 'ti ti-file-type-xls',    color: '#27ae60', label: 'XLS'  },
            'xlsx': { icon: 'ti ti-file-type-xlsx',   color: '#27ae60', label: 'XLSX' },
            'csv':  { icon: 'ti ti-file-spreadsheet', color: '#27ae60', label: 'CSV'  },
            'txt':  { icon: 'ti ti-file-text',        color: '#7f8c8d', label: 'TXT'  },
            'zip':  { icon: 'ti ti-file-zip',         color: '#8e44ad', label: 'ZIP'  },
            'rar':  { icon: 'ti ti-file-zip',         color: '#8e44ad', label: 'RAR'  },
        };

        previewArea.innerHTML = '';

        if (imageExts.indexOf(ext) !== -1) {
            var reader = new FileReader();
            reader.onload = function(e) {
                previewArea.innerHTML =
                    '<img src="' + e.target.result + '" ' +
                    'class="img-thumbnail doc-preview-img" ' +
                    'style="max-height:80px; max-width:120px; object-fit:cover;" ' +
                    'alt="' + fileName + '">';
            };
            reader.readAsDataURL(file);
        } else {
            var info = fileIconMap[ext] || { icon: 'ti ti-file', color: '#95a5a6', label: ext.toUpperCase() };
            previewArea.innerHTML =
                '<span class="doc-file-icon-link d-inline-flex align-items-center gap-1">' +
                    '<i class="' + info.icon + ' doc-file-icon" style="font-size:2.2rem; color:' + info.color + ';"></i>' +
                    '<span class="badge" style="background:' + info.color + '; font-size:0.7rem;">' + info.label + '</span>' +
                '</span>' +
                '<div class="text-muted small mt-1" style="word-break:break-all; max-width:160px;">' + fileName + '</div>';
        }
    }
</script>
@endpush

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

@php
    $company_settings = \App\Models\Utility::settings();
@endphp
@section('page-title')
{{ __('Edit Employee') }}
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
<li class="breadcrumb-item"><a href="{{ url('employee') }}">{{ __('Employee') }}</a></li>
<li class="breadcrumb-item">{{ __('Edit Employee') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="">
        <div class="">

            {{ Form::model($employee, ['route' => ['employee.update', $employee->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data', 'class' => 'employee-edit-form']) }}
            <div class="row">
                <div class="col-md-6 ">
                    <div class="card em-card">
                        <div class="card-header">
                            <h5>{{ __('Personal Detail') }}</h5>
                        </div>
                        <div class="card-body">

                            <div class="row">
                                <div class="form-group col-md-6">
                                    {!! Form::label('name', __('First Name'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    {!! Form::text('name', null, ['class' => 'form-control', 'required' => 'required']) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('last_name', __('Last Name'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    {!! Form::text('last_name', null, ['class' => 'form-control', 'required' => 'required']) !!}
                                </div>
                                <x-mobile divClass="col-md-6" name="phone" label="{{ __('Phone') }}"
                                    placeholder="{{ __('Enter employee phone') }}" id="phone" required="true">
                                </x-mobile>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {!! Form::label('dob', __('Date of Birth'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                        {!! Form::date('dob',
                                        !empty($employee->dob)
                                        ? \Carbon\Carbon::parse($employee->dob)
                                        : null,
                                        [
                                        'class' => 'form-control w-100',
                                        'required',
                                        'autocomplete' => 'off',
                                        'placeholder' => 'Select Date of Birth'

                                        ])
                                        !!}
                                    </div>
                                </div>
                                <div class="col-md-6 ">
                                    <div class="form-group ">
                                        {!! Form::label('gender', __('Gender'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                        <div class="d-flex radio-check">
                                            <div class="custom-control custom-radio custom-control-inline">
                                                <input type="radio" id="g_male" value="Male" name="gender"
                                                    class="form-check-input" required
                                                    {{ $employee->gender == 'Male' ? 'checked' : '' }}>
                                                <label class="form-check-label"
                                                    for="g_male">{{ __('Male') }}</label>
                                            </div>
                                            <div class="custom-control custom-radio ms-1 custom-control-inline">
                                                <input type="radio" id="g_female" value="Female" name="gender"
                                                    class="form-check-input"
                                                    {{ $employee->gender == 'Female' ? 'checked' : '' }}>
                                                <label class="form-check-label"
                                                    for="g_female">{{ __('Female') }}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group ">
                                        {!! Form::label('email', __('Email'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                        {!! Form::email('email', old('email'), [
                                        'class' => 'form-control',
                                        'required' => 'required',
                                        'placeholder' => 'Enter employee email',
                                        ]) !!}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {!! Form::label('passcode', __('Passcode'), ['class' => 'form-label']) !!}
                                        {!! Form::text('passcode', old('passcode', $employee->user->passcode ?? ''), [
                                        'class' => 'form-control',
                                        'placeholder' => __('Enter employee passcode'),
                                        ]) !!}
                                    </div>
                                </div>
                                <div class="form-group col-md-12">
                                    {{ Form::label('address', __('Address'), ['class' => 'form-label']) }}<span class="text-danger pl-1">*</span>
                                    {{ Form::textarea('address', null, ['class' => 'form-control' ,'placeholder'=>__('Enter address'),'rows'=>'3']) }}
                                </div>
                                 <!-- <div class="col-md-6">
                                    <div class="form-group ">
                                        {!! Form::label('address', __('Address'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                        {!! Form::textarea('address', null, ['class' => 'form-control', 'required' => 'required', 'rows' => 2]) !!}
                                    </div>
                                </div> -->

                            </div>

                            @if (\Auth::user()->type == 'employee')
                            {{-- {!! Form::submit('Update', ['class' => 'btn  btn-primary float-right']) !!} --}}
                            @endif
                        </div>
                    </div>
                </div>
                @if (\Auth::user()->type != 'employee')
                <div class="col-md-6 ">
                        <div class="card em-card">
                            <div class="card-header">
                                <h5>{{ __('Company Detail') }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        {!! Form::label('employee_id', __('Employee ID'), ['class' => 'form-label']) !!}
                                        {!! Form::text('employee_id', $employeesId, ['class' => 'form-control', 'disabled' => 'disabled']) !!}
                                    </div>
                                    <div class=" form-group col-md-6">
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
                                        {{ Form::select('branch_id', $branches, null, ['class' => 'form-control branch_id', 'required' => 'required', 'placeholder' => 'Select Branch']) }}
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
                                        {{ Form::select('department_id', $departments, null, ['class' => 'form-control department_id', 'id' => 'department_id', 'required' => 'required']) }}
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
                                        {{ Form::select('subdepartment_id', $subdepartments, $employee->subdepartment, ['class' => 'form-control subdepartment_id', 'id' => 'subdepartment_id', 'required' => 'required']) }}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {{ Form::label('designation_id', __('Select Designation'), ['class' => 'form-label']) }}
                                        <a href="javascript:void(0)" data-title="{{ __('Create New Designation') }}"
                                            onclick="modalShow([{'department': '{{ $departments }}'}, {'name' : ''}], 'create-designation','Create Designation','designation')"
                                            data-bs-toggle="tooltip" title="{{ __('Create New Designation') }}"
                                            class="btn btn-sm btn-primary addBtn"
                                            data-bs-original-title="{{ __('Create') }}">
                                            <i class="ti ti-plus"></i>
                                        </a>


                                        <div class="form-icon-user">
                                            <div class="designation_div">
                                                <select class="form-control designation_id" name="designation_id"
                                                    id="choices-multiple" placeholder="Select Designation">
                                                </select>
                                            </div>
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
                                                {{ Form::select('employment_type_id', $employmentTypes ?? [], old('employment_type_id', $employee->employment_type_id ?? null), ['class' => 'form-control employment_type_id', 'placeholder' => 'Select Employment Type']) }}
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
                                            <div class="shift_div">
                                                {{ Form::select('shift_id', $shift, $employee->shift_id, ['class' => 'form-control shift_id', 'required' => 'required', 'placeholder' => 'Select Shift']) }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        {{ Form::label('company_shift_time', __('Select Shift Time'), ['class' => 'form-label']) }}
                                        <span class="text-danger pl-1">*</span>

                                        <div class="form-icon-user">
                                            @php
                                                $shiftOptions = [];
                                                if (!empty($company_shifts) && is_array($company_shifts)) {
                                                    foreach ($company_shifts as $index => $shift) {
                                                        $label = $shift['start'] . ' - ' . $shift['end'];
                                                        $shiftOptions[$index] = $label;
                                                    }
                                                }

                                                $selectedShiftIndex = old('company_shift_time', null);
                                                if ($selectedShiftIndex === null &&  is_array($company_shifts) && !empty($employee->company_start_time) && !empty($employee->company_end_time)) {
                                                    foreach ($company_shifts as $index => $shift) {
                                                        if ($shift['start'] === $employee->company_start_time &&
                                                            $shift['end'] === $employee->company_end_time) {
                                                            $selectedShiftIndex = $index;
                                                            break;
                                                        }
                                                    }
                                                }
                                            @endphp

                                            @if(!empty($shiftOptions))
                                                {{ Form::select('company_shift_time', $shiftOptions, $selectedShiftIndex, [
                                                    'class' => 'form-control',
                                                    'id' => 'company_shift_time',
                                                    'placeholder' => __('Select Shift Time'),
                                                    'onchange' => 'loadShiftBreakTimes()'
                                                ]) }}
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
                                        {{ Form::select('refresh_type', ['fixed' => 'Fixed Time', 'flexible' => 'Flexible Time'], $employee->refresh_type, ['class' => 'form-control', 'id' => 'refresh_break_type', 'placeholder' => 'Select Refresh Break Time', 'onchange' => 'toggleShiftBreakType()']) }}
                                    </div>

                                    <!-- Refresh Break Fixed Time Fields -->
                                    <!-- <div class="form-group col-md-6" id="refreshStartDiv" style="display:none;">
                                        {{ Form::label('refresh_start', __('Refresh Start Time'), ['class' => 'form-label']) }}
                                        {{ Form::time('refresh_start', old('refresh_start', $employee->refresh_start), ['class' => 'form-control', 'id' => 'refresh_start']) }}
                                    </div> -->

                                    <!-- <div class="form-group col-md-6" id="refreshEndDiv" style="display:none;">
                                        {{ Form::label('refresh_end', __('Refresh End Time'), ['class' => 'form-label']) }}
                                        {{ Form::time('refresh_end', old('refresh_end', $employee->refresh_end), ['class' => 'form-control', 'id' => 'refresh_end']) }}
                                    </div> -->

                                    <!-- Refresh Break Flexible Time Fields -->
                                    <!-- <div class="form-group col-md-6" id="refreshMinDiv" style="display:none;">
                                        {{ Form::label('refresh_minutes', __('Refresh Minutes'), ['class' => 'form-label']) }}
                                        {{ Form::number('refresh_minutes', old('refresh_minutes', $employee->refresh_minutes ?? 0), ['class' => 'form-control', 'id' => 'refresh_minutes', 'min' => 0]) }}
                                    </div> -->
                                    @php
                                        $initialBreakType = old('refresh_type', $employee->refresh_type ?? null);
                                    @endphp

                                    <!-- Lunch Time Fields -->
                                    <div class="form-group col-md-6" id="lunchTimeDiv" style="{{ $initialBreakType === 'fixed' ? 'display:block;' : 'display:none;' }}">
                                        {{ Form::label('lunch_start', __('Lunch Start Time'), ['class' => 'form-label']) }}
                                        {{ Form::time('lunch_start', old('lunch_start', $employee->lunch_start), ['class' => 'form-control', 'id' => 'lunch_start']) }}
                                    </div>

                                    <div class="form-group col-md-6" id="lunchEndDiv" style="{{ $initialBreakType === 'fixed' ? 'display:block;' : 'display:none;' }}">
                                        {{ Form::label('lunch_end', __('Lunch End Time'), ['class' => 'form-label']) }}
                                        {{ Form::time('lunch_end', old('lunch_end', $employee->lunch_end), ['class' => 'form-control', 'id' => 'lunch_end']) }}
                                    </div>

                                    <div class="form-group col-md-6" id="lunchMinDiv" style="{{ $initialBreakType === 'flexible' ? 'display:block;' : 'display:none;' }}">
                                        {{ Form::label('lunch_minutes', __('Lunch Minutes'), ['class' => 'form-label']) }}
                                        {{ Form::number('lunch_minutes', old('lunch_minutes', $employee->lunch_minutes ?? 0), ['class' => 'form-control', 'id' => 'lunch_minutes', 'min' => 0]) }}
                                    </div>

                                    <!-- Tea Time Fields -->
                                    <div class="form-group col-md-6" id="teaTimeDiv" style="{{ $initialBreakType === 'fixed' ? 'display:block;' : 'display:none;' }}">
                                        {{ Form::label('tea_start', __('Tea Start Time'), ['class' => 'form-label']) }}
                                        {{ Form::time('tea_start', old('tea_start', $employee->tea_start), ['class' => 'form-control', 'id' => 'tea_start']) }}
                                    </div>

                                    <div class="form-group col-md-6" id="teaEndDiv" style="{{ $initialBreakType === 'fixed' ? 'display:block;' : 'display:none;' }}">
                                        {{ Form::label('tea_end', __('Tea End Time'), ['class' => 'form-label']) }}
                                        {{ Form::time('tea_end', old('tea_end', $employee->tea_end), ['class' => 'form-control', 'id' => 'tea_end']) }}
                                    </div>

                                    <div class="form-group col-md-6" id="teaMinDiv" style="{{ $initialBreakType === 'flexible' ? 'display:block;' : 'display:none;' }}">
                                        {{ Form::label('tea_minutes', __('Tea Minutes'), ['class' => 'form-label']) }}
                                        {{ Form::number('tea_minutes', old('tea_minutes', $employee->tea_minutes ?? 0), ['class' => 'form-control', 'id' => 'tea_minutes', 'min' => 0]) }}
                                    </div>

                                    <!-- Hidden fields to store shift times -->
                                    {{ Form::hidden('company_start_time', '', ['id' => 'company_start_time']) }}
                                    {{ Form::hidden('company_end_time', '', ['id' => 'company_end_time']) }}

                                    <div class="form-group col-md-12">
                                        <div class="row">

                                            <!-- @php
                                                $refreshType = $company_settings['refreshtime'] ?? 'fixed';
                                            @endphp
                                            @if($refreshType === 'fixed')
                                                <div class="col-md-3 p-2">
                                                    <div class="form-group">
                                                        {{ Form::label('refresh_start', __('Refresh Start'), ['class' => 'form-label']) }}
                                                        {{ Form::time('refresh_start', old('refresh_start', $employee->refresh_start), ['class' => 'form-control', 'required']) }}
                                                    </div>
                                                </div>
                                                <div class="col-md-3 p-2">
                                                    <div class="form-group">
                                                        {{ Form::label('refresh_end', __('Refresh End'), ['class' => 'form-label']) }}
                                                        {{ Form::time('refresh_end', old('refresh_end', $employee->refresh_end), ['class' => 'form-control', 'required']) }}
                                                    </div>
                                                </div>
                                            @else
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        {{ Form::label('refresh_minutes', __('Refresh Minutes'), ['class' => 'form-label']) }}
                                                        {{ Form::number('refresh_minutes', old('refresh_minutes', $employee->refresh_minutes ?? 0), ['class' => 'form-control', 'required', 'min' => 0]) }}
                                                    </div>
                                                </div>
                                            @endif -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="col-md-6 ">
                        <div class="employee-detail-wrap ">
                            <div class="card em-card">
                                <div class="card-header">
                                    <h5>{{ __('Company Detail') }}</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="info">
                                                <strong>{{ __('Branch') }}</strong>
                                                <span>{{ !empty($employee->branch) ? $employee->branch->name : '' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info font-style">
                                                <strong>{{ __('Department') }}</strong>
                                                <span>{{ !empty($employee->department) ? $employee->department->name : '' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info font-style">
                                                <strong>{{ __('Designation') }}</strong>
                                                <span>{{ !empty($employee->designation) ? $employee->designation->name : '' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info font-style">
                                                <strong>{{ __('shift') }}</strong>
                                                <span>{{ !empty($employee->shift) ? $employee->shift->name : '' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info">
                                                <strong>{{ __('Date Of Joining') }}</strong>
                                                <span>{{ \Auth::user()->dateFormat($employee->company_doj) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
                @if (\Auth::user()->type != 'employee')
                <div class="row">

                <div class="col-md-6 ">
                    <div class="card em-card" id="document-card">
                        <div class="card-header">
                            <h5>{{ __('Document') }}</h5>
                        </div>
                        <div class="card-body">
                            @php
                            $employeedoc = $employee->documents()->pluck('document_value', 'document_id')->toArray();
                            @endphp

                            @foreach ($documents as $key => $document)
                            @php
                                $logo        = \App\Models\Utility::get_file('uploads/document');
                                $docType     = $document->document_type ?? 'file';
                                $rawValue    = $employeedoc[$document->id] ?? null;
                                $docRecord   = $employeeDocuments[$document->id] ?? null;
                                $isRequested = $docRecord ? (int)$docRecord->is_requested : 0;

                                // Safe decode: supports old plain-path records, plain-text records, and new JSON records
                                $parsed    = null;
                                if ($rawValue && $rawValue[0] === '{') {
                                    $decoded = json_decode($rawValue, true);
                                    if (json_last_error() === JSON_ERROR_NONE) {
                                        $parsed = $decoded;
                                    }
                                }
                                if ($docType === 'text') {
                                    $textValue = $parsed['text'] ?? $rawValue;
                                    $fileValue = null;
                                } elseif ($docType === 'both') {
                                    $textValue = $parsed['text'] ?? null;
                                    // Old records for 'both' type are plain file paths
                                    $fileValue = $parsed['file'] ?? ($parsed === null ? $rawValue : null);
                                } else {
                                    // 'file' type — always a plain path (old or new)
                                    $textValue = null;
                                    $fileValue = $parsed['file'] ?? $rawValue;
                                }

                                $ext         = $fileValue ? strtolower(pathinfo($fileValue, PATHINFO_EXTENSION)) : null;
                                $isImage     = in_array($ext, ['jpg','jpeg','png','gif','webp','bmp','svg']);
                                $previewId   = 'prev_' . $document->id . '_' . $key;
                                $fileUrl     = $fileValue ? $logo . '/' . $fileValue : null;

                                $fileIconMap = [
                                    'pdf'  => ['icon' => 'ti ti-file-type-pdf',  'color' => '#e74c3c', 'label' => 'PDF'],
                                    'doc'  => ['icon' => 'ti ti-file-type-doc',  'color' => '#2980b9', 'label' => 'DOC'],
                                    'docx' => ['icon' => 'ti ti-file-type-docx', 'color' => '#2980b9', 'label' => 'DOCX'],
                                    'xls'  => ['icon' => 'ti ti-file-type-xls',  'color' => '#27ae60', 'label' => 'XLS'],
                                    'xlsx' => ['icon' => 'ti ti-file-type-xlsx', 'color' => '#27ae60', 'label' => 'XLSX'],
                                    'csv'  => ['icon' => 'ti ti-file-spreadsheet','color'=> '#27ae60', 'label' => 'CSV'],
                                    'txt'  => ['icon' => 'ti ti-file-text',       'color' => '#7f8c8d', 'label' => 'TXT'],
                                    'zip'  => ['icon' => 'ti ti-file-zip',        'color' => '#8e44ad', 'label' => 'ZIP'],
                                    'rar'  => ['icon' => 'ti ti-file-zip',        'color' => '#8e44ad', 'label' => 'RAR'],
                                ];
                                $iconInfo = $ext && isset($fileIconMap[$ext])
                                    ? $fileIconMap[$ext]
                                    : ['icon' => 'ti ti-file', 'color' => '#95a5a6', 'label' => strtoupper($ext ?? 'FILE')];
                            @endphp
                            <div class="row mb-3 pb-2 border-bottom">
                                <div class="form-group col-12">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <label class="form-label mb-0 fw-bold">
                                            {{ $document->name }}
                                            @if ($document->is_required == 1)
                                                <span class="text-danger">*</span>
                                            @endif
                                            <span class="badge bg-warning text-dark ms-2 req-doc-badge req-doc-badge-{{ $document->id }} {{ $isRequested ? '' : 'd-none' }}">
                                                <i class="ti ti-alert-circle me-1"></i>please upload your document
                                            </span>
                                        </label>
                                        <button type="button" 
                                                class="btn btn-sm {{ $isRequested ? 'btn-warning' : 'btn-primary' }} request-doc-btn" 
                                                data-doc-id="{{ $document->id }}" 
                                                data-emp-id="{{ $employee->employee_id }}" 
                                                data-is-requested="{{ $isRequested }}">
                                            <i class="ti ti-send me-1"></i><span class="btn-text">{{ $isRequested ? __('Requested') : __('Request Document') }}</span>
                                        </button>
                                    </div>
                                    <input type="hidden" name="emp_doc_id[{{ $document->id }}]" value="{{ $document->id }}">

                                    {{-- TEXT field — shown for 'text' and 'both' types --}}
                                    @if ($docType === 'text' || $docType === 'both')
                                        <input type="text"
                                            class="form-control mb-2"
                                            name="document_text[{{ $document->id }}]"
                                            id="document_text_{{ $document->id }}"
                                            value="{{ $textValue ?? '' }}"
                                            placeholder="{{ __('Enter') }} {{ $document->name }}">
                                    @endif

                                    {{-- FILE field — shown for 'file' and 'both' types --}}
                                    @if ($docType === 'file' || $docType === 'both')
                                        <div class="choose-files">
                                            <label for="document[{{ $document->id }}]">
                                                <div class="bg-primary document">
                                                    <i class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                                </div>
                                                {{-- No required on hidden file inputs — browser can't focus hidden fields --}}
                                                <input type="file"
                                                    class="form-control file d-none @error('document') is-invalid @enderror"
                                                    name="document[{{ $document->id }}]"
                                                    id="document[{{ $document->id }}]"
                                                    data-filename="{{ $document->id . '_filename' }}"
                                                    data-preview-id="{{ $previewId }}"
                                                    onchange="handleDocPreview(this)">
                                            </label>

                                            {{-- Preview area: show existing file if already uploaded --}}
                                            <div id="{{ $previewId }}" class="doc-preview-area mt-2">
                                                @if ($fileValue)
                                                    @if ($isImage)
                                                        <a href="{{ $fileUrl }}" target="_blank">
                                                            <img src="{{ $fileUrl }}"
                                                                 class="img-thumbnail doc-preview-img"
                                                                 style="max-height:80px; max-width:120px; object-fit:cover;"
                                                                 alt="{{ $document->name }}">
                                                        </a>
                                                    @else
                                                        <a href="{{ $fileUrl }}" target="_blank"
                                                           class="doc-file-icon-link d-inline-flex align-items-center gap-1 text-decoration-none"
                                                           data-bs-toggle="tooltip" title="{{ $fileValue }}">
                                                            <i class="{{ $iconInfo['icon'] }} doc-file-icon"
                                                               style="font-size:2.2rem; color:{{ $iconInfo['color'] }};"></i>
                                                            <span class="badge"
                                                                  style="background:{{ $iconInfo['color'] }}; font-size:0.7rem;">
                                                                {{ $iconInfo['label'] }}
                                                            </span>
                                                        </a>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                    <div class="col-md-6">
                        <div class="card em-card">
                            <div class="card-header">
                                <h5>{{ __('Bank Account Detail') }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        {!! Form::label('account_holder_name', __('Account Holder Name'), ['class' => 'form-label']) !!}
                                        {!! Form::text('account_holder_name', !empty($employee->account_holder_name)?$employee->account_holder_name:'', ['class' => 'form-control',
                                    'placeholder' => 'Enter account holder name']) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('account_number', __('Account Number'), ['class' => 'form-label']) !!}
                                        {!! Form::number('account_number', !empty($employee->account_number)?$employee->account_number:'', ['class' => 'form-control',
                                    'placeholder' => 'Enter account number']) !!}

                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('bank_name', __('Bank Name'), ['class' => 'form-label']) !!}
                                        {!! Form::text('bank_name', !empty($employee->bank_name)?$employee->bank_name:'', ['class' => 'form-control',
                                    'placeholder' => 'Enter bank name']) !!}

                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('bank_identifier_code', __(\App\Models\Utility::bankCodeLabel()), ['class' => 'form-label']) !!}
                                        {!! Form::text('bank_identifier_code', !empty($employee->bank_identifier_code)?$employee->bank_identifier_code:'', ['class' => 'form-control',
                                    'placeholder' => 'Enter bank identifier code']) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('branch_location', __('Branch Location'), ['class' => 'form-label']) !!}
                                        {!! Form::text('branch_location', !empty($employee->branch_location)?$employee->branch_location:'', ['class' => 'form-control',
                                    'placeholder' => 'Enter branch location']) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('tax_payer_id', __('Tax Payer Id'), ['class' => 'form-label']) !!}
                                        {!! Form::text('tax_payer_id', !empty($employee->tax_payer_id)?$employee->tax_payer_id:'', ['class' => 'form-control',
                                    'placeholder' => 'Enter tax payer id']) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                <div class="row">
                    <div class="col-md-6 ">
                        <div class="employee-detail-wrap">
                            <div class="card em-card" id="document-card">
                                <div class="card-header">
                                    <h5>{{ __('Document Detail') }}</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @php
                                        $employeedoc = $employee
                                        ->documents()
                                        ->pluck('document_value', 'document_id');
                                        $logo = \App\Models\Utility::get_file('uploads/document/');
                                        $hasAnyRequested = false;
                                        @endphp
                                        @foreach ($documents as $key => $document)
                                        @php
                                            $docType     = $document->document_type ?? 'file';
                                            $rawValue    = $employeedoc[$document->id] ?? null;
                                            $docRecord   = $employeeDocuments[$document->id] ?? null;
                                            $isRequested = $docRecord ? (int)$docRecord->is_requested : 0;
                                            if ($isRequested) { $hasAnyRequested = true; }
                                            $parsed      = null;
                                            if ($rawValue && $rawValue[0] === '{') {
                                                $dec = json_decode($rawValue, true);
                                                if (json_last_error() === JSON_ERROR_NONE) { $parsed = $dec; }
                                            }
                                            $textValue   = $docType === 'text'  ? ($parsed['text'] ?? $rawValue) : ($parsed['text'] ?? null);
                                            $fileValue   = $docType === 'file'  ? ($parsed['file'] ?? $rawValue) : ($parsed['file'] ?? ($parsed === null && $docType === 'both' ? $rawValue : null));

                                            $ext         = $fileValue ? strtolower(pathinfo($fileValue, PATHINFO_EXTENSION)) : null;
                                            $isImage     = in_array($ext, ['jpg','jpeg','png','gif','webp','bmp','svg']);
                                            $previewId   = 'emp_prev_' . $document->id . '_' . $key;
                                            $fileUrl     = $fileValue ? $logo . '/' . $fileValue : null;

                                            $fileIconMap = [
                                                'pdf'  => ['icon' => 'ti ti-file-type-pdf',  'color' => '#e74c3c', 'label' => 'PDF'],
                                                'doc'  => ['icon' => 'ti ti-file-type-doc',  'color' => '#2980b9', 'label' => 'DOC'],
                                                'docx' => ['icon' => 'ti ti-file-type-docx', 'color' => '#2980b9', 'label' => 'DOCX'],
                                                'xls'  => ['icon' => 'ti ti-file-type-xls',  'color' => '#27ae60', 'label' => 'XLS'],
                                                'xlsx' => ['icon' => 'ti ti-file-type-xlsx', 'color' => '#27ae60', 'label' => 'XLSX'],
                                                'csv'  => ['icon' => 'ti ti-file-spreadsheet','color'=> '#27ae60', 'label' => 'CSV'],
                                                'txt'  => ['icon' => 'ti ti-file-text',       'color' => '#7f8c8d', 'label' => 'TXT'],
                                                'zip'  => ['icon' => 'ti ti-file-zip',        'color' => '#8e44ad', 'label' => 'ZIP'],
                                                'rar'  => ['icon' => 'ti ti-file-zip',        'color' => '#8e44ad', 'label' => 'RAR'],
                                            ];
                                            $iconInfo = $ext && isset($fileIconMap[$ext])
                                                ? $fileIconMap[$ext]
                                                : ['icon' => 'ti ti-file', 'color' => '#95a5a6', 'label' => strtoupper($ext ?? 'FILE')];
                                        @endphp
                                        <div class="col-md-12 mb-3 pb-2 border-bottom">
                                            <div class="form-group">
                                                <label class="form-label font-weight-bold">
                                                    {{ $document->name }}
                                                    @if ($document->is_required == 1)
                                                        <span class="text-danger">*</span>
                                                    @endif
                                                    @if ($isRequested)
                                                        <span class="badge bg-warning text-dark ms-2">
                                                            <i class="ti ti-alert-circle me-1"></i>please upload your document
                                                        </span>
                                                    @endif
                                                </label>
                                                <input type="hidden" name="emp_doc_id[{{ $document->id }}]" value="{{ $document->id }}">

                                                @if ($isRequested)
                                                    {{-- Requested document: Show upload input options --}}
                                                    @if ($docType === 'text' || $docType === 'both')
                                                        <input type="text"
                                                            class="form-control mb-2"
                                                            name="document_text[{{ $document->id }}]"
                                                            id="document_text_{{ $document->id }}"
                                                            value="{{ $textValue ?? '' }}"
                                                            placeholder="{{ __('Enter') }} {{ $document->name }}">
                                                    @endif

                                                    @if ($docType === 'file' || $docType === 'both')
                                                        <div class="choose-files">
                                                            <label for="document[{{ $document->id }}]">
                                                                <div class="bg-primary document">
                                                                    <i class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                                                </div>
                                                                <input type="file"
                                                                    class="form-control file d-none @error('document') is-invalid @enderror"
                                                                    name="document[{{ $document->id }}]"
                                                                    id="document[{{ $document->id }}]"
                                                                    data-filename="{{ $document->id . '_filename' }}"
                                                                    data-preview-id="{{ $previewId }}"
                                                                    onchange="handleDocPreview(this)">
                                                            </label>

                                                            <div id="{{ $previewId }}" class="doc-preview-area mt-2">
                                                                @if ($fileValue)
                                                                    @if ($isImage)
                                                                        <a href="{{ $fileUrl }}" target="_blank">
                                                                            <img src="{{ $fileUrl }}"
                                                                                 class="img-thumbnail doc-preview-img"
                                                                                 style="max-height:80px; max-width:120px; object-fit:cover;"
                                                                                 alt="{{ $document->name }}">
                                                                        </a>
                                                                    @else
                                                                        <a href="{{ $fileUrl }}" target="_blank"
                                                                           class="doc-file-icon-link d-inline-flex align-items-center gap-1 text-decoration-none"
                                                                           data-bs-toggle="tooltip" title="{{ $fileValue }}">
                                                                            <i class="{{ $iconInfo['icon'] }} doc-file-icon"
                                                                               style="font-size:2.2rem; color:{{ $iconInfo['color'] }};"></i>
                                                                            <span class="badge"
                                                                                  style="background:{{ $iconInfo['color'] }}; font-size:0.7rem;">
                                                                                {{ $iconInfo['label'] }}
                                                                            </span>
                                                                        </a>
                                                                    @endif
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endif
                                                @else
                                                    {{-- Not requested: Show read-only display --}}
                                                    <div class="info mt-1">
                                                        @if ($docType === 'text')
                                                            <span>{{ !empty($textValue) ? $textValue : '-' }}</span>
                                                        @elseif ($docType === 'both')
                                                            @if ($textValue) <div><span>{{ $textValue }}</span></div> @endif
                                                            @if ($fileValue)
                                                                <div><span><a href="{{ $fileUrl }}" target="_blank">{{ $fileValue }}</a></span></div>
                                                            @elseif (!$textValue)
                                                                <span>-</span>
                                                            @endif
                                                        @else
                                                            @if ($fileValue)
                                                                @if ($isImage)
                                                                    <a href="{{ $fileUrl }}" target="_blank">
                                                                        <img src="{{ $fileUrl }}" class="img-thumbnail doc-preview-img" style="max-height:80px; max-width:120px; object-fit:cover;" alt="{{ $document->name }}">
                                                                    </a>
                                                                @else
                                                                    <a href="{{ $fileUrl }}" target="_blank" class="doc-file-icon-link d-inline-flex align-items-center gap-1 text-decoration-none">
                                                                        <i class="{{ $iconInfo['icon'] }} doc-file-icon" style="font-size:2.2rem; color:{{ $iconInfo['color'] }};"></i>
                                                                        <span class="badge" style="background:{{ $iconInfo['color'] }}; font-size:0.7rem;">{{ $iconInfo['label'] }}</span>
                                                                    </a>
                                                                @endif
                                                            @else
                                                                <span>-</span>
                                                            @endif
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        @endforeach
                                        @if ($hasAnyRequested)
                                            <div class="col-md-12 text-end mt-2">
                                                <input type="submit" value="{{ __('Save Documents') }}" class="btn btn-primary">
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 ">
                        <div class="employee-detail-wrap">
                            <div class="card em-card">
                                <div class="card-header">
                                    <h5>{{ __('Bank Account Detail') }}</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="info">
                                                <strong>{{ __('Account Holder Name') }}</strong>
                                                <span>{{ $employee->account_holder_name }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info font-style">
                                                <strong>{{ __('Account Number') }}</strong>
                                                <span>{{ $employee->account_number }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info font-style">
                                                <strong>{{ __('Bank Name') }}</strong>
                                                <span>{{ $employee->bank_name }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info">
                                                <strong>{{ __(\App\Models\Utility::bankCodeLabel()) }}</strong>
                                                <span>{{ $employee->bank_identifier_code }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info">
                                                <strong>{{ __('Branch Location') }}</strong>
                                                <span>{{ $employee->branch_location }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info">
                                                <strong>{{ __('Tax Payer Id') }}</strong>
                                                <span>{{ $employee->tax_payer_id }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info">
                                                <strong>{{ __('PF/UAN Number') }}</strong>
                                                <span>{{ $employee->pf_id }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info">
                                                <strong>{{ __('ESIC') }}</strong>
                                                <span>{{ $employee->esic_id }}</span>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- ── Leave Type Assignment ─────────────────────────────────────────── --}}
                @if (\Auth::user()->type != 'employee')
                <div class="row">
                    <div class="col-md-12">
                        <div class="card em-card">
                            <div class="card-header">
                                <h5>{{ __('Assign Leave Types') }}</h5>
                            </div>
                            <div class="card-body table-border-style">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th style="width:50px;">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="select_all_leave_types">
                                                    </div>
                                                </th>
                                                <th>{{ __('Leave Type') }}</th>
                                                <th class="text-center" style="width:120px;">{{ __('Paid/Unpaid') }}</th>
                                                <th class="text-center" style="width:150px;">{{ __('Total Days') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($leaveTypes as $leaveType)
                                                @php
                                                    $isAssigned = array_key_exists($leaveType->id, $assignedLeaveTypes ?? []);
                                                    $assignedDays = $isAssigned ? ($assignedLeaveTypes[$leaveType->id]['total_days'] ?? $leaveType->days) : $leaveType->days;
                                                    $isPaid = $isAssigned ? ($assignedLeaveTypes[$leaveType->id]['is_paid'] ?? $leaveType->is_paid) : $leaveType->is_paid;
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <div class="form-check">
                                                            <input class="form-check-input leave-type-check" type="checkbox"
                                                                name="leave_types[{{ $leaveType->id }}][checked]"
                                                                value="1"
                                                                id="leave_type_{{ $leaveType->id }}"
                                                                {{ $isAssigned ? 'checked' : '' }}>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <label for="leave_type_{{ $leaveType->id }}" class="mb-0">
                                                            {{ $leaveType->title }}
                                                        </label>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="form-check form-switch d-flex justify-content-center mb-0">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="leave_types[{{ $leaveType->id }}][is_paid]"
                                                                value="1"
                                                                id="is_paid_{{ $leaveType->id }}"
                                                                {{ $isPaid ? 'checked' : '' }}>
                                                            <label class="form-check-label ms-2 small" for="is_paid_{{ $leaveType->id }}" id="is_paid_label_{{ $leaveType->id }}">
                                                                {{ $isPaid ? __('Paid') : __('Unpaid') }}
                                                            </label>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.5" min="0"
                                                            class="form-control text-center"
                                                            name="leave_types[{{ $leaveType->id }}][days]"
                                                            value="{{ $assignedDays }}"
                                                            placeholder="{{ $leaveType->days }}">
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
                @endif

                @if (\Auth::user()->type != 'employee')
                <div class="float-end">
                    <button type="submit" class="btn  btn-primary">{{ 'Update' }}</button>
                </div>
                @endif
                <div class="col-12">
                    {!! Form::close() !!}
                </div>
            </div>
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
    <script type="text/javascript">
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                const modal = document.querySelector('.modal.show');

                if (modal) {
                    submitForm();
                } else {
                    const mainForm = document.querySelector('.employee-edit-form');
                    if (mainForm) {
                        mainForm.querySelector('button[type="submit"]')?.click();
                    }
                }
            }
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
                    var emp_selct = ` <select class="form-control  designation_id select2" name="designation_id" id="choices-multiple"
                                            placeholder="Select Designation" >
                                            </select>`;
                    $('.designation_div').html(emp_selct);
                    $('.designation_id').append('<option value="">Select any Designation</option>');
                    $.each(data, function(key, value) {
                        var select = '';
                        if (key == '{{ $employee->designation_id }}') {
                            select = 'selected';
                        }

                        $('.designation_id').append('<option value="' + key + '"  ' + select + '>' +
                            value + '</option>');
                    });
                    new Choices('#choices-multiple', {
                        removeItemButton: true,
                    });
                }
            });
        }

        $(document).ready(function() {
            var d_id = $('#department_id').val();
            var designation_id = '{{ $employee->designation_id }}';

            console.log(d_id);
            getDesignation(d_id);

            var s_id = $('#shift_id').val();
            var shift_id = '{{ $employee->shift_id }}';
        });

        $(document).on('change', 'select[name=department_id]', function() {
            var department_id = $(this).val();
            getDesignation(department_id);
        });
        $(document).on('change', 'select[name=shift_id]', function() {
            var shift_id = $(this).val();
            getDesignation(shift_id);
        });
    </script>
    <script>
        // $(document).ready(function()){

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
                    console.log(response);
                    $('#subdepartment_id').html(response)
                }
            })
        })
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
                                                                                <label class="form-label">' + key1
                        .toUpperCase() +
                        '</label>';
                    // console.log(value1);

                    if (value1 != '') {
                        html += '<div class="form-icon-user">\
                                                                                <select class="form-control" name="' +
                            key1 +
                            '">';
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
                                                                                    <input name="' + key1 + '" id="' +
                            key1 +
                            '" placeholder="' +
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

            // Set hidden fields with shift times
            document.getElementById('company_start_time').value = shift.start || '';
            document.getElementById('company_end_time').value = shift.end || '';

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
            toggleShiftBreakType();
            toggleRefreshBreakType();
        });

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
                'pdf':  { icon: 'ti ti-file-type-pdf',   color: '#e74c3c', label: 'PDF'  },
                'doc':  { icon: 'ti ti-file-type-doc',   color: '#2980b9', label: 'DOC'  },
                'docx': { icon: 'ti ti-file-type-docx',  color: '#2980b9', label: 'DOCX' },
                'xls':  { icon: 'ti ti-file-type-xls',   color: '#27ae60', label: 'XLS'  },
                'xlsx': { icon: 'ti ti-file-type-xlsx',  color: '#27ae60', label: 'XLSX' },
                'csv':  { icon: 'ti ti-file-spreadsheet',color: '#27ae60', label: 'CSV'  },
                'txt':  { icon: 'ti ti-file-text',       color: '#7f8c8d', label: 'TXT'  },
                'zip':  { icon: 'ti ti-file-zip',        color: '#8e44ad', label: 'ZIP'  },
                'rar':  { icon: 'ti ti-file-zip',        color: '#8e44ad', label: 'RAR'  },
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

        // Select All leave types checkbox
        $(document).on('change', '#select_all_leave_types', function() {
            $('.leave-type-check').prop('checked', $(this).prop('checked'));
        });
        $(document).on('change', '.leave-type-check', function() {
            var total = $('.leave-type-check').length;
            var checked = $('.leave-type-check:checked').length;
            $('#select_all_leave_types').prop('checked', total === checked);
        });

        // Toggle Paid/Unpaid label text
        $(document).on('change', '[id^="is_paid_"]', function() {
            var id = $(this).attr('id');
            var leaveTypeId = id.replace('is_paid_', '');
            var $label = $('#is_paid_label_' + leaveTypeId);
            $label.text($(this).prop('checked') ? '{{ __("Paid") }}' : '{{ __("Unpaid") }}');
        });

        // AJAX Request Document toggle
        $(document).on('click', '.request-doc-btn', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var docId = $btn.data('doc-id');
            var empId = $btn.data('emp-id');
            var currentStatus = parseInt($btn.data('is-requested')) || 0;
            var newStatus = currentStatus === 1 ? 0 : 1;

            $btn.prop('disabled', true);

            $.ajax({
                url: "{{ route('employee.request-document') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    employee_id: empId,
                    document_id: docId,
                    is_requested: newStatus
                },
                success: function(response) {
                    $btn.prop('disabled', false);
                    if (response.success) {
                        $btn.data('is-requested', newStatus);
                        var $badge = $('.req-doc-badge-' + docId);
                        if (newStatus === 1) {
                            $badge.removeClass('d-none');
                            $btn.removeClass('btn-primary').addClass('btn-warning');
                            $btn.find('.btn-text').text("{{ __('Requested') }}");
                            show_toastr('Success', response.message, 'success');
                        } else {
                            $badge.addClass('d-none');
                            $btn.removeClass('btn-warning').addClass('btn-primary');
                            $btn.find('.btn-text').text("{{ __('Request Document') }}");
                            show_toastr('Success', response.message, 'success');
                        }
                    } else {
                        show_toastr('Error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    $btn.prop('disabled', false);
                    show_toastr('Error', '{{ __("Something went wrong. Please try again.") }}', 'error');
                }
            });
        });
    </script>
    @endpush

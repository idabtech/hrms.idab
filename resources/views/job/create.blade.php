@php
    $prefix = app()->environment('local') ? '' : 'public/';
@endphp
@extends('layouts.admin')
@section('page-title')
{{ __('Create Job') }}
@endsection
@push('css-page')
    <style>
        .addBtn {
            float: right;
        }
    </style>
    <link href="{{ asset($prefix . 'libs/bootstrap-tagsinput/dist/bootstrap-tagsinput.css') }}" rel="stylesheet" />
@endpush
@push('script-page')
    <script src='{{ asset('assets/js/plugins/tinymce/tinymce.min.js') }}'></script>
    <script src="{{ asset($prefix . 'libs/bootstrap-tagsinput/dist/bootstrap-tagsinput.min.js') }}"></script>

    <script>
        var e = $('[data-toggle="tags"]');
        e.length && e.each(function() {
            $(this).tagsinput({
                tagClass: "badge badge-primary"
            })
        });
    </script>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('job.index') }}">{{ __('Manage Job') }}</a></li>
    <li class="breadcrumb-item">{{ __('Create Job') }}</li>
@endsection


@section('content')
    <div class="row">


        {{ Form::open(['url' => 'job', 'method' => 'post']) }}
        <div class="row mt-3">
            <div class="col-md-6 ">
                <div class="card card-fluid job-card">
                    <div class="card-body ">
                        <div class="row">
                            <div class="form-group col-md-12">
                                {!! Form::label('title', __('Job Title'), ['class' => 'col-form-label']) !!}<span class="text-danger pl-1">*</span>
                                {!! Form::text('title', old('title'), [
                                    'class' => 'form-control',
                                    'required' => 'required',
                                    'placeholder' => 'Enter job title',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('branch', __('Branch'), ['class' => 'col-form-label']) !!}<span class="text-danger pl-1">*</span>
                                <a href="javascript:void(0)" data-title="{{ __('Create New Branch') }}"
                                    onclick="modalShow([{'name' : ''}], 'create-branch', 'Create Branch','branch')"
                                    data-bs-toggle="tooltip" title="{{ __('Create New Branch') }}"
                                    class="btn btn-sm btn-primary addBtn" data-bs-original-title="{{ __('Create') }}">
                                    <i class="ti ti-plus"></i>
                                </a>
                                {{ Form::select('branch', $branches, null, ['class' => 'form-control branch_id', 'required' => 'required']) }}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('category', __('Job Category'), ['class' => 'col-form-label']) !!}<span class="text-danger pl-1">*</span>
                                <a href="javascript:void(0)" data-title="{{ __('Create New Job Category') }}"
                                    onclick="modalShow([{'name' : ''}], 'create-job-category', 'Create Job Category','job-category')"
                                    data-bs-toggle="tooltip" title="{{ __('Create New Job Category') }}"
                                    class="btn btn-sm btn-primary addBtn" data-bs-original-title="{{ __('Create') }}">
                                    <i class="ti ti-plus"></i>
                                </a>
                                {{ Form::select('category', $categories, null, ['class' => 'form-control category', 'required' => 'required']) }}
                            </div>

                            <div class="form-group col-md-6">
                                {!! Form::label('position', __('No. of Positions'), ['class' => 'col-form-label']) !!}<span class="text-danger pl-1">*</span>
                                {!! Form::number('position', old('positions'), [
                                    'class' => 'form-control',
                                    'required' => 'required',
                                    'min' => '1',
                                    'placeholder' => 'open Positions',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('status', __('Status'), ['class' => 'col-form-label']) !!}
                                {{ Form::select('status', $status, null, ['class' => 'form-control select2', 'required' => 'required']) }}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('start_date', __('Start Date'), ['class' => 'col-form-label']) !!}<span class="text-danger pl-1">*</span>
                                {!! Form::date('start_date', old('start_date'), ['class' => 'form-control ', 'autocomplete' => 'off']) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('end_date', __('End Date'), ['class' => 'col-form-label']) !!}<span class="text-danger pl-1">*</span>
                                {!! Form::date('end_date', old('end_date'), ['class' => 'form-control ', 'autocomplete' => 'off']) !!}
                            </div>
                            <div class="form-group col-md-12">
                                {!! Form::label('skill', __('Skill Box'), ['class' => 'col-form-label']) !!}<span class="text-danger pl-1">*</span>
                                {!! Form::text('skill', old('skill'), [
                                    'class' => 'form-control ',
                                    'autocomplete' => 'off',
                                    'required' => 'required',
                                    'placeholder' => 'Skills',
                                ]) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 ">
                <div class="card card-fluid job-card">
                    <div class="card-body ">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <h6>{{ __('Need to Ask ?') }}</h6>
                                    <div class="my-4">
                                        <div class="form-check custom-checkbox">
                                            {!! Form::checkbox('applicant[]', 'gender', false, [
                                                'class' => 'form-check-input',
                                                'id' => 'check-gender',
                                            ]) !!}
                                            {!! Form::label('check-gender', __('Gender'), ['class' => 'form-check-label']) !!}
                                        </div>

                                        <div class="form-check custom-checkbox">
                                            {!! Form::checkbox('applicant[]', 'dob', false, [
                                                'class' => 'form-check-input',
                                                'id' => 'check-dob',
                                            ]) !!}
                                            {!! Form::label('check-dob', __('Date Of Birth'), ['class' => 'form-check-label']) !!}
                                        </div>

                                        <div class="form-check custom-checkbox">
                                            {!! Form::checkbox('applicant[]', 'address', false, [
                                                'class' => 'form-check-input',
                                                'id' => 'check-address',
                                            ]) !!}
                                            {!! Form::label('check-address', __('Address'), ['class' => 'form-check-label']) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <h6>{{ __('Need to show Option ?') }}</h6>
                                    <div class="my-4">
                                        <div class="form-check custom-checkbox">
                                            {!! Form::checkbox('visibility[]', 'profile', false, [
                                                'class' => 'form-check-input',
                                                'id' => 'check-profile',
                                            ]) !!}
                                            {!! Form::label('check-profile', __('Profile Image'), ['class' => 'form-check-label']) !!}
                                        </div>

                                        <div class="form-check custom-checkbox">
                                            {!! Form::checkbox('visibility[]', 'resume', false, [
                                                'class' => 'form-check-input',
                                                'id' => 'check-resume',
                                            ]) !!}
                                            {!! Form::label('check-resume', __('Resume'), ['class' => 'form-check-label']) !!}
                                        </div>

                                        <div class="form-check custom-checkbox">
                                            {!! Form::checkbox('visibility[]', 'letter', false, [
                                                'class' => 'form-check-input',
                                                'id' => 'check-letter',
                                            ]) !!}
                                            {!! Form::label('check-letter', __('Cover Letter'), ['class' => 'form-check-label']) !!}
                                        </div>

                                        <div class="form-check custom-checkbox">
                                            {!! Form::checkbox('visibility[]', 'terms', false, [
                                                'class' => 'form-check-input',
                                                'id' => 'check-terms',
                                            ]) !!}
                                            {!! Form::label('check-terms', __('Terms And Conditions'), ['class' => 'form-check-label']) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-md-12">
                                <h6>{{ __('Custom Questions') }}</h6>
                                <div class="my-4">
                                    @foreach ($customQuestion as $question)
                                        <div class="form-check custom-checkbox">
                                            {!! Form::checkbox('custom_question[]', $question->id, false, [
                                                'id' => 'custom_question_' . $question->id,
                                                'required' => $question->is_required == 'yes' ? 'required' : null,
                                                'class' => 'form-check-input',
                                            ]) !!}
                                            {!! Form::label(
                                                'custom_question_' . $question->id,
                                                $question->question . ($question->is_required == 'yes' ? ' <span class="text-danger">*</span>' : ''),
                                                ['class' => 'form-check-label'],
                                                false,
                                            ) !!}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-fluid job-card">
                    <div class="card-body ">
                        <div class="row">
                            <div class="form-group col-md-12">
                                {!! Form::label('description', __('Job Description'), ['class' => 'col-form-label']) !!}<span class="text-danger pl-1">*</span>
                                {!! Form::textarea('description', null, [
                                    'class' => 'form-control editor',
                                    'rows' => '17',
                                    'id' => 'description',
                                    'placeholder' => 'job description',
                                    'required' => 'required',
                                ]) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-fluid job-card">
                    <div class="card-body ">
                        <div class="row">
                            <div class="form-group col-md-12">
                                {!! Form::label('requirement', __('Job Requirement'), ['class' => 'col-form-label']) !!}
                                {!! Form::textarea('requirement', null, [
                                    'class' => 'form-control editor',
                                    'rows' => '10',
                                    'id' => 'requirement',
                                    'placeholder' => 'job requirement',
                                    'required' => 'required',
                                ]) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 text-end">
                <div class="form-group">
                    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
@endsection

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
<script>
    var globalRoute = '';
    var html = "";
    var globalBranch = '';
    var globalJobCategory = '';
    var newData = "";

    function modalShow(data, route, title, section) {
        globalRoute = route;
        html = '';
        newData = "";
        $('#staticBackdropLabel').text(title);

        html += '<div class="row">';
        $.each(data, function(key, value) {
            $.each(value, function(key1, value1) {
                html += '<div class="col-lg-12 col-md-12 col-sm-12">\
                        <div class="form-group">\
                            <label class="form-label">' + key1.toUpperCase() + '</label>';
                // console.log(value1);

                if (value1 != '') {
                    html += '<div class="form-icon-user">\
                            <select class="form-control" name="' + key1 + '">';
                    value1 = JSON.parse(value1);
                    if (newData.length != '') {
                        value1 = newData;
                    }
                    $.each(value1, function(key2, value2) {
                        html += '<option value="' + key2 + '">' + value2 + '</option>';
                    })
                    html += '</select>\
                        </div>';
                } else {
                    html += '<div class="form-icon-user">\
                                <input name="' + key1 + '" id="' + key1 + '" placeholder="' + key1.toUpperCase() + '" class="form-control">\
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
                    case 'job-category':
                        $('.category').html(response.output);
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
</script>

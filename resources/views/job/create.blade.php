<style>
    .addBtn{
        float: right;
    }
</style>
@extends('layouts.admin')
@section('page-title')
    {{ __('Create Job') }}
@endsection
@push('css-page')
    <link href="{{ asset('public/libs/bootstrap-tagsinput/dist/bootstrap-tagsinput.css') }}" rel="stylesheet" />
@endpush
@push('script-page')
    <script src='{{ asset('assets/js/plugins/tinymce/tinymce.min.js') }}'></script>
    <script src="{{ asset('public/libs/bootstrap-tagsinput/dist/bootstrap-tagsinput.min.js') }}"></script>

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
                            {!! Form::label('title', __('Job Title'), ['class' => 'col-form-label']) !!}
                            {!! Form::text('title', old('title'), ['class' => 'form-control', 'required' => 'required' ,'placeholder'=>'Enter job title']) !!}
                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('branch', __('Branch'), ['class' => 'col-form-label']) !!}
                            <a href="#" data-title="{{ __('Create New Branch') }}" onclick="modalShow([{'name' : ''}], 'create-branch', 'Create Branch','branch')" data-bs-toggle="tooltip"
                                title="{{ __('Create New Branch') }}" class="btn btn-sm btn-primary addBtn"
                                data-bs-original-title="{{ __('Create') }}">
                                <i class="ti ti-plus"></i>
                            </a>
                            {{ Form::select('branch', $branches, null, ['class' => 'form-control branch_id', 'required' => 'required']) }}
                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('category', __('Job Category'), ['class' => 'col-form-label']) !!}
                            <a href="#" data-title="{{ __('Create New Job Category') }}" onclick="modalShow([{'name' : ''}], 'create-job-category', 'Create Job Category','job-category')" data-bs-toggle="tooltip"
                                title="{{ __('Create New Job Category') }}" class="btn btn-sm btn-primary addBtn"
                                data-bs-original-title="{{ __('Create') }}">
                                <i class="ti ti-plus"></i>
                            </a>
                            {{ Form::select('category', $categories, null, ['class' => 'form-control category', 'required' => 'required']) }}
                        </div>

                        <div class="form-group col-md-6">
                            {!! Form::label('position', __('No. of Positions'), ['class' => 'col-form-label']) !!}
                            {!! Form::text('position', old('positions'), ['class' => 'form-control', 'required' => 'required']) !!}
                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('status', __('Status'), ['class' => 'col-form-label']) !!}
                            {{ Form::select('status', $status, null, ['class' => 'form-control select2', 'required' => 'required']) }}
                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('start_date', __('Start Date'), ['class' => 'col-form-label']) !!}
                            {!! Form::date('start_date', old('start_date'), ['class' => 'form-control ', 'autocomplete' => 'off']) !!}
                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('end_date', __('End Date'), ['class' => 'col-form-label']) !!}
                            {!! Form::date('end_date', old('end_date'), ['class' => 'form-control ', 'autocomplete' => 'off']) !!}
                        </div>
                        <div class="form-group col-md-12">
                            <label class="col-form-label" for="skill">{{ __('Skill Box') }}</label>
                            <input type="text" class="form-control" value="" data-toggle="tags" name="skill"
                                placeholder="Skill" />
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
                                        <input type="checkbox" class="form-check-input" name="applicant[]" value="gender"
                                            id="check-gender">
                                        <label class="form-check-label" for="check-gender">{{ __('Gender') }} </label>
                                    </div>
                                    <div class="form-check custom-checkbox">
                                        <input type="checkbox" class="form-check-input" name="applicant[]" value="dob"
                                            id="check-dob">
                                        <label class="form-check-label" for="check-dob">{{ __('Date Of Birth') }}</label>
                                    </div>
                                    <div class="form-check custom-checkbox">
                                        <input type="checkbox" class="form-check-input" name="applicant[]" value="address"
                                            id="check-address">
                                        <label class="form-check-label" for="check-address">{{ __('Address') }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <h6>{{ __('Need to show Option ?') }}</h6>
                                <div class="my-4">
                                    <div class="form-check custom-checkbox">
                                        <input type="checkbox" class="form-check-input" name="visibility[]" value="profile"
                                            id="check-profile">
                                        <label class="form-check-label" for="check-profile">{{ __('Profile Image') }}
                                        </label>
                                    </div>
                                    <div class="form-check custom-checkbox">
                                        <input type="checkbox" class="form-check-input" name="visibility[]" value="resume"
                                            id="check-resume">
                                        <label class="form-check-label" for="check-resume">{{ __('Resume') }}</label>
                                    </div>
                                    <div class="form-check custom-checkbox">
                                        <input type="checkbox" class="form-check-input" name="visibility[]" value="letter"
                                            id="check-letter">
                                        <label class="form-check-label"
                                            for="check-letter">{{ __('Cover Letter') }}</label>
                                    </div>
                                    <div class="form-check custom-checkbox">
                                        <input type="checkbox" class="form-check-input" name="visibility[]" value="terms"
                                            id="check-terms">
                                        <label class="form-check-label"
                                            for="check-terms">{{ __('Terms And Conditions') }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-md-12">
                            <h6>{{ __('Custom Questions') }}</h6>
                            <div class="my-4">
                                @foreach ($customQuestion as $question)
                                    <div class="form-check custom-checkbox">
                                        <input type="checkbox" class="form-check-input" name="custom_question[]"
                                            value="{{ $question->id }}" @if($question->is_required == 'yes') required @endif id="custom_question_{{ $question->id }}">
                                        <label class="form-check-label"
                                            for="custom_question_{{ $question->id }}">{{ $question->question}}@if ($question->is_required == 'yes')
                                            <span class="text-danger">*</span>
                                        @endif
                                        </label>
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
                            {!! Form::label('description', __('Job Description'), ['class' => 'col-form-label']) !!}
                            <textarea class="form-control editor " name="description" id="description" rows="17"></textarea>
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
                            <textarea class="form-control editor " name="requirement" id="requirement" rows="10"></textarea>
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

<div class="modal fade" id="staticBackdrop" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
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
    function modalShow(data, route, title,section){
        globalRoute = route;
        html = '';
        newData = "";
        $('#staticBackdropLabel').text(title);

        html += '<div class="row">';
        $.each(data, function (key, value) { 
            $.each(value, function(key1, value1){
                html += '<div class="col-lg-12 col-md-12 col-sm-12">\
                        <div class="form-group">\
                            <label class="form-label">'+key1.toUpperCase()+'</label>';
                            // console.log(value1);

                if(value1 != ''){
                    html += '<div class="form-icon-user">\
                            <select class="form-control" name="'+key1+'">';
                                value1 = JSON.parse(value1);
                                if(newData.length != ''){
                                    value1 = newData;
                                }
                                $.each(value1, function(key2, value2){
                                    html += '<option value="'+key2+'">'+value2+'</option>';
                                })    
                    html += '</select>\
                        </div>';
                }else{
                    html +=  '<div class="form-icon-user">\
                                <input name="'+key1+'" id="'+key1+'" placeholder="'+key1.toUpperCase()+'" class="form-control">\
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

    function submitForm(){
        var input = [];
        $("#myForm :input").each(function(){
            var value = $(this).val(); 
            var name = $(this).attr('name'); 
            input.push({
                'name' : name,
                'value' : value
            });
        });
        var url = "<?= url('') ?>/" + globalRoute;
        
        $.ajax({
            type: "post",
            url: url,
            data: input,
            success: function (response) {
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

    function closeModal(){
        $('#staticBackdrop').modal('hide');
        html = "";
    }
</script>
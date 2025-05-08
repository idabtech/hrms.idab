{{ Form::open(['url' => 'job-application', 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-12">
            {{ Form::label('job', __('Job'), ['class' => 'form-label']) }}
            <a href="#" data-title="{{ __('Create New Job') }}" onclick="modalShow([{'job_title' : ''},{'branch' : '{{$branches}}'},{'job_category' : '{{$job_category}}'},{'no_of_positions' : ''},{'status' : ''},{'skill_box' : ''}], 'create-job', 'Create Job', 'job')" data-bs-toggle="tooltip"
                title="{{ __('Create New Job') }}" style="float: right" class="btn btn-sm btn-primary addBtn"
                data-bs-original-title="{{ __('Create') }}">
                <i class="ti ti-plus"></i>
            </a>
            {{ Form::select('job', $jobs, null, ['class' => 'form-control job', 'id' => 'jobs']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
            {{ Form::text('name', null, ['class' => 'form-control name']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('email', __('Email'), ['class' => 'form-label']) }}
            {{ Form::text('email', null, ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('phone', __('Phone'), ['class' => 'form-label']) }}
            {{ Form::text('phone', null, ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-md-6 gender d-none">
            {{--  class change dob to gender  --}}
            {!! Form::label('dob', __('Date of Birth'), ['class' => 'form-label']) !!}
            {!! Form::date('dob', old('dob'), ['class' => 'form-control d_week', 'autocomplete' => 'off']) !!}
        </div>
        <div class="form-group col-md-6 gender d-none">
            {!! Form::label('gender', __('Gender'), ['class' => 'form-label']) !!}
            <div class="d-flex radio-check">
                <div class="form-check form-check-inline form-group">
                    <input type="radio" id="g_male" value="Male" name="gender" class="form-check-input">
                    <label class="form-check-label" for="g_male">{{ __('Male') }}</label>
                </div>
                <div class="form-check form-check-inline form-group">
                    <input type="radio" id="g_female" value="Female" name="gender" class="form-check-input">
                    <label class="form-check-label" for="g_female">{{ __('Female') }}</label>
                </div>
            </div>
        </div>
        <div class="form-group col-md-12 address d-none">
            {{ Form::label('address', __('Address'), ['class' => 'form-label']) }}
            {{ Form::textarea('address', null, ['class' => 'form-control' ,'placeholder'=>'Enter address','rows'=>'3']) }}
        </div>
        <div class="form-group col-md-6 address d-none">
            {{ Form::label('city', __('City'), ['class' => 'form-label']) }}
            {{ Form::text('city', null, ['class' => 'form-control' ,'placeholder'=>'Enter city']) }}
        </div>
        <div class="form-group col-md-6 address d-none">
            {{ Form::label('state', __('State'), ['class' => 'form-label']) }}
            {{ Form::text('state', null, ['class' => 'form-control' ,'placeholder'=>'Enter state']) }}
        </div>
        <div class="form-group col-md-6 address d-none">
            {{ Form::label('country', __('Country'), ['class' => 'form-label']) }}
            {{ Form::text('country', null, ['class' => 'form-control' ,'placeholder'=>'Enter country']) }}
        </div>
        <div class="form-group col-md-6 address d-none">
            {{ Form::label('zip_code', __('Zip Code'), ['class' => 'form-label']) }}
            {{ Form::text('zip_code', null, ['class' => 'form-control' ,'placeholder'=>'Enter zip code']) }}
        </div>

        <div class="form-group col-md-6 resume d-none">
            {{--  class changed profile to resume  --}}
            {{ Form::label('profile', __('Profile'), ['class' => 'form-label']) }}
            <div class="choose-files ">
                <label for="profile" >
                    <div class=" bg-primary image_update">
                        <i class="ti ti-upload px-1"></i>{{__('Choose file here')}}
                    </div>
                    <input type="file" class="custom-input-file d-none" name="profile" id="profile" onchange="document.getElementById('blah').src = window.URL.createObjectURL(this.files[0])">
                    <img id="blah" src="" width="100" />
                </label>
            </div>
        </div>

        <div class="form-group col-md-6 resume d-none">
            {{ Form::label('resume', __('CV / Resume'), ['class' => 'form-label']) }}
            <div class="choose-files ">
                <label for="resume" >
                    <div class=" bg-primary image_update">
                        <i class="ti ti-upload px-1"></i>{{__('Choose file here')}}
                    </div>
                    <input type="file" name="resume" id="resume" class="custom-input-file d-none" data-filename="resume_create"  onchange="document.getElementById('blah1').src = window.URL.createObjectURL(this.files[0])">
                    <img id="blah1"  width="100"src=""/>
                </label>
            </div>
        </div>

        <div class="form-group col-md-12 resume d-none">
            {{--  letter to resume  --}}
            {{ Form::label('cover_letter', __('Cover Letter'), ['class' => 'form-label']) }}
            {{ Form::textarea('cover_letter', null, ['class' => 'form-control']) }}
        </div>
        @foreach ($questions as $question)
            <div class="form-group col-md-12  resume question_{{ $question->id }} d-none">
                {{--  question to resume  --}}
                {{ Form::label($question->question, $question->question, ['class' => 'form-label']) }}
                <input type="text" class="form-control" name="question[{{ $question->question }}]"
                    {{ $question->is_required == 'yes' ? 'required' : '' }}>
            </div>
        @endforeach

    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn  btn-primary">
</div>
{{ Form::close() }}

<div class="modal fade" id="staticBackdrop" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title" id="staticBackdropLabel"></h5>
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
    var globalJob = '';
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
                    case 'job':
                        $('.job').html(response.output);
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
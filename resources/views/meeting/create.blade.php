@php
    $setting = App\Models\Utility::settings();
@endphp
<style>
    .addBtn{
        float: right;
    }
</style>
{{ Form::open(['url' => 'meeting', 'method' => 'post']) }}
<div class="modal-body">

    <div class="row">


        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('branch_id', __('Branch'), ['class' => 'form-label']) }}
                <a href="javascript:void(0)" data-title="{{ __('Create New Branch') }}" onclick="modalShow([{'name' : ''}], 'create-branch', 'Create Branch')" data-bs-toggle="tooltip"
                    title="{{ __('Create New Branch') }}" class="btn btn-sm btn-primary addBtn"
                    data-bs-original-title="{{ __('Create') }}">
                    <i class="ti ti-plus"></i>
                </a>
                {{-- <div class="form-icon-user">
                    <select class="form-control branch_id" name="branch_id"
                        placeholder="Select Branch" id="branch_id">
                        <option value="">{{ __('Select Branch') }}</option>
                        <option value="0">{{ __('All Branch') }}</option>
                        @foreach ($branch as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div> --}}
                <div class="form-icon-user">
                    {{ Form::select('branch_id', $branch, null, ['class' => 'form-control branch_id', 'required' => 'required', 'placeholder' => 'Select Branch','id' => 'branch_id']) }}
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('department_id', __('Department'), ['class' => 'form-label']) }}
                <a href="javascript:void(0)" data-title="{{ __('Create New Department') }}" onclick="modalShow([{'branch': '{{$branch}}'}, {'name' : ''},{'slug' : ''}], 'create-department','Create Department','department')" data-bs-toggle="tooltip"
                    title="{{ __('Create New Department') }}"  class="btn btn-sm btn-primary addBtn "
                    data-bs-original-title="{{ __('Create') }}">
                    <i class="ti ti-plus"></i>
                </a>
                <div class="form-icon-user">
                    <div class="department_div">
                        <select class="form-control department_id" name="department_id[]" id="choices-multiple"
                        placeholder="Select Department" multiple>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('employee_id', __('Employee'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    <div class="employee_div">
                        <select class="form-control  employee_id" name="employee_id[]" id="choices-multiple"
                        placeholder="Select Employee" required>
                        </select>
                    </div>
                </div>
            </div>
        </div>


        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('title', __('Meeting Title'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::text('title', null, ['class' => 'form-control ', 'required' => 'required' ,'placeholder' => __('Enter Meeting Title')]) }}
                </div>
            </div>
        </div>


        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('date', __('Meeting Date'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::date('date', null, ['class' => 'form-control ', 'required' => 'required' ,'autocomplete'=>'off']) }}
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('time', __('Meeting Time'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::time('time', null, ['class' => 'form-control', 'required' => 'required']) }}
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('link', __('Meeting Link'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::text('link', null, ['class' => 'form-control ', 'required' => 'required' ,'placeholder' => __('Enter Meeting Link')]) }}
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('username', __('Meeting Username'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::text('username', null, ['class' => 'form-control ', 'required' => 'required' ,'placeholder' => __('Enter Meeting Username')]) }}
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('password', __('Meeting Password'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::text('password', null, ['class' => 'form-control ', 'required' => 'required' ,'placeholder' => __('Enter Meeting Password')]) }}
                </div>
            </div>
        </div>


        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{ Form::label('note', __('Meeting Note'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::textarea('note', null, ['class' => 'form-control', 'rows' => '3']) }}
                </div>
            </div>
        </div>
        @if(isset($setting['is_enabled']) && $setting['is_enabled'] =='on')
        <div class="form-group col-md-6">
            {{ Form::label('synchronize_type', __('Synchroniz in Google Calendar ?'), ['class' => 'form-label']) }}
            <div class=" form-switch">
                <input type="checkbox" class="form-check-input mt-2" name="synchronize_type" id="switch-shadow"
                    value="google_calender">
                <label class="form-check-label" for="switch-shadow"></label>
            </div>
        </div>
        @endif
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="Cancel" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
</div>
{{ Form::close() }}

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
    var globalDepartment = '';
    var newData = "";
    function modalShow(data, route, title,section){
        html = "";
        globalRoute = route;
        newData = "";
        switch (section) {
            case 'department':
                getData('get-branch');
                break;
            default:
                break;
        }
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
                    case 'department':
                        $('.department_id').html(response.output);
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

    function getData(url){
        var url = "<?= url('') ?>/" + url;
        $.ajax({
            type: "get",
            url: url,
            async: false,
            success: function (response) {
                newData = JSON.parse(response);
            }
        });
    }
</script>

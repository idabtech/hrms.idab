<style>
    .addBtn {
        float: right;
    }
</style>
{{ Form::open(['url' => 'announcement', 'method' => 'post']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('title', __('Announcement Title'), ['class' => 'col-form-label']) }}
                {{ Form::text('title', null, ['class' => 'form-control', 'placeholder' => __('Enter Announcement Title'), 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('branch_id', __('Branch'), ['class' => 'col-form-label']) }}
                <a href="javascript:void(0)" data-title="{{ __('Create New Branch') }}"
                    onclick="modalShow([{'name' : ''}], 'create-branch', 'Create Branch','branch')"
                    data-bs-toggle="tooltip" title="{{ __('Create New Branch') }}" class="btn btn-sm btn-primary addBtn"
                    data-bs-original-title="{{ __('Create') }}">
                    <i class="ti ti-plus"></i>
                </a>
                {{-- <select class="form-control branch_id" name="branch_id" id="branch_id" placeholder="Select Branch">
                    <option value="">{{ __('Select Branch') }}</option>
                    <option value="0">{{ __('All Branch') }}</option>
                    @foreach ($branch as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select> --}}
                <div class="form-icon-user">
                    {{ Form::select('branch_id', $branch, null, ['class' => 'form-control branch_id', 'required' => 'required', 'placeholder' => 'Select Branch']) }}
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('department_id', __('Department'), ['class' => 'col-form-label']) }}
                <a href="javascript:void(0)" data-title="{{ __('Create New Department') }}"
                    onclick="modalShow([{'branch': '{{ $branch }}'}, {'name' : ''}], 'create-department','Create Department','department')"
                    data-bs-toggle="tooltip" title="{{ __('Create New Department') }}"
                    class="btn btn-sm btn-primary addBtn " data-bs-original-title="{{ __('Create') }}">
                    <i class="ti ti-plus"></i>
                </a>
                <div class="department_div">
                    <select class="form-control department_id" id="department_id" name="department_id[]"
                        placeholder="Select Department" multiple>
                    </select>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('employee_id', __('Employee'), ['class' => 'col-form-label']) }}


                <div class="employee_div">
                    <select class="form-control select2  employee_id" name="employee_id[]" id="employee_id"
                        placeholder="Select Employee" multiple>
                    </select>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('start_date', __('Announcement start Date'), ['class' => 'col-form-label']) }}
                {{ Form::text('start_date', null, ['class' => 'form-control d_week', 'autocomplete' => 'off', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('end_date', __('Announcement End Date'), ['class' => 'col-form-label']) }}
                {{ Form::text('end_date', null, ['class' => 'form-control d_week', 'autocomplete' => 'off', 'required' => 'required']) }}
            </div>
        </div>
        <div class="form-group">
            {{ Form::label('description', __('Announcement Description'), ['class' => 'col-form-label']) }}
            {{ Form::textarea('description', null, ['class' => 'form-control', 'placeholder' => __('Enter Announcement Title'), 'rows' => '3', 'required' => 'required']) }}
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="Cancel" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="btn  btn-primary">

    </div>
</div>
{{ Form::close() }}

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
    var globalDepartment = '';
    var newData = "";

    function modalShow(data, route, title, section) {
        globalRoute = route;
        html = '';
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
                    case 'department':
                        $('.department_id').html(response.output);
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
</script>

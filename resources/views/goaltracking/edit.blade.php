<style>
    .addBtn{
        float: right;
    }
</style>
{{ Form::model($goalTracking, ['route' => ['goaltracking.update', $goalTracking->id], 'method' => 'PUT']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('branch', __('Branch*'), ['class' => 'col-form-label']) }}
                <a href="javascript:void(0)" data-title="{{ __('Create New Branch') }}" onclick="modalShow([{'name' : ''}], 'create-branch','Create Branch','branch')" data-bs-toggle="tooltip"
                title="{{ __('Create New Branch') }}"  class="btn btn-sm btn-primary createBranchBtn addBtn"
                data-bs-original-title="{{ __('Create') }}">
                <i class="ti ti-plus"></i>
                </a>
                {{ Form::select('branch', $brances, null, ['class' => 'form-control branch_id', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('goal_type', __('GoalTypes*'), ['class' => 'col-form-label']) }}
                <a href="javascript:void(0)" data-title="{{ __('Create New Goal Type') }}" onclick="modalShow([{'name' : ''}], 'create-goal-type','Create Goal Type', 'goaltype')" data-bs-toggle="tooltip"
                title="{{ __('Create New Goal type') }}"  class="btn btn-sm btn-primary createBranchBtn addBtn"
                data-bs-original-title="{{ __('Create') }}">
                <i class="ti ti-plus"></i>
                </a>
                {{ Form::select('goal_type', $goalTypes, null, ['class' => 'form-control goal_id', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('start_date', __('Start Date*'), ['class' => 'col-form-label']) }}
                {{ Form::text('start_date', null, ['class' => 'form-control d_week','autocomplete'=>'off' ,'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('end_date', __('End Date*'), ['class' => 'col-form-label']) }}
                {{ Form::text('end_date', null, ['class' => 'form-control d_week','autocomplete'=>'off' ,'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('subject', __('Subject*'), ['class' => 'col-form-label']) }}
                {{ Form::text('subject', null, ['class' => 'form-control','placeholder'=>'Enter subject' ,'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('target_achievement', __('Target Achievement'), ['class' => 'col-form-label']) }}
                {{ Form::text('target_achievement', null, ['class' => 'form-control' ,'placeholder'=>'Enter target achievement']) }}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('description', __('Description'), ['class' => 'col-form-label']) }}
                {{ Form::textarea('description', null, ['class' => 'form-control', 'rows' => '3' ,'placeholder'=>'Enter description']) }}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('status', __('Status'), ['class' => 'col-form-label']) }}
                {{ Form::select('status', $status, null, ['class' => 'form-control select2']) }}
            </div>
        </div>
        <div class="col-md-12">
            <fieldset id='demo1' class="rate">
                <input class="stars" type="radio" id="rating-5" name="rating" value="5"
                    {{ $goalTracking->rating == 5 ? 'checked' : '' }}>
                <label class="full" for="rating-5" title="Awesome - 5 stars"></label>
                <input class="stars" type="radio" id="rating-4" name="rating" value="4"
                    {{ $goalTracking->rating == 4 ? 'checked' : '' }}>
                <label class="full" for="rating-4" title="Pretty good - 4 stars"></label>
                <input class="stars" type="radio" id="rating-3" name="rating" value="3"
                    {{ $goalTracking->rating == 3 ? 'checked' : '' }}>
                <label class="full" for="rating-3" title="Meh - 3 stars"></label>
                <input class="stars" type="radio" id="rating-2" name="rating" value="2"
                    {{ $goalTracking->rating == 2 ? 'checked' : '' }}>
                <label class="full" for="rating-2" title="Kinda bad - 2 stars"></label>
                <input class="stars" type="radio" id="technical-1" name="rating" value="1"
                    {{ $goalTracking->rating == 1 ? 'checked' : '' }}>
                <label class="full" for="technical-1" title="Sucks big time - 1 star"></label>
            </fieldset>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <input type="range" class=" slider  w-100 mb-0 " name="progress" id="myRange"
                    value="{{ $goalTracking->progress }}" min="1" max="100"
                    oninput="ageOutputId.value = myRange.value">
                <output name="ageOutputName" id="ageOutputId">{{ $goalTracking->progress }}</output>
                %
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <input type="button" value="Cancel" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
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
    var globalGoalType = '';
    function modalShow(data, route, title, section){
        globalRoute = route;
        html ="";
        var newData = "";
        switch (section) {
            case 'branch':
                newData = globalBranch;
                break;
            case 'goaltype':
                newData = globalGoalType;
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
                                if(newData.length > 0){
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
                        globalBranch = response.branch;
                        break;
                    case 'goaltype':
                        $('.goal_id').html(response.output);
                        globalGoalType = response.goaltype;
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

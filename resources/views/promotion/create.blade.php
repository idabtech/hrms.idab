{{ Form::open(['url' => 'promotion', 'method' => 'post']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-lg-6 col-md-6">
            {{ Form::label('employee_id', __('Employee'), ['class' => 'col-form-label']) }}
            {{ Form::select('employee_id', $employees, null, ['class' => 'form-control select2', 'required' => 'required']) }}
        </div>
        <div class="form-group col-lg-6 col-md-6">
            {{ Form::label('designation_id', __('Designation'), ['class' => 'col-form-label']) }}
            <a href="#" data-title="{{ __('Create New Designation') }}" onclick="modalShow([{'department': '{{$departments}}'}, {'name' : ''}], 'create-designation','Create Designation','designation')" data-bs-toggle="tooltip"
                title="{{ __('Create New Designation') }}" style="float: right"  class="btn btn-sm btn-primary createDeptBtn"
                data-bs-original-title="{{ __('Create') }}">
                <i class="ti ti-plus"></i>
            </a>
            {{ Form::select('designation_id', $designations, null, ['class' => 'form-control designation_id' , 'required' => 'required']) }}
        </div>
        <div class="form-group col-lg-6 col-md-6">
            {{ Form::label('promotion_title', __('Promotion Title'), ['class' => 'col-form-label']) }}
            {{ Form::text('promotion_title', null, ['class' => 'form-control','placeholder'=>'Enter Promotion Title' , 'required' => 'required']) }}
        </div>
        <div class="form-group col-lg-6 col-md-6">
            {{ Form::label('promotion_date', __('Promotion Date'), ['class' => 'col-form-label']) }}
            {{ Form::text('promotion_date', null, ['class' => 'form-control d_week','autocomplete'=>'off' , 'required' => 'required']) }}
        </div>
        <div class="form-group col-lg-12">
            {{ Form::label('description', __('Description'), ['class' => 'col-form-label']) }}
            {{ Form::textarea('description', null, ['class' => 'form-control', 'placeholder' => __('Enter Description'),'rows'=>'3' , 'required' => 'required']) }}
        </div>
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
    var globalDesignation = '';
    var newData = "";
    function modalShow(data, route, title,section){
        html = "";
        globalRoute = route;
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
                    case 'designation':
                        $('.designation_id').html(response.output);
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
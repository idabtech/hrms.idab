
    {{ Form::open(['url' => 'company-policy', 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
    <div class="modal-body">
        <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-6">
                <div class="form-group">
                    {{ Form::label('branch', __('Branch'), ['class' => 'form-label']) }}
                    <a href="javascript:void(0)" data-title="{{ __('Create New Branch') }}" onclick="modalShow([{'name' : ''}], 'create-branch', 'Create Branch','branch')" data-bs-toggle="tooltip"
                    title="{{ __('Create New Branch') }}" style="float: right" class="btn btn-sm btn-primary addBtn"
                    data-bs-original-title="{{ __('Create') }}">
                    <i class="ti ti-plus"></i>
                    </a>
                    <div class="form-icon-user">
                        {{ Form::select('branch', $branch, null, ['class' => 'form-control branch_id', 'required' => 'required']) }}
                    </div>
                </div>
            </div>

            <div class="col-lg-6 col-md-6 col-sm-6">
                <div class="form-group">
                    {{ Form::label('title', __('Title'), ['class' => 'form-label']) }}
                    <div class="form-icon-user">
                        {{ Form::text('title', null, ['class' => 'form-control', 'placeholder' => __('Enter Company Policy Title')]) }}
                    </div>

                </div>
            </div>

            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="form-group">
                    {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
                    <div class="form-icon-user">
                        {{ Form::textarea('description', null, ['class' => 'form-control', 'rows' => '3']) }}
                    </div>
                </div>
            </div>

            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="form-group">
                    {{ Form::label('attachment', __('Attachment'), ['class' => 'col-form-label']) }}
                    <div class="choose-files ">
                        <label for="attachment">
                            <div class=" bg-primary attachment "> <i
                                    class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                            </div>
                            <input type="file" class="form-control file" name="attachment" id="attachment" onchange="document.getElementById('blah').src = window.URL.createObjectURL(this.files[0])">
                            <img id="blah"  width="100" src="" />
                        </label>
                    </div>
                </div>
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
        var globalBranch = '';
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
                        case 'branch':
                            $('.branch_id').html(response.output);
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
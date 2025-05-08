
{{ Form::open(['route' => ['attendence.import'], 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
<div class="modal-body">

    <div class="row">

        <div class="col-md-12 mb-6">
            <label for="file" class="form-label">Download sample product CSV file</label>
            <a href="{{ asset(Storage::url('uploads/sample')) . '/sample_attendence.csv' }}"
                class="btn btn-sm btn-primary ">
                <i class="ti ti-download"></i> {{ __('Download') }}
            </a>
        </div>

        <div class="choose-files mt-3">
            <label for="file" class="file-upload">
                <div class=" bg-primary "> <i
                        class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                </div>
                <input type="file" class="form-control file"
                    name="file" id="file"
                    data-filename="file">
            </label>
        </div>
        <label id="file-name"></label>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="Cancel" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Upload') }}" class="btn btn-primary">
</div>
{{ Form::close() }}
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script>
    $(document).ready(function(){
        $('#file').change(function() {
            var file = $(this).val().split('\\').pop();
            $('#file-name').html(file);
            console.log($(this));
        });
    });
</script>
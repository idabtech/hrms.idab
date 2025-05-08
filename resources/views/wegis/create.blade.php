
<form method="POST" action="{{route('wegis-store')}}"class="simple-example">
    @csrf
    <div class="modal-body">

        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="form-group">
                    {{ Form::label('wegis_type', __('Wegis Type'), ['class' => 'form-label']) }}
                    
                    <div class="form-icon-user">
                        {{ Form::select('wegis_type', $wegisType, null, ['class' => 'form-control','placeholder' => __('Select Wegis Type')]) }}
                    </div>
                </div>
            </div>
    
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="form-group">
                    {{ Form::label('minimum', __('Minimum'), ['class' => 'form-label']) }}
                    <div class="form-icon-user">
                        {{ Form::text('minimum', null, ['class' => 'form-control', 'id' => 'minimum', 'placeholder' => __('Enter Minimum Wegis'), 'onkeyup' => 'checkDaGreaterthanMinimum()']) }}
                    </div>
                    @error('minimum')
                        <span class="invalid-name" role="alert">
                            <strong class="text-danger">{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="form-group">
                    {{ Form::label('da', __('DA'), ['class' => 'form-label']) }}
                    <div class="form-icon-user">
                        {{ Form::text('da', null, ['class' => 'form-control', 'id' => 'da', 'placeholder' => __('Enter DA'), 'onkeyup' => 'checkDaGreaterthanMinimum()']) }}
                    </div>
                    @error('da')
                        <span class="invalid-name" role="alert">
                            <strong class="text-danger">{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div id="errordiv" align="center" style="margin-left: auto; margin-right: auto;"> 
                    <span id="error" style="color: red; display:none">DA Must Greater Than Minimum</span> 
                </div>
            </div>
    
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="Cancel" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
    </div>
</form>
<script>
 function checkDaGreaterthanMinimum(){
    var minimum = Number(document.getElementById("minimum").value);
    var da = Number(document.getElementById("da").value);
    var errorElement = document.getElementById("error");
    if(minimum != "" && da != ""){
        if(minimum > da){
            errorElement.style.display = 'block';
        }else{
            errorElement.style.display = 'none';
        }
    }
 }
</script>
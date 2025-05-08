<form method="POST" action="{{route('wegis.update')}}"class="simple-example">
    @csrf
    <div class="modal-body">

        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="form-group">
                    {{ Form::label('wegis_type', __('Wegis Type'), ['class' => 'form-label']) }}
                    <div class="form-icon-user">
                        {{ Form::select('wegis_type', $wegisType, $wegis->wegis_type, ['class' => 'form-control','placeholder' => __('Select Wegis Type')]) }}
                    </div>
                </div>
            </div>
    
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="form-group">
                    {{ Form::label('minimum', __('Minimum'), ['class' => 'form-label']) }}
                    <div class="form-icon-user">
                        {{ Form::text('minimum', $wegis->minimum, ['class' => 'form-control']) }}
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
                        {{ Form::text('da', $wegis->da, ['class' => 'form-control']) }}
                    </div>
                    @error('da')
                        <span class="invalid-name" role="alert">
                            <strong class="text-danger">{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>
    
        </div>
    </div>
    <input type="hidden" name="id" value="@if(isset($wegis)) {{$wegis->id}}  @endif">
    <div class="modal-footer">
        <input type="button" value="Cancel" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
    </div>
</form>
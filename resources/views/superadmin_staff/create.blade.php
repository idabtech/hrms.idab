{!! Form::open(['route' => 'superadmin-staff.store', 'method' => 'post', 'class' => 'needs-validation', 'novalidate']) !!}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-12">
            {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}<x-required></x-required>
            <div class="form-icon-user">
                {!! Form::text('name', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Name')]) !!}
            </div>
        </div>
        <div class="form-group col-12">
            {{ Form::label('email', __('Email'), ['class' => 'form-label']) }}<x-required></x-required>
            <div class="form-icon-user">
                {!! Form::email('email', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Email')]) !!}
            </div>
        </div>
        <div class="form-group col-12">
            {{ Form::label('password', __('Password'), ['class' => 'form-label']) }}<x-required></x-required>
            <div class="form-icon-user">
                {!! Form::password('password', ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Password')]) !!}
            </div>
        </div>
        <div class="form-group col-12">
            {{ Form::label('role', __('User Role'), ['class' => 'form-label']) }}<x-required></x-required>
            <div class="form-icon-user">
                {!! Form::select('role', $roles, null, ['class' => 'form-control', 'required' => 'required']) !!}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
</div>
{!! Form::close() !!}

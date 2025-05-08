
@extends('layouts.auth')
@section('page-title')
    {{ __('verify otp') }}
@endsection
@php
    $logo = asset(Storage::url('uploads/logo/'));
    $logo = \App\Models\Utility::get_file('uploads/logo/');
@endphp
@section('content')
<div class="card">
    <div class="row align-items-center text-start">
        <div class="col-xl-6">
            {{ Form::open(['route' => 'otp.verification', 'method' => 'post']) }}
            @csrf
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">{{ __('Enter OTP') }}</label>
                    {{ Form::text('otp', null, ['class' => 'form-control', 'placeholder' => __('Enter otp')]) }}
                </div>
                <div class="form-group">
                    <input type="hidden" name="mobile" value="{{$mobile}}">
                </div>
                <button type="submit" class="btn btn-primary">Verify OTP</button>
            </div>
         </form>
        </div>
    </div>
</div>
@endsection

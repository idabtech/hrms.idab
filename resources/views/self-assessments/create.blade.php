@extends('layouts.admin')

@section('page-title')
    {{ __('Create Self Assessment') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('self-assessments.index') }}">{{ __('Self Assessment') }}</a></li>
    <li class="breadcrumb-item">{{ __('Create') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            {{ Form::model($assessment, ['route' => ['self-assessments.store'], 'method' => 'POST', 'class' => 'self-assessment-form', 'novalidate']) }}
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Monthly Self Assessment Form') }}</h5>
                    </div>
                    <div class="card-body">
                        @include('self-assessments.partials.form')
                    </div>
                    <div class="card-footer text-end">
                        <a href="{{ route('self-assessments.index') }}" class="btn btn-light me-2">{{ __('Cancel') }}</a>
                        <input type="submit" value="{{ __('Save as Draft') }}" class="btn btn-primary">
                    </div>
                </div>
            {{ Form::close() }}
        </div>
    </div>
@endsection

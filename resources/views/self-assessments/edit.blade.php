@extends('layouts.admin')

@section('page-title')
    {{ __('Edit Self Assessment') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('self-assessments.index') }}">{{ __('Self Assessment') }}</a></li>
    <li class="breadcrumb-item">{{ __('Edit') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            {{ Form::model($assessment, ['route' => ['self-assessments.update', $assessment->id], 'method' => 'PUT', 'class' => 'self-assessment-form', 'novalidate']) }}
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>{{ __('Edit Self Assessment Form') }}</h5>
                        <span class="badge bg-secondary p-2 px-3 rounded">{{ __('Draft') }}</span>
                    </div>
                    <div class="card-body">
                        @include('self-assessments.partials.form')
                    </div>
                    <div class="card-footer text-end">
                        <a href="{{ route('self-assessments.show', $assessment->id) }}" class="btn btn-light me-2">{{ __('Cancel') }}</a>
                        <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
                    </div>
                </div>
            {{ Form::close() }}
        </div>
    </div>
@endsection

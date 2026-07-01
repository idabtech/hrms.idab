@extends('layouts.admin')

@section('page-title', __('Upload Document'))

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('toolkit_documents.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">{{ __('Title') }}</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('Upload File') }}</label>
                    <input type="file" name="file" class="form-control" accept=".pdf,.docx,.xlsx,.csv" required>
                </div>
                <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
            </form>
        </div>
    </div>
@endsection

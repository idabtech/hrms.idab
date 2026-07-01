@extends('layouts.admin')

@php
    use Illuminate\Support\Facades\Crypt;
    $encId = Crypt::encrypt($document->id);
@endphp

@section('page-title', __('Edit Document'))

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('toolkit_documents.update', $encId) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label">{{ __('Title') }}</label>
                    <input type="text" name="title" value="{{ $document->title }}" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('Replace File (optional)') }}</label>
                    <input type="file" name="file" class="form-control" accept=".pdf,.docx,.xlsx,.csv">
                </div>
                <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
            </form>
        </div>
    </div>
@endsection

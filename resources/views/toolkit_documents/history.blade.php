@extends('layouts.admin')
@section('page-title', __('Edit History: ') . $document->title)

@section('content')
    <div class="card">
        <div class="card-header">{{ __('Edit Versions') }}</div>
        <div class="card-body">
            <ul class="list-group">
                @foreach($edits as $edit)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        {{ \Auth::user()->DateTimeFormat($edit->created_at) }}
                        <a href="{{ asset('storage/uploads/toolkit-documents/' . $edit->edited_file_path) }}" class="btn btn-sm btn-success" target="_blank">
                            {{ __('Download Version') }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endsection

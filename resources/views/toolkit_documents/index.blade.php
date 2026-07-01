@extends('layouts.admin')

@php use Illuminate\Support\Facades\Crypt; @endphp

@section('page-title', __('Toolkit Documents'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Documents') }}</li>
@endsection

@can('Create Toolkit')
    @section('action-button')
        <a href="{{ route('toolkit_documents.create') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
            title="{{ __('Upload New Document') }}">
            <i class="ti ti-plus"></i>
        </a>
    @endsection
@endcan

@section('content')
    <div class="card">
        <div class="card-body table-border-style">
            <div class="table-responsive">
                <table class="table" id="document-table">
                    <thead>
                        <tr>
                            <th>{{ __('Title') }}</th>
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('Editable') }}</th>
                            @can(['Edit Toolkit', 'Delete Toolkit'])
                                <th>{{ __('Actions') }}</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($documents as $doc)
                            <tr>
                                <td>{{ $doc->title }}</td>
                                <td>{{ strtoupper($doc->file_type) }}</td>
                                <td>{{ $doc->is_editable ? 'Yes' : 'No' }}</td>
                                <td class="Action d-flex">
                                    @php $encId = Crypt::encrypt($doc->id); @endphp

                                    @can('Preview Toolkit')
                                        @if ($doc->is_editable)
                                            <a target="_blank" href="{{ route('toolkit_documents.edit-content', $encId) }}"
                                                class="btn btn-sm btn-info mx-1" title="{{ __('Edit') }}">
                                                <i class="ti ti-pencil text-white"></i>
                                            </a>
                                        @endif
                                    @endcan

                                    @can('Preview Toolkit')
                                        <a href="{{ route('toolkit_documents.preview', $encId) }}"
                                            class="btn btn-sm btn-warning mx-1" title="{{ __('Preview') }}">
                                            <i class="ti ti-eye text-white"></i>
                                        </a>
                                    @endcan

                                    <a href="{{ route('toolkit_documents.history', $encId) }}"
                                        class="btn btn-sm btn-secondary mx-1" title="{{ __('View History') }}">
                                        <i class="ti ti-history text-white"></i>
                                    </a>

                                    <a href="{{ route('toolkit_documents.download', $encId) }}"
                                        class="btn btn-sm btn-success mx-1" title="{{ __('Download') }}">
                                        <i class="ti ti-download text-white"></i>
                                    </a>

                                    @can('Delete Toolkit')
                                        <form action="{{ route('toolkit_documents.destroy', $encId) }}" method="POST"
                                            onsubmit="return confirm('Are you sure?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger mx-1" title="{{ __('Delete') }}">
                                                <i class="ti ti-trash text-white"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

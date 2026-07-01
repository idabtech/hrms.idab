@extends('layouts.admin')

@php
    use Illuminate\Support\Facades\Crypt;
    $encId = Crypt::encrypt($document->id);
@endphp

@section('page-title', __('Preview Document'))

@section('content')
    <style>
        .docx-preview img,
        .xlsx-preview img {
            max-width: 100% !important;
            height: auto !important;
        }

        .docx-preview table,
        .xlsx-preview table {
            width: 100% !important;
            border-collapse: collapse;
        }

        .docx-preview td,
        .docx-preview th,
        .xlsx-preview td,
        .xlsx-preview th {
            border: 1px solid #ccc;
            padding: 6px;
        }

        .docx-preview,
        .xlsx-preview {
            overflow-x: auto;
        }
    </style>
    <div class="card">
        <div class="card-body">
            @if (in_array($ext, ['pdf']))
                <iframe src="{{ asset($fileUrl) }}" width="100%" height="600px"></iframe>
            @elseif ($ext === 'docx')
                <div class="border p-3 docx-preview" style="min-height:300px;">
                    {!! $htmlContent !!}
                </div>
            @elseif ($ext === 'xlsx')
                @if (isset($spreadsheetData['error']))
                    <div class="alert alert-danger">Error: {{ $spreadsheetData['error'] }}</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            @foreach ($spreadsheetData as $row)
                                <tr>
                                    @foreach ($row as $cell)
                                        <td>{!! nl2br(e($cell)) !!}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </table>
                    </div>
                @endif
            @elseif (in_array($ext, ['txt', 'csv', 'log', 'html', 'xml', 'json', 'md']))
                <pre>{{ file_get_contents(storage_path('app/public/' . $document->file_path)) }}</pre>
            @else
                <div class="alert alert-info">
                    This format is not natively viewable. <br>
                    <a href="{{ route('toolkit_documents.download', $encId) }}" class="btn btn-sm btn-success">Download
                        Instead</a>
                </div>
            @endif

            @if ($document->is_editable)
                <a href="{{ route('toolkit_documents.edit-content', $encId) }}" class="btn btn-primary mt-3">Edit
                    Content</a>
            @endif
        </div>
    </div>
@endsection

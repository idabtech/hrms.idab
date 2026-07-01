@extends('layouts.admin')

@php
    use Illuminate\Support\Facades\Crypt;
    $encId = Crypt::encrypt($document->id);
@endphp

@section('page-title', __('Edit Document Content'))

@section('content')
    <div class="card">
        <div class="card-body">

            <!-- Toolbar -->
            <div class="mb-2 border rounded p-2 bg-light" id="editorToolbar">
                <button type="button" onclick="format('bold')"><b>B</b></button>
                <button type="button" onclick="format('italic')"><i>I</i></button>
                <button type="button" onclick="format('underline')"><u>U</u></button>
                <button type="button" onclick="format('insertUnorderedList')">• List</button>
                <button type="button" onclick="format('insertOrderedList')">1. List</button>
                <button type="button" onclick="format('justifyLeft')">Left</button>
                <button type="button" onclick="format('justifyCenter')">Center</button>
                <button type="button" onclick="format('justifyRight')">Right</button>
                <button type="button" onclick="insertLink()">🔗 Link</button>
                <button type="button" onclick="insertImage()">🖼 Image</button>
                <button type="button" onclick="removeFormat()">Clear</button>
            </div>

            <form method="POST" action="{{ route('toolkit_documents.save-content', $encId) }}"
                onsubmit="return prepareContentForSubmit();">
                @csrf

                <div class="form-group">
                    <label><strong>Editable Document Preview</strong></label>

                    <div id="editableContent nutrient" contenteditable="true" class="border p-3 rounded bg-white"
                        style="min-height: 500px; overflow-x: auto; white-space: pre-wrap;">
                        {!! old('content', $content ?? 'No content found or unsupported file type.') !!}
                    </div>

                    @if (!empty($images))
                        <div class="mt-4">
                            <h5>Extracted Images from PDF:</h5>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach ($images as $img)
                                    <img src="{{ $img }}" style="max-width: 200px;" class="img-thumbnail" />
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Hidden input to submit edited content -->
                    <input type="hidden" name="content" id="hiddenContent">
                </div>

                <button type="submit" class="btn btn-success mt-3">
                    Save Changes
                </button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function format(command, value = null) {
            document.execCommand(command, false, value);
        }

        function insertLink() {
            const url = prompt("Enter URL:");
            if (url) format('createLink', url);
        }

        function insertImage() {
            const url = prompt("Enter image URL:");
            if (url) format('insertImage', url);
        }

        function removeFormat() {
            format('removeFormat');
        }

        function prepareContentForSubmit() {
            const editableDiv = document.getElementById('editableContent');
            document.getElementById('hiddenContent').value = editableDiv.innerHTML;
            return true;
        }
    </script>

    <style>
        #editableContent img,
        #editableContent img {
            max-width: 100% !important;
            height: auto !important;
        }

        #editorToolbar button {
            margin-right: 5px;
            padding: 4px 8px;
            border: 1px solid #ccc;
            background-color: #f9f9f9;
            cursor: pointer;
        }

        #editorToolbar button:hover {
            background-color: #e2e6ea;
        }

        #editableContent img {
            max-width: 100%;
            height: auto;
        }

        #editableContent table {
            width: 100%;
            border-collapse: collapse;
        }

        #editableContent table td,
        #editableContent table th {
            border: 1px solid #ccc;
            padding: 8px;
        }
    </style>
@endpush

@extends('layouts.admin')

@section('content')
    <div class="container mx-auto p-2 sm:p-4 max-w-full">
        <h2 class="text-xl sm:text-2xl font-bold mb-3 sm:mb-4">Edit PDF Document</h2>

        <div id="tool-pdf" class="w-full">
            <div id="pdf-editor-responsive-wrapper" class="w-full overflow-x-auto">
                <div id="pdf-editor-container" class="space-y-10 w-full max-w-full"></div>
            </div>
        </div>
        <!-- Other tool containers are included in the toolbar partial -->
        <button id="save-btn" class="mt-4 sm:mt-6 px-4 sm:px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 w-full sm:w-auto">
            Save Changes
        </button>
    </div>
    <style>
        /* Responsive PDF editor container and canvas */
        #pdf-editor-responsive-wrapper {
            width: 100%;
            max-width: 100vw;
            overflow-x: auto;
        }
        #pdf-editor-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            max-width: 100vw;
        }
        #pdf-editor-container > div {
            width: 100%;
            max-width: 100vw;
            overflow-x: auto;
            display: flex;
            justify-content: center;
        }
        #pdf-editor-container canvas {
            max-width: 100%;
            height: auto !important;
        }
        @media (max-width: 768px) {
            #pdf-editor-container {
                padding-left: 0;
                padding-right: 0;
            }
            #pdf-editor-container > div {
                padding-left: 0;
                padding-right: 0;
            }
            #pdf-editor-container canvas {
                max-width: 98vw;
            }
        }
        @media (max-width: 480px) {
            #pdf-editor-container canvas {
                max-width: 96vw;
            }
        }
        /* Responsive Save button */
        #save-btn {
            width: 100%;
            max-width: 320px;
            margin-left: auto;
            margin-right: auto;
            display: block;
        }
    </style>
    <!-- Modal for showModal -->
    <div id="global-modal" style="display:none;position:fixed;z-index:20000;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.25);align-items:center;justify-content:center;">
        <div id="global-modal-content" style="background:#fff;padding:1.5rem 1.5rem 1rem 1.5rem;border-radius:8px;max-width:96vw;min-width:260px;box-shadow:0 8px 32px #0003;position:relative;">
            <div id="global-modal-message" style="margin-bottom:1rem;font-size:1.1rem;"></div>
            <form id="global-modal-form"></form>
            <div id="global-modal-note" class="text-muted"></div>
            <div style="display:flex;gap:0.5rem;justify-content:flex-end;margin-top:1rem;">
                <button type="button" id="global-modal-cancel" style="padding:0.4rem 1.2rem;background:#f3f4f6;border:none;border-radius:4px;color:#444;font-weight:500;cursor:pointer;">Cancel</button>
                <button type="submit" id="global-modal-ok" style="padding:0.4rem 1.2rem;background:#2563eb;border:none;border-radius:4px;color:#fff;font-weight:500;cursor:pointer;">OK</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        window.PDF_EDITOR_DATA = {
            pdfUrl: "{{ asset('storage/uploads/toolkit-documents/' . $document->file_path) }}",
            saveUrl: "{{ route('toolkit_documents.save_edits', $document->id) }}",
            csrf: "{{ csrf_token() }}"
        };
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.0/fabric.min.js"
        integrity="sha384-K2R7AzsJQPCDnhTlIEZr5pifaODgyx7XqbqkZMtLKzGJNo/Irtg9yU85F+1YwL8X" crossorigin="anonymous">
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.14.305/pdf.min.js"
        integrity="sha384-LaMxPj+8jqeQw+WwjLnUqCK+wZZy1JQ/YF90OlZbwae2Pw1rfjzN6sBYBJ9l6y/k" crossorigin="anonymous">
    </script>

    <script src="{{ asset('js/pdf-editor-tools.js') }}"></script>
    <script src="{{ asset('js/pdf-editor-file.js') }}"></script>
    <script src="{{ asset('js/pdf-editor-edit.js') }}"></script>
    <script src="{{ asset('js/pdf-editor-insert.js') }}"></script>
    <script src="{{ asset('js/pdf-editor-format.js') }}"></script>
    <script src="{{ asset('js/pdf-editor-shapes.js') }}"></script>
    <script src="{{ asset('js/pdf-editor-object.js') }}"></script>
    <script src="{{ asset('js/pdf-editor-image.js') }}"></script>
    <script src="{{ asset('js/pdf-editor-special.js') }}"></script>
    <script src="{{ asset('js/pdf-editor-view.js') }}"></script>
@endpush

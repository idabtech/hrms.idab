<style>
    .swal-over-modal {
        z-index: 99999 !important;
    }
</style>
<div class="modal-body">
    {{-- Upload Section --}}
    <div class="row mb-3">
        <div class="col-12">
            <h6 class="mb-2">{{ __('Upload Attachment') }}</h6>
            <form id="warning-attachment-form" enctype="multipart/form-data">
                @csrf
                <div class="input-group">
                    <input type="file" name="file" id="warning-attachment-file" class="form-control" multiple>
                    <button type="button" class="btn btn-primary" id="btn-upload-warning-attachment">
                        <i class="ti ti-upload"></i> {{ __('Upload') }}
                    </button>
                </div>
                <small class="text-muted">{{ __('You can upload multiple files. Allowed types based on system settings.') }}</small>
            </form>
            <div id="upload-progress" class="mt-2" style="display:none;">
                <div class="progress" style="height: 5px;">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: 0%"></div>
                </div>
            </div>
        </div>
    </div>

    <hr>

    {{-- Existing Attachments --}}
    <h6 class="mb-3">{{ __('Attachments') }} <span class="badge bg-secondary" id="attachment-count">{{ $attachments->count() }}</span></h6>

    <div id="warning-attachments-list">
        @if ($attachments->isEmpty())
            <div class="text-center text-muted py-3" id="no-attachments-msg">
                <i class="ti ti-file-off" style="font-size: 2rem;"></i>
                <p class="mt-2">{{ __('No attachments yet.') }}</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="40">#</th>
                            <th>{{ __('File Name') }}</th>
                            <th width="150">{{ __('Uploaded') }}</th>
                            <th width="120" class="text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody id="attachments-tbody">
                        @foreach ($attachments as $attachment)
                            <tr id="attachment-row-{{ $attachment->id }}">
                                <td><small>{{ $attachment->sort_order }}</small></td>
                                <td>
                                    <i class="ti ti-paperclip me-1"></i>
                                    <a href="{{ route('warning.attachment.download', [$warning->id, $attachment->id]) }}" target="_blank" title="{{ $attachment->file_name }}">
                                        {{ \Illuminate\Support\Str::limit($attachment->file_name, 40) }}
                                    </a>
                                </td>
                                <td><small class="text-muted">{{ $attachment->created_at->format('d M Y') }}</small></td>
                                <td class="text-end">
                                    <a href="{{ route('warning.attachment.download', [$warning->id, $attachment->id]) }}" class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="tooltip" title="{{ __('Download') }}">
                                        <i class="ti ti-download"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-attachment" data-id="{{ $attachment->id }}" data-bs-toggle="tooltip" title="{{ __('Delete') }}">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
</div>

<script>
(function() {
    var warningId = {{ $warning->id }};
    var uploadUrl = '{{ route("warning.attachment.upload", $warning->id) }}';
    var attachmentsUrl = '{{ route("warning.attachments", $warning->id) }}';
    var csrfToken = '{{ csrf_token() }}';

    // Upload
    $('#btn-upload-warning-attachment').on('click', function() {
        var files = $('#warning-attachment-file')[0].files;
        if (files.length === 0) {
            show_toastr('Error', '{{ __("Please select a file to upload.") }}', 'error');
            return;
        }

        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="ti ti-loader ti-spin"></i> {{ __("Uploading...") }}');
        $('#upload-progress').show();

        var uploaded = 0;
        var total = files.length;
        var errors = [];

        function uploadNext(index) {
            if (index >= total) {
                $btn.prop('disabled', false).html('<i class="ti ti-upload"></i> {{ __("Upload") }}');
                $('#upload-progress').hide();
                $('#warning-attachment-file').val('');

                if (errors.length > 0) {
                    show_toastr('Error', errors.join('<br>'), 'error');
                }
                if (uploaded > 0) {
                    show_toastr('Success', uploaded + ' {{ __("file(s) uploaded successfully.") }}', 'success');
                    // Reload modal content to show new attachments
                    reloadAttachments();
                }
                return;
            }

            var formData = new FormData();
            formData.append('file', files[index]);
            formData.append('_token', csrfToken);

            var percent = Math.round(((index + 1) / total) * 100);
            $('#upload-progress .progress-bar').css('width', percent + '%');

            $.ajax({
                url: uploadUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.is_success) {
                        uploaded++;
                    } else {
                        errors.push(res.error || '{{ __("Upload failed.") }}');
                    }
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON ? (xhr.responseJSON.error || xhr.responseJSON.message) : '{{ __("Upload failed.") }}';
                    errors.push(files[index].name + ': ' + msg);
                },
                complete: function() {
                    uploadNext(index + 1);
                }
            });
        }

        uploadNext(0);
    });

    // Delete with SweetAlert
    $(document).on('click', '.btn-delete-attachment', function() {
        var $btn = $(this);
        var attachmentId = $btn.data('id');

        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                container: 'swal-over-modal',
                confirmButton: 'btn btn-success',
                cancelButton: 'btn btn-danger'
            },
            buttonsStyling: false
        });

        swalWithBootstrapButtons.fire({
            title: '{{ __("Are you sure?") }}',
            text: '{{ __("This action can not be undone. Do you want to continue?") }}',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '{{ __("Yes") }}',
            cancelButtonText: '{{ __("No") }}',
            reverseButtons: true
        }).then(function(result) {
            if (result.isConfirmed) {
                $btn.prop('disabled', true);

                $.ajax({
                    url: '{{ route("warning.attachment.delete", [$warning->id, ":aid"]) }}'.replace(':aid', attachmentId),
                    type: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    data: { _token: csrfToken },
                    success: function(res) {
                        if (res.is_success) {
                            show_toastr('Success', res.message, 'success');
                            // Reload to get fresh data from server
                            reloadAttachments();
                        } else {
                            show_toastr('Error', res.error || '{{ __("Delete failed.") }}', 'error');
                            $btn.prop('disabled', false);
                        }
                    },
                    error: function(xhr) {
                        var msg = xhr.responseJSON ? (xhr.responseJSON.error || xhr.responseJSON.message) : '{{ __("Delete failed.") }}';
                        show_toastr('Error', msg, 'error');
                        $btn.prop('disabled', false);
                    }
                });
            }
        });
    });

    function reloadAttachments() {
        $.ajax({
            url: attachmentsUrl,
            type: 'GET',
            cache: false,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(html) {
                $('#commonModal .body').html(html);
            }
        });
    }
})();
</script>

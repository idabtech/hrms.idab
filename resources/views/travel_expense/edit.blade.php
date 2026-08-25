{{ Form::model($travelExpense, ['route' => ['travel-expenses.update', $travelExpense->id], 'method' => 'PUT', 'class' => 'needs-validation', 'novalidate', 'enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('employee_id', __('Select Employee'), ['class' => 'col-form-label']) }}<x-required></x-required>
            {{ Form::select('employee_id', $employees, null, ['class' => 'form-control select2', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('type', __('Type'), ['class' => 'col-form-label']) }}<x-required></x-required>
            {{ Form::select('type', $types, null, ['class' => 'form-control select2', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('title', __('Title / Purpose'), ['class' => 'col-form-label']) }}<x-required></x-required>
            {{ Form::text('title', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Title or Purpose')]) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('amount', __('Amount'), ['class' => 'col-form-label']) }}<x-required></x-required>
            {{ Form::number('amount', null, ['class' => 'form-control', 'required' => 'required', 'step' => '0.01', 'min' => '0', 'placeholder' => __('Enter Amount')]) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('start_date', __('Start Date'), ['class' => 'col-form-label']) }}<x-required></x-required>
            {{ Form::date('start_date', null, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('end_date', __('End Date'), ['class' => 'col-form-label']) }}<x-required></x-required>
            {{ Form::date('end_date', null, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('documents', __('Add Bills / Documents (Multiple)'), ['class' => 'col-form-label']) }}
            <input type="file" name="documents[]" class="form-control" multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('description', __('Description'), ['class' => 'col-form-label']) }}
            {{ Form::textarea('description', null, ['class' => 'form-control', 'rows' => '3', 'placeholder' => __('Enter Description')]) }}
        </div>

        @if($travelExpense->documents->count() > 0)
            <div class="col-md-12 mt-3">
                <h6>{{ __('Existing Attached Files') }}</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle">
                        <thead>
                            <tr>
                                <th>{{ __('File Name') }}</th>
                                <th width="160px">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($travelExpense->documents as $doc)
                                @php
                                    $fileUrl = asset(Storage::url('travel_expenses/' . $doc->file_path));
                                    $ext = strtolower(pathinfo($doc->file_name, PATHINFO_EXTENSION));
                                    $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg']);
                                @endphp
                                <tr id="doc-row-{{ $doc->id }}">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($isImage)
                                                <a href="{{ $fileUrl }}" target="_blank">
                                                    <img src="{{ $fileUrl }}" alt="{{ $doc->file_name }}" class="rounded me-2 border" style="width: 35px; height: 35px; object-fit: cover;">
                                                </a>
                                            @else
                                                <i class="ti ti-file-text me-2 text-primary fs-4"></i>
                                            @endif
                                            <span>{{ $doc->file_name }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ $fileUrl }}" target="_blank" class="btn btn-sm btn-info me-1" title="{{ __('View') }}">
                                            <i class="ti ti-eye text-white"></i>
                                        </a>
                                        <a href="{{ $fileUrl }}" download="{{ $doc->file_name }}" class="btn btn-sm btn-primary me-1" title="{{ __('Download') }}">
                                            <i class="ti ti-download text-white"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger delete-doc-btn" data-id="{{ $doc->id }}" title="{{ __('Delete') }}">
                                            <i class="ti ti-trash text-white"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
</div>
{{ Form::close() }}

<script>
    $(document).ready(function() {
        if ($('.select2').length) {
            $('.select2').select2({
                dropdownParent: $('.modal-body')
            });
        }

        $('.delete-doc-btn').on('click', function() {
            var docId = $(this).data('id');
            if (confirm("{{ __('Are you sure you want to delete this file?') }}")) {
                $.ajax({
                    url: "{{ url('travel-expenses/document') }}/" + docId,
                    type: 'DELETE',
                    data: {
                        "_token": "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if(response.success) {
                            $('#doc-row-' + docId).remove();
                            show_toastr('Success', response.message, 'success');
                        } else {
                            show_toastr('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        show_toastr('Error', "{{ __('Failed to delete document.') }}", 'error');
                    }
                });
            }
        });
    });
</script>

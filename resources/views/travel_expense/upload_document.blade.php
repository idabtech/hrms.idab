{{ Form::open(['route' => ['travel-expenses.store-requested-document', $travelExpense->id], 'method' => 'POST', 'enctype' => 'multipart/form-data', 'class' => 'needs-validation', 'novalidate']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-12 mb-3">
            <div class="p-3 bg-light rounded">
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block">{{ __('Title') }}</small>
                        <strong class="text-dark">{{ $travelExpense->title }}</strong>
                    </div>
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block">{{ __('Type') }}</small>
                        <span class="badge bg-primary">{{ ucfirst($travelExpense->type) }}</span>
                    </div>
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block">{{ __('Amount') }}</small>
                        <strong class="text-success">{{ \Auth::user()->priceFormat($travelExpense->amount) }}</strong>
                    </div>
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block">{{ __('Date Range') }}</small>
                        <small class="fw-bold">{{ \Auth::user()->dateFormat($travelExpense->start_date) }} - {{ \Auth::user()->dateFormat($travelExpense->end_date) }}</small>
                    </div>
                </div>
            </div>
        </div>

        @if($travelExpense->documents && $travelExpense->documents->count() > 0)
            <div class="col-12 mb-3">
                <label class="form-label font-weight-bold">{{ __('Attached Documents / Bills') }}</label>
                <div class="row g-2">
                    @foreach($travelExpense->documents as $doc)
                        @php
                            $extension = pathinfo($doc->file_name, PATHINFO_EXTENSION);
                            $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                            $fileUrl = asset('storage/travel_expenses/' . $doc->file_path);
                        @endphp
                        <div class="col-md-4 col-sm-6 document-item-{{ $doc->id }}">
                            <div class="card mb-2 border shadow-none">
                                <div class="card-body p-2 text-center">
                                    @if($isImage)
                                        <img src="{{ $fileUrl }}" class="img-fluid rounded mb-2" style="height: 60px; object-fit: cover; width: 100%;">
                                    @else
                                        <div class="py-2"><i class="ti ti-file-text text-primary fs-2"></i></div>
                                    @endif
                                    <div class="text-truncate small mb-2" title="{{ $doc->file_name }}">{{ $doc->file_name }}</div>
                                    <div class="btn-group btn-group-sm w-100">
                                        <a href="{{ $fileUrl }}" target="_blank" class="btn btn-outline-info p-1" title="{{ __('View') }}"><i class="ti ti-eye"></i></a>
                                        <a href="{{ $fileUrl }}" download="{{ $doc->file_name }}" class="btn btn-outline-success p-1" title="{{ __('Download') }}"><i class="ti ti-download"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="col-12 form-group">
            {{ Form::label('documents', __('Upload Bills / Documents (Multiple)'), ['class' => 'form-label']) }}<x-required></x-required>
            <input type="file" name="documents[]" id="documents" class="form-control" multiple required>
            <small class="text-muted">{{ __('You can select multiple bill or document images/PDFs.') }}</small>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Upload Documents') }}" class="btn btn-primary">
</div>
{{ Form::close() }}

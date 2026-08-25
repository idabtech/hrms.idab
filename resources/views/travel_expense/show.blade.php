<div class="modal-body">
    <div class="row">
        <div class="col-md-6 mb-3">
            <strong>{{ __('Type') }}:</strong>
            <div>
                @if($travelExpense->type == 'travel')
                    <span class="badge bg-primary p-2 px-3 rounded">{{ __('Travel') }}</span>
                @else
                    <span class="badge bg-info p-2 px-3 rounded">{{ __('Voucher') }}</span>
                @endif
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <strong>{{ __('Employee') }}:</strong>
            <div>{{ !empty($travelExpense->employee) ? $travelExpense->employee->name : '-' }}</div>
        </div>

        <div class="col-md-6 mb-3">
            <strong>{{ __('Title / Purpose') }}:</strong>
            <div>{{ $travelExpense->title }}</div>
        </div>

        <div class="col-md-6 mb-3">
            <strong>{{ __('Amount') }}:</strong>
            <div>{{ \Auth::user()->priceFormat($travelExpense->amount) }}</div>
        </div>

        <div class="col-md-6 mb-3">
            <strong>{{ __('Start Date') }}:</strong>
            <div>{{ \Auth::user()->dateFormat($travelExpense->start_date) }}</div>
        </div>

        <div class="col-md-6 mb-3">
            <strong>{{ __('End Date') }}:</strong>
            <div>{{ \Auth::user()->dateFormat($travelExpense->end_date) }}</div>
        </div>

        <div class="col-md-12 mb-3">
            <strong>{{ __('Description') }}:</strong>
            <div>{{ $travelExpense->description ?? '-' }}</div>
        </div>

        <div class="col-md-12">
            <h6 class="border-bottom pb-2">{{ __('Attached Bills & Documents') }} ({{ $travelExpense->documents->count() }})</h6>
            @if($travelExpense->documents->count() > 0)
                <ul class="list-group mb-3">
                    @foreach($travelExpense->documents as $doc)
                        @php
                            $fileUrl = asset(Storage::url('travel_expenses/' . $doc->file_path));
                            $ext = strtolower(pathinfo($doc->file_name, PATHINFO_EXTENSION));
                            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg']);
                        @endphp
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <div class="d-flex align-items-center my-1">
                                @if($isImage)
                                    <a href="{{ $fileUrl }}" target="_blank">
                                        <img src="{{ $fileUrl }}" alt="{{ $doc->file_name }}" class="rounded me-2 border" style="width: 45px; height: 45px; object-fit: cover;">
                                    </a>
                                @else
                                    <i class="ti ti-file-text me-2 text-primary fs-3"></i>
                                @endif
                                <span>{{ $doc->file_name }}</span>
                            </div>
                            <div class="my-1">
                                <a href="{{ $fileUrl }}" target="_blank" class="btn btn-sm btn-info text-white me-1" title="{{ __('View') }}">
                                    <i class="ti ti-eye me-1"></i> {{ __('View') }}
                                </a>
                                <a href="{{ $fileUrl }}" download="{{ $doc->file_name }}" class="btn btn-sm btn-primary" title="{{ __('Download') }}">
                                    <i class="ti ti-download me-1"></i> {{ __('Download') }}
                                </a>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-muted">{{ __('No bills or documents attached.') }}</p>
            @endif
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Close') }}" class="btn btn-light" data-bs-dismiss="modal">
</div>

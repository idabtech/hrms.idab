{{ Form::open(['url' => 'leave/changeaction', 'method' => 'post']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-12">

            {{-- ── Summary table ────────────────────────────────────────── --}}
            <table class="table modal-table mb-4">
                <tr>
                    <th>{{ __('Employee') }}</th>
                    <td><span class="fw-bold text-dark">{{ $employee->name ?? '' }}</span></td>
                </tr>
                <tr>
                    <th>{{ __('Leave Type') }}</th>
                    <td><span class="badge bg-primary px-3 py-2 rounded">{{ $leavetype->title ?? '' }}</span></td>
                </tr>
                <tr>
                    <th>{{ __('Applied On') }}</th>
                    <td>{{ \Auth::user()->dateFormat($leave->applied_on) }}</td>
                </tr>
                <tr>
                    <th>{{ __('Start Date') }}</th>
                    <td><i class="ti ti-calendar-event me-1 text-primary"></i>{{ \Auth::user()->dateFormat($leave->start_date) }}</td>
                </tr>
                <tr>
                    <th>{{ __('End Date') }}</th>
                    <td><i class="ti ti-calendar-due me-1 text-danger"></i>{{ \Auth::user()->dateFormat($leave->end_date) }}</td>
                </tr>
                <tr>
                    <th>{{ __('Total Days') }}</th>
                    <td>
                        <span class="fw-bold fs-6 text-dark">{{ $leave->total_leave_days }}</span>
                        @if ($leave->dayDetails->isNotEmpty())
                            <small class="text-muted ms-2">
                                ({{ __('Paid') }}: <span class="text-success fw-bold">{{ $leave->paidDaysCount() }}</span>
                                &nbsp;|&nbsp;
                                {{ __('Unpaid') }}: <span class="text-danger fw-bold">{{ $leave->unpaidDaysCount() }}</span>)
                            </small>
                        @else
                            {{-- Legacy record: derive from leave type --}}
                            <small class="text-muted ms-2">
                                @if ($leavetype && $leavetype->is_paid)
                                    (<span class="text-success fw-bold">{{ __('Paid') }}</span>)
                                @else
                                    (<span class="text-danger fw-bold">{{ __('Unpaid') }}</span>)
                                @endif
                            </small>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>{{ __('Leave Reason') }}</th>
                    <td><span class="text-secondary">{{ $leave->leave_reason ?? '-' }}</span></td>
                </tr>
                <tr>
                    <th>{{ __('Current Status') }}</th>
                    <td>
                        @if ($leave->status == 'Pending')
                            <span class="badge bg-warning p-2 px-3 rounded">{{ __('Pending') }}</span>
                        @elseif ($leave->status == 'Approved')
                            <span class="badge bg-success p-2 px-3 rounded">{{ __('Approved') }}</span>
                        @else
                            <span class="badge bg-danger p-2 px-3 rounded">{{ $leave->status }}</span>
                        @endif
                    </td>
                </tr>

                {{-- ── Requested / Uploaded Documents Summary ──────────────── --}}
                @php
                    $latestDoc = $leave->documents()->latest()->first();
                @endphp
                @if($latestDoc)
                    <tr>
                        <th>{{ __('Document Status') }}</th>
                        <td>
                            @if($latestDoc->status === 'uploaded')
                                <span class="badge bg-success p-2 px-3 rounded"><i class="ti ti-file-check me-1"></i>{{ __('Documents Uploaded') }}</span>
                            @else
                                <span class="badge bg-warning p-2 px-3 rounded"><i class="ti ti-clock me-1"></i>{{ __('Requested / Pending Upload') }}</span>
                            @endif
                        </td>
                    </tr>
                    @if(!empty($latestDoc->request_reason))
                        <tr>
                            <th>{{ __('Doc Request Note') }}</th>
                            <td><span class="text-dark fw-semibold">{{ $latestDoc->request_reason }}</span></td>
                        </tr>
                    @endif
                    @if(!empty($latestDoc->file_paths))
                        <tr>
                            <th>{{ __('Uploaded Proofs') }}</th>
                            <td>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($latestDoc->file_paths as $index => $path)
                                        <a href="{{ asset($path) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded px-3 shadow-sm">
                                            <i class="ti ti-file-text me-1"></i>{{ $latestDoc->file_names[$index] ?? ('Document ' . ($index + 1)) }}
                                        </a>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    @endif
                @endif

                <input type="hidden" value="{{ $leave->id }}" name="leave_id">
            </table>

            {{-- ── Per-day breakdown (if any) ───────────────────────────── --}}
            @if ($leave->dayDetails->isNotEmpty())
                <label class="col-form-label fw-bold mb-2"><i class="ti ti-list-details me-1 text-primary"></i>{{ __('Day-wise Breakdown') }}</label>
                <div class="table-responsive mb-3">
                    <table class="table table-sm table-bordered align-items-center">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Day') }}</th>
                                <th>{{ __('Duration') }}</th>
                                <th>{{ __('Period') }}</th>
                                <th>{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($leave->dayDetails as $detail)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($detail->date)->format('Y-m-d') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($detail->date)->format('D') }}</td>
                                    <td>
                                        @if ($detail->day_duration === 'half_day')
                                            <span class="badge bg-info">{{ __('Half Day') }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ __('Full Day') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($detail->day_duration === 'half_day')
                                            <span class="text-capitalize">{{ ucfirst($detail->half_day_period ?? '-') }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($detail->day_status === 'paid')
                                            <span class="badge bg-success">{{ __('Paid') }}</span>
                                        @else
                                            <span class="badge bg-danger">{{ __('Unpaid') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @elseif ($leave->start_date && $leave->end_date)
                {{-- Legacy records: no dayDetails stored, generate fallback from leave type --}}
                @php
                    $fallbackStatus = ($leavetype && $leavetype->is_paid) ? 'paid' : 'unpaid';
                    $fallbackDuration = ($leave->leave_duration === 'half_day') ? 'half_day' : 'full_day';
                    $fallbackPeriod = $leave->half_day_period ?? null;
                    $fallbackDays = \Carbon\CarbonPeriod::create($leave->start_date, $leave->end_date);
                @endphp
                <label class="col-form-label fw-bold mb-2"><i class="ti ti-list-details me-1 text-primary"></i>{{ __('Day-wise Breakdown') }}
                    <small class="text-muted">({{ __('auto-generated from leave type') }})</small>
                </label>
                <div class="table-responsive mb-3">
                    <table class="table table-sm table-bordered align-items-center">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Day') }}</th>
                                <th>{{ __('Duration') }}</th>
                                <th>{{ __('Period') }}</th>
                                <th>{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($fallbackDays as $day)
                                <tr>
                                    <td>{{ $day->format('Y-m-d') }}</td>
                                    <td>{{ $day->format('D') }}</td>
                                    <td>
                                        @if ($fallbackDuration === 'half_day')
                                            <span class="badge bg-info">{{ __('Half Day') }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ __('Full Day') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($fallbackDuration === 'half_day' && $fallbackPeriod)
                                            <span class="text-capitalize">{{ ucfirst($fallbackPeriod) }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($fallbackStatus === 'paid')
                                            <span class="badge bg-success">{{ __('Paid') }}</span>
                                        @else
                                            <span class="badge bg-danger">{{ __('Unpaid') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

        </div>
    </div>
</div>

<div class="modal-footer d-flex align-items-center justify-content-between pt-3 border-top w-100">
    @php
        $actorEmployee = \App\Models\Employee::where('user_id', \Auth::id())->first();
        $isOwnLeave = $actorEmployee && $actorEmployee->id == $leave->employee_id;
    @endphp

    {{-- Left: Edit & Request Doc Buttons --}}
    <div class="d-flex align-items-center gap-2">
        @if (\Auth::user()->type != 'employee' || $leave->status == 'Pending')
            <a href="javascript:void(0)"
                data-url="{{ URL::to('leave/' . $leave->id . '/edit') }}"
                data-ajax-popup="true" data-size="lg"
                data-title="{{ __('Edit Leave') }}"
                class="btn btn-sm btn-info text-white px-3 shadow-sm">
                <i class="ti ti-pencil me-1"></i> {{ __('Edit Leave') }}
            </a>
        @endif

        @can('Request Leave Document')
            @if (\Auth::user()->type != 'employee' && !$isOwnLeave)
                <a href="javascript:void(0)"
                    data-url="{{ route('leave.request-document-modal', $leave->id) }}"
                    data-ajax-popup="true" data-size="lg"
                    data-title="{{ __('Leave Documents') }}"
                    class="btn btn-sm btn-warning text-white px-3 shadow-sm">
                    <i class="ti ti-file-plus me-1"></i> {{ __('Request Doc') }}
                </a>

                @if($latestDoc)
                    <a href="javascript:void(0)"
                        onclick="if(confirm('{{ __('Are you sure you want to cancel this document request?') }}')){ document.getElementById('action-cancel-doc-form-{{ $leave->id }}').submit(); }"
                        class="btn btn-sm btn-outline-danger px-3 shadow-sm"
                        title="{{ __('Cancel Document Request') }}">
                        <i class="ti ti-trash me-1"></i> {{ __('Cancel Doc Request') }}
                    </a>
                @endif
            @endif
        @endcan
    </div>

    {{-- Right: Change Status Dropdown --}}
    @if (\Auth::user()->type != 'employee' && !$isOwnLeave)
        <div class="dropdown d-inline-block">
            <button class="btn btn-sm btn-primary dropdown-toggle px-3 shadow-sm d-inline-flex align-items-center" type="button" id="changeStatusDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="ti ti-adjustments-horizontal me-1"></i> {{ __('Change Status') }}
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="changeStatusDropdown">
                <li>
                    <button type="submit" name="status" value="Approved" class="dropdown-item text-success d-flex align-items-center fw-semibold py-2">
                        <i class="ti ti-check me-2 fs-5"></i> {{ __('Approved') }}
                    </button>
                </li>
                <li>
                    <button type="submit" name="status" value="Reject" class="dropdown-item text-danger d-flex align-items-center fw-semibold py-2">
                        <i class="ti ti-x me-2 fs-5"></i> {{ __('Reject') }}
                    </button>
                </li>
                <li>
                    <button type="submit" name="status" value="Pending" class="dropdown-item text-warning d-flex align-items-center fw-semibold py-2">
                        <i class="ti ti-clock me-2 fs-5"></i> {{ __('Pending') }}
                    </button>
                </li>
            </ul>
        </div>
    @else
        <button type="button" class="btn btn-sm btn-secondary px-3" data-bs-dismiss="modal">{{ __('Close') }}</button>
    @endif
</div>
{{ Form::close() }}

@if($latestDoc)
    <form id="action-cancel-doc-form-{{ $leave->id }}" action="{{ route('leave.cancel-document-request', $leave->id) }}" method="POST" style="display: none;">
        @csrf
    </form>
@endif

{{ Form::open(['url' => 'leave/changeaction', 'method' => 'post']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-12">

            {{-- ── Summary table ────────────────────────────────────────── --}}
            <table class="table modal-table">
                <tr>
                    <th>{{ __('Employee') }}</th>
                    <td>{{ $employee->name ?? '' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Leave Type') }}</th>
                    <td>{{ $leavetype->title ?? '' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Applied On') }}</th>
                    <td>{{ \Auth::user()->dateFormat($leave->applied_on) }}</td>
                </tr>
                <tr>
                    <th>{{ __('Start Date') }}</th>
                    <td>{{ \Auth::user()->dateFormat($leave->start_date) }}</td>
                </tr>
                <tr>
                    <th>{{ __('End Date') }}</th>
                    <td>{{ \Auth::user()->dateFormat($leave->end_date) }}</td>
                </tr>
                <tr>
                    <th>{{ __('Total Days') }}</th>
                    <td>
                        {{ $leave->total_leave_days }}
                        @if ($leave->dayDetails->isNotEmpty())
                            <small class="text-muted ms-1">
                                ({{ __('Paid') }}: <span class="text-success fw-bold">{{ $leave->paidDaysCount() }}</span>
                                &nbsp;|&nbsp;
                                {{ __('Unpaid') }}: <span class="text-danger fw-bold">{{ $leave->unpaidDaysCount() }}</span>)
                            </small>
                        @else
                            {{-- Legacy record: derive from leave type --}}
                            <small class="text-muted ms-1">
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
                    <td>{{ $leave->leave_reason ?? '' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Status') }}</th>
                    <td>
                        @if ($leave->status == 'Pending')
                            <span class="badge bg-warning">{{ $leave->status }}</span>
                        @elseif ($leave->status == 'Approved')
                            <span class="badge bg-success">{{ $leave->status }}</span>
                        @else
                            <span class="badge bg-danger">{{ $leave->status }}</span>
                        @endif
                    </td>
                </tr>
                <input type="hidden" value="{{ $leave->id }}" name="leave_id">
            </table>

            {{-- ── Per-day breakdown (if any) ───────────────────────────── --}}
            @if ($leave->dayDetails->isNotEmpty())
                <label class="col-form-label fw-bold">{{ __('Day-wise Breakdown') }}</label>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
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
                <label class="col-form-label fw-bold">{{ __('Day-wise Breakdown') }}
                    <small class="text-muted">({{ __('auto-generated from leave type') }})</small>
                </label>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
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
{{ Form::close() }}

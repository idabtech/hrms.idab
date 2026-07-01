<div class="modal fade" id="bulkRemoveModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Bulk Remove Shifts') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="bulkRemoveForm">
                <div class="modal-body">

                    <div class="alert alert-warning py-2">
                        <i class="ti ti-alert-triangle me-2"></i>
                        {{ __('Removes or hides shifts for the selected employees in the given date range. Default shifts will be suppressed for those dates.') }}
                    </div>

                    {{-- Date range --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Start Date') }} <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="start_date" id="removeStartDate" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('End Date') }} <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="end_date" id="removeEndDate" required>
                        </div>
                    </div>

                    {{-- Apply on days --}}
                    <div class="mb-3">
                        <label class="form-label">{{ __('Apply on Days') }}</label>
                        <div class="mb-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="selectWeekdaysRemove()">{{ __('Weekdays') }}</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="selectWeekendsRemove()">{{ __('Weekends') }}</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="selectAllDaysRemove()">{{ __('All') }}</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearDaysRemove()">{{ __('Clear') }}</button>
                        </div>
                        <div class="d-flex flex-wrap gap-3">
                            @php
                                $rotaSettings  = \App\Models\Utility::settings();
                                $rotaDaysRm    = array_map('intval', array_filter(array_map('trim', explode(',', $rotaSettings['rota_working_days'] ?? '1,2,3,4,5')), 'strlen'));
                                $satPatternRm  = $rotaSettings['saturday_pattern'] ?? 'none';
                                $satPatternLabelsRm = [
                                    'none' => __('Always Off'),
                                    'all'  => __('Every Sat'),
                                    'odd'  => __('Odd Sats (1st,3rd,5th)'),
                                    'even' => __('Even Sats (2nd,4th)'),
                                ];
                            @endphp
                            @foreach([1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri',6=>'Sat',0=>'Sun'] as $val => $label)
                            @php
                                $isOffRm = !in_array($val, $rotaDaysRm);
                                $isSatWithPatternRm = ($val === 6 && !$isOffRm && $satPatternRm !== 'all');
                                $satIsEffectivelyOffRm = ($val === 6 && !$isOffRm && $satPatternRm === 'none');
                                if ($satIsEffectivelyOffRm) $isOffRm = true;
                            @endphp
                            <div class="form-check {{ $isOffRm ? 'opacity-50' : '' }}"
                                 title="{{ $isOffRm ? __('This day is set as non-working in company settings') : ($isSatWithPatternRm ? __('Saturday pattern: ') . $satPatternLabelsRm[$satPatternRm] . '. ' . __('The backend will automatically skip non-working Saturdays.') : '') }}">
                                <input class="form-check-input bulk-remove-weekday-check" type="checkbox"
                                       name="weekdays[]" value="{{ $val }}" id="rwd_{{ $val }}"
                                       {{ $isOffRm ? 'disabled' : 'checked' }}>
                                <label class="form-check-label {{ $isOffRm ? 'text-muted text-decoration-line-through' : '' }}"
                                       for="rwd_{{ $val }}">
                                    {{ __($label) }}
                                    @if($isOffRm)
                                        <span class="badge bg-secondary ms-1" style="font-size:0.65rem;">{{ __('Off') }}</span>
                                    @elseif($isSatWithPatternRm)
                                        <span class="badge bg-info text-dark ms-1" style="font-size:0.65rem;" title="{{ $satPatternLabelsRm[$satPatternRm] }}">
                                            {{ $satPatternRm === 'odd' ? __('Odd') : __('Even') }}
                                        </span>
                                    @endif
                                </label>
                            </div>
                            @endforeach
                        </div>
                        <div class="mt-2" style="font-size:0.78rem; color:#6c757d;">
                            <i class="ti ti-info-circle me-1"></i>
                            {{ __('Days marked as Off are non-working days per company settings and cannot be selected.') }}
                            @if(in_array(6, $rotaDaysRm) && $satPatternRm !== 'all' && $satPatternRm !== 'none')
                                {{ __('Saturday is set to') }} <strong>{{ $satPatternLabelsRm[$satPatternRm] }}</strong> — {{ __('non-working Saturdays in the range will be automatically skipped.') }}
                            @endif
                        </div>
                    </div>

                    {{-- Employee --}}
                    <div class="mb-3">
                        <label class="form-label">{{ __('Select Employee') }} <span class="text-danger">*</span></label>
                        <div class="mb-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="selectAllStaffRemove()">{{ __('All') }}</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearAllStaffRemove()">{{ __('Clear') }}</button>
                        </div>
                        <div class="border rounded p-3" style="max-height:200px;overflow-y:auto;">
                            <div id="staffRemoveCheckboxes"></div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="ti ti-trash me-1"></i>{{ __('Remove Shifts') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

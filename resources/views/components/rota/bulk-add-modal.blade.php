<div class="modal fade" id="bulkAddModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Bulk Assign Shifts') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="bulkAddForm">
                <div class="modal-body">

                    {{-- Shift template --}}
                    <div class="mb-3">
                        <label class="form-label">{{ __('Shift Template') }} <span class="text-danger">*</span></label>
                        <select class="form-select" name="template_id" id="bulkShiftSelect" required>
                            <option value="">{{ __('Select Shift Template') }}</option>
                        </select>
                        <div id="bulkShiftPreview" class="mt-1" style="display:none;">
                            <span class="badge bg-light text-dark border" id="bulkShiftPreviewText"></span>
                        </div>
                    </div>

                    {{-- Date range --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Start Date') }} <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="start_date" id="bulkStartDate" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('End Date') }} <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="end_date" id="bulkEndDate" required>
                        </div>
                    </div>

                    {{-- Apply on days --}}
                    <div class="mb-3">
                        <label class="form-label">{{ __('Apply on Days') }}</label>
                        <div class="mb-2 d-flex gap-1 flex-wrap">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="selectWeekdays()">{{ __('Weekdays') }}</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="selectWeekends()">{{ __('Weekends') }}</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="selectAllDays()">{{ __('All') }}</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearDays()">{{ __('Clear') }}</button>
                        </div>
                        <div class="d-flex flex-wrap gap-3">
                            @php
                                $rotaSettings = \App\Models\Utility::settings();
                                $rotaDays     = array_map('intval', array_filter(array_map('trim', explode(',', $rotaSettings['rota_working_days'] ?? '1,2,3,4,5')), 'strlen'));
                                $satPattern   = $rotaSettings['saturday_pattern'] ?? 'none';
                                $satPatternLabels = [
                                    'none' => __('Always Off'),
                                    'all'  => __('Every Sat'),
                                    'odd'  => __('Odd Sats (1st,3rd,5th)'),
                                    'even' => __('Even Sats (2nd,4th)'),
                                ];
                            @endphp
                            @foreach([1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri',6=>'Sat',0=>'Sun'] as $val => $label)
                            @php
                                $isOff = !in_array($val, $rotaDays);
                                // Saturday with pattern: it's in working days but pattern controls which ones work.
                                // Treat it as selectable — backend will skip non-working Saturdays per pattern.
                                // But if pattern is 'none', treat Saturday as off even if in working days.
                                $isSatWithPattern = ($val === 6 && !$isOff && $satPattern !== 'all');
                                $satIsEffectivelyOff = ($val === 6 && !$isOff && $satPattern === 'none');
                                if ($satIsEffectivelyOff) $isOff = true;
                            @endphp
                            <div class="form-check {{ $isOff ? 'opacity-50' : '' }}"
                                 title="{{ $isOff ? __('This day is set as non-working in company settings') : ($isSatWithPattern ? __('Saturday pattern: ') . $satPatternLabels[$satPattern] . '. ' . __('The backend will automatically skip non-working Saturdays.') : '') }}">
                                <input class="form-check-input bulk-weekday-check" type="checkbox"
                                       name="weekdays[]" value="{{ $val }}" id="bwd_{{ $val }}"
                                       {{ $isOff ? 'disabled' : 'checked' }}>
                                <label class="form-check-label {{ $isOff ? 'text-muted text-decoration-line-through' : '' }}"
                                       for="bwd_{{ $val }}">
                                    {{ __($label) }}
                                    @if($isOff)
                                        <span class="badge bg-secondary ms-1" style="font-size:0.65rem;">{{ __('Off') }}</span>
                                    @elseif($isSatWithPattern)
                                        <span class="badge bg-info text-dark ms-1" style="font-size:0.65rem;" title="{{ $satPatternLabels[$satPattern] }}">
                                            {{ $satPattern === 'odd' ? __('Odd') : __('Even') }}
                                        </span>
                                    @endif
                                </label>
                            </div>
                            @endforeach
                        </div>
                        <div class="mt-2" style="font-size:0.78rem; color:#6c757d;">
                            <i class="ti ti-info-circle me-1"></i>
                            {{ __('Days marked as Off are non-working days per company settings and cannot be selected.') }}
                            @if(in_array(6, $rotaDays) && $satPattern !== 'all' && $satPattern !== 'none')
                                {{ __('Saturday is set to') }} <strong>{{ $satPatternLabels[$satPattern] }}</strong> — {{ __('non-working Saturdays in the range will be automatically skipped.') }}
                            @endif
                        </div>
                    </div>

                    {{-- Employee --}}
                    <div class="mb-3">
                        <label class="form-label">{{ __('Select Employee') }} <span class="text-danger">*</span></label>
                        <div class="mb-2 d-flex gap-1">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="selectAllStaff()">{{ __('All') }}</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearAllStaff()">{{ __('Clear') }}</button>
                        </div>
                        <div class="border rounded p-3" style="max-height:200px;overflow-y:auto;">
                            <div id="staffCheckboxes"></div>
                        </div>
                    </div>

                    {{-- Overwrite toggle --}}
                    <div class="border rounded p-3 bg-light">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" name="overwrite"
                                   id="bulkOverwrite" value="1"
                                   onchange="toggleOverwriteInfo()">
                            <label class="form-check-label fw-semibold" for="bulkOverwrite">
                                {{ __('Override existing shifts in this range') }}
                            </label>
                        </div>
                        {{-- Info shown when OFF --}}
                        <div id="overwriteOffInfo" class="mt-2" style="font-size:0.82rem;color:#6c757d;">
                            <i class="ti ti-info-circle me-1"></i>
                            {{ __('Days that already have a shift assigned will be skipped.') }}
                        </div>
                        {{-- Info shown when ON --}}
                        <div id="overwriteOnInfo" class="mt-2 text-warning" style="font-size:0.82rem;display:none;">
                            <i class="ti ti-alert-triangle me-1"></i>
                            {{ __('Existing shifts in this range will be replaced with the selected template.') }}
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-check me-1"></i>{{ __('Assign Shifts') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('custom-scripts')
<script>
// Toggle overwrite info text
function toggleOverwriteInfo() {
    const on  = document.getElementById('bulkOverwrite').checked;
    document.getElementById('overwriteOffInfo').style.display = on  ? 'none'  : '';
    document.getElementById('overwriteOnInfo').style.display  = on  ? ''      : 'none';
}

// Enhance the shift template select to show a preview badge
document.addEventListener('DOMContentLoaded', function () {
    const sel     = document.getElementById('bulkShiftSelect');
    const preview = document.getElementById('bulkShiftPreview');
    const text    = document.getElementById('bulkShiftPreviewText');
    if (!sel) return;
    sel.addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        if (!opt.value) {
            preview.style.display = 'none';
            return;
        }
        text.textContent = opt.dataset.start + ' – ' + opt.dataset.end;
        preview.style.display = '';
    });
});
</script>
@endpush

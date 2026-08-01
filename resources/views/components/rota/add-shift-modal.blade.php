<div class="modal fade" id="addShiftModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addShiftModalTitle">{{ __('Add Shift') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addShiftForm">
                {{-- Hidden: DB row id (set when editing an existing row) --}}
                <input type="hidden" name="rota_id" id="rotaId">
                {{-- Hidden: tracks whether we are saving as default (date=null) --}}
                <input type="hidden" name="is_default" id="isDefaultHidden" value="0">

                <div class="modal-body">

                    {{-- Employee --}}
                    <div class="mb-3">
                        <label class="form-label">{{ __('Employee') }} <span class="text-danger">*</span></label>
                        <select class="form-select" name="employee_id" id="addShiftEmployeeSelect" required></select>
                    </div>

                    {{-- Default shift toggle --}}
                    <div class="mb-3" id="isDefaultToggleWrapper">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="isDefaultToggle">
                            <label class="form-check-label fw-semibold" for="isDefaultToggle">
                                {{ __('Set as Default Shift') }}
                                <small class="text-muted fw-normal d-block">
                                    {{ __('Default shift shows on every day unless overridden for a specific date.') }}
                                </small>
                            </label>
                        </div>
                    </div>

                    {{-- Date (hidden when "Set as Default" is on) --}}
                    <div class="mb-3" id="dateFieldWrapper">
                        <label class="form-label">{{ __('Date') }} <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="date" id="addShiftDate">
                    </div>

                    {{-- Info banner shown when editing a default shift for a specific date --}}
                    <div class="alert alert-info py-2 px-3 mb-3 d-none" id="overrideBanner" style="font-size:0.85rem;">
                        <i class="ti ti-info-circle me-1"></i>
                        {{ __('You are overriding the default shift for this specific date only.') }}
                    </div>

                    {{-- Quick-fill from template --}}
                    <div class="mb-3">
                        <label class="form-label">{{ __('Fill from Shift Template') }}</label>
                        <select class="form-select" id="addShiftTemplateSelect">
                            <option value="">{{ __('— select to auto-fill times —') }}</option>
                        </select>
                    </div>

                    {{-- Type --}}
                    <div class="mb-3">
                        <label class="form-label">{{ __('Shift Type') }} <span class="text-danger">*</span></label>
                        <select class="form-select" name="type" id="addShiftType" required>
                            <option value="regular">{{ __('Regular') }}</option>
                            <option value="overtime">{{ __('Overtime') }}</option>
                            <option value="holiday">{{ __('Holiday') }}</option>
                            <option value="night">{{ __('Night') }}</option>
                            <option value="weekend">{{ __('Weekend') }}</option>
                            <option value="emergency">{{ __('Emergency') }}</option>
                            <option value="training">{{ __('Training') }}</option>
                            <option value="leave">{{ __('Leave') }}</option>
                        </select>
                    </div>

                    {{-- Times --}}
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">{{ __('Start Time') }} <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="start_time" id="addShiftStart" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">{{ __('End Time') }}</label>
                            <input type="time" class="form-control" name="end_time" id="addShiftEnd">
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div class="mb-3">
                        <label class="form-label">{{ __('Notes') }}</label>
                        <input type="text" class="form-control" name="notes" id="addShiftNotes"
                               placeholder="{{ __('Optional notes') }}" maxlength="255">
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="addShiftSubmitBtn">{{ __('Save Shift') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('custom-scripts')
<script>
// ── Pending modal data — set before show(), applied on shown.bs.modal ─────────
var _pendingModalData = null;

// Set a date value on a Flatpickr-enhanced input correctly.
// Flatpickr hides the real input and shows an altInput — .value alone won't update the display.
function _setDateValue(inputId, dateStr) {
    var el = document.getElementById(inputId);
    if (!el) return;
    if (el._flatpickr) {
        // Use Flatpickr's API so the visible altInput updates too
        if (dateStr) {
            el._flatpickr.setDate(dateStr, false, 'Y-m-d');
        } else {
            el._flatpickr.clear();
        }
    } else {
        el.value = dateStr || '';
    }
}

// Single persistent listener — runs every time the modal finishes opening
document.getElementById('addShiftModal').addEventListener('shown.bs.modal', function () {
    if (!_pendingModalData) {
        return;
    }
    var d = _pendingModalData;
    _pendingModalData = null;

    if (d.date !== undefined)        _setDateValue('addShiftDate', d.date);
    if (d.employee_id !== undefined) document.getElementById('addShiftEmployeeSelect').value = d.employee_id;
    if (d.start_time !== undefined)  document.getElementById('addShiftStart').value           = d.start_time;
    if (d.end_time !== undefined)    document.getElementById('addShiftEnd').value             = d.end_time;
    if (d.type !== undefined)        document.getElementById('addShiftType').value            = d.type;
    if (d.notes !== undefined)       document.getElementById('addShiftNotes').value           = d.notes;
});

// ── Toggle: default shift switch ──────────────────────────────────────────────
document.getElementById('isDefaultToggle').addEventListener('change', function () {
    const isDefault = this.checked;
    document.getElementById('isDefaultHidden').value          = isDefault ? '1' : '0';
    document.getElementById('dateFieldWrapper').style.display = isDefault ? 'none' : '';
    document.getElementById('addShiftDate').required          = !isDefault;
    document.getElementById('overrideBanner').classList.add('d-none');
});

// ── Helper: open the modal ────────────────────────────────────────────────────
function _showAddShiftModal() {
    const modalEl = document.getElementById('addShiftModal');
    const inst    = bootstrap.Modal.getInstance(modalEl);
    if (inst) inst.dispose();
    new bootstrap.Modal(modalEl).show();
}

// ── Open for a specific Employee + date (cell click / Assign button) ─────────────
function openAddShiftForStaffAndDate(staffId, date) {
    const staff = allStaff.find(s => String(s.id) === String(staffId));

    document.getElementById('rotaId').value                   = '';
    document.getElementById('isDefaultHidden').value          = '0';
    document.getElementById('isDefaultToggle').checked        = false;
    document.getElementById('isDefaultToggleWrapper').style.display = 'none';
    document.getElementById('dateFieldWrapper').style.display = '';
    document.getElementById('addShiftDate').required          = true;
    _setDateValue('addShiftDate', date);
    document.getElementById('addShiftType').value             = 'regular';
    document.getElementById('addShiftStart').value            = '';
    document.getElementById('addShiftEnd').value              = '';
    document.getElementById('addShiftNotes').value            = '';
    document.getElementById('addShiftTemplateSelect').value   = '';
    document.getElementById('overrideBanner').classList.add('d-none');
    document.getElementById('addShiftModalTitle').textContent = '{{ __("Add Shift") }}';

    if (staff) {
        document.getElementById('addShiftEmployeeSelect').value = staff.employee_id;
    }

    _pendingModalData = {
        date:        date || '',
        employee_id: staff ? staff.employee_id : undefined,
        type:        'regular',
        start_time:  '',
        end_time:    '',
        notes:       '',
    };

    _showAddShiftModal();
}

// ── Open for adding an extra shift (Overtime/Emergency) for employee + date ─────
function openAddExtraShiftForStaffAndDate(staffId, date) {
    const staff = typeof allStaff !== 'undefined' ? allStaff.find(s => String(s.id) === String(staffId) || String(s.employee_id) === String(staffId)) : null;

    document.getElementById('rotaId').value                   = '';
    document.getElementById('isDefaultHidden').value          = '0';
    document.getElementById('isDefaultToggle').checked        = false;
    document.getElementById('isDefaultToggleWrapper').style.display = 'none';
    document.getElementById('dateFieldWrapper').style.display = '';
    document.getElementById('addShiftDate').required          = true;
    _setDateValue('addShiftDate', date);
    document.getElementById('addShiftType').value             = 'overtime';
    document.getElementById('addShiftStart').value            = '';
    document.getElementById('addShiftEnd').value              = '';
    document.getElementById('addShiftNotes').value            = '';
    document.getElementById('addShiftTemplateSelect').value   = '';
    document.getElementById('overrideBanner').classList.add('d-none');
    document.getElementById('addShiftModalTitle').textContent = '{{ __("Add Extra Shift") }}';

    if (staff) {
        document.getElementById('addShiftEmployeeSelect').value = staff.employee_id;
    } else if (staffId) {
        document.getElementById('addShiftEmployeeSelect').value = staffId;
    }

    _pendingModalData = {
        date:        date || '',
        employee_id: staff ? staff.employee_id : staffId,
        type:        'overtime',
        start_time:  '',
        end_time:    '',
        notes:       '',
    };

    _showAddShiftModal();
}

// ── Edit existing shift row ───────────────────────────────────────────────────
function editShift(rotaId) {
    if (!rotaId) return;
    rotaFetch(`${ROTA_URLS.rotaEntry}/${rotaId}`)
        .then(r => {
            const d         = r.data;
            const isDefault = d.is_default;

            document.getElementById('rotaId').value                   = d.id;
            document.getElementById('addShiftEmployeeSelect').value   = d.employee_id;
            document.getElementById('addShiftType').value             = d.type || 'regular';
            document.getElementById('addShiftStart').value            = d.start_time;
            document.getElementById('addShiftEnd').value              = d.end_time;
            document.getElementById('addShiftNotes').value            = d.notes || '';
            document.getElementById('addShiftTemplateSelect').value   = '';
            document.getElementById('isDefaultToggle').checked        = isDefault;
            document.getElementById('isDefaultHidden').value          = isDefault ? '1' : '0';
            document.getElementById('isDefaultToggleWrapper').style.display = isDefault ? '' : 'none';
            document.getElementById('dateFieldWrapper').style.display = isDefault ? 'none' : '';
            document.getElementById('addShiftDate').required          = !isDefault;
            _setDateValue('addShiftDate', isDefault ? '' : (d.date || ''));
            document.getElementById('overrideBanner').classList.add('d-none');
            document.getElementById('addShiftModalTitle').textContent = '{{ __("Edit Shift") }}';

            _pendingModalData = {
                date:        isDefault ? '' : (d.date || ''),
                employee_id: d.employee_id,
                type:        d.type || 'regular',
                start_time:  d.start_time,
                end_time:    d.end_time,
                notes:       d.notes || '',
            };

            _showAddShiftModal();
        })
        .catch(err => show_toastr('Error', err.message || '{{ __("Failed to load shift") }}', 'error'));
}

// ── Override default shift for a specific date ────────────────────────────────
function editDefaultShiftForDate(rotaId, date) {
    if (!rotaId) return;
    rotaFetch(`${ROTA_URLS.rotaEntry}/${rotaId}`)
        .then(r => {
            const d = r.data;

            document.getElementById('rotaId').value                   = '';
            document.getElementById('addShiftEmployeeSelect').value   = d.employee_id;
            document.getElementById('addShiftType').value             = d.type || 'regular';
            document.getElementById('addShiftStart').value            = d.start_time;
            document.getElementById('addShiftEnd').value              = d.end_time;
            document.getElementById('addShiftNotes').value            = d.notes || '';
            document.getElementById('addShiftTemplateSelect').value   = '';
            document.getElementById('isDefaultToggle').checked        = false;
            document.getElementById('isDefaultHidden').value          = '0';
            document.getElementById('dateFieldWrapper').style.display = '';
            document.getElementById('addShiftDate').required          = true;
            _setDateValue('addShiftDate', date);
            document.getElementById('overrideBanner').classList.remove('d-none');
            document.getElementById('addShiftModalTitle').textContent = '{{ __("Override Shift for Date") }}';

            _pendingModalData = {
                date:        date || '',
                employee_id: d.employee_id,
                type:        d.type || 'regular',
                start_time:  d.start_time,
                end_time:    d.end_time,
                notes:       d.notes || '',
            };

            _showAddShiftModal();
        })
        .catch(err => show_toastr('Error', err.message || '{{ __("Failed to load shift") }}', 'error'));
}
</script>
@endpush

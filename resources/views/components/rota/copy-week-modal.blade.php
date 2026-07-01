<div class="modal fade" id="copyWeekModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Copy Shifts') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="copyWeekForm">
                <div class="modal-body">

                    {{-- Source period info (read-only) --}}
                    <div class="mb-3 d-none">
                        <label class="form-label text-muted">{{ __('Copy shifts from') }}</label>
                        <div class="border rounded px-3 py-2 bg-light d-flex align-items-center gap-2">
                            <i class="ti ti-calendar-event text-primary"></i>
                            <strong id="copySourceLabel" class="text-dark">—</strong>
                        </div>
                    </div>

                    {{-- Staff info (read-only) --}}
                    <div class="mb-3 d-none">
                        <label class="form-label text-muted">{{ __('Staff member') }}</label>
                        <div class="border rounded px-3 py-2 bg-light d-flex align-items-center gap-2">
                            <i class="ti ti-user text-primary"></i>
                            <strong id="copyStaffLabel" class="text-dark">—</strong>
                        </div>
                    </div>

                    {{-- Target date range --}}
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">{{ __('Start Date') }} <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="target_start" id="targetStartDate" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">{{ __('End Date') }} <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="target_end" id="targetEndDate" required>
                        </div>
                    </div>

                    <div class="alert alert-info py-2 mb-0" style="font-size:0.82rem;">
                        <i class="ti ti-info-circle me-1"></i>
                        {{ __('Shifts from the source period will be copied day-by-day to the target period. Existing shifts in the target will be skipped.') }}
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-copy me-1"></i>{{ __('Copy Shifts') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

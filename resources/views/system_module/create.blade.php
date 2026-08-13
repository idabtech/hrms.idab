{{ Form::open(['url' => 'system-modules', 'method' => 'post', 'id' => 'create-system-module-form']) }}
<div class="modal-body p-4">
    <div class="row g-3">
        <!-- Module Name -->
        <div class="col-12">
            {{ Form::label('name', __('Module Name'), ['class' => 'form-label fw-bold text-dark']) }} <span class="text-danger">*</span>
            {{ Form::text('name', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Module Name e.g. Business Management')]) }}
        </div>

        <!-- Icon Class -->
        <div class="col-12">
            {{ Form::label('icon', __('Icon Class'), ['class' => 'form-label fw-bold text-dark']) }}
            <div class="input-group">
                <span class="input-group-text bg-white"><i id="icon-preview" class="ti ti-box text-primary fs-5"></i></span>
                {{ Form::text('icon', null, ['class' => 'form-control', 'id' => 'icon_input', 'placeholder' => __('e.g. ti ti-building')]) }}
            </div>
            <small class="text-muted">{{ __('Use Tabler Icons class e.g. ti ti-building, ti ti-user, ti ti-shield') }}</small>
        </div>

        <!-- Description -->
        <div class="col-12">
            {{ Form::label('description', __('Description'), ['class' => 'form-label fw-bold text-dark']) }}
            {{ Form::textarea('description', null, ['class' => 'form-control', 'rows' => '2', 'placeholder' => __('Short description of this module')]) }}
        </div>

        <!-- Module Type -->
        <div class="col-12">
            {{ Form::label('module_type', __('Module Type'), ['class' => 'form-label fw-bold text-dark']) }} <span class="text-danger">*</span>
            {{ Form::select('module_type', ['company' => __('Company'), 'super_admin' => __('Super Admin'), 'both' => __('Both')], 'company', ['class' => 'form-select', 'required' => 'required']) }}
        </div>

        <!-- Available For selector cards -->
        <div class="col-12">
            {{ Form::label('available_for', __('Available For'), ['class' => 'form-label fw-bold text-dark']) }} <span class="text-danger">*</span>
            <p class="small text-muted mb-2">{{ __('Restricts this module to a specific account type. Use "Both" if it applies to all users.') }}</p>
            <input type="hidden" name="available_for" id="available_for_input" value="both">
            <div class="row g-2">
                <div class="col-4">
                    <div class="card available-for-card active p-3 text-center border rounded-3 cursor-pointer" data-value="both">
                        <i class="ti ti-users fs-3 text-secondary mb-1"></i>
                        <span class="fw-semibold small text-dark d-block">{{ __('Both') }}</span>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card available-for-card p-3 text-center border rounded-3 cursor-pointer" data-value="individual">
                        <i class="ti ti-user fs-3 text-info mb-1"></i>
                        <span class="fw-semibold small text-dark d-block">{{ __('Individual') }}</span>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card available-for-card p-3 text-center border rounded-3 cursor-pointer" data-value="business">
                        <i class="ti ti-building fs-3 text-warning mb-1"></i>
                        <span class="fw-semibold small text-dark d-block">{{ __('Business') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Parent Module -->
        <div class="col-12">
            {{ Form::label('parent_id', __('Parent Module (optional — set if this is a sub-module)'), ['class' => 'form-label fw-bold text-dark']) }}
            {{ Form::select('parent_id', ['' => __('— None (top-level module) —')] + $parentModules, null, ['class' => 'form-select']) }}
        </div>

        <!-- Sort Order & Status -->
        <div class="col-md-6">
            {{ Form::label('sort_order', __('Sort Order'), ['class' => 'form-label fw-bold text-dark']) }}
            {{ Form::number('sort_order', 0, ['class' => 'form-control', 'min' => 0]) }}
        </div>
        <div class="col-md-6 d-flex flex-column justify-content-center">
            {{ Form::label('status', __('Status'), ['class' => 'form-label fw-bold text-dark mb-1']) }}
            <div class="form-check form-switch mt-1">
                <input class="form-check-input" type="checkbox" role="switch" name="status" id="module_status" value="1" checked>
                <label class="form-check-label fw-semibold text-dark" for="module_status">{{ __('Active') }}</label>
            </div>
        </div>

        <!-- Plan Builder Behaviour Section -->
        <div class="col-12 mt-4">
            <h6 class="fw-bold text-dark mb-1">{{ __('Plan Builder Behaviour') }}</h6>
            <p class="small text-muted mb-3">{{ __('Controls how this module appears in the plan create/edit screen.') }}</p>

            <div class="row g-3">
                <!-- Always Included Card -->
                <div class="col-md-6">
                    <div class="card bg-light border p-3 rounded-3 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-dark text-white fw-bold"><i class="ti ti-lock me-1"></i>{{ __('Always Included') }}</span>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input behavior-switch" type="checkbox" name="is_always_included" id="is_always_included" value="1">
                            </div>
                        </div>
                        <small class="text-muted d-block" style="font-size: 0.78rem;">
                            {{ __('Injected into every plan automatically. Hidden from the plan builder — admin never sees this as a toggle.') }}
                        </small>
                        <small class="text-secondary fst-italic mt-1 d-block" style="font-size: 0.72rem;">
                            {{ __('Example: Dashboard, Settings, Category') }}
                        </small>
                    </div>
                </div>

                <!-- Auto Included Card -->
                <div class="col-md-6">
                    <div class="card bg-light border p-3 rounded-3 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-primary text-white fw-bold"><i class="ti ti-link me-1"></i>{{ __('Auto Included') }}</span>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input behavior-switch" type="checkbox" name="is_auto_included" id="is_auto_included" value="1">
                            </div>
                        </div>
                        <small class="text-muted d-block" style="font-size: 0.78rem;">
                            {{ __('Shown as a locked badge in the plan builder. Injected automatically when its trigger parent module is enabled.') }}
                        </small>
                        <small class="text-secondary fst-italic mt-1 d-block" style="font-size: 0.72rem;">
                            {{ __('Example: Roles (auto with Staff), Post Job (auto with Jobs)') }}
                        </small>
                    </div>
                </div>

                <!-- Chargeable Summary Box -->
                <div class="col-12">
                    <div class="card bg-light-success border border-success-subtle p-3 rounded-3" id="chargeable_notice">
                        <div class="d-flex align-items-center gap-2">
                            <i class="ti ti-currency-dollar text-success fs-4"></i>
                            <div>
                                <span class="fw-bold text-success" style="font-size: 0.85rem;">{{ __('Chargeable') }}</span>
                                <small class="text-muted d-block" style="font-size: 0.78rem;">
                                    {{ __('Both flags are off. This module will appear as a toggle card in the plan builder that the admin can enable per plan.') }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer border-top bg-light">
    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary rounded-2">
</div>
{{ Form::close() }}

<style>
    .available-for-card {
        transition: all 0.2s ease-in-out;
    }
    .available-for-card:hover {
        border-color: #5c59e8 !important;
        background-color: #f8fafc;
    }
    .available-for-card.active {
        border-color: #5c59e8 !important;
        background-color: #f1f3f9 !important;
        box-shadow: 0 0 0 2px rgba(92, 89, 232, 0.2);
    }
</style>

<script>
    $(document).on('click', '.available-for-card', function() {
        $('.available-for-card').removeClass('active');
        $(this).addClass('active');
        $('#available_for_input').val($(this).data('value'));
    });

    $(document).on('input', '#icon_input', function() {
        var val = $(this).val();
        if (val) {
            $('#icon-preview').attr('class', val + ' fs-5 text-primary');
        } else {
            $('#icon-preview').attr('class', 'ti ti-box fs-5 text-primary');
        }
    });

    $(document).on('change', '.behavior-switch', function() {
        var isAlways = $('#is_always_included').is(':checked');
        var isAuto = $('#is_auto_included').is(':checked');

        if (isAlways || isAuto) {
            $('#chargeable_notice').addClass('opacity-50');
        } else {
            $('#chargeable_notice').removeClass('opacity-50');
        }
    });
</script>

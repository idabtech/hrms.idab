@extends('layouts.admin')

@section('page-title')
    {{ __('Manage System Modules') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('System Modules') }}</li>
@endsection

@section('action-button')
    <a href="javascript:void(0)" data-url="{{ route('system-modules.create') }}" data-ajax-popup="true"
       data-title="{{ __('Create New Module') }}" data-size="lg" data-bs-toggle="tooltip" title="{{ __('Create') }}"
       class="btn btn-sm btn-primary" data-bs-original-title="{{ __('Create') }}">
        <i class="ti ti-plus"></i>
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table" id="pc-dt-simple">
                            <thead>
                                <tr>
                                    <th>{{ __('MODULE NAME') }}</th>
                                    <th>{{ __('SLUG') }}</th>
                                    <th>{{ __('PARENT MODULE') }}</th>
                                    <th>{{ __('PERMISSIONS') }}</th>
                                    <th>{{ __('TYPE') }}</th>
                                    <th>{{ __('AVAILABLE FOR') }}</th>
                                    <th>{{ __('PLAN INCLUSION') }}</th>
                                    <th>{{ __('STATUS') }}</th>
                                    <th>{{ __('ORDER') }}</th>
                                    <th width="200px">{{ __('ACTION') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($modules as $mod)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                @if(!empty($mod->icon))
                                                    <i class="{{ $mod->icon }} fs-5 text-primary"></i>
                                                @else
                                                    <i class="ti ti-box fs-5 text-secondary"></i>
                                                @endif
                                                <span>{{ $mod->name }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border px-2 py-1" style="font-size: 0.8rem; font-family: monospace;">{{ $mod->slug }}</span>
                                        </td>
                                        <td>
                                            @if($mod->parent)
                                                <span class="badge bg-light text-dark border px-2.5 py-1.5 rounded-pill fw-normal">
                                                    {{ $mod->parent->name }}
                                                </span>
                                            @else
                                                <span class="text-muted">&mdash;</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-info text-white rounded-pill px-2.5 py-1">
                                                {{ $mod->permissions_count }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($mod->module_type == 'company')
                                                <span class="badge bg-warning text-dark px-2.5 py-1.5 rounded-2 fw-bold" style="background-color: #ff9f43 !important;">{{ __('Company') }}</span>
                                            @elseif($mod->module_type == 'super_admin')
                                                <span class="badge bg-dark text-white px-2.5 py-1.5 rounded-2 fw-bold" style="background-color: #0c2556 !important;">{{ __('Super Admin') }}</span>
                                            @else
                                                <span class="badge bg-primary text-white px-2.5 py-1.5 rounded-2 fw-bold">{{ __('Both') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($mod->available_for == 'both')
                                                <span class="badge bg-secondary text-white px-2.5 py-1.5 rounded-2 fw-semibold"><i class="ti ti-users me-1"></i>{{ __('Both') }}</span>
                                            @elseif($mod->available_for == 'business')
                                                <span class="badge bg-warning text-dark px-2.5 py-1.5 rounded-2 fw-semibold" style="background-color: #ff9f43 !important;"><i class="ti ti-building me-1"></i>{{ __('Business') }}</span>
                                            @else
                                                <span class="badge bg-info text-white px-2.5 py-1.5 rounded-2 fw-semibold"><i class="ti ti-user me-1"></i>{{ __('Individual') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($mod->is_always_included)
                                                <span class="badge bg-secondary text-white px-2.5 py-1.5 rounded-2 fw-semibold"><i class="ti ti-check me-1"></i>{{ __('Always Included') }}</span>
                                            @elseif($mod->is_auto_included)
                                                <span class="badge bg-primary text-white px-2.5 py-1.5 rounded-2 fw-semibold"><i class="ti ti-link me-1"></i>{{ __('Auto') }}</span>
                                            @else
                                                <span class="badge bg-success text-white px-2.5 py-1.5 rounded-2 fw-semibold"><i class="ti ti-currency-dollar me-1"></i>{{ __('Chargeable') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="form-check form-switch d-inline-block mb-0">
                                                <input class="form-check-input status-toggle-checkbox" type="checkbox" role="switch"
                                                       data-url="{{ route('system-modules.status-toggle', $mod->id) }}"
                                                       {{ $mod->status ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                        <td class="fw-bold text-muted">{{ $mod->sort_order }}</td>
                                        <td>
                                            <div class="action-btn bg-info ms-2">
                                                <a href="javascript:void(0)" data-url="{{ route('system-modules.edit', $mod->id) }}"
                                                   data-ajax-popup="true" data-title="{{ __('Update Module') }}" data-size="lg"
                                                   class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{ __('Edit') }}"
                                                   data-bs-original-title="{{ __('Edit') }}">
                                                    <i class="ti ti-pencil text-white"></i>
                                                </a>
                                            </div>
                                            <div class="action-btn bg-danger ms-2">
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['system-modules.destroy', $mod->id], 'id' => 'delete-form-' . $mod->id]) !!}
                                                    <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{ __('Delete') }}"
                                                       data-bs-original-title="{{ __('Delete') }}">
                                                        <i class="ti ti-trash text-white"></i>
                                                    </a>
                                                {!! Form::close() !!}
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script-page')
    <script>
        $(document).on('change', '.status-toggle-checkbox', function() {
            var url = $(this).data('url');
            var $this = $(this);
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        toastrs('Success', response.message, 'success');
                    } else {
                        toastrs('Error', response.message, 'error');
                        $this.prop('checked', !$this.is(':checked'));
                    }
                },
                error: function() {
                    toastrs('Error', '{{ __("Failed to update status.") }}', 'error');
                    $this.prop('checked', !$this.is(':checked'));
                }
            });
        });
    </script>
@endpush

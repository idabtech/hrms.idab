@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Payslip') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('payslip') }}</li>
@endsection

@section('content')
    <div class="col-12">
        <div class="card mt-3">
            <div class="card-header d-flex align-items-xxl-center justify-content-between gap-3 flex-column flex-xxl-row">
                <h5>{{ __('Find Employee Payslip') }}</h5>
                <div class="row flex-1 justify-content-end align-items-center row-gap-1">
                    <div class="col-xxl-2 col-lg-3 col-sm-6 col-12">
                        <div class="btn-box">
                            <select class="form-control month_date" name="year" tabindex="-1" aria-hidden="true">
                                <option value="--">--</option>
                                @foreach ($month as $k => $mon)
                                    @php $selected = date('m') == $k ? 'selected' : ''; @endphp
                                    <option value="{{ $k }}" {{ $selected }}>{{ $mon }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-xxl-2 col-lg-3 col-sm-6 col-12">
                        <div class="btn-box">
                            {{ Form::select('year', $year, date('Y'), ['class' => 'form-control year_date']) }}
                        </div>
                    </div>
                    <div class="col-auto d-flex gap-3">
                        @if (Auth::user()->type == 'company' || Auth::user()->type == 'hr' || Auth::user()->type == 'HR')
                            {{ Form::open(['route' => ['payslip.export'], 'method' => 'POST', 'id' => 'payslip_form']) }}
                            <input type="hidden" name="filter_month" class="filter_month">
                            <input type="hidden" name="filter_year" class="filter_year">
                            <input type="submit" value="{{ __('Export') }}" class="btn btn-primary me-1">
                            {{ Form::close() }}

                            <a href="javascript:void(0)" onclick="generatePayslip(); return false;"
                                data-title="{{ __('Generate Payslip') }}" data-bs-toggle="tooltip"
                                title="{{ __('Generate Payslip') }}" class="btn btn-sm btn-primary me-1">
                                <i class="ti ti-report-money"></i>
                            </a>
                            <button type="button" class="btn btn-danger btn-sm" id="bulk_delete_payslip" data-bs-toggle="tooltip" title="{{ __('Bulk Delete Payslips') }}">
                                <i class="ti ti-trash me-1"></i> {{ __('Bulk Delete') }}
                            </button>
                        @endif
                        @can('Create Pay Slip')
                            <input type="button" value="{{ __('Bulk Payment') }}" class="btn btn-primary" id="bulk_payment">
                        @endcan
                    </div>
                </div>
            </div>
            <div class="card-body table-border-style">
                <div class="table-responsive">
                    <table class="table" id="pc-dt-render-column-cells">
                        <thead>
                            <tr>
                                @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'hr' || \Auth::user()->type == 'HR')
                                    <th style="width: 40px;"><input type="checkbox" class="form-check-input" id="select_all_payslips"></th>
                                @endif
                                <th>{{ __('Employee Id') }}</th>
                                @if (\Auth::user()->type != 'employee')
                                    <th>{{ __('Name') }}</th>
                                @endif
                                <th>{{ __('Payroll Type') }}</th>
                                <th>{{ __('Salary') }}</th>
                                <th>{{ __('Net Salary') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>

                {{-- Summary Footer Card --}}
                <div class="card-footer bg-light border-0 py-3 mt-3 rounded" id="payslip_summary_bar" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <h6 class="mb-0 text-dark fw-bold"><i class="ti ti-calculator me-1 text-primary"></i> {{ __('Payroll Summary') }}</h6>
                        <div class="d-flex align-items-center gap-4 flex-wrap">
                            <div class="d-flex align-items-center">
                                <span class="text-muted fw-bold me-2">{{ __('Total Salary:') }}</span>
                                <span class="badge bg-primary fs-6 p-2 px-3 shadow-sm" id="footer_total_salary">-</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="text-muted fw-bold me-2">{{ __('Total Net Salary:') }}</span>
                                <span class="badge bg-success fs-6 p-2 px-3 shadow-sm" id="footer_total_net_salary">-</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Detail modal uses existing commonModal via data-ajax-popup --}}
@endsection

@push('script-page')
    <script>
        $(document).ready(function() {
            let savedMonth = localStorage.getItem('selectedMonth');
            let savedYear = localStorage.getItem('selectedYear');
            if (savedMonth) $('.month_date').val(savedMonth);
            if (savedYear) $('.year_date').val(savedYear);
            localStorage.removeItem('selectedMonth');
            localStorage.removeItem('selectedYear');
            callback();

            function callback() {
                var month = $(".month_date").val();
                var year = $(".year_date").val();
                $('.filter_month').val(month);
                $('.filter_year').val(year);

                if (month == '') {
                    month = '{{ date('m', strtotime('last month')) }}';
                    year = '{{ date('Y') }}';
                    $('.filter_month').val(month);
                    $('.filter_year').val(year);
                }

                var datePicker = year + '-' + month;

                $.ajax({
                    url: '{{ route('payslip.search_json') }}',
                    type: 'POST',
                    data: { "datePicker": datePicker, "_token": "{{ csrf_token() }}" },
                    success: function(data) {
                        var tr = '';
                        var payslips = Array.isArray(data) ? data : (data.data || []);

                        if (payslips.length > 0) {
                            $.each(payslips, function(idx, v) {
                                var id = v[0];
                                var payslip_id = v[16];
                                var url_employee = v['url'];

                                // Status badge
                                var statusClass = (v[15] == 'Paid' || v[15] == 'paid') ? 'bg-success' : 'bg-danger';
                                var status = '<div class="badge ' + statusClass + ' p-2 px-3">' + v[15] + '</div>';

                                // Action buttons
                                var actions = '';
                                // Detail button (ajax popup)
                                actions += '<a href="javascript:void(0)" data-url="{{ url('payslip/detail/') }}/' + id + '/' + datePicker + '" data-ajax-popup="true" data-size="md" data-title="{{ __('Payslip Details') }} - ' + v[2] + '" class="btn btn-sm btn-outline-info me-1" data-bs-toggle="tooltip" title="{{ __('Details') }}"><i class="ti ti-eye"></i></a>';

                                if (payslip_id != 0) {
                                    actions += '<a href="javascript:void(0)" data-url="{{ url('payslip/pdf/') }}/' + id + '/' + datePicker + '" class="btn-sm btn btn-warning me-1" data-size="lg" data-bs-toggle="tooltip" title="{{ __('Payslip') }}" data-ajax-popup="true" data-title="{{ __('Employee Payslip') }}"><i class="ti ti-report-money"></i></a>';
                                }
                                if (v[15] == "UnPaid" && payslip_id != 0) {
                                    actions += '<a href="{{ url('payslip/paysalary/') }}/' + id + '/' + datePicker + '" class="btn-sm btn btn-primary me-1" data-bs-toggle="tooltip" title="{{ __('Click To Paid') }}"><i class="ti ti-currency-dollar"></i></a>';
                                }
                                @if ((\Auth::user()->type == 'company' || \Auth::user()->type == 'hr' || \Auth::user()->type == 'HR') && (\App\Models\Utility::isUkRequest() || request()->boolean('uk_preview')) && \App\Services\HmrcService::isEnabled())
                                if (v[15] == "Paid" && payslip_id != 0) {
                                    actions += '<button type="button" class="btn-sm btn btn-outline-success me-1 btn-hmrc-fps" data-employee-id="' + id + '" data-month="' + datePicker + '" data-bs-toggle="tooltip" title="{{ __('Submit to HMRC') }}"><i class="ti ti-send"></i></button>';
                                }
                                @endif
                                if (payslip_id != 0 && v[15] == "UnPaid") {
                                    actions += '<a href="javascript:void(0)" data-url="{{ url('payslip/editemployee/') }}/' + payslip_id + '" data-ajax-popup="true" data-size="lg" class="btn-sm btn btn-info me-1" data-bs-toggle="tooltip" title="{{ __('Edit') }}" data-title="{{ __('Edit Employee salary') }}"><i class="ti ti-pencil"></i></a>';
                                }
                                @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'hr' || \Auth::user()->type == 'HR')
                                var delUrl = '{{ route('payslip.delete', ':id') }}'.replace(':id', payslip_id);
                                actions += '<a href="javascript:void(0)" data-url="' + delUrl + '" class="payslip_delete btn btn-danger btn-sm" data-bs-toggle="tooltip" title="{{ __('Delete') }}"><i class="ti ti-trash"></i></a>';
                                @endif

                                @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'hr' || \Auth::user()->type == 'HR')
                                var chkTd = '<td><input type="checkbox" class="form-check-input payslip-checkbox" value="' + payslip_id + '"></td>';
                                tr += '<tr>' +
                                    chkTd +
                                    '<td><a class="btn btn-outline-primary btn-sm" href="' + url_employee + '">' + v[1] + '</a></td>' +
                                    '<td>' + v[2] + '</td>' +
                                    '<td>' + v[3] + '</td>' +
                                    '<td>' + (v[4] ? v[4] : '-') + '</td>' +
                                    '<td><strong>' + (v[6] ? v[6] : '-') + '</strong></td>' +
                                    '<td>' + status + '</td>' +
                                    '<td>' + actions + '</td>' +
                                    '</tr>';
                                @else
                                tr += '<tr>' +
                                    '<td><a class="btn btn-outline-primary btn-sm" href="' + url_employee + '">' + v[1] + '</a></td>' +
                                    '<td>' + v[2] + '</td>' +
                                    '<td>' + (v[4] ? v[4] : '-') + '</td>' +
                                    '<td><strong>' + (v[6] ? v[6] : '-') + '</strong></td>' +
                                    '<td>' + status + '</td>' +
                                    '<td>' + actions + '</td>' +
                                    '</tr>';
                                @endif
                            });
                        } else {
                            var colspan = $('#pc-dt-render-column-cells thead tr th').length;
                            tr = '<tr><td class="dataTables-empty" colspan="' + colspan + '">{{ __('No entries found') }}</td></tr>';
                        }

                        $('#pc-dt-render-column-cells tbody').html(tr);

                        var table = document.querySelector("#pc-dt-render-column-cells");
                        var datatable = new simpleDatatables.DataTable(table, {
                            perPage: 10,
                            perPageSelect: [10, 25, 50, 100],
                            paging: true,
                            firstLast: true,
                            truncatePager: false
                        });

                        // Ensure Next/Prev pagination navigation is visible
                        var pagerContainer = $(table).closest('.card-body').find('.dataTable-bottom');
                        if (pagerContainer.length > 0 && pagerContainer.find('.dataTable-pagination-list').length === 0) {
                            var navHtml = '<nav class="dataTable-pagination"><ul class="dataTable-pagination-list">' +
                                '<li class="pager disabled"><a href="javascript:void(0)" data-page="1">‹</a></li>' +
                                '<li class="active"><a href="javascript:void(0)" data-page="1">1</a></li>' +
                                '<li class="pager disabled"><a href="javascript:void(0)" data-page="1">›</a></li>' +
                                '</ul></nav>';
                            pagerContainer.append(navHtml);
                        }

                        if (!Array.isArray(data) && payslips.length > 0) {
                            $('#footer_total_salary').html(data.total_salary || '-');
                            $('#footer_total_net_salary').html(data.total_net_salary || '-');
                            $('#payslip_summary_bar').show();

                            var tfootHtml = '<tfoot><tr class="fw-bold bg-light" style="border-top: 2px solid #dee2e6;">' +
                                '<td colspan="{{ \Auth::user()->type != 'employee' ? 4 : 2 }}" class="text-end fw-bold fs-6">{{ __('Total:') }}</td>' +
                                '<td class="fw-bold text-primary fs-6">' + (data.total_salary || '-') + '</td>' +
                                '<td class="fw-bold text-success fs-6">' + (data.total_net_salary || '-') + '</td>' +
                                '<td colspan="2"></td>' +
                                '</tr></tfoot>';

                            $('#pc-dt-render-column-cells tfoot').remove();
                            $('.dataTable-container table tfoot').remove();
                            $('.dataTable-container table, #pc-dt-render-column-cells').append(tfootHtml);
                        } else {
                            $('#payslip_summary_bar').hide();
                            $('#pc-dt-render-column-cells tfoot').remove();
                            $('.dataTable-container table tfoot').remove();
                        }
                    },
                    error: function(data) {
                        data = data.responseJSON;
                        show_toastr('error', data.error);
                    }
                });
            }

            $(document).on("change", ".month_date,.year_date", function() { callback(); });

            $(document).on("change", "#select_all_payslips", function() {
                $(".payslip-checkbox").prop("checked", $(this).is(":checked"));
            });

            // Bulk Delete payslips
            $(document).on("click", "#bulk_delete_payslip", function() {
                var selectedIds = $(".payslip-checkbox:checked").map(function() {
                    return $(this).val();
                }).get().filter(function(id) {
                    return id && id != '0';
                });

                var month = $(".month_date").val();
                var year = $(".year_date").val();
                var datePicker = year + '-' + month;

                var confirmMsg = '';
                if (selectedIds.length > 0) {
                    confirmMsg = 'Are you sure you want to delete the ' + selectedIds.length + ' selected payslip(s)?';
                } else {
                    confirmMsg = 'No specific payslips checked. Are you sure you want to delete ALL generated payslips for ' + datePicker + '?';
                }

                if (confirm(confirmMsg)) {
                    $.ajax({
                        url: '{{ route('payslip.bulk_delete') }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            ids: selectedIds,
                            month: datePicker
                        },
                        success: function(response) {
                            if (response.success) {
                                show_toastr('success', response.message, 'success');
                                setTimeout(function() { location.reload(); }, 800);
                            } else {
                                show_toastr('error', response.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to delete payslips.';
                            show_toastr('error', msg, 'error');
                        }
                    });
                }
            });

            // Bulk payment
            $(document).on("click", "#bulk_payment", function() {
                var month = $(".month_date").val();
                var year = $(".year_date").val();
                var datePicker = year + '-' + month;
                var title = 'Bulk Payment';
                var size = 'md';
                var url = 'payslip/bulk_pay_create/' + datePicker;

                $("#commonModal .modal-title").html(title);
                $("#commonModal .modal-dialog").addClass('modal-' + size);
                $.ajax({
                    url: url,
                    success: function(data) {
                        if (data.length) {
                            $('#commonModal .body').html(data);
                            $("#commonModal").modal('show');
                        } else {
                            show_toastr('error', 'Permission denied.');
                            $("#commonModal").modal('hide');
                        }
                    },
                    error: function(data) {
                        data = data.responseJSON;
                        show_toastr('error', data.error);
                    }
                });
            });

            // Delete payslip
            $(document).on("click", ".payslip_delete", function() {
                var confirmation = confirm("Are you sure you want to delete this payslip?");
                var url = $(this).data('url');
                if (confirmation) {
                    $.ajax({
                        type: "GET",
                        url: url,
                        dataType: "JSON",
                        success: function(data) {
                            show_toastr('error', 'Payslip Deleted Successfully', 'success');
                            setTimeout(function() { location.reload(); }, 800);
                        },
                    });
                }
            });
        });

        function generatePayslip() {
            var month = $(".month_date").val();
            var year = $(".year_date").val();

            $.ajax({
                url: '{{ route('payslip.store') }}',
                type: 'POST',
                data: { "month": month, "year": year, "_token": "{{ csrf_token() }}" },
                success: function(response) {
                    if (response.success) {
                        localStorage.setItem('selectedMonth', month);
                        localStorage.setItem('selectedYear', year);
                        show_toastr('success', response.message, 'success');
                        setTimeout(function() { location.reload(); }, 800);
                    } else {
                        show_toastr('error', response.message, 'error');
                    }
                },
                error: function(data) {
                    data = data.responseJSON;
                    show_toastr('error', data.error, 'error');
                }
            });
        }
    </script>
    @if(\App\Services\HmrcService::isEnabled())
        <script>
        // HMRC Submit FPS
        $(document).on('click', '.btn-hmrc-fps', function() {
            var $btn = $(this);
            var employeeId = $btn.data('employee-id');
            var month = $btn.data('month');
            if (!confirm('{{ __("Submit this payslip to HMRC as a Full Payment Submission (FPS)?") }}')) return;
            $btn.prop('disabled', true).html('<i class="ti ti-loader ti-spin"></i>');
            $.ajax({
                url: '{{ url("hmrc/submit-fps") }}/' + employeeId + '/' + month,
                type: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    if (res.success) {
                        show_toastr('Success', res.message, 'success');
                        $btn.removeClass('btn-outline-success').addClass('btn-success').html('<i class="ti ti-check"></i>').attr('title', '{{ __("Submitted") }}');
                    } else {
                        show_toastr('Error', res.message, 'error');
                        $btn.prop('disabled', false).html('<i class="ti ti-send"></i>');
                    }
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON ? xhr.responseJSON.message : '{{ __("HMRC submission failed.") }}';
                    show_toastr('Error', msg, 'error');
                    $btn.prop('disabled', false).html('<i class="ti ti-send"></i>');
                }
            });
        });
        </script>
    @endif
@endpush

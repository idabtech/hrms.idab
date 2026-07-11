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
                        @if (Auth::user()->type == 'company' || Auth::user()->type == 'hr')
                            {{ Form::open(['route' => ['payslip.export'], 'method' => 'POST', 'id' => 'payslip_form']) }}
                            <input type="hidden" name="filter_month" class="filter_month">
                            <input type="hidden" name="filter_year" class="filter_year">
                            <input type="submit" value="{{ __('Export') }}" class="btn btn-primary me-1">
                            {{ Form::close() }}

                            <a href="javascript:void(0)" onclick="generatePayslip(); return false;"
                                data-title="{{ __('Generate Payslip') }}" data-bs-toggle="tooltip"
                                title="{{ __('Generate Payslip') }}" class="btn btn-sm btn-primary">
                                <i class="ti ti-report-money"></i>
                            </a>
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
                                <th>{{ __('Employee Id') }}</th>
                                @if (\Auth::user()->type != 'employee')
                                    <th>{{ __('Name') }}</th>
                                @endif
                                <th>{{ __('Payroll Type') }}</th>
                                <th>{{ __('Net Salary') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
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
                        if (data.length > 0) {
                            $.each(data, function(idx, v) {
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
                                @if ((\Auth::user()->type == 'company' || \Auth::user()->type == 'hr') && (\App\Models\Utility::isUkRequest() || request()->boolean('uk_preview')))
                                if (v[15] == "Paid" && payslip_id != 0) {
                                    actions += '<button type="button" class="btn-sm btn btn-outline-success me-1 btn-hmrc-fps" data-employee-id="' + id + '" data-month="' + datePicker + '" data-bs-toggle="tooltip" title="{{ __('Submit to HMRC') }}"><i class="ti ti-send"></i></button>';
                                }
                                @endif
                                if (payslip_id != 0 && v[15] == "UnPaid") {
                                    actions += '<a href="javascript:void(0)" data-url="{{ url('payslip/editemployee/') }}/' + payslip_id + '" data-ajax-popup="true" data-size="lg" class="btn-sm btn btn-info me-1" data-bs-toggle="tooltip" title="{{ __('Edit') }}" data-title="{{ __('Edit Employee salary') }}"><i class="ti ti-pencil"></i></a>';
                                }
                                @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'hr')
                                var delUrl = '{{ route('payslip.delete', ':id') }}'.replace(':id', payslip_id);
                                actions += '<a href="javascript:void(0)" data-url="' + delUrl + '" class="payslip_delete btn btn-danger btn-sm" data-bs-toggle="tooltip" title="{{ __('Delete') }}"><i class="ti ti-trash"></i></a>';
                                @endif

                                @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'hr')
                                tr += '<tr>' +
                                    '<td><a class="btn btn-outline-primary btn-sm" href="' + url_employee + '">' + v[1] + '</a></td>' +
                                    '<td>' + v[2] + '</td>' +
                                    '<td>' + v[3] + '</td>' +
                                    '<td><strong>' + v[6] + '</strong></td>' +
                                    '<td>' + status + '</td>' +
                                    '<td>' + actions + '</td>' +
                                    '</tr>';
                                @else
                                tr += '<tr>' +
                                    '<td><a class="btn btn-outline-primary btn-sm" href="' + url_employee + '">' + v[1] + '</a></td>' +
                                    '<td>' + v[2] + '</td>' +
                                    '<td><strong>' + v[6] + '</strong></td>' +
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
                        var datatable = new simpleDatatables.DataTable(table);
                    },
                    error: function(data) {
                        data = data.responseJSON;
                        show_toastr('error', data.error);
                    }
                });
            }

            $(document).on("change", ".month_date,.year_date", function() { callback(); });

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
@endpush

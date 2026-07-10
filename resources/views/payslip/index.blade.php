@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Payslip') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('payslip') }}</li>
@endsection

@section('content')
    <!-- @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'hr')
        <div class="row">
            <div class="col-sm-12">
                <div class="mt-2" id="multiCollapseExample1">
                    <div class="card">
                        <div class="card-body">
                            {{ Form::open(['route' => ['payslip.store'], 'method' => 'POST', 'id' => 'payslip_form']) }}
                            <div class="row align-items-center justify-content-end">
                                <div class="col-xl-10">
                                    <div class="row">
                                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                            <div class="btn-box">
                                            </div>
                                        </div>
                                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                            <div class="btn-box">
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6 col-sm-12 col-12">
                                            <div class="btn-box">
                                                {{ Form::label('month', __('Select Month'), ['class' => 'form-label']) }}
                                                {{ Form::select('month', $month, date('m'), ['class' => 'month-btn form-control select', 'id' => 'month']) }}
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6 col-sm-12 col-12">
                                            <div class="btn-box">
                                                {{ Form::label('year', __('Select Year'), ['class' => 'form-label']) }}
                                                {{ Form::select('year', $year, date('Y'), ['class' => 'form-control select']) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <div class="row">
                                        <div class="col-auto mt-4">
                                            <a href="javascript:void(0)"
                                                onclick="generatePayslip(); return false;"
                                                data-title="{{ __('Generate Payslip') }}" data-bs-toggle="tooltip"
                                                title="{{ __('Generate Payslip') }}"
                                                data-original-title="{{ __('payslip') }}" class="btn btn-sm btn-primary">
                                                <i class="ti ti-report-money"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif -->

    <div class="col-12">
        <div class="card mt-3">
            <div class="card-header d-flex align-items-xxl-center justify-content-between gap-3 flex-column flex-xxl-row">
                <h5>{{ __('Find Employee Payslip') }}</h5>
                <div class="row flex-1 justify-content-end align-items-center row-gap-1">
                    <div class="col-xxl-2 col-lg-3 col-sm-6 col-12">
                        <div class="btn-box">
                            <select class="form-control month_date " name="year" tabindex="-1" aria-hidden="true">
                                <option value="--">--</option>
                                @foreach ($month as $k => $mon)
                                    @php
                                        $selected = date('m') == $k ? 'selected' : '';
                                    @endphp
                                    <option value="{{ $k }}" {{ $selected }}>{{ $mon }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-xxl-2 col-lg-3 col-sm-6 col-12">
                        <div class="btn-box">
                            {{ Form::select('year', $year, date('Y'), ['class' => 'form-control year_date ']) }}
                        </div>
                    </div>
                    <div class="col-auto d-flex gap-3">
                        @if (Auth::user()->type == 'company' || Auth::user()->type == 'hr')
                            {{ Form::open(['route' => ['payslip.export'], 'method' => 'POST', 'id' => 'payslip_form']) }}
                            <input type="hidden" name="filter_month" class="filter_month">
                            <input type="hidden" name="filter_year" class="filter_year">
                            <input type="submit" value="{{ __('Export') }}" class="btn btn-primary me-1">
                            {{ Form::close() }}

                             <a href="javascript:void(0)"
                            onclick="generatePayslip(); return false;"
                            data-title="{{ __('Generate Payslip') }}" data-bs-toggle="tooltip"
                            title="{{ __('Generate Payslip') }}"
                            data-original-title="{{ __('payslip') }}" class="btn btn-sm btn-primary">
                            <i class="ti ti-report-money"></i>
                        </a>
                        @endif
                        @can('Create Pay Slip')
                            <input type="button" value="{{ __('Bulk Payment') }}" class="btn btn-primary" id="bulk_payment">
                        @endcan


                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table" id="pc-dt-render-column-cells">
                        <thead>
                            <tr>
                                <th>{{ __('Employee Id') }}</th>
                                @if (\Auth::user()->type != 'employee')
                                    <th>{{ __('Name') }}</th>
                                @endif
                                <th>{{ __('Payroll Type') }}</th>
                                <th>{{ __('Salary') }}</th>
                                <th>{{ __('Basic Salary') }}</th>
                                <th>{{ __('Net Salary') }}</th>
                                <th>{{ __('HRA') }}</th>
                                <th>{{ __('DA') }}</th>
                                <th>{{ __('Allowances') }}</th>
                                <th>{{ __('Pension') }}</th>
                                <th>{{ __('Commission') }}</th>
                                <th>{{ __('Loan/Advance') }}</th>
                                <th>{{ __('Statutory Deductions') }}</th>
                                <th>{{ __('Bonus') }}</th>
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
@endsection

@push('script-page')
    <script>
        $(document).ready(function() {

            let savedMonth = localStorage.getItem('selectedMonth');
            let savedYear  = localStorage.getItem('selectedYear');

            if (savedMonth) {
                $('.month_date').val(savedMonth);
            }

            if (savedYear) {
                $('.year_date').val(savedYear);
            }

            // OPTIONAL: clear after use
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
                    data: {
                        "datePicker": datePicker,
                        "_token": "{{ csrf_token() }}",
                    },
                    success: function(data) {
                        var datatable_data = {
                            data: data
                        };

                        function renderstatus(data, cell, row) {
                            if (data == 'Paid')
                                return '<div class="badge bg-success p-2 px-3 payroll-status"><a href="javascript:void(0)" class="text-white">' +
                                    data + '</a></div>';
                            else
                                return '<div class="badge bg-danger p-2 px-3 payroll-status"><a href="javascript:void(0)" class="text-white">' +
                                    data + '</a></div>';
                        }

                        function renderButton(data, cell, row) {

                            var $div = $(row);
                            employee_id = $div.find('td:eq(0)').text();
                            status = $div.find('td:eq(6)').text();

                            var month = $(".month_date").val();
                            var year = $(".year_date").val();
                            var id = employee_id;
                            var payslip_id = data;
                            var clickToPaid = '';
                            var payslip = '';
                            var view = '';
                            var edit = '';
                            var deleted = '';
                            var form = '';

                            if (data != 0) {
                                var payslip =
                                    '<a href="javascript:void(0)" data-url="{{ url('payslip/pdf/') }}/' + id +
                                    '/' + datePicker +
                                    '" data-size="xl"  data-ajax-popup="true" class="btn btn-primary" data-title="{{ __('Employee Payslip') }}">' +
                                    '{{ __('Payslip') }}' + '</a> ';
                            }

                            if (status == "UnPaid" && data != 0) {
                                clickToPaid = '<a href="{{ url('payslip/paysalary/') }}/' + id +
                                    '/' + datePicker + '"  class="view-btn primary-bg btn-sm">' +
                                    '{{ __('Click To Paid') }}' + '</a>  ';
                            }

                            if (data != 0) {
                                view =
                                    '<a href="javascript:void(0)" data-url="{{ url('payslip/showemployee/') }}/' +
                                    payslip_id +
                                    '"  data-ajax-popup="true" class="view-btn gray-bg" data-title="{{ __('View Employee Detail') }}">' +
                                    '{{ __('View') }}' + '</a>';
                            }

                            if (data != 0 && status == "UnPaid") {
                                edit =
                                    '<a href="javascript:void(0)" data-url="{{ url('payslip/editemployee/') }}/' +
                                    payslip_id +
                                    '"  data-ajax-popup="true" class="view-btn blue-bg" data-title="{{ __('Edit Employee salary') }}">' +
                                    '{{ __('Edit') }}' + '</a>';
                            }

                            var url = '{{ route('payslip.delete', ':id') }}';
                            url = url.replace(':id', payslip_id);

                            @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'employee')
                                if (data != 0) {
                                    deleted = '<a href="javascript:void(0)"  data-url="' + url +
                                        '" class="payslip_delete view-btn red-bg" >' +
                                        '{{ __('Delete') }}' + '</a>';
                                }
                            @endif

                            return view + payslip + clickToPaid + edit + deleted + form;
                        }

                        console.clear();
                        var tr = '';
                        if (data.length > 0) {
                            $.each(data, function(indexInArray, valueOfElement) {
                                console.log(valueOfElement);

                                var status =
                                    '<div class="badge bg-danger p-2 px-3 payroll-status"><a href="javascript:void(0)" class="text-white">' +
                                    valueOfElement[15] + '</a></div>';
                                if (valueOfElement[15] == 'Paid' || valueOfElement[15] ==
                                    'paid') {
                                    var status =
                                        '<div class="badge bg-success p-2 px-3 payroll-status"><a href="javascript:void(0)" class="text-white">' +
                                        valueOfElement[15] + '</a></div>';
                                }

                                var id = valueOfElement[0];
                                var employee_id = valueOfElement[1];
                                var payslip_id = valueOfElement[16];

                                if (valueOfElement[16] != 0) {
                                    // var payslip =
                                    //     '<a href="javascript:void(0)" data-url="{{ url('payslip/pdf/') }}/' +
                                    //     id +
                                    //     '/' + datePicker +
                                    //     '" data-size="lg" data-toggle="tooltip" title="{{ __('Payslip') }}" data-ajax-popup="true" class=" btn-sm btn btn-warning me-1" data-title="{{ __('Employee Payslip') }}">' +
                                    //     '<i class="ti ti-report-money"></i>' + '</a> ';
                                    var payslip =
                                        '<a href="javascript:void(0)" data-url="{{ url('payslip/pdf/') }}/' +id +'/' + datePicker +
                                        '" class="btn-sm btn btn-warning me-2" data-size="lg" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Payslip') }}" data-ajax-popup="true" data-title="{{ __('Employee Payslip') }}">' +
                                        '<i class="ti ti-report-money"></i>' +
                                        '</a>';
                                }
                                if (valueOfElement[15] == "UnPaid" && valueOfElement[16] != 0) {
                                    var clickToPaid =
                                        '<a href="{{ url('payslip/paysalary/') }}/' + id +
                                        '/' + datePicker +
                                        '" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Click To Paid') }}" class="btn-sm btn btn-primary me-1">' +
                                        '<i class="ti ti-currency-dollar"></i>' + '</a>  ';
                                } else {
                                    var clickToPaid = '';
                                }

                                // HMRC Submit FPS button — shows for Paid payslips (company/hr only, UK IP only)
                                var hmrcBtn = '';
                                @if ((\Auth::user()->type == 'company' || \Auth::user()->type == 'hr') && (\App\Models\Utility::isUkRequest() || request()->boolean('uk_preview')))
                                if (valueOfElement[15] == "Paid" && valueOfElement[16] != 0) {
                                    hmrcBtn = '<button type="button" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Submit to HMRC') }}" class="btn-sm btn btn-outline-success me-1 btn-hmrc-fps" data-employee-id="' + id + '" data-month="' + datePicker + '">' +
                                        '<i class="ti ti-send"></i>' + '</button>';
                                }
                                @endif

                                if (valueOfElement[16] != 0 && valueOfElement[15] == "UnPaid") {
                                    var edit =
                                        '<a href="javascript:void(0)" data-url="{{ url('payslip/editemployee/') }}/' +
                                        payslip_id +
                                        '"  data-ajax-popup="true" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Edit') }}" data-size="lg" class="btn-sm btn btn-info me-2" data-title="{{ __('Edit Employee salary') }}">' +
                                        '<i class="ti ti-pencil"></i>' + '</a>';
                                } else {
                                    var edit = '';
                                }

                                var url = '{{ route('payslip.delete', ':id') }}';
                                url = url.replace(':id', payslip_id);

                                @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'hr')
                                    var deleted = '<a href="javascript:void(0)"  data-url="' + url +
                                        '" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Delete') }}" class="payslip_delete view-btn btn btn-danger btn-sm"  >' +
                                        '<i class="ti ti-trash"></i>' + '</a>';
                                @else
                                    var deleted = '';
                                @endif

                                var url_employee = valueOfElement['url'];
                                @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'hr')
                                    tr +=
                                        '<tr>' +
                                        '<td> <a class="btn btn-outline-primary" href="' +
                                        url_employee + '">' + valueOfElement[1] + '</a></td> ' +
                                        '<td>' + valueOfElement[2] + '</td> ' +
                                        '<td>' + valueOfElement[3] + '</td>' +
                                        '<td>' + valueOfElement[4] + '</td>' +
                                        '<td>' + valueOfElement[5] + '</td>' +
                                        '<td>' + valueOfElement[6] + '</td>' +
                                        '<td>' + valueOfElement[7] + '</td>' +
                                        '<td>' + valueOfElement[8] + '</td>' +
                                        '<td>' + valueOfElement[9] + '</td>' +
                                        '<td>' + valueOfElement[10] + '</td>' +
                                        '<td>' + valueOfElement[11] + '</td>' +
                                        '<td>' + valueOfElement[12] + '</td>' +
                                        '<td>' + valueOfElement[13] + '</td>' +
                                        '<td>' + valueOfElement[14] + '</td>' +
                                        '<td>' + status + '</td>' +
                                        '<td>' + payslip + clickToPaid + edit + hmrcBtn + deleted +
                                        '</td>' +
                                        '</tr>';
                                @else
                                    tr +=
                                        '<tr>' +
                                        '<td> <a class="btn btn-outline-primary" href="' +
                                        url_employee + '">' + valueOfElement[1] + '</a></td> ' +
                                        '<td>' + valueOfElement[2] + '</td> ' +
                                        '<td>' + valueOfElement[4] + '</td>' +
                                        '<td>' + valueOfElement[5] + '</td>' +
                                        '<td>' + valueOfElement[6] + '</td>' +
                                        '<td>' + valueOfElement[7] + '</td>' +
                                        '<td>' + valueOfElement[8] + '</td>' +
                                        '<td>' + valueOfElement[9] + '</td>' +
                                        '<td>' + valueOfElement[10] + '</td>' +
                                        '<td>' + valueOfElement[11] + '</td>' +
                                        '<td>' + valueOfElement[12] + '</td>' +
                                        '<td>' + valueOfElement[13] + '</td>' +
                                        '<td>' + valueOfElement[14] + '</td>' +
                                        '<td>' + status + '</td>' +
                                        '<td>' + payslip + clickToPaid + edit + deleted +
                                        '</td>' +
                                        '</tr>';
                                @endif
                            });
                        } else {
                            var colspan = $('#pc-dt-render-column-cells thead tr th').length;
                            var tr = '<tr><td class="dataTables-empty" colspan="' + colspan +
                                '">{{ __('No entries found') }}</td></tr>';
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

            $(document).on("change", ".month_date,.year_date", function() {
                callback();
            });

            //bulkpayment Click
            $(document).on("click", "#bulk_payment", function() {
                var month = $(".month_date").val();
                var year = $(".year_date").val();
                var datePicker = year + '_' + month;

            });
            $(document).on('click', '#bulk_payment',
                'a[data-ajax-popup="true"], button[data-ajax-popup="true"], div[data-ajax-popup="true"]',
                function() {
                    var month = $(".month_date").val();
                    var year = $(".year_date").val();
                    var datePicker = year + '-' + month;

                    var title = 'Bulk Payment';
                    var size = 'md';
                    var url = 'payslip/bulk_pay_create/' + datePicker;

                    // return false;

                    $("#commonModal .modal-title").html(title);
                    $("#commonModal .modal-dialog").addClass('modal-' + size);
                    $.ajax({
                        url: url,
                        success: function(data) {
                            // alert(data);
                            // return false;
                            if (data.length) {
                                $('#commonModal .body').html(data);
                                $("#commonModal").modal('show');
                                // common_bind();
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

            $(document).on("click", ".payslip_delete", function() {
                var confirmation = confirm("are you sure you want to delete this payslip?");
                var url = $(this).data('url');

                if (confirmation) {
                    $.ajax({
                        type: "GET",
                        url: url,
                        dataType: "JSON",
                        success: function(data) {
                            // show_toastr(data.status, data.msg, 'data.status');
                            show_toastr('error', 'Payslip Deleted Successfully', 'success');

                            setTimeout(function() {
                                location.reload();
                            }, 800)
                        },
                    });
                }
            });
        });

        function generatePayslip() {
            var month = $(".month_date").val();
            var year = $(".year_date").val();
            var datePicker = year + '-' + month;

            $.ajax({
                url: '{{ route('payslip.store') }}',
                type: 'POST',
                data: {
                    "month": month,
                    "year": year,
                    "datePicker": datePicker,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(response) {

                    if (response.success) {

                        localStorage.setItem('selectedMonth', month);
                        localStorage.setItem('selectedYear', year);

                        show_toastr('success', response.message, 'success');

                        setTimeout(function() {
                            location.reload();
                        }, 800);

                    } else {
                        //  Handle "already created" case here
                        show_toastr('error', response.message, 'error');
                    }
                },
                error: function(data) {
                    data = data.responseJSON;
                    show_toastr('error', data.error, 'error');
                }
            });
        }

        // ── HMRC Submit FPS ──────────────────────────────────────────────────
        $(document).on('click', '.btn-hmrc-fps', function() {
            var $btn = $(this);
            var employeeId = $btn.data('employee-id');
            var month = $btn.data('month');

            if (!confirm('{{ __("Submit this payslip to HMRC as a Full Payment Submission (FPS)?") }}')) {
                return;
            }

            $btn.prop('disabled', true).html('<i class="ti ti-loader ti-spin"></i>');

            $.ajax({
                url: '{{ url("hmrc/submit-fps") }}/' + employeeId + '/' + month,
                type: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    if (res.success) {
                        show_toastr('Success', res.message, 'success');
                        $btn.removeClass('btn-outline-success').addClass('btn-success')
                            .html('<i class="ti ti-check"></i>').attr('title', '{{ __("Submitted") }}');
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

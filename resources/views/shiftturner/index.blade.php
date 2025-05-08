@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Shift Turner') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Shift Turner') }}</li>
@endsection


@section('content')
    <div class="row">

        <div class="col-sm-12 col-lg-12 col-xl-12 col-md-12">
            <div class=" mt-2 " id="multiCollapseExample1" style="">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(['route' => ['shiftturner.index'], 'method' => 'post', 'id' => 'shift_turner']) }}
                        <div class="d-flex align-items-center justify-content-end">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                <div class="btn-box">
                                    {{ Form::label('department_id', __('Department'), ['class' => 'form-label']) }}
                                    {{ Form::select('department_id', $department, isset($_GET['department_id']) ? $_GET['department_id'] : '', ['class' => 'form-control select', 'id' => 'department_id']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                <div class="btn-box">
                                    {{ Form::label('sub_department_id', __('Sub Department'), ['class' => 'form-label']) }}
                                    {{ Form::select('sub_department_id', $sub_department, isset($_GET['sub_department_id']) ? $_GET['sub_department_id'] : '', ['class' => 'form-control select', 'id' => 'subdepartment_id']) }}
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>


        <div class="col-xl-12">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    <div class="card-body py-0">

                        <div class="table-responsive">
                            <form action="{{ route('shiftturner.store') }}" method="post">
                                @csrf
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" name="allcheck"
                                                        id="allcheck" value="all">
                                                </div>
                                            </th>
                                            <th>{{ __('Employee') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="employee-list">
                                        @foreach ($employees as $employee)
                                            <tr>
                                                <td>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input turner" value="{{ $employee->id }}"
                                                            type="checkbox" name="employee_id[]"
                                                            @if (in_array($employee->id, $check_emp_list)) {{ 'checked' }} @endif
                                                            id="checkbox{{ $employee->id }}">
                                                    </div>
                                                </td>
                                                <td>{{ $employee->name }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <button class="btn btn-primary mt-4">Save Changes</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            var checkbox = document.getElementById("allcheck");
            var turner = document.querySelectorAll(".turner");
            // console.log(turner);
            checkbox.addEventListener("change", function() {
                turner.forEach(function(turner_list) {
                    turner_list.checked = checkbox.checked;
                });
            })

            var department_id = document.getElementById('department_id');
            department_id.addEventListener('change', function() {
                var department = department_id.value
                if (department == '') {
                    location.reload();
                }
                $.ajax({
                    url: '{{ route('getSubDepartmentandEmployee') }}',
                    type: "post",
                    data: {
                        '_token': '{{ csrf_token() }}',
                        'department_id': department
                    },
                    success: function(response) {
                        var result = JSON.parse(response)
                        document.getElementById('employee-list').innerHTML = result.employee_output
                        document.getElementById('subdepartment_id').innerHTML = result.subdepartment_output
                    }
                })
            })

            var subdepartment_id = document.getElementById('subdepartment_id');
            subdepartment_id.addEventListener('change', function() {
                var subdepartment = subdepartment_id.value
                if (subdepartment == '') {
                    location.reload();
                }
                $.ajax({
                    url: '{{ route('getEmployeeList') }}',
                    type: "post",
                    data: {
                        '_token': '{{ csrf_token() }}',
                        'subdepartment_id': subdepartment
                    },
                    success: function(response) {
                        document.getElementById('employee-list').innerHTML = response
                    }
                })
            })
        </script>
    @endsection

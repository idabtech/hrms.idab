{{ Form::model($role, ['route' => ['roles.update', $role->id], 'method' => 'PUT']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group">
            {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
            @if ($role->name == 'employee')
                <p class="form-control">{{ $role->name }}</p>
            @else
                <div class="form-icon-user">
                    {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Enter Role Name')]) }}
                </div>
            @endif
            @error('name')
                <span class="invalid-name" role="alert">
                    <strong class="text-danger">{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="form-group">
            @if (!empty($permissions))
                <h6 class="my-3">{{ __('Assign Permission to Roles') }} </h6>
                <table class="table  mb-0" id="dataTable-1">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" class="align-middle checkbox_middle form-check-input"
                                    name="checkall" id="checkall">
                            </th>
                            <th>{{ __('Module') }} </th>
                            <th>{{ __('Permissions') }} </th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $modules = [
                                'User',
                                'Role',
                                'Award',
                                'Transfer',
                                'Resignation',
                                'Travel',
                                'Promotion',
                                'Complaint',
                                'Warning',
                                'Termination',
                                'Department',
                                'Designation',
                                'Document Type',
                                'Branch',
                                'Award Type',
                                'Termination Type',
                                'Employee',
                                'Payslip Type',
                                'Allowance Option',
                                'Loan Option',
                                'Deduction Option',
                                'Set Salary',
                                'Allowance',
                                'Commission',
                                'Loan',
                                'Saturation Deduction',
                                'Other Payment',
                                'Overtime',
                                'Pay Slip',
                                'Account List',
                                'Payee',
                                'Payer',
                                'Income Type',
                                'Expense Type',
                                'Payment Type',
                                'Deposit',
                                'Expense',
                                'Transfer Balance',
                                'Event',
                                'Announcement',
                                'Leave Type',
                                'Leave',
                                'Meeting',
                                'Ticket',
                                'Attendance',
                                'TimeSheet',
                                'Holiday',
                                'Plan',
                                'Assets',
                                'Document',
                                'Employee Profile',
                                'Employee Last Login',
                                'Indicator',
                                'Appraisal',
                                'Goal Tracking',
                                'Goal Type',
                                'Competencies',
                                'Company Policy',
                                'Trainer',
                                'Training',
                                'Training Type',
                                'Job Category',
                                'Job Stage',
                                'Job',
                                'Job Application',
                                'Job OnBoard',
                                'Job Application Note',
                                'Job Application Skill',
                                'Custom Question',
                                'Interview Schedule',
                                'Career',
                                'Report',
                                'Performance Type',
                            ];
                            if (Auth::user()->type == 'super admin') {
                                $modules[] = 'Language';
                            }

                            $permissionTypes = [
                                'Manage',
                                'Create',
                                'Edit',
                                'Reset Password',
                                'Delete',
                                'Show',
                                'Move',
                                'client permission',
                                'invite user',
                                'buy',
                                'Add',
                            ];
                        @endphp

                        @foreach ($modules as $module)
                            @php
                                $hasPermission = false;
                                foreach ($permissionTypes as $type) {
                                    if (in_array($type . ' ' . $module, (array) $permissions)) {
                                        $hasPermission = true;
                                        break;
                                    }
                                }
                            @endphp

                            @if ($hasPermission)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="align-middle ischeck form-check-input"
                                            name="checkall" data-id="{{ str_replace(' ', '', $module) }}">
                                    </td>
                                    <td>
                                        <label class="ischeck" data-id="{{ str_replace(' ', '', $module) }}">
                                            {{ ucfirst($module) }}
                                        </label>
                                    </td>
                                    <td>
                                        <div class="row">
                                            @foreach ($permissionTypes as $type)
                                                @php
                                                    $permissionKey = array_search(
                                                        $type . ' ' . $module,
                                                        (array) $permissions,
                                                    );
                                                @endphp
                                                @if ($permissionKey !== false)
                                                    <div class="col-md-3 custom-control custom-checkbox">
                                                        {{ Form::checkbox('permissions[]', $permissionKey, $role->permission, ['class' => 'form-check-input isscheck isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $permissionKey]) }}
                                                        {{ Form::label('permission' . $permissionKey, ucfirst($type), ['class' => 'form-label font-weight-500']) }}
                                                        <br>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    <input type="submit" value="{{ __('Update') }}" class="btn  btn-primary">
</div>
{{ Form::close() }}


<script>
    $(document).ready(function() {
        $("#checkall").click(function() {
            $('input:checkbox').not(this).prop('checked', this.checked);
        });
        $(".ischeck").click(function() {
            var ischeck = $(this).data('id');
            $('.isscheck_' + ischeck).prop('checked', this.checked);
        });
    });
</script>

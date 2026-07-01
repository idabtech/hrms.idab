<div id="dailyView" style="display:none;">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th width="200">{{ __('Employee') }}</th>
                <th>{{ __('Shifts') }}</th>
                <th width="120">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody id="dailyTableBody"></tbody>
    </table>
</div>

@push('custom-scripts')
<script>
function renderDailyView() {
    const dateStr         = currentDate.toISOString().split('T')[0];
    const selectedStaffId = document.getElementById('staffFilter').value;
    const tbody           = document.getElementById('dailyTableBody');

    tbody.innerHTML = '<tr><td colspan="3" class="text-center p-4"><div class="spinner-border" role="status"></div><div class="mt-2">Loading...</div></td></tr>';

    fetch(`${ROTA_URLS.shifts}?start_date=${dateStr}&end_date=${dateStr}`, {
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    })
    .then(r => { if (!r.ok) throw new Error('Failed'); return r.json(); })
    .then(data => {
        const shifts      = data.data?.dailySummary?.[0]?.shifts || [];
        const staffToShow = selectedStaffId && selectedStaffId !== 'all'
            ? allStaff.filter(s => s.id == selectedStaffId)
            : allStaff;

        tbody.innerHTML = '';

        if (!staffToShow.length) {
            tbody.innerHTML = '<tr><td colspan="3" class="text-center p-4"><div class="alert alert-info mb-0">No employee found.</div></td></tr>';
            return;
        }

        staffToShow.forEach(staff => {
            const staffShifts = shifts.filter(s => s.userId == `user_${staff.id}`);
            const tr          = document.createElement('tr');

            const shiftsHtml = staffShifts.length
                ? staffShifts.map(shift => buildShiftBadge(shift, dateStr, 'daily')).join('')
                : '<span class="text-muted">{{ __("No shift assigned") }}</span>';

            tr.innerHTML = `
                <td><strong>${staff.name || 'Unknown'}</strong><br>
                    <small class="text-muted">${staff.role || 'Staff'}</small></td>
                <td style="min-width:300px;">${shiftsHtml}</td>
                <td>
                    ${window.canCreateRota ? `<button class="btn btn-sm btn-primary" onclick="openAddShiftForStaff(${staff.id})">
                        <i class="ti ti-plus"></i> {{ __('Assign') }}
                    </button>` : ''}
                </td>`;
            tbody.appendChild(tr);
        });

        if (shifts.length) {
            const sumTr = document.createElement('tr');
            sumTr.className = 'table-light';
            sumTr.innerHTML = `<td colspan="3" class="text-center py-2">
                <small class="text-muted"><strong>${shifts.length}</strong> {{ __('shifts for') }}
                <strong>${staffToShow.length}</strong> {{ __('staff on') }} ${currentDate.toLocaleDateString()}</small></td>`;
            tbody.appendChild(sumTr);
        }
    })
    .catch(() => {
        tbody.innerHTML = '<tr><td colspan="3" class="text-center p-4"><div class="alert alert-danger mb-0">Failed to load. Please try again.</div></td></tr>';
    });
}
</script>
@endpush

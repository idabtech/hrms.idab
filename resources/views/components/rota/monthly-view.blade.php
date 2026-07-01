<div id="monthlyView" style="display:none;">
    <div id="monthlyCalendars"></div>
</div>

@push('custom-scripts')
<script>
function renderMonthlyView() {
    const year      = currentMonth.getFullYear();
    const month     = currentMonth.getMonth() + 1;
    const startDate = `${year}-${String(month).padStart(2, '0')}-01`;
    const lastDay   = new Date(year, month, 0).getDate();
    const endDate   = `${year}-${String(month).padStart(2, '0')}-${lastDay}`;
    const selectedStaffId = document.getElementById('staffFilter').value;
    const container = document.getElementById('monthlyCalendars');

    container.innerHTML = '<div class="text-center p-4"><div class="spinner-border" role="status"></div><div class="mt-2">Loading monthly shifts...</div></div>';

    fetch(`${ROTA_URLS.shifts}?start_date=${startDate}&end_date=${endDate}`, {
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    })
    .then(r => { if (!r.ok) throw new Error('Failed to fetch shifts'); return r.json(); })
    .then(data => {
        const dailySummary = data.data?.dailySummary || [];
        container.innerHTML = '';

        if (!allStaff || !allStaff.length) {
            container.innerHTML = '<div class="alert alert-warning"><i class="ti ti-users-off me-2"></i>No employee available.</div>';
            return;
        }

        const staffToShow = selectedStaffId && selectedStaffId !== 'all'
            ? allStaff.filter(s => s.id == selectedStaffId)
            : allStaff;

        if (!staffToShow.length) {
            container.innerHTML = '<div class="alert alert-info">No employee found.</div>';
            return;
        }

        staffToShow.forEach(staff => {
            const staffShifts = dailySummary.filter(d => d.shifts?.some(s => s.userId == `user_${staff.id}`));
            const card = document.createElement('div');
            card.className = 'card mb-3';
            card.innerHTML = `
                <div class="card-header">
                    <h6 class="mb-0">${staff.name} <small class="text-muted">(${staff.role || 'Staff'})</small></h6>
                </div>
                <div class="card-body">
                    <div class="monthly-calendar" id="calendar-${staff.id}"></div>
                </div>`;
            container.appendChild(card);
            setTimeout(() => renderStaffMonthCalendar(staff.id, year, month, staffShifts), 10);
        });
    })
    .catch(err => {
        console.error('Monthly view error:', err);
        container.innerHTML = '<div class="alert alert-danger">Failed to load monthly view. Please try again.</div>';
    });
}

function renderStaffMonthCalendar(staffId, year, month, staffShifts) {
    const calendarEl = document.getElementById(`calendar-${staffId}`);
    if (!calendarEl) return;

    const firstDay    = new Date(year, month - 1, 1).getDay();
    const daysInMonth = new Date(year, month, 0).getDate();

    let html = '<div style="overflow-x:auto;"><table class="table table-bordered table-sm"><thead><tr>';
    ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'].forEach(d => {
        html += `<th class="text-center" style="width:14.28%;min-width:80px;">${d}</th>`;
    });
    html += '</tr></thead><tbody><tr>';

    for (let i = 0; i < firstDay; i++) html += '<td style="height:80px;"></td>';

    for (let day = 1; day <= daysInMonth; day++) {
        if ((firstDay + day - 1) % 7 === 0 && day !== 1) html += '</tr><tr>';

        const dateStr   = `${year}-${String(month).padStart(2,'0')}-${String(day).padStart(2,'0')}`;
        const dayShifts = staffShifts.find(d => d.date === dateStr)?.shifts?.filter(s => s.userId == `user_${staffId}`) || [];

        html += `<td style="vertical-align:top;padding:4px;height:80px;cursor:pointer;position:relative;"
                    onclick="window.canCreateRota ? openAddShiftForStaffAndDate(${staffId}, '${dateStr}') : null">`;
        html += `<div class="text-center mb-1"><strong>${day}</strong></div>`;

        dayShifts.forEach(shift => {
            html += buildShiftBadge(shift, dateStr, 'monthly');
        });

        html += '</td>';
    }

    const totalCells     = firstDay + daysInMonth;
    const remainingCells = totalCells % 7;
    if (remainingCells !== 0) {
        for (let i = remainingCells; i < 7; i++) html += '<td style="height:80px;"></td>';
    }

    html += '</tr></tbody></table></div>';

    try {
        calendarEl.innerHTML = html;
    } catch (err) {
        console.error('Error rendering calendar for staff', staffId, err);
        calendarEl.innerHTML = '<div class="alert alert-warning">Error rendering calendar</div>';
    }
}
</script>
@endpush

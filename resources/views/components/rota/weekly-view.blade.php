<div id="weeklyView">
    <div style="overflow-x: auto;">
        <table class="table table-bordered">
            <thead>
                <tr id="weeklyHeaders">
                    <th width="150">{{ __('Employee') }}</th>
                </tr>
            </thead>
            <tbody id="weeklyTableBody"></tbody>
        </table>
    </div>
</div>

@push('custom-scripts')
<script>
function renderWeeklyView() {
    const days = [];
    for(let i = 0; i < 7; i++) {
        const day = new Date(weekStart);
        day.setDate(day.getDate() + i);
        days.push(day);
    }

    const startDate = days[0].toISOString().split('T')[0];
    const endDate   = days[6].toISOString().split('T')[0];
    const selectedStaffId = document.getElementById('staffFilter').value;
    const tbody = document.getElementById('weeklyTableBody');

    tbody.innerHTML = '<tr><td colspan="8" class="text-center p-4"><div class="spinner-border" role="status"></div><div class="mt-2">Loading weekly shifts...</div></td></tr>';

    let headers = '<th width="150">Staff</th>';
    days.forEach(day => {
        const dayName  = day.toLocaleDateString('en-US', {weekday: 'short'}).toUpperCase();
        const monthDay = day.toLocaleDateString('en-US', {month: 'short', day: '2-digit'}).toUpperCase();
        headers += `<th class="text-center">${dayName}<br>${monthDay}</th>`;
    });
    document.getElementById('weeklyHeaders').innerHTML = headers;

    fetch(`${ROTA_URLS.shifts}?start_date=${startDate}&end_date=${endDate}`, {
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    })
    .then(r => { if (!r.ok) throw new Error('Failed to fetch weekly shifts'); return r.json(); })
    .then(data => {
        const dailySummary = data.data?.dailySummary || [];
        tbody.innerHTML = '';

        const staffToShow = selectedStaffId && selectedStaffId !== 'all'
            ? allStaff.filter(s => s.id == selectedStaffId)
            : allStaff;

        if (!staffToShow.length) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center p-4"><div class="alert alert-info mb-0">No employee found.</div></td></tr>';
            return;
        }

        staffToShow.forEach(staff => {
            try {
                const tr = document.createElement('tr');
                let cells = `<td><strong>${staff.name || 'Unknown'}</strong><br><small class="text-muted">${staff.role || 'Staff'}</small></td>`;

                days.forEach(day => {
                    const dateStr   = day.toISOString().split('T')[0];
                    const dayData   = dailySummary.find(d => d.date === dateStr);
                    const staffShifts = dayData?.shifts?.filter(s => s.userId == `user_${staff.id}`) || [];

                    cells += `<td style="vertical-align:top;padding:0.5rem;width:14%;cursor:pointer;"
                                onclick="window.canCreateRota ? openAddShiftForStaffAndDate(${staff.id}, '${dateStr}') : null">`;

                    if (staffShifts.length) {
                        staffShifts.forEach(shift => {
                            cells += buildShiftBadge(shift, dateStr, 'weekly');
                        });
                    } else {
                        cells += `<div class="text-center text-muted" style="padding:1rem 0;">+</div>`;
                    }

                    cells += `</td>`;
                });

                tr.innerHTML = cells;
                tbody.appendChild(tr);
            } catch (err) {
                console.error(`Error rendering staff ${staff.id}:`, err);
            }
        });
    })
    .catch(err => {
        console.error('Weekly view error:', err);
        tbody.innerHTML = '<tr><td colspan="8" class="text-center p-4"><div class="alert alert-danger mb-0">Failed to load weekly shifts. Please try again.</div></td></tr>';
    });
}
</script>
@endpush

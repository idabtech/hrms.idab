<div id="yearlyView" style="display:none;">
    <div style="overflow-x: auto;">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th width="150">{{ __('Employee') }}</th>
                    <th class="text-center">{{ __('JAN') }}</th>
                    <th class="text-center">{{ __('FEB') }}</th>
                    <th class="text-center">{{ __('MAR') }}</th>
                    <th class="text-center">{{ __('APR') }}</th>
                    <th class="text-center">{{ __('MAY') }}</th>
                    <th class="text-center">{{ __('JUN') }}</th>
                    <th class="text-center">{{ __('JUL') }}</th>
                    <th class="text-center">{{ __('AUG') }}</th>
                    <th class="text-center">{{ __('SEP') }}</th>
                    <th class="text-center">{{ __('OCT') }}</th>
                    <th class="text-center">{{ __('NOV') }}</th>
                    <th class="text-center">{{ __('DEC') }}</th>
                    <th class="text-center" width="100">{{ __('Total') }}</th>
                </tr>
            </thead>
            <tbody id="yearlyTableBody"></tbody>
        </table>
    </div>
</div>

@push('custom-scripts')
<script>
function renderYearlyView() {
    const startDate = `${currentYear}-01-01`;
    const endDate = `${currentYear}-12-31`;
    const selectedStaffId = document.getElementById('staffFilter').value;
    const tbody = document.getElementById('yearlyTableBody');

    // Show loading state
    tbody.innerHTML = '<tr><td colspan="14" class="text-center p-4"><div class="spinner-border" role="status"></div><div class="mt-2">Loading yearly data...</div></td></tr>';

    fetch(`${ROTA_URLS.shifts}?start_date=${startDate}&end_date=${endDate}`, {
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    })
    .then(r => {
        if (!r.ok) throw new Error('Failed to fetch yearly data');
        return r.json();
    })
    .then(data => {
        const dailySummary = data.data?.dailySummary || [];
        tbody.innerHTML = '';

        // Ensure allStaff is loaded
        if (!allStaff || allStaff.length === 0) {
            tbody.innerHTML = '<tr><td colspan="14" class="text-center p-4"><div class="alert alert-warning mb-0"><i class="ti ti-users-off me-2"></i>No employee available. Please add employee to view rotas.</div></td></tr>';
            return;
        }

        const staffToShow = selectedStaffId && selectedStaffId !== 'all' ? allStaff.filter(s => s.id == selectedStaffId) : allStaff;

        if (staffToShow.length === 0) {
            tbody.innerHTML = '<tr><td colspan="14" class="text-center p-4"><div class="alert alert-info mb-0">No employee found.</div></td></tr>';
            return;
        }

        staffToShow.forEach(staff => {
            const tr = document.createElement('tr');
            let cells = `<td><strong>${staff.name || 'Unknown'}</strong><br><small class="text-muted">${staff.role || 'Staff'}</small></td>`;
            let totalShifts = 0;

            // Process each month
            for(let month = 1; month <= 12; month++) {
                try {
                    const monthShifts = dailySummary.filter(d => {
                        if (!d.date) return false;
                        const date = new Date(d.date);
                        return date.getFullYear() === currentYear &&
                               date.getMonth() + 1 === month &&
                               d.shifts?.some(s => s.userId == `user_${staff.id}`);
                    });

                    const monthShiftCount = monthShifts.reduce((sum, d) => {
                        if (!d.shifts || !Array.isArray(d.shifts)) return sum;
                        return sum + d.shifts.filter(s => s.userId == `user_${staff.id}`).length;
                    }, 0);

                    totalShifts += monthShiftCount;

                    // Add click handler for month cell
                    const cellClass = monthShiftCount > 0 ? 'text-center cursor-pointer' : 'text-center';
                    const cellStyle = monthShiftCount > 0 ? 'style="cursor: pointer;"' : '';
                    const cellContent = monthShiftCount > 0 ? `<span class="badge bg-primary">${monthShiftCount}</span>` : '-';

                    cells += `<td class="${cellClass}" ${cellStyle} onclick="viewMonthDetails(${staff.id}, ${month}, ${currentYear})">${cellContent}</td>`;
                } catch (error) {
                    console.error(`Error processing month ${month} for staff ${staff.id}:`, error);
                    cells += `<td class="text-center">-</td>`;
                }
            }

            cells += `<td class="text-center"><strong class="text-primary">${totalShifts}</strong></td>`;
            tr.innerHTML = cells;
            tbody.appendChild(tr);
        });

        // Add summary row if multiple staff
        if (staffToShow.length > 1) {
            addYearlySummaryRow(dailySummary, staffToShow);
        }
    })
    .catch(err => {
        console.error('Yearly view error:', err);
        tbody.innerHTML = '<tr><td colspan="14" class="text-center p-4"><div class="alert alert-danger mb-0">Failed to load yearly data. Please try again.</div></td></tr>';
    });
}

function addYearlySummaryRow(dailySummary, staffToShow) {
    const tbody = document.getElementById('yearlyTableBody');
    const tr = document.createElement('tr');
    tr.className = 'table-secondary';

    let cells = '<td><strong>Total</strong></td>';
    let grandTotal = 0;

    for(let month = 1; month <= 12; month++) {
        const monthTotal = dailySummary.filter(d => {
            if (!d.date) return false;
            const date = new Date(d.date);
            return date.getFullYear() === currentYear &&
                   date.getMonth() + 1 === month &&
                   d.shifts?.some(s => staffToShow.some(staff => s.userId == `user_${staff.id}`));
        }).reduce((sum, d) => {
            if (!d.shifts) return sum;
            return sum + d.shifts.filter(s => staffToShow.some(staff => s.userId == `user_${staff.id}`)).length;
        }, 0);

        grandTotal += monthTotal;
        cells += `<td class="text-center"><strong>${monthTotal || '-'}</strong></td>`;
    }

    cells += `<td class="text-center"><strong class="text-success">${grandTotal}</strong></td>`;
    tr.innerHTML = cells;
    tbody.appendChild(tr);
}

function viewMonthDetails(staffId, month, year) {
    // Switch to monthly view and navigate to specific month
    currentMonth = new Date(year, month - 1, 1);
    document.getElementById('staffFilter').value = staffId;

    // Switch to monthly view
    currentView = 'dayGridMonth';
    document.querySelectorAll('.view-tabs .btn').forEach(btn => btn.classList.remove('active'));
    document.querySelector('[data-view="dayGridMonth"]').classList.add('active');

    // Hide all views and show monthly
    document.querySelectorAll('[id$="View"]').forEach(view => view.style.display = 'none');
    document.getElementById('monthlyView').style.display = 'block';

    renderMonthlyView();
    updateDateLabel();
}
</script>
@endpush

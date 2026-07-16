@php
    // All template data is pre-computed from
    // App\Http\Controllers\PaySlipController::preparePayslipTemplateData().
@endphp
<style>
.min-wrap { font-family:'Inter','Segoe UI',Arial,sans-serif; font-size:11px; color:#333; max-width:800px; margin:0 auto; background:#f5f5f5; padding:16px; }
.min-wrap * { box-sizing:border-box; }
.min-card { background:#fff; border:1px solid #e0e0e0; box-shadow:0 1px 6px rgba(0,0,0,0.04); }
.min-pad { padding:12px 18px; }
.min-flex { display:flex; gap:14px; }
.min-flex > div { flex:1; }
.min-hr { height:1px; background:#e8e8e8; margin:8px 0; }
.min-mono { color:{{ $_themeColor }}; font-weight:600; }
.min-grid-title { font-size:9px; text-transform:uppercase; letter-spacing:1.2px; color:#999; margin-bottom:5px; border-bottom:1px solid #eee; padding-bottom:3px; }
.min-table { width:100%; border-collapse:collapse; }
.min-table td { padding:2px 3px; font-size:10px; color:#444; }
.min-lbl { color:#888; width:50%; }
.min-val { text-align:right; font-weight:500; }
.min-total td { font-weight:700; color:{{ $_themeColor }}; border-top:1.5px solid #ccc !important; padding-top:4px; font-size:10.5px; }
.min-net { background:#222; color:#fff; padding:10px 16px; display:flex; justify-content:space-between; align-items:center; margin:7px 0; }
.min-net label { font-size:12px; font-weight:500; letter-spacing:0.5px; }
.min-net span { font-size:17px; font-weight:700; }
.min-stamp { display:flex; justify-content:space-between; align-items:center; padding:5px 0; font-size:9px; color:#999; border-top:1px solid #e8e8e8; margin-top:5px; }
.min-stamp img { width:50px; height:50px; border-radius:50%; object-fit:contain; border:1.5px solid {{ $_themeColor }}44; }
.min-stamp-ph { width:50px; height:50px; border-radius:50%; border:1.5px dashed #ccc; display:flex; align-items:center; justify-content:center; font-size:6px; color:#bbb; text-align:center; }
.min-sig { display:flex; justify-content:space-between; padding:8px 24px 2px; }
.min-sig-item { text-align:center; min-width:100px; }
.min-sig-line { border-top:1px solid #ccc; width:90px; margin:8px auto 2px; }
.min-sig-lbl { font-size:8px; color:#aaa; }
.min-badge { display:inline-block; font-weight:600; font-size:9px; }
.min-paid { color:#198754; }
.min-unpaid { color:#dc3545; }
</style>
<div class="min-wrap">
    @if(!($previewMode ?? false))
    <div style="display:flex; justify-content:flex-end; gap:8px; margin-bottom:14px;">
        <a href="{{ $downloadUrl }}" class="btn btn-sm btn-primary">
            <i class="fa fa-download me-1"></i>{{ __('Download PDF') }}
        </a>
        @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'hr')
            <button type="button" class="btn btn-sm btn-warning"
                onclick="payslipEmailSend({{ $employee->id }},'{{ $payslip->salary_month }}')">
                <i class="fa fa-paper-plane me-1"></i>{{ __('Send Email') }}
            </button>
        @endif
    </div>
    @endif

    <div class="min-card">
        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid {{ $_themeColor }}; padding:12px 18px;">
            <div>
                <div style="font-size:9px; color:#999; letter-spacing:1.5px; text-transform:uppercase;">{{ __('PAY SLIP') }}</div>
                <div style="font-size:13px; font-weight:600; color:{{ $_themeColor }};">{{ $slipLabel }}</div>
            </div>
            <img src="{{ $logoUrl }}" alt="logo" style="height:28px;">
        </div>

        <div class="min-pad">
            {{-- Employee Info Bar --}}
            <div style="display:flex; justify-content:space-between; align-items:center; background:#fafafa; padding:8px 12px; margin-bottom:8px;">
                <div><strong style="font-size:12px;">{{ $employee->name }}</strong><br><span style="font-size:9px; color:#777;">{{ optional($employee->designation)->name ?? '—' }}</span></div>
                <div style="text-align:right; font-size:9px; color:#777;">
                    <div>{{ __('ID') }}: {{ $employee->employee_id ?? '—' }}</div>
                    <div>{{ $payslipTypeName }} &mdash; <span style="font-weight:600;{{ $isPaid ? 'color:#198754;' : 'color:#dc3545;' }}">{{ $isPaid ? __('Paid') : __('Unpaid') }}</span></div>
                </div>
            </div>

            {{-- Grid: Employee + Payment + Salary --}}
            <div class="min-flex" style="margin-bottom:6px;">
                <div>
                    <div class="min-grid-title">{{ __('Employee') }}</div>
                    <table class="min-table">
                        <tr><td class="min-lbl">{{ __('Department') }}</td><td class="min-val">{{ optional($employee->department)->name ?? '—' }}</td></tr>
                        <tr><td class="min-lbl">{{ __('DOJ') }}</td><td class="min-val">{{ $employee->company_doj ?? '—' }}</td></tr>
                        <tr><td class="min-lbl">{{ __('PAN No.') }}</td><td class="min-val">{{ $employee->tax_payer_id ?? '—' }}</td></tr>
                        @if($_isUkRequest)
                        <tr><td class="min-lbl">{{ __('NI Number') }}</td><td class="min-val">{{ $niNumber }}</td></tr>
                        <tr><td class="min-lbl">{{ __('Tax Code') }}</td><td class="min-val">{{ $taxCode }}</td></tr>
                        @endif
                    </table>
                </div>
                <div>
                    <div class="min-grid-title">{{ __('Payment') }}</div>
                    <table class="min-table">
                        <tr><td class="min-lbl">{{ __('Bank') }}</td><td class="min-val">{{ $employee->bank_name ?? '—' }}</td></tr>
                        <tr><td class="min-lbl">{{ __('Account') }}</td><td class="min-val">{{ $employee->account_number ?? '—' }}</td></tr>
                        <tr><td class="min-lbl">{{ __(\App\Models\Utility::bankCodeLabel()) }}</td><td class="min-val">{{ $employee->bank_identifier_code ?? '—' }}</td></tr>
                        <tr><td class="min-lbl">{{ __('Holder') }}</td><td class="min-val">{{ $employee->account_holder_name ?? '—' }}</td></tr>
                    </table>
                </div>
                <div>
                    <div class="min-grid-title">{{ __('Salary') }}</div>
                    <table class="min-table">
                        <tr><td class="min-lbl">{{ __('Gross') }}</td><td class="min-val min-mono">{{ $_fmtMoney($_storedSalary) }}</td></tr>
                        <tr><td class="min-lbl">{{ __('Per Day') }}</td><td class="min-val">{{ $_fmtMoney($perDaySalary) }}</td></tr>
                        <tr><td class="min-lbl">{{ __('Per Hour') }}</td><td class="min-val">{{ $_fmtMoney($perHourSalary) }}</td></tr>
                        <tr><td class="min-lbl">{{ __('Days Paid') }}</td><td class="min-val">{{ $daysPaid }}</td></tr>
                    </table>
                </div>
            </div>

            {{-- Monthly: Attendance + Leave Grid | Hourly: Attendance + Rate --}}
            @if(!$isHourly)
            <div class="min-flex" style="margin-bottom:6px;">
                <div>
                    <div class="min-grid-title">{{ __('Days & Work') }}</div>
                    <table class="min-table">
                        <tr><td class="min-lbl">{{ __('Working') }}</td><td class="min-val min-mono">{{ $officeDays }}</td></tr>
                        <tr><td class="min-lbl">{{ __('Present') }}</td><td class="min-val" style="color:#198754;">{{ $presentDaysFmt }}</td></tr>
                        <tr><td class="min-lbl">{{ __('Leave') }}</td><td class="min-val" style="color:#e07900;">{{ $approvedLeaves }}</td></tr>
                        <tr><td class="min-lbl">{{ __('Absent') }}</td><td class="min-val" style="color:#dc3545;">{{ $absentDaysFmt }}</td></tr>
                        @if($extraDays>0)
                        <tr><td class="min-lbl" style="color:#856404;">{{ __('Extra/OT') }}</td><td class="min-val" style="color:#856404;font-weight:600;">+{{ $extraDaysFmt }}</td></tr>
                        @endif
                        <tr><td class="min-lbl">{{ __('Hours') }}</td><td class="min-val">{{ $totalWorkHours }}h</td></tr>
                    </table>
                </div>
                <div>
                    <div class="min-grid-title">{{ __('Leave') }}</div>
                    <table class="min-table">
                        <tr><td class="min-lbl" style="font-weight:700;">{{ __('This Month') }}</td><td class="min-val" style="font-weight:700;">{{ $approvedLeaves }}</td></tr>
                        <tr><td class="min-lbl">{{ __('Paid') }}</td><td class="min-val" style="color:#198754;">{{ $paidLeaveDays }}</td></tr>
                        <tr><td class="min-lbl">{{ __('Unpaid') }}</td><td class="min-val" style="color:#dc3545;">{{ $unpaidLeaveDays }}</td></tr>
                    </table>
                </div>
                <div>
                    <div class="min-grid-title">{{ __('Summary') }}</div>
                    <table class="min-table">
                        <tr><td class="min-lbl">{{ __('Allocated') }}</td><td class="min-val">{{ $totalLeaveAlloc }}</td></tr>
                        <tr><td class="min-lbl">{{ __('Remaining') }}</td><td class="min-val" style="color:#0056b3;">{{ $remainingLeaves }}</td></tr>
                        <tr><td class="min-lbl">{{ __('Used') }}</td><td class="min-val">{{ $usedLeaves }}</td></tr>
                    </table>
                </div>
            </div>
            @else
            <div class="min-flex" style="margin-bottom:6px;">
                <div>
                    <div class="min-grid-title">{{ __('Attendance') }}</div>
                    <table class="min-table">
                        <tr><td class="min-lbl">{{ __('Working') }}</td><td class="min-val min-mono">{{ $officeDays }}</td></tr>
                        <tr><td class="min-lbl">{{ __('Present') }}</td><td class="min-val" style="color:#198754;">{{ $presentDaysFmt }}</td></tr>
                        <tr><td class="min-lbl">{{ __('Absent') }}</td><td class="min-val" style="color:#dc3545;">{{ $absentDaysFmt }}</td></tr>
                        <tr><td class="min-lbl">{{ __('Hours') }}</td><td class="min-val">{{ $totalWorkHours }}h</td></tr>
                    </table>
                </div>
                <div>
                    <div class="min-grid-title">{{ __('Hourly Rate') }}</div>
                    <table class="min-table">
                        <tr><td class="min-lbl">{{ __('Rate') }} <b style="color:{{ $_themeColor }};">(A)</b></td><td class="min-val" style="color:{{ $_themeColor }};">{{ $_fmtMoney($perHourSalary) }}</td></tr>
                        <tr><td class="min-lbl">{{ __('Hours') }} <b style="color:#198754;">(B)</b></td><td class="min-val" style="color:#198754;">{{ $totalWorkHours }} hrs</td></tr>
                        <tr style="background:{{ $_themeColor }}10;"><td class="min-lbl"><strong>{{ __('Gross') }} (A &times; B)</strong></td><td class="min-val" style="color:{{ $_themeColor }};"><strong>{{ $_fmtMoney($_storedSalary) }}</strong></td></tr>
                    </table>
                </div>
                <div>
                    <div class="min-grid-title">{{ __('Payslip') }}</div>
                    <table class="min-table">
                        <tr><td class="min-lbl">{{ $payslipTypeName }}</td><td class="min-val min-mono">{{ $slipLabel }}</td></tr>
                        <tr><td class="min-lbl">{{ __('Status') }}</td><td class="min-val {{ $isPaid ? 'min-paid' : 'min-unpaid' }}">{{ $isPaid ? __('Paid') : __('Unpaid') }}</td></tr>
                    </table>
                </div>
            </div>
            @endif

            <div class="min-hr"></div>

            {{-- Earnings & Deductions --}}
            <div class="min-flex" style="margin-bottom:6px;">
                <div>
                    <div class="min-grid-title">{{ __('Earnings') }}</div>
                    <table class="min-table">
                        @foreach($earRows as $er)
                        <tr><td class="min-lbl">{{ $er['label'] }}</td><td class="min-val">{{ $er['amount']!==null ? $_fmtMoney($er['amount']) : '' }}</td></tr>
                        @endforeach
                        <tr class="min-total"><td class="min-lbl">{{ __('Total') }}</td><td class="min-val">{{ $_fmtMoney($totalEarnings) }}</td></tr>
                    </table>
                </div>
                <div>
                    <div class="min-grid-title">{{ __('Deductions') }}</div>
                    <table class="min-table">
                        @forelse($dedRows as $dr)
                        <tr><td class="min-lbl">{{ $dr['label'] }}</td><td class="min-val">{{ $dr['amount']!==null ? $_fmtMoney($dr['amount']) : '—' }}</td></tr>
                        @empty
                        <tr><td class="min-lbl" style="color:#aaa;font-style:italic;">{{ __('None') }}</td><td></td></tr>
                        @endforelse
                        <tr class="min-total"><td class="min-lbl">{{ __('Total') }}</td><td class="min-val">{{ $_fmtMoney($totalDeductions) }}</td></tr>
                    </table>
                </div>
            </div>

            {{-- Net Pay --}}
            <div class="min-net">
                <label>{{ __('Net Pay') }}</label>
                <span>{{ $_fmtMoney($netSalary) }}</span>
            </div>

            {{-- Stamp --}}
            <div class="min-stamp">
                <div>{{ __('Authorized Digital Stamp') }}<br><span style="font-size:8px;color:#bbb;">{{ $companyName }}</span></div>
                <img src="{{ $_stampUrl ?? '' }}" alt="Stamp" style="{{ $_stampUrl ? 'width:50px;height:50px;border-radius:50%;object-fit:contain;border:1.5px solid '.$_themeColor.'44;' : 'display:none;' }}">
                @if(!$_stampUrl)<div class="min-stamp-ph">{{ __('Stamp') }}</div>@endif
            </div>

            {{-- Signatures --}}
            <div class="min-sig">
                <div class="min-sig-item"><div class="min-sig-line"></div><div class="min-sig-lbl">{{ __('Employee') }}</div></div>
                <div class="min-sig-item"><div class="min-sig-line"></div><div class="min-sig-lbl">{{ __('Authorized') }}</div></div>
            </div>
        </div>

        <div style="background:#fafafa; padding:6px 18px; font-size:8px; text-align:center; color:#999; border-top:1px solid #e8e8e8;">
            @if($companyPhone)<strong>{{ __('Tel') }}:</strong> {{ $companyPhone }} &nbsp;|&nbsp; @endif
            @if($companyWeb)<strong>{{ __('Web') }}:</strong> {{ $companyWeb }} &nbsp;|&nbsp; @endif
            @if($companyEmail)<strong>{{ __('Email') }}:</strong> {{ $companyEmail }} @endif
        </div>
    </div>
</div>
<script>
window.addEventListener('message',function(e){if(e.data&&e.data.type==='payslip-stamp'&&e.data.dataUrl){var img=document.querySelector('.min-stamp img');if(img){img.src=e.data.dataUrl;img.style.display='';}var ph=document.querySelector('.min-stamp-ph');if(ph)ph.style.display='none';}});

window.payslipEmailSend = function(eid, month) {
    $.ajax({
        url: '{{ url('payslip/send') }}/' + eid + '/' + month,
        type: 'GET',
        data: { _token: '{{ csrf_token() }}' },
        success: function(r) { show_toastr('Success', r.message, 'success'); },
        error: function(r) { show_toastr('Error', r.message, 'error'); }
    });
};
</script>

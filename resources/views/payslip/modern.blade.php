@php
    // All template data is pre-computed from
    // App\Http\Controllers\PaySlipController::preparePayslipTemplateData().
@endphp
<style>
.mod-wrap { font-family:'Segoe UI',Arial,sans-serif; font-size:12px; color:#1a1a1a; max-width:800px; margin:0 auto; background:#f8f9fa; padding:14px; }
.mod-wrap * { box-sizing:border-box; }
.mod-card { background:#fff; border-radius:10px; box-shadow:0 2px 12px rgba(0,0,0,0.08); overflow:hidden; }
.mod-grad { background:linear-gradient(135deg,{{ $_themeColor }},{{ $_themeColor }}dd); color:#fff; padding:16px 20px; display:flex; justify-content:space-between; align-items:center; }
.mod-grad h2 { font-size:15px; font-weight:700; margin:0; letter-spacing:1px; }
.mod-grad span { font-size:11px; opacity:0.9; }
.mod-body { padding:16px 20px; }
.mod-grid { display:flex; gap:16px; }
.mod-grid > div { flex:1; background:{{ $_bgLight }}; border-radius:8px; padding:10px 14px; }
.mod-grid-title { font-weight:700; font-size:11px; color:{{ $_themeColor }}; border-bottom:2px solid {{ $_themeColor }}33; padding-bottom:4px; margin-bottom:6px; text-transform:uppercase; letter-spacing:0.5px; }
.mod-table { width:100%; border-collapse:collapse; }
.mod-table td { padding:3px 4px; font-size:11px; border-bottom:1px solid #f0f0f0; }
.mod-lbl { color:#666; width:55%; }
.mod-val { text-align:right; font-weight:600; }
.mod-total td { font-weight:700; color:{{ $_themeColor }}; border-top:2px solid {{ $_themeColor }} !important; padding-top:5px; font-size:12px; }
.mod-net { background:linear-gradient(135deg,{{ $_themeColor }},{{ $_themeColor }}dd); color:#fff; border-radius:8px; padding:12px 18px; display:flex; justify-content:space-between; align-items:center; margin:8px 0; }
.mod-net label { font-size:13px; font-weight:600; }
.mod-net span { font-size:20px; font-weight:700; }
.mod-stamp { display:flex; justify-content:space-between; align-items:center; padding:6px 0; font-size:10px; color:#888; border-top:1px solid #e8e8e8; margin-top:6px; }
.mod-stamp img { width:56px; height:56px; border-radius:50%; object-fit:contain; border:2px solid {{ $_themeColor }}44; }
.mod-stamp-ph { width:56px; height:56px; border-radius:50%; border:2px dashed {{ $_themeColor }}44; display:flex; align-items:center; justify-content:center; font-size:7px; color:{{ $_themeColor }}66; text-align:center; padding:4px; }
.mod-sig { display:flex; justify-content:space-between; padding:10px 30px 4px; }
.mod-sig-item { text-align:center; min-width:120px; }
.mod-sig-line { border-top:1.5px solid #999; width:110px; margin:10px auto 3px; }
.mod-sig-lbl { font-size:9px; color:#888; }
.mod-badge { display:inline-block; padding:1px 8px; border-radius:8px; font-size:10px; font-weight:600; }
.mod-paid { background:#d4edda; color:#155724; }
.mod-unpaid { background:#f8d7da; color:#721c24; }
</style>
<div class="mod-wrap">
    @if(!($previewMode ?? false))
    <div style="display:flex; justify-content:flex-end; gap:8px; margin-bottom:14px;">
        <a href="{{ $downloadUrl }}" class="btn btn-sm btn-primary">
            <i class="fa fa-download me-1"></i>{{ __('Download PDF') }}
        </a>
        @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'hr' || \Auth::user()->type == 'HR')
            <button type="button" class="btn btn-sm btn-warning"
                onclick="payslipEmailSend({{ $employee->id }},'{{ $payslip->salary_month }}')">
                <i class="fa fa-paper-plane me-1"></i>{{ __('Send Email') }}
            </button>
        @endif
    </div>
    @endif

    <div class="mod-card">
        <div class="mod-grad">
            <div><h2>{{ __('PAY SLIP') }}</h2><span style="font-size:10px; opacity:0.8;">{{ $companyName }}</span></div>
            <div style="text-align:right;"><span>{{ $slipLabel }}</span><br><img src="{{ $logoUrl }}" alt="logo" style="height:32px; margin-top:4px;"></div>
        </div>
        <div class="mod-body">
            {{-- Employee + Payment Grid --}}
            @if($_showEmployeeDetails || $_showPaymentDetails)
            <div class="mod-grid" style="margin-bottom:10px;">
                @if($_showEmployeeDetails)
                <div>
                    <div class="mod-grid-title">{{ __('Employee') }}</div>
                    <table class="mod-table">
                        @if($_showName)
                        <tr><td class="mod-lbl">{{ __('Name') }}</td><td class="mod-val" style="color:{{ $_themeColor }};">{{ $employee->name }}</td></tr>
                        @endif
                        @if($_showDesignation)
                        <tr><td class="mod-lbl">{{ __('Designation') }}</td><td class="mod-val">{{ optional($employee->designation)->name ?? '—' }}</td></tr>
                        @endif
                        @if($_showEmployeeId)
                        <tr><td class="mod-lbl">{{ __('Employee ID') }}</td><td class="mod-val">{{ $employee->employee_id ?? '—' }}</td></tr>
                        @endif
                        @if($_showDepartment)
                        <tr><td class="mod-lbl">{{ __('Department') }}</td><td class="mod-val">{{ optional($employee->department)->name ?? '—' }}</td></tr>
                        @endif
                        @if($_showDateOfJoining)
                        <tr><td class="mod-lbl">{{ __('DOJ') }}</td><td class="mod-val">{{ $employee->company_doj ?? '—' }}</td></tr>
                        @endif
                        @if($_isUkRequest && $_showNiNumber)
                        <tr><td class="mod-lbl">{{ __('NI Number') }}</td><td class="mod-val">{{ $niNumber }}</td></tr>
                        @endif
                        @if($_isUkRequest && $_showTaxCode)
                        <tr><td class="mod-lbl">{{ __('Tax Code') }}</td><td class="mod-val">{{ $taxCode }}</td></tr>
                        @endif
                    </table>
                </div>
                @endif
                @if($_showPaymentDetails)
                <div>
                    <div class="mod-grid-title">{{ __('Payment') }}</div>
                    <table class="mod-table">
                        @if($_showBankName)
                        <tr><td class="mod-lbl">{{ __('Bank Name') }}</td><td class="mod-val">{{ $employee->bank_name ?? '—' }}</td></tr>
                        @endif
                        @if($_showAccountNo)
                        <tr><td class="mod-lbl">{{ __('Account No.') }}</td><td class="mod-val">{{ $employee->account_number ?? '—' }}</td></tr>
                        @endif
                        @if($_showBankCode)
                        <tr><td class="mod-lbl">{{ __(\App\Models\Utility::bankCodeLabel()) }}</td><td class="mod-val">{{ $employee->bank_identifier_code ?? '—' }}</td></tr>
                        @endif
                        @if($_showAccountHolder)
                        <tr><td class="mod-lbl">{{ __('Account Holder') }}</td><td class="mod-val">{{ $employee->account_holder_name ?? '—' }}</td></tr>
                        @endif
                        @if($_showPayPeriod)
                        <tr><td class="mod-lbl">{{ __('Pay Period') }}</td><td class="mod-val">{{ $slipLabel }}</td></tr>
                        @endif
                        <tr><td class="mod-lbl">{{ __('Status') }}</td><td class="mod-val"><span class="mod-badge {{ $isPaid ? 'mod-paid' : 'mod-unpaid' }}">{{ $isPaid ? __('Paid') : __('Unpaid') }}</span></td></tr>
                    </table>
                </div>
                @endif
            </div>
            @endif

            {{-- Monthly: Attendance + Leave Grid | Hourly: Attendance only --}}
            @if(!$isHourly)
            <div class="mod-grid" style="margin-bottom:10px;">
                <div>
                    <div class="mod-grid-title">{{ __('Days & Work') }}</div>
                    <table class="mod-table">
                        <tr><td class="mod-lbl">{{ __('Working Days') }}</td><td class="mod-val" style="color:{{ $_themeColor }};">{{ $officeDays }}</td></tr>
                        <tr><td class="mod-lbl">{{ __('Present') }}</td><td class="mod-val" style="color:#198754;">{{ $presentDaysFmt }}</td></tr>
                        <tr><td class="mod-lbl">{{ __('Leave') }}</td><td class="mod-val" style="color:#e07900;">{{ $approvedLeaves }}</td></tr>
                        <tr><td class="mod-lbl">{{ __('Absent') }}</td><td class="mod-val" style="color:#dc3545;">{{ $absentDaysFmt }}</td></tr>
                        @if($extraDays>0)
                        <tr><td class="mod-lbl" style="color:#856404;">{{ __('Extra / OT') }}</td><td class="mod-val" style="color:#856404;font-weight:bold;">+{{ $extraDaysFmt }}</td></tr>
                        @endif
                        <tr><td class="mod-lbl">{{ __('Total Hours') }}</td><td class="mod-val">{{ $totalWorkHours }} hrs</td></tr>
                    </table>
                </div>
                <div>
                    <div class="mod-grid-title">{{ __('Leave Summary') }}</div>
                    <table class="mod-table">
                        <tr><td class="mod-lbl" style="width:40%;font-weight:700;">{{ __('This Month') }}</td><td style="width:20%;text-align:right;font-weight:700;">{{ $approvedLeaves }}</td><td class="mod-lbl" style="width:40%;font-weight:700;">{{ __('Overall') }}</td></tr>
                        <tr><td class="mod-lbl">{{ __('Paid') }}</td><td style="text-align:right;color:#198754;">{{ $paidLeaveDays }}</td><td class="mod-lbl">{{ __('Allocated') }}: <strong>{{ $totalLeaveAlloc }}</strong></td></tr>
                        <tr><td class="mod-lbl">{{ __('Unpaid') }}</td><td style="text-align:right;color:#dc3545;">{{ $unpaidLeaveDays }}</td><td class="mod-lbl">{{ __('Remaining') }}: <strong style="color:#0056b3;">{{ $remainingLeaves }}</strong></td></tr>
                    </table>
                </div>
            </div>
            @else
            {{-- Hourly: compact attendance row --}}
            <div class="mod-grid" style="margin-bottom:10px;">
                <div>
                    <div class="mod-grid-title">{{ __('Attendance') }}</div>
                    <table class="mod-table">
                        <tr><td class="mod-lbl">{{ __('Working Days') }}</td><td class="mod-val" style="color:{{ $_themeColor }};">{{ $officeDays }}</td></tr>
                        <tr><td class="mod-lbl">{{ __('Present') }}</td><td class="mod-val" style="color:#198754;">{{ $presentDaysFmt }}</td></tr>
                        <tr><td class="mod-lbl">{{ __('Absent') }}</td><td class="mod-val" style="color:#dc3545;">{{ $absentDaysFmt }}</td></tr>
                        <tr><td class="mod-lbl">{{ __('Total Hours') }}</td><td class="mod-val">{{ $totalWorkHours }} hrs</td></tr>
                    </table>
                </div>
                <div>
                    <div class="mod-grid-title">{{ __('Hourly Rate') }}</div>
                    <table class="mod-table">
                        <tr><td class="mod-lbl">{{ __('Hourly Rate') }} <b style="color:{{ $_themeColor }};">(A)</b></td><td class="mod-val" style="color:{{ $_themeColor }};">{{ $_fmtMoney($perHourSalary) }}</td></tr>
                        <tr><td class="mod-lbl">{{ __('Hours Worked') }} <b style="color:#198754;">(B)</b></td><td class="mod-val" style="color:#198754;">{{ $totalWorkHours }} hrs</td></tr>
                        <tr style="background:{{ $_themeColor }}10;"><td class="mod-lbl"><strong>{{ __('Gross Earned') }} (A &times; B)</strong></td><td class="mod-val" style="color:{{ $_themeColor }};"><strong>{{ $_fmtMoney($_storedSalary) }}</strong></td></tr>
                    </table>
                </div>
            </div>
            @endif

            {{-- Salary Details --}}
            <div class="mod-grid" style="margin-bottom:10px;">
                <div>
                    <div class="mod-grid-title">{{ __('Salary') }}</div>
                    <table class="mod-table">
                        <tr><td class="mod-lbl">{{ __('Payslip Type') }}</td><td class="mod-val">{{ $payslipTypeName }}</td></tr>
                        @if($isHourly)
                        <tr><td class="mod-lbl">{{ __('Hourly Rate') }}</td><td class="mod-val" style="color:{{ $_themeColor }};">{{ $_fmtMoney($perHourSalary) }}</td></tr>
                        <tr><td class="mod-lbl">{{ __('Hours') }}</td><td class="mod-val">{{ $totalWorkHours }} hrs</td></tr>
                        <tr><td class="mod-lbl">{{ __('Gross Earned') }}</td><td class="mod-val" style="color:{{ $_themeColor }};">{{ $_fmtMoney($_storedSalary) }}</td></tr>
                        @else
                        <tr><td class="mod-lbl">{{ __('Gross Salary') }}</td><td class="mod-val" style="color:{{ $_themeColor }};">{{ $_fmtMoney($_storedSalary) }}</td></tr>
                        <tr><td class="mod-lbl">{{ __('Per Day Rate') }}</td><td class="mod-val">{{ $_fmtMoney($perDaySalary) }}</td></tr>
                        <tr><td class="mod-lbl">{{ __('Per Hour Rate') }}</td><td class="mod-val">{{ $_fmtMoney($perHourSalary) }}</td></tr>
                        <tr><td class="mod-lbl">{{ __('Days Paid') }}</td><td class="mod-val">{{ $daysPaid == floor($daysPaid) ? (int) $daysPaid : $daysPaid }} @if($paidLeaveDays>0)<span style="font-size:9px;color:#198754;">(+{{ $paidLeaveDays }})</span>@endif</td></tr>
                        @endif
                    </table>
                </div>
                <div>
                    <div class="mod-grid-title">{{ __('Payslip') }}</div>
                    <table class="mod-table">
                        <tr><td class="mod-lbl">{{ __('Pay Type') }}</td><td class="mod-val">{{ $payslipTypeName }}</td></tr>
                        <tr><td class="mod-lbl">{{ __('Pay Period') }}</td><td class="mod-val">{{ $slipLabel }}</td></tr>
                        <tr><td class="mod-lbl">{{ __('Gross Salary') }}</td><td class="mod-val" style="color:{{ $_themeColor }};">{{ $_fmtMoney($_storedSalary) }}</td></tr>
                        <tr><td class="mod-lbl">{{ __('Total Deductions') }}</td><td class="mod-val" style="color:#dc3545;">{{ $_fmtMoney($totalDeductions) }}</td></tr>
                    </table>
                </div>
            </div>

            {{-- Earnings & Deductions --}}
            <div class="mod-grid" style="margin-bottom:8px;">
                <div>
                    <div class="mod-grid-title">{{ __('Earnings') }}</div>
                    <table class="mod-table">
                        @foreach($earRows as $er)
                        <tr><td class="mod-lbl">{{ $er['label'] }}</td><td class="mod-val">{{ $er['amount']!==null ? $_fmtMoney($er['amount']) : '' }}</td></tr>
                        @endforeach
                        <tr class="mod-total"><td class="mod-lbl">{{ __('Total Earnings') }}</td><td class="mod-val">{{ $_fmtMoney($totalEarnings) }}</td></tr>
                    </table>
                </div>
                <div>
                    <div class="mod-grid-title">{{ __('Deductions') }}</div>
                    <table class="mod-table">
                        @forelse($dedRows as $dr)
                        <tr><td class="mod-lbl">{{ $dr['label'] }}</td><td class="mod-val">{{ $dr['amount']!==null ? $_fmtMoney($dr['amount']) : '—' }}</td></tr>
                        @empty
                        <tr><td class="mod-lbl" style="color:#aaa;font-style:italic;">{{ __('No deductions') }}</td><td></td></tr>
                        @endforelse
                        <tr class="mod-total"><td class="mod-lbl">{{ __('Total Deductions') }}</td><td class="mod-val">{{ $_fmtMoney($totalDeductions) }}</td></tr>
                    </table>
                </div>
            </div>

            {{-- Net Pay --}}
            <div class="mod-net">
                <label>{{ __('Net Pay') }}</label>
                <span>{{ $_fmtMoney($netSalary) }}</span>
            </div>

            {{-- Stamp --}}
            <div class="mod-stamp">
                <div>{{ __('Authorized Digital Stamp') }} &mdash; {{ $companyName }}</div>
                <img src="{{ $_stampUrl ?? '' }}" alt="Stamp" style="{{ $_stampUrl ? 'width:56px;height:56px;border-radius:50%;object-fit:contain;border:2px solid '.$_themeColor.'44;' : 'display:none;' }}">
                @if(!$_stampUrl)<div class="mod-stamp-ph">{{ __('Stamp') }}</div>@endif
            </div>

            {{-- Signatures --}}
            @if($_showSignatures)
            <div class="mod-sig">
                <div class="mod-sig-item"><div class="mod-sig-line"></div><div class="mod-sig-lbl">{{ __('Employee Signature') }}</div></div>
                <div class="mod-sig-item"><div class="mod-sig-line"></div><div class="mod-sig-lbl">{{ __('Authorized Signatory') }}</div></div>
            </div>
            @endif
        </div>
        @if($_showFooter)
        <div style="background:{{ $_themeColor }}; color:#fff; text-align:center; padding:6px 18px; font-size:9px; line-height:1.6;">
            @if($companyPhone)<strong>{{ __('Tel') }}:</strong> {{ $companyPhone }} &nbsp;|&nbsp; @endif
            @if($companyWeb)<strong>{{ __('Web') }}:</strong> {{ $companyWeb }} &nbsp;|&nbsp; @endif
            @if($companyEmail)<strong>{{ __('Email') }}:</strong> {{ $companyEmail }} @endif
        </div>
        @endif
    </div>
</div>
<script>
window.addEventListener('message',function(e){if(e.data&&e.data.type==='payslip-stamp'&&e.data.dataUrl){var img=document.querySelector('.mod-stamp img');if(img){img.src=e.data.dataUrl;img.style.display='';}var ph=document.querySelector('.mod-stamp-ph');if(ph)ph.style.display='none';}});

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

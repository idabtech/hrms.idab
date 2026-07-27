@php
    // All template data is pre-computed from
    // App\Http\Controllers\PaySlipController::preparePayslipTemplateData().
@endphp
<style>
.elg-wrap { font-family:'Candara','Trebuchet MS','Segoe UI',sans-serif; font-size:11px; color:#3a3a3a; max-width:800px; margin:0 auto; background:linear-gradient(135deg,#faf6f0,#f5f0ea); padding:16px; }
.elg-wrap * { box-sizing:border-box; }
.elg-card { background:rgba(255,255,255,0.95); border:1px solid #d4c9b8; backdrop-filter:blur(4px); }
.elg-top { height:3px; background:linear-gradient(90deg,{{ $_themeColor }}66,{{ $_themeColor }},{{ $_themeColor }}66); }
.elg-header { padding:14px 20px 10px; border-bottom:1px solid #e8ddd0; display:flex; justify-content:space-between; align-items:center; }
.elg-header h2 { font-size:17px; font-weight:300; color:#5a4a3a; margin:0; letter-spacing:4px; text-transform:uppercase; }
.elg-header span { font-size:10px; color:#b8a892; letter-spacing:1px; }
.elg-header img { height:34px; opacity:0.85; }
.elg-body { padding:12px 20px 14px; }
.elg-grid { display:flex; gap:14px; margin-bottom:8px; }
.elg-grid > div { flex:1; background:rgba(232,221,208,0.2); border:1px solid #e8ddd0; padding:8px 10px; }
.elg-title { font-size:9px; font-weight:600; text-transform:uppercase; letter-spacing:2px; color:#b8a892; border-bottom:1px solid #e8ddd0; padding-bottom:3px; margin-bottom:5px; }
.elg-table { width:100%; border-collapse:collapse; }
.elg-table td { padding:2px 3px; font-size:10px; border-bottom:1px solid #f0ebe0; }
.elg-lbl { color:#9a8a78; width:50%; }
.elg-val { text-align:right; font-weight:600; color:#5a4a3a; }
.elg-total td { font-weight:700; color:{{ $_themeColor }}; border-top:1.5px solid {{ $_themeColor }}66 !important; padding-top:4px; font-size:10.5px; }
.elg-net { background:linear-gradient(135deg,#5a4a3a,#7a6a58); color:#f5ece0; padding:10px 18px; display:flex; justify-content:space-between; align-items:center; margin:7px 0; }
.elg-net label { font-size:12px; font-weight:300; letter-spacing:2px; text-transform:uppercase; }
.elg-net span { font-size:17px; font-weight:700; color:#fff; }
.elg-stamp { display:flex; justify-content:space-between; align-items:center; padding:5px 0; border-top:1px solid #e8ddd0; margin-top:5px; font-size:9px; color:#b8a892; }
.elg-stamp img { width:50px; height:50px; border-radius:50%; object-fit:contain; border:1.5px solid {{ $_themeColor }}44; }
.elg-stamp-ph { width:50px; height:50px; border-radius:50%; border:1.5px dashed #d4c9b8; display:flex; align-items:center; justify-content:center; font-size:6px; color:#d4c9b8; text-align:center; }
.elg-sig { display:flex; justify-content:space-between; padding:8px 28px 2px; }
.elg-sig-item { text-align:center; min-width:100px; }
.elg-sig-line { border-top:1px solid #d4c9b8; width:80px; margin:8px auto 2px; }
.elg-sig-lbl { font-size:8px; color:#c8b8a8; letter-spacing:0.5px; }
.elg-badge { display:inline-block; padding:2px 10px; border-radius:10px; font-size:9px; font-weight:600; }
.elg-paid { background:#e8f0e0; color:#5a7a4a; }
.elg-unpaid { background:#f8e0e0; color:#aa5a5a; }
.elg-footer { background:#f0ebe5; padding:6px 20px; font-size:8px; text-align:center; color:#b8a892; line-height:1.6; letter-spacing:0.5px; border-top:1px solid #e8ddd0; }
.elg-footer strong { color:#7a6a58; }
</style>
<div class="elg-wrap">

    @if(!($previewMode ?? false))
    <div style="display:flex; justify-content:flex-end; gap:8px; margin-bottom:10px;">
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

    <div class="elg-card">
        <div class="elg-top"></div>
        <div class="elg-header">
            <div><h2>{{ __('PAY SLIP') }}</h2><span>{{ $slipLabel }}</span></div>
            <img src="{{ $logoUrl }}" alt="logo">
        </div>
        <div class="elg-body">

            {{-- Employee + Payment Grid --}}
            @if($_showEmployeeDetails || $_showPaymentDetails)
            <div class="elg-grid">
                @if($_showEmployeeDetails)
                <div>
                    <div class="elg-title">{{ __('Employee') }}</div>
                    <table class="elg-table">
                        @if($_showName)
                        <tr><td class="elg-lbl">{{ __('Name') }}</td><td class="elg-val" style="color:{{ $_themeColor }};">{{ $employee->name }}</td></tr>
                        @endif
                        @if($_showDesignation)
                        <tr><td class="elg-lbl">{{ __('Designation') }}</td><td class="elg-val">{{ optional($employee->designation)->name ?? '—' }}</td></tr>
                        @endif
                        @if($_showEmployeeId)
                        <tr><td class="elg-lbl">{{ __('Employee ID') }}</td><td class="elg-val">{{ $employee->employee_id ?? '—' }}</td></tr>
                        @endif
                        @if($_showDepartment)
                        <tr><td class="elg-lbl">{{ __('Department') }}</td><td class="elg-val">{{ optional($employee->department)->name ?? '—' }}</td></tr>
                        @endif
                        @if($_showDateOfJoining)
                        <tr><td class="elg-lbl">{{ __('DOJ') }}</td><td class="elg-val">{{ $employee->company_doj ?? '—' }}</td></tr>
                        @endif
                        @if($_isUkRequest && $_showNiNumber)
                        <tr><td class="elg-lbl">{{ __('NI Number') }}</td><td class="elg-val">{{ $niNumber }}</td></tr>
                        @endif
                        @if($_isUkRequest && $_showTaxCode)
                        <tr><td class="elg-lbl">{{ __('Tax Code') }}</td><td class="elg-val">{{ $taxCode }}</td></tr>
                        @endif
                    </table>
                </div>
                @endif
                @if($_showPaymentDetails)
                <div>
                    <div class="elg-title">{{ __('Payment') }}</div>
                    <table class="elg-table">
                        @if($_showBankName)
                        <tr><td class="elg-lbl">{{ __('Bank Name') }}</td><td class="elg-val">{{ $employee->bank_name ?? '—' }}</td></tr>
                        @endif
                        @if($_showAccountNo)
                        <tr><td class="elg-lbl">{{ __('Account No.') }}</td><td class="elg-val">{{ $employee->account_number ?? '—' }}</td></tr>
                        @endif
                        @if($_showBankCode)
                        <tr><td class="elg-lbl">{{ __(\App\Models\Utility::bankCodeLabel()) }}</td><td class="elg-val">{{ $employee->bank_identifier_code ?? '—' }}</td></tr>
                        @endif
                        @if($_showAccountHolder)
                        <tr><td class="elg-lbl">{{ __('Account Holder') }}</td><td class="elg-val">{{ $employee->account_holder_name ?? '—' }}</td></tr>
                        @endif
                        @if($_showPayPeriod)
                        <tr><td class="elg-lbl">{{ __('Pay Period') }}</td><td class="elg-val">{{ $slipLabel }}</td></tr>
                        @endif
                        <tr><td class="elg-lbl">{{ __('Status') }}</td><td class="elg-val"><span class="elg-badge {{ $isPaid ? 'elg-paid' : 'elg-unpaid' }}">{{ $isPaid ? __('Paid') : __('Unpaid') }}</span></td></tr>
                    </table>
                </div>
                @endif
            </div>
            @endif

            {{-- Monthly/Hourly conditional --}}
            @if(!$isHourly)
            <div class="elg-grid">
                <div>
                    <div class="elg-title">{{ __('Days & Work') }}</div>
                    <table class="elg-table">
                        <tr><td class="elg-lbl">{{ __('Working Days') }}</td><td class="elg-val" style="color:{{ $_themeColor }};">{{ $officeDays }}</td></tr>
                        <tr><td class="elg-lbl">{{ __('Present') }}</td><td class="elg-val" style="color:#5a7a4a;">{{ $presentDaysFmt }}</td></tr>
                        <tr><td class="elg-lbl">{{ __('Leave') }}</td><td class="elg-val" style="color:#c8a050;">{{ $approvedLeaves }}</td></tr>
                        <tr><td class="elg-lbl">{{ __('Absent') }}</td><td class="elg-val" style="color:#aa5a5a;">{{ $absentDaysFmt }}</td></tr>
                        @if($extraDays>0)
                        <tr><td class="elg-lbl" style="color:#856404;">{{ __('Extra / OT') }}</td><td class="elg-val" style="color:#856404;">+{{ $extraDaysFmt }}</td></tr>
                        @endif
                        <tr><td class="elg-lbl">{{ __('Hours') }}</td><td class="elg-val">{{ $totalWorkHours }} hrs</td></tr>
                    </table>
                </div>
                <div>
                    <div class="elg-title">{{ __('Leave Summary') }}</div>
                    <table class="elg-table">
                        <tr><td class="elg-lbl" style="width:35%;font-weight:700;">{{ __('This Month') }}</td><td style="width:15%;text-align:right;font-weight:700;">{{ $approvedLeaves }}</td><td class="elg-lbl" style="width:50%;font-weight:700;">{{ __('Overall') }}</td></tr>
                        <tr><td class="elg-lbl">{{ __('Paid') }}</td><td style="text-align:right;color:#5a7a4a;">{{ $paidLeaveDays }}</td><td class="elg-lbl">{{ __('Allocated') }}: <strong>{{ $totalLeaveAlloc }}</strong></td></tr>
                        <tr><td class="elg-lbl">{{ __('Unpaid') }}</td><td style="text-align:right;color:#aa5a5a;">{{ $unpaidLeaveDays }}</td><td class="elg-lbl">{{ __('Remaining') }}: <strong style="color:#0056b3;">{{ $remainingLeaves }}</strong></td></tr>
                    </table>
                </div>
            </div>
            @else
            <div class="elg-grid">
                <div>
                    <div class="elg-title">{{ __('Attendance') }}</div>
                    <table class="elg-table">
                        <tr><td class="elg-lbl">{{ __('Working Days') }}</td><td class="elg-val" style="color:{{ $_themeColor }};">{{ $officeDays }}</td></tr>
                        <tr><td class="elg-lbl">{{ __('Present') }}</td><td class="elg-val" style="color:#5a7a4a;">{{ $presentDaysFmt }}</td></tr>
                        <tr><td class="elg-lbl">{{ __('Absent') }}</td><td class="elg-val" style="color:#aa5a5a;">{{ $absentDaysFmt }}</td></tr>
                        <tr><td class="elg-lbl">{{ __('Hours') }}</td><td class="elg-val">{{ $totalWorkHours }} hrs</td></tr>
                    </table>
                </div>
                <div>
                    <div class="elg-title">{{ __('Hourly Rate') }}</div>
                    <table class="elg-table">
                        <tr><td class="elg-lbl">{{ __('Rate') }} <b style="color:{{ $_themeColor }};">(A)</b></td><td class="elg-val" style="color:{{ $_themeColor }};">{{ $_fmtMoney($perHourSalary) }}</td></tr>
                        <tr><td class="elg-lbl">{{ __('Hours') }} <b style="color:#5a7a4a;">(B)</b></td><td class="elg-val" style="color:#5a7a4a;">{{ $totalWorkHours }} hrs</td></tr>
                        <tr style="background:rgba(232,221,208,0.3);"><td class="elg-lbl"><strong>{{ __('Gross Earned') }} (A &times; B)</strong></td><td class="elg-val" style="color:{{ $_themeColor }};"><strong>{{ $_fmtMoney($_storedSalary) }}</strong></td></tr>
                    </table>
                </div>
            </div>
            @endif

            {{-- Salary --}}
            <div class="elg-grid">
                <div>
                    <div class="elg-title">{{ __('Salary') }}</div>
                    <table class="elg-table">
                        <tr><td class="elg-lbl">{{ __('Payslip Type') }}</td><td class="elg-val">{{ $payslipTypeName }}</td></tr>
                        @if($isHourly)
                        <tr><td class="elg-lbl">{{ __('Hourly Rate') }}</td><td class="elg-val" style="color:{{ $_themeColor }};">{{ $_fmtMoney($perHourSalary) }}</td></tr>
                        <tr><td class="elg-lbl">{{ __('Hours') }}</td><td class="elg-val">{{ $totalWorkHours }} hrs</td></tr>
                        <tr><td class="elg-lbl"><strong>{{ __('Gross Earned') }}</strong></td><td class="elg-val" style="color:{{ $_themeColor }};"><strong>{{ $_fmtMoney($_storedSalary) }}</strong></td></tr>
                        @else
                        <tr><td class="elg-lbl">{{ __('Gross Salary') }}</td><td class="elg-val" style="color:{{ $_themeColor }};">{{ $_fmtMoney($_storedSalary) }}</td></tr>
                        <tr><td class="elg-lbl">{{ __('Per Day') }}</td><td class="elg-val">{{ $_fmtMoney($perDaySalary) }}</td></tr>
                        <tr><td class="elg-lbl">{{ __('Per Hour') }}</td><td class="elg-val">{{ $_fmtMoney($perHourSalary) }}</td></tr>
                        <tr><td class="elg-lbl">{{ __('Days Paid') }}</td><td class="elg-val">{{ $daysPaid == floor($daysPaid) ? (int) $daysPaid : $daysPaid }} @if($paidLeaveDays>0)<span style="font-size:8px;color:#5a7a4a;">(+{{ $paidLeaveDays }})</span>@endif</td></tr>
                        @endif
                    </table>
                </div>
                <div>
                    <div class="elg-title">{{ __('Summary') }}</div>
                    <table class="elg-table">
                        <tr><td class="elg-lbl">{{ __('Pay Period') }}</td><td class="elg-val">{{ $slipLabel }}</td></tr>
                        <tr><td class="elg-lbl">{{ __('Total Earnings') }}</td><td class="elg-val" style="color:{{ $_themeColor }};">{{ $_fmtMoney($totalEarnings) }}</td></tr>
                        <tr><td class="elg-lbl">{{ __('Total Deductions') }}</td><td class="elg-val" style="color:#aa5a5a;">{{ $_fmtMoney($totalDeductions) }}</td></tr>
                        <tr><td class="elg-lbl">{{ __('Status') }}</td><td class="elg-val"><span class="elg-badge {{ $isPaid ? 'elg-paid' : 'elg-unpaid' }}">{{ $isPaid ? __('Paid') : __('Unpaid') }}</span></td></tr>
                    </table>
                </div>
            </div>

            {{-- Earnings & Deductions --}}
            <div class="elg-grid" style="margin-bottom:7px;">
                <div>
                    <div class="elg-title">{{ __('Earnings') }}</div>
                    <table class="elg-table">
                        @foreach($earRows as $er)
                        <tr><td class="elg-lbl">{{ $er['label'] }}</td><td class="elg-val">{{ $er['amount']!==null ? $_fmtMoney($er['amount']) : '' }}</td></tr>
                        @endforeach
                        <tr class="elg-total"><td class="elg-lbl">{{ __('Total Earnings') }}</td><td class="elg-val">{{ $_fmtMoney($totalEarnings) }}</td></tr>
                    </table>
                </div>
                <div>
                    <div class="elg-title">{{ __('Deductions') }}</div>
                    <table class="elg-table">
                        @forelse($dedRows as $dr)
                        <tr><td class="elg-lbl">{{ $dr['label'] }}</td><td class="elg-val">{{ $dr['amount']!==null ? $_fmtMoney($dr['amount']) : '—' }}</td></tr>
                        @empty
                        <tr><td class="elg-lbl" style="color:#c8b8a8;font-style:italic;">{{ __('No deductions') }}</td><td></td></tr>
                        @endforelse
                        <tr class="elg-total"><td class="elg-lbl">{{ __('Total Deductions') }}</td><td class="elg-val">{{ $_fmtMoney($totalDeductions) }}</td></tr>
                    </table>
                </div>
            </div>

            {{-- Net Pay --}}
            <div class="elg-net">
                <label>{{ __('Net Pay') }}</label>
                <span>{{ $_fmtMoney($netSalary) }}</span>
            </div>

            {{-- Stamp --}}
            <div class="elg-stamp">
                <div>{{ __('Authorized Digital Stamp') }}<br><span style="font-size:8px;color:#d4c9b8;">{{ $companyName }}</span></div>
                <img src="{{ $_stampUrl ?? '' }}" alt="Stamp" style="{{ $_stampUrl ? 'width:50px;height:50px;border-radius:50%;object-fit:contain;border:1.5px solid '.$_themeColor.'44;' : 'display:none;' }}">
                @if(!$_stampUrl)<div class="elg-stamp-ph">{{ __('Stamp') }}</div>@endif
            </div>

            {{-- Signatures --}}
            @if($_showSignatures)
            <div class="elg-sig">
                <div class="elg-sig-item"><div class="elg-sig-line"></div><div class="elg-sig-lbl">{{ __('Employee Signature') }}</div></div>
                <div class="elg-sig-item"><div class="elg-sig-line"></div><div class="elg-sig-lbl">{{ __('Authorized Signatory') }}</div></div>
            </div>
            @endif
        </div>
        @if($_showFooter)
        <div class="elg-footer">
            @if($companyPhone)<strong>{{ __('Tel') }}:</strong> {{ $companyPhone }} &nbsp;|&nbsp; @endif
            @if($companyWeb)<strong>{{ __('Web') }}:</strong> {{ $companyWeb }} &nbsp;|&nbsp; @endif
            @if($companyEmail)<strong>{{ __('Email') }}:</strong> {{ $companyEmail }} @endif
        </div>
        @endif
    </div>
</div>
<script>
window.addEventListener('message',function(e){if(e.data&&e.data.type==='payslip-stamp'&&e.data.dataUrl){var img=document.querySelector('.elg-stamp img');if(img){img.src=e.data.dataUrl;img.style.display='';}var ph=document.querySelector('.elg-stamp-ph');if(ph)ph.style.display='none';}});

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

@php
    // All template data is pre-computed from
    // App\Http\Controllers\PaySlipController::preparePayslipTemplateData().
@endphp
<style>
.exe-wrap { font-family:'Palatino','Georgia','Times New Roman',serif; font-size:11px; color:#1c1c1c; max-width:800px; margin:0 auto; background:#1a1a2e; padding:16px; }
.exe-wrap * { box-sizing:border-box; }
.exe-card { background:#fff; border:1px solid #c9a862; position:relative; }
.exe-accent { height:4px; background:linear-gradient(90deg,#1a1a2e,#c9a862,#1a1a2e); }
.exe-header { padding:16px 22px 12px; border-bottom:1px solid #c9a862; display:flex; justify-content:space-between; align-items:center; background:linear-gradient(135deg,#faf8f3,#fff); }
.exe-header h2 { font-family:'Palatino',serif; font-size:20px; color:#1a1a2e; margin:0; letter-spacing:3px; }
.exe-header span { font-size:10px; color:#c9a862; letter-spacing:1px; }
.exe-header img { height:36px; }
.exe-body { padding:14px 22px 16px; }
.exe-sec-title { font-family:'Palatino',serif; font-weight:700; font-size:11px; color:#1a1a2e; border-bottom:2px solid #c9a862; padding-bottom:3px; margin-bottom:6px; text-transform:uppercase; letter-spacing:1px; }
.exe-grid { display:flex; gap:14px; }
.exe-grid > div { flex:1; background:#fcfaf5; border:1px solid #e8dcc8; padding:8px 10px; }
.exe-table { width:100%; border-collapse:collapse; }
.exe-table td { padding:3px 4px; font-size:10px; border-bottom:1px solid #f0ece0; }
.exe-lbl { color:#888; width:50%; font-style:italic; font-size:9px; }
.exe-val { text-align:right; font-weight:600; color:#333; }
.exe-mono { color:#1a1a2e; font-weight:700; }
.exe-total td { font-weight:700; color:#1a1a2e; border-top:2px solid #c9a862 !important; padding-top:5px; font-size:11px; }
.exe-net { background:linear-gradient(135deg,#1a1a2e,#2d2d5e); color:#c9a862; padding:12px 20px; display:flex; justify-content:space-between; align-items:center; margin:8px 0; border:1px solid #c9a862; }
.exe-net label { font-family:'Palatino',serif; font-size:14px; font-weight:700; letter-spacing:1px; }
.exe-net span { font-size:19px; font-weight:700; color:#fff; }
.exe-stamp { display:flex; justify-content:space-between; align-items:center; padding:6px 0; border-top:1px solid #e8dcc8; margin-top:6px; }
.exe-stamp img { width:56px; height:56px; border-radius:50%; object-fit:contain; border:2px solid #c9a86266; }
.exe-stamp-ph { width:56px; height:56px; border-radius:50%; border:2px dashed #c9a86266; display:flex; align-items:center; justify-content:center; font-size:7px; color:#c9a86288; text-align:center; padding:4px; }
.exe-sig { display:flex; justify-content:space-between; padding:10px 30px 4px; }
.exe-sig-item { text-align:center; min-width:120px; }
.exe-sig-line { border-top:1.5px solid #c9a862; width:110px; margin:10px auto 3px; }
.exe-sig-lbl { font-size:9px; color:#c9a862; letter-spacing:0.5px; }
.exe-badge { display:inline-block; padding:1px 8px; border-radius:8px; font-size:9px; font-weight:600; }
.exe-paid { background:#d4edda; color:#155724; }
.exe-unpaid { background:#f8d7da; color:#721c24; }
.exe-footer { background:#1a1a2e; padding:8px 22px; font-size:9px; text-align:center; color:#c9a862; line-height:1.7; border-top:1px solid #c9a862; }
.exe-footer strong { color:#fff; }
</style>
<div class="exe-wrap">
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

    <div class="exe-card">
        <div class="exe-accent"></div>
        <div class="exe-header">
            <div>
                <h2>{{ __('PAY SLIP') }}</h2>
                <span>{{ $slipLabel }}</span>
            </div>
            <img src="{{ $logoUrl }}" alt="logo">
        </div>
        <div class="exe-body">

            {{-- Employee + Payment Grid --}}
            @if($_showEmployeeDetails || $_showPaymentDetails)
            <div class="exe-grid" style="margin-bottom:10px;">
                @if($_showEmployeeDetails)
                <div>
                    <div class="exe-sec-title">{{ __('Employee') }}</div>
                    <table class="exe-table">
                        @if($_showName)
                        <tr><td class="exe-lbl">{{ __('Name') }}</td><td class="exe-val exe-mono">{{ $employee->name }}</td></tr>
                        @endif
                        @if($_showDesignation)
                        <tr><td class="exe-lbl">{{ __('Designation') }}</td><td class="exe-val">{{ optional($employee->designation)->name ?? '—' }}</td></tr>
                        @endif
                        @if($_showEmployeeId)
                        <tr><td class="exe-lbl">{{ __('Employee ID') }}</td><td class="exe-val">{{ $employee->employee_id ?? '—' }}</td></tr>
                        @endif
                        @if($_showDepartment)
                        <tr><td class="exe-lbl">{{ __('Department') }}</td><td class="exe-val">{{ optional($employee->department)->name ?? '—' }}</td></tr>
                        @endif
                        @if($_showDateOfJoining)
                        <tr><td class="exe-lbl">{{ __('DOJ') }}</td><td class="exe-val">{{ $employee->company_doj ?? '—' }}</td></tr>
                        @endif
                        @if($_isUkRequest && $_showNiNumber)
                        <tr><td class="exe-lbl">{{ __('NI Number') }}</td><td class="exe-val">{{ $niNumber }}</td></tr>
                        @endif
                        @if($_isUkRequest && $_showTaxCode)
                        <tr><td class="exe-lbl">{{ __('Tax Code') }}</td><td class="exe-val">{{ $taxCode }}</td></tr>
                        @endif
                    </table>
                </div>
                @endif
                @if($_showPaymentDetails)
                <div>
                    <div class="exe-sec-title">{{ __('Payment') }}</div>
                    <table class="exe-table">
                        @if($_showBankName)
                        <tr><td class="exe-lbl">{{ __('Bank Name') }}</td><td class="exe-val">{{ $employee->bank_name ?? '—' }}</td></tr>
                        @endif
                        @if($_showAccountNo)
                        <tr><td class="exe-lbl">{{ __('Account No.') }}</td><td class="exe-val">{{ $employee->account_number ?? '—' }}</td></tr>
                        @endif
                        @if($_showBankCode)
                        <tr><td class="exe-lbl">{{ __(\App\Models\Utility::bankCodeLabel()) }}</td><td class="exe-val">{{ $employee->bank_identifier_code ?? '—' }}</td></tr>
                        @endif
                        @if($_showAccountHolder)
                        <tr><td class="exe-lbl">{{ __('Account Holder') }}</td><td class="exe-val">{{ $employee->account_holder_name ?? '—' }}</td></tr>
                        @endif
                        @if($_showPayPeriod)
                        <tr><td class="exe-lbl">{{ __('Pay Period') }}</td><td class="exe-val">{{ $slipLabel }}</td></tr>
                        @endif
                        <tr><td class="exe-lbl">{{ __('Status') }}</td><td class="exe-val"><span class="exe-badge {{ $isPaid ? 'exe-paid' : 'exe-unpaid' }}">{{ $isPaid ? __('Paid') : __('Unpaid') }}</span></td></tr>
                    </table>
                </div>
                @endif
            </div>
            @endif

            @if(!$isHourly)
            {{-- Monthly: Days & Work + Leave + Salary --}}
            <div class="exe-grid" style="margin-bottom:10px;">
                <div>
                    <div class="exe-sec-title">{{ __('Days & Work') }}</div>
                    <table class="exe-table">
                        <tr><td class="exe-lbl">{{ __('Working Days') }}</td><td class="exe-val exe-mono">{{ $officeDays }}</td></tr>
                        <tr><td class="exe-lbl">{{ __('Present') }}</td><td class="exe-val" style="color:#198754;">{{ $presentDaysFmt }}</td></tr>
                        <tr><td class="exe-lbl">{{ __('Leave') }}</td><td class="exe-val" style="color:#e07900;">{{ $approvedLeaves }}</td></tr>
                        <tr><td class="exe-lbl">{{ __('Absent') }}</td><td class="exe-val" style="color:#dc3545;">{{ $absentDaysFmt }}</td></tr>
                        @if($extraDays>0)
                        <tr><td class="exe-lbl" style="color:#856404;">{{ __('Extra / OT') }}</td><td class="exe-val" style="color:#856404;font-weight:700;">+{{ $extraDaysFmt }}</td></tr>
                        @endif
                        <tr><td class="exe-lbl">{{ __('Hours') }}</td><td class="exe-val">{{ $totalWorkHours }} hrs</td></tr>
                    </table>
                </div>
                <div>
                    <div class="exe-sec-title">{{ __('Leave') }}</div>
                    <table class="exe-table">
                        <tr><td class="exe-lbl" style="width:35%;font-weight:700;">{{ __('This Month') }}</td><td style="width:15%;text-align:right;font-weight:700;">{{ $approvedLeaves }}</td><td class="exe-lbl" style="width:50%;font-weight:700;">{{ __('Overall') }}</td></tr>
                        <tr><td class="exe-lbl">{{ __('Paid') }}</td><td style="text-align:right;color:#198754;">{{ $paidLeaveDays }}</td><td class="exe-lbl">{{ __('Allocated') }}: <strong>{{ $totalLeaveAlloc }}</strong></td></tr>
                        <tr><td class="exe-lbl">{{ __('Unpaid') }}</td><td style="text-align:right;color:#dc3545;">{{ $unpaidLeaveDays }}</td><td class="exe-lbl">{{ __('Remaining') }}: <strong style="color:#0056b3;">{{ $remainingLeaves }}</strong></td></tr>
                    </table>
                </div>
                <div>
                    <div class="exe-sec-title">{{ __('Salary') }}</div>
                    <table class="exe-table">
                        <tr><td class="exe-lbl">{{ __('Gross') }}</td><td class="exe-val exe-mono">{{ $_fmtMoney($_storedSalary) }}</td></tr>
                        <tr><td class="exe-lbl">{{ __('Per Day') }}</td><td class="exe-val">{{ $_fmtMoney($perDaySalary) }}</td></tr>
                        <tr><td class="exe-lbl">{{ __('Per Hour') }}</td><td class="exe-val">{{ $_fmtMoney($perHourSalary) }}</td></tr>
                        <tr><td class="exe-lbl">{{ __('Days Paid') }}</td><td class="exe-val" style="color:#0056b3;">{{ $daysPaid == floor($daysPaid) ? (int) $daysPaid : $daysPaid }} @if($paidLeaveDays>0)<span style="font-size:9px;color:#198754;">(+{{ $paidLeaveDays }})</span>@endif</td></tr>
                    </table>
                </div>
            </div>
            @else
            {{-- Hourly: Attendance + Hourly Rate + Payslip --}}
            <div class="exe-grid" style="margin-bottom:10px;">
                <div>
                    <div class="exe-sec-title">{{ __('Attendance') }}</div>
                    <table class="exe-table">
                        <tr><td class="exe-lbl">{{ __('Working Days') }}</td><td class="exe-val exe-mono">{{ $officeDays }}</td></tr>
                        <tr><td class="exe-lbl">{{ __('Present') }}</td><td class="exe-val" style="color:#198754;">{{ $presentDaysFmt }}</td></tr>
                        <tr><td class="exe-lbl">{{ __('Absent') }}</td><td class="exe-val" style="color:#dc3545;">{{ $absentDaysFmt }}</td></tr>
                        <tr><td class="exe-lbl">{{ __('Hours') }}</td><td class="exe-val">{{ $totalWorkHours }} hrs</td></tr>
                    </table>
                </div>
                <div>
                    <div class="exe-sec-title">{{ __('Hourly Rate') }}</div>
                    <table class="exe-table">
                        <tr><td class="exe-lbl">{{ __('Rate') }} <b style="color:#1a1a2e;">(A)</b></td><td class="exe-val exe-mono">{{ $_fmtMoney($perHourSalary) }}</td></tr>
                        <tr><td class="exe-lbl">{{ __('Hours') }} <b style="color:#198754;">(B)</b></td><td class="exe-val" style="color:#198754;">{{ $totalWorkHours }} hrs</td></tr>
                        <tr style="background:{{ $_themeColor }}10;"><td class="exe-lbl"><strong>{{ __('Gross Earned') }} (A &times; B)</strong></td><td class="exe-val exe-mono"><strong>{{ $_fmtMoney($_storedSalary) }}</strong></td></tr>
                    </table>
                </div>
                <div>
                    <div class="exe-sec-title">{{ __('Payslip') }}</div>
                    <table class="exe-table">
                        <tr><td class="exe-lbl">{{ $payslipTypeName }}</td><td class="exe-val">{{ $slipLabel }}</td></tr>
                        <tr><td class="exe-lbl">{{ __('Status') }}</td><td class="exe-val"><span class="exe-badge {{ $isPaid ? 'exe-paid' : 'exe-unpaid' }}">{{ $isPaid ? __('Paid') : __('Unpaid') }}</span></td></tr>
                    </table>
                </div>
            </div>
            @endif

            {{-- Earnings & Deductions --}}
            <div class="exe-grid" style="margin-bottom:8px;">
                <div>
                    <div class="exe-sec-title">{{ __('Earnings') }}</div>
                    <table class="exe-table">
                        @foreach($earRows as $er)
                        <tr><td class="exe-lbl">{{ $er['label'] }}</td><td class="exe-val">{{ $er['amount']!==null ? $_fmtMoney($er['amount']) : '' }}</td></tr>
                        @endforeach
                        <tr class="exe-total"><td class="exe-lbl">{{ __('Total Earnings') }}</td><td class="exe-val">{{ $_fmtMoney($totalEarnings) }}</td></tr>
                    </table>
                </div>
                <div>
                    <div class="exe-sec-title">{{ __('Deductions') }}</div>
                    <table class="exe-table">
                        @forelse($dedRows as $dr)
                        <tr><td class="exe-lbl">{{ $dr['label'] }}</td><td class="exe-val">{{ $dr['amount']!==null ? $_fmtMoney($dr['amount']) : '—' }}</td></tr>
                        @empty
                        <tr><td class="exe-lbl" style="color:#aaa;font-style:italic;">{{ __('No deductions') }}</td><td></td></tr>
                        @endforelse
                        <tr class="exe-total"><td class="exe-lbl">{{ __('Total Deductions') }}</td><td class="exe-val">{{ $_fmtMoney($totalDeductions) }}</td></tr>
                    </table>
                </div>
            </div>

            {{-- Net Pay --}}
            <div class="exe-net">
                <label>{{ __('Net Pay') }}</label>
                <span>{{ $_fmtMoney($netSalary) }}</span>
            </div>

            {{-- Stamp --}}
            <div class="exe-stamp">
                <div style="font-size:9px;color:#1a1a2e;font-weight:600;">{{ __('Authorized Digital Stamp') }}<br><span style="font-size:8px;color:#c9a862;">{{ $companyName }}</span></div>
                <img src="{{ $_stampUrl ?? '' }}" alt="Stamp" style="{{ $_stampUrl ? 'width:56px;height:56px;border-radius:50%;object-fit:contain;border:2px solid #c9a86266;' : 'display:none;' }}">
                @if(!$_stampUrl)<div class="exe-stamp-ph">{{ __('Stamp') }}</div>@endif
            </div>

            {{-- Signatures --}}
            @if($_showSignatures)
            <div class="exe-sig">
                <div class="exe-sig-item"><div class="exe-sig-line"></div><div class="exe-sig-lbl">{{ __('Employee Signature') }}</div></div>
                <div class="exe-sig-item"><div class="exe-sig-line"></div><div class="exe-sig-lbl">{{ __('Authorized Signatory') }}</div></div>
            </div>
            @endif
        </div>
        @if($_showFooter)
        <div class="exe-footer">
            @if($companyPhone)<strong>{{ __('Tel') }}:</strong> {{ $companyPhone }} &nbsp;|&nbsp; @endif
            @if($companyWeb)<strong>{{ __('Web') }}:</strong> {{ $companyWeb }} &nbsp;|&nbsp; @endif
            @if($companyEmail)<strong>{{ __('Email') }}:</strong> {{ $companyEmail }} @endif
        </div>
        @endif
    </div>
</div>
<script>
window.addEventListener('message',function(e){if(e.data&&e.data.type==='payslip-stamp'&&e.data.dataUrl){var img=document.querySelector('.exe-stamp img');if(img){img.src=e.data.dataUrl;img.style.display='';}var ph=document.querySelector('.exe-stamp-ph');if(ph)ph.style.display='none';}});

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

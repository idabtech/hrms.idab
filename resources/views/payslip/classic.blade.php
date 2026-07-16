@php
    // All template data is pre-computed from
    // App\Http\Controllers\PaySlipController::preparePayslipTemplateData().
@endphp
<style>
.cls-wrap { font-family:'Georgia','Times New Roman',serif; font-size:11px; color:#2c2c2c; max-width:800px; margin:0 auto; background:#fcf8f0; padding:16px; }
.cls-wrap * { box-sizing:border-box; }
.cls-card { background:#fff; border:2px solid #d4a843; box-shadow:0 2px 16px rgba(212,168,67,0.12); position:relative; }
.cls-border-top { height:4px; background:linear-gradient(90deg,{{ $_themeColor }},#d4a843,{{ $_themeColor }}); }
.cls-header { padding:14px 20px 10px; border-bottom:2px double #d4a843; display:flex; justify-content:space-between; align-items:center; }
.cls-header h2 { font-family:'Georgia',serif; font-size:18px; color:{{ $_themeColor }}; margin:0; letter-spacing:2px; text-transform:uppercase; text-shadow:1px 1px 0 rgba(212,168,67,0.2); }
.cls-header span { font-size:10px; color:#888; }
.cls-header img { height:34px; }
.cls-body { padding:14px 20px; }
.cls-section { margin-bottom:10px; }
.cls-section-title { font-family:'Georgia',serif; font-weight:700; font-size:11px; color:{{ $_themeColor }}; border-bottom:2px solid #d4a843; padding-bottom:3px; margin-bottom:6px; text-transform:uppercase; letter-spacing:1px; }
.cls-two-col { display:flex; gap:14px; }
.cls-two-col > div { flex:1; background:#fefcf7; border:1px solid #d4a84344; border-radius:4px; padding:8px 10px; }
.cls-table { width:100%; border-collapse:collapse; }
.cls-table td { padding:3px 4px; font-size:10px; border-bottom:1px solid #f0e8d5; }
.cls-lbl { color:#666; width:50%; font-style:italic; }
.cls-val { text-align:right; font-weight:600; }
.cls-total td { font-weight:700; color:{{ $_themeColor }}; border-top:2px solid #d4a843 !important; padding-top:5px; font-size:11px; }
.cls-net { background:linear-gradient(135deg,{{ $_themeColor }},#d4a843); color:#fff; padding:10px 18px; display:flex; justify-content:space-between; align-items:center; margin:8px 0; border-radius:4px; }
.cls-net label { font-family:'Georgia',serif; font-size:13px; font-weight:700; }
.cls-net span { font-size:18px; font-weight:700; }
.cls-stamp { display:flex; justify-content:space-between; align-items:center; padding:6px 0; border-top:1px solid #d4a84344; margin-top:6px; }
.cls-stamp img { width:56px; height:56px; border-radius:50%; object-fit:contain; border:2px solid {{ $_themeColor }}44; }
.cls-stamp-ph { width:56px; height:56px; border-radius:50%; border:2px dashed #d4a84366; display:flex; align-items:center; justify-content:center; font-size:7px; color:#d4a84388; text-align:center; padding:4px; }
.cls-sig { display:flex; justify-content:space-between; padding:10px 30px 4px; }
.cls-sig-item { text-align:center; min-width:120px; }
.cls-sig-line { border-top:1.5px solid #d4a843; width:100px; margin:10px auto 3px; }
.cls-sig-lbl { font-size:9px; color:#888; font-style:italic; }
.cls-badge { display:inline-block; padding:1px 8px; border-radius:8px; font-size:9px; font-weight:600; }
.cls-paid { background:#d4edda; color:#155724; }
.cls-unpaid { background:#f8d7da; color:#721c24; }
.cls-footer { background:{{ $_themeColor }}; padding:6px 18px; font-size:9px; text-align:center; color:#fff; line-height:1.6; font-style:italic; }
.cls-footer strong { font-style:normal; }
</style>
<div class="cls-wrap">
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

    <div class="cls-card">
        <div class="cls-border-top"></div>
        <div class="cls-header">
            <div>
                <h2>{{ __('PAY SLIP') }}</h2>
                <span style="font-size:9px; color:#999;">{{ $slipLabel }} &mdash; {{ $companyName }}</span>
            </div>
            <img src="{{ $logoUrl }}" alt="logo">
        </div>
        <div class="cls-body">

            {{-- Employee + Payment Grid --}}
            <div class="cls-two-col" style="margin-bottom:10px;">
                <div>
                    <div class="cls-section-title">{{ __('Employee') }}</div>
                    <table class="cls-table">
                        <tr><td class="cls-lbl">{{ __('Name') }}</td><td class="cls-val" style="color:{{ $_themeColor }};">{{ $employee->name }}</td></tr>
                        <tr><td class="cls-lbl">{{ __('Designation') }}</td><td class="cls-val">{{ optional($employee->designation)->name ?? '—' }}</td></tr>
                        <tr><td class="cls-lbl">{{ __('Employee ID') }}</td><td class="cls-val">{{ $employee->employee_id ?? '—' }}</td></tr>
                        <tr><td class="cls-lbl">{{ __('Department') }}</td><td class="cls-val">{{ optional($employee->department)->name ?? '—' }}</td></tr>
                        <tr><td class="cls-lbl">{{ __('DOJ') }}</td><td class="cls-val">{{ $employee->company_doj ?? '—' }}</td></tr>
                        <tr><td class="cls-lbl">{{ __('PAN No.') }}</td><td class="cls-val">{{ $employee->tax_payer_id ?? '—' }}</td></tr>
                        @if($_isUkRequest)
                        <tr><td class="cls-lbl">{{ __('NI Number') }}</td><td class="cls-val">{{ $niNumber }}</td></tr>
                        <tr><td class="cls-lbl">{{ __('Tax Code') }}</td><td class="cls-val">{{ $taxCode }}</td></tr>
                        @endif
                    </table>
                </div>
                <div>
                    <div class="cls-section-title">{{ __('Payment') }}</div>
                    <table class="cls-table">
                        <tr><td class="cls-lbl">{{ __('Bank Name') }}</td><td class="cls-val">{{ $employee->bank_name ?? '—' }}</td></tr>
                        <tr><td class="cls-lbl">{{ __('Account No.') }}</td><td class="cls-val">{{ $employee->account_number ?? '—' }}</td></tr>
                        <tr><td class="cls-lbl">{{ __(\App\Models\Utility::bankCodeLabel()) }}</td><td class="cls-val">{{ $employee->bank_identifier_code ?? '—' }}</td></tr>
                        <tr><td class="cls-lbl">{{ __('Account Holder') }}</td><td class="cls-val">{{ $employee->account_holder_name ?? '—' }}</td></tr>
                        <tr><td class="cls-lbl">{{ __('Transaction') }}</td><td class="cls-val">NEFT</td></tr>
                        <tr><td class="cls-lbl">{{ __('Status') }}</td><td class="cls-val"><span class="cls-badge {{ $isPaid ? 'cls-paid' : 'cls-unpaid' }}">{{ $isPaid ? __('Paid') : __('Unpaid') }}</span></td></tr>
                    </table>
                </div>
            </div>

            @if(!$isHourly)
            {{-- Monthly: Days & Work + Leave Grid --}}
            <div class="cls-two-col" style="margin-bottom:10px;">
                <div>
                    <div class="cls-section-title">{{ __('Days & Work') }}</div>
                    <table class="cls-table">
                        <tr><td class="cls-lbl">{{ __('Working Days') }}</td><td class="cls-val" style="color:{{ $_themeColor }};">{{ $officeDays }}</td></tr>
                        <tr><td class="cls-lbl">{{ __('Present') }}</td><td class="cls-val" style="color:#198754;">{{ $presentDaysFmt }}</td></tr>
                        <tr><td class="cls-lbl">{{ __('Leave') }}</td><td class="cls-val" style="color:#e07900;">{{ $approvedLeaves }}</td></tr>
                        <tr><td class="cls-lbl">{{ __('Absent') }}</td><td class="cls-val" style="color:#dc3545;">{{ $absentDaysFmt }}</td></tr>
                        @if($extraDays>0)
                        <tr><td class="cls-lbl" style="color:#856404;">{{ __('Extra / OT') }}</td><td class="cls-val" style="color:#856404;font-weight:bold;">+{{ $extraDaysFmt }}</td></tr>
                        @endif
                        <tr><td class="cls-lbl">{{ __('Total Hours') }}</td><td class="cls-val">{{ $totalWorkHours }} hrs</td></tr>
                    </table>
                </div>
                <div>
                    <div class="cls-section-title">{{ __('Leave Summary') }}</div>
                    <table class="cls-table">
                        <tr><td class="cls-lbl" style="width:35%;font-weight:700;">{{ __('This Month') }}</td><td style="width:20%;text-align:right;font-weight:700;">{{ $approvedLeaves }}</td><td class="cls-lbl" style="width:45%;font-weight:700;">{{ __('Overall') }}</td></tr>
                        <tr><td class="cls-lbl">{{ __('Paid') }}</td><td style="text-align:right;color:#198754;">{{ $paidLeaveDays }}</td><td class="cls-lbl">{{ __('Allocated') }}: <strong>{{ $totalLeaveAlloc }}</strong></td></tr>
                        <tr><td class="cls-lbl">{{ __('Unpaid') }}</td><td style="text-align:right;color:#dc3545;">{{ $unpaidLeaveDays }}</td><td class="cls-lbl">{{ __('Remaining') }}: <strong style="color:#0056b3;">{{ $remainingLeaves }}</strong></td></tr>
                    </table>
                </div>
            </div>
            @else
            {{-- Hourly: compact attendance + rate grid --}}
            <div class="cls-two-col" style="margin-bottom:10px;">
                <div>
                    <div class="cls-section-title">{{ __('Attendance') }}</div>
                    <table class="cls-table">
                        <tr><td class="cls-lbl">{{ __('Working Days') }}</td><td class="cls-val" style="color:{{ $_themeColor }};">{{ $officeDays }}</td></tr>
                        <tr><td class="cls-lbl">{{ __('Present') }}</td><td class="cls-val" style="color:#198754;">{{ $presentDaysFmt }}</td></tr>
                        <tr><td class="cls-lbl">{{ __('Absent') }}</td><td class="cls-val" style="color:#dc3545;">{{ $absentDaysFmt }}</td></tr>
                        <tr><td class="cls-lbl">{{ __('Total Hours') }}</td><td class="cls-val">{{ $totalWorkHours }} hrs</td></tr>
                    </table>
                </div>
                <div>
                    <div class="cls-section-title">{{ __('Hourly Calculation') }}</div>
                    <table class="cls-table">
                        <tr><td class="cls-lbl">{{ __('Hourly Rate') }} <b style="color:{{ $_themeColor }};">(A)</b></td><td class="cls-val" style="color:{{ $_themeColor }};">{{ $_fmtMoney($perHourSalary) }}</td></tr>
                        <tr><td class="cls-lbl">{{ __('Hours Worked') }} <b style="color:#198754;">(B)</b></td><td class="cls-val" style="color:#198754;">{{ $totalWorkHours }} hrs</td></tr>
                        <tr style="background:{{ $_themeColor }}10;"><td class="cls-lbl"><strong>{{ __('Gross Earned') }} (A &times; B)</strong></td><td class="cls-val" style="color:{{ $_themeColor }};"><strong>{{ $_fmtMoney($_storedSalary) }}</strong></td></tr>
                    </table>
                </div>
            </div>
            @endif

            {{-- Salary Details --}}
            <div class="cls-section">
                <div class="cls-section-title">{{ __('Salary Details') }}</div>
                <table class="cls-table" style="width:60%;">
                    <tr><td class="cls-lbl">{{ __('Payslip Type') }}</td><td class="cls-val">{{ $payslipTypeName }}</td></tr>
                    @if($isHourly)
                    <tr><td class="cls-lbl">{{ __('Hourly Rate') }}</td><td class="cls-val" style="color:{{ $_themeColor }};">{{ $_fmtMoney($perHourSalary) }}</td></tr>
                    <tr><td class="cls-lbl">{{ __('Hours') }}</td><td class="cls-val">{{ $totalWorkHours }} hrs</td></tr>
                    <tr><td class="cls-lbl">{{ __('Gross Earned') }}</td><td class="cls-val" style="color:{{ $_themeColor }};">{{ $_fmtMoney($_storedSalary) }}</td></tr>
                    @else
                    <tr><td class="cls-lbl">{{ __('Gross Salary') }}</td><td class="cls-val" style="color:{{ $_themeColor }};">{{ $_fmtMoney($_storedSalary) }}</td></tr>
                    <tr><td class="cls-lbl">{{ __('Per Day Rate') }}</td><td class="cls-val">{{ $_fmtMoney($perDaySalary) }}</td></tr>
                    <tr><td class="cls-lbl">{{ __('Per Hour Rate') }}</td><td class="cls-val">{{ $_fmtMoney($perHourSalary) }}</td></tr>
                    <tr><td class="cls-lbl">{{ __('Days Paid') }}</td><td class="cls-val" style="color:#0056b3;">{{ $daysPaid == floor($daysPaid) ? (int) $daysPaid : $daysPaid }} @if($paidLeaveDays>0)<span style="font-size:8px;color:#198754;">(+{{ $paidLeaveDays }})</span>@endif</td></tr>
                    @endif
                    <tr><td class="cls-lbl">{{ __('Pay Period') }}</td><td class="cls-val">{{ $slipLabel }}</td></tr>
                    <tr><td class="cls-lbl">{{ __('Pay Status') }}</td><td class="cls-val {{ $isPaid ? 'cls-paid' : 'cls-unpaid' }}">{{ $isPaid ? __('Paid') : __('Unpaid') }}</td></tr>
                </table>
            </div>

            {{-- Earnings & Deductions --}}
            <div class="cls-two-col" style="margin-bottom:8px;">
                <div>
                    <div class="cls-section-title">{{ __('Earnings') }}</div>
                    <table class="cls-table">
                        @foreach($earRows as $er)
                        <tr><td class="cls-lbl">{{ $er['label'] }}</td><td class="cls-val">{{ $er['amount']!==null ? $_fmtMoney($er['amount']) : '' }}</td></tr>
                        @endforeach
                        <tr class="cls-total"><td class="cls-lbl">{{ __('Total Earnings') }}</td><td class="cls-val">{{ $_fmtMoney($totalEarnings) }}</td></tr>
                    </table>
                </div>
                <div>
                    <div class="cls-section-title">{{ __('Deductions') }}</div>
                    <table class="cls-table">
                        @forelse($dedRows as $dr)
                        <tr><td class="cls-lbl">{{ $dr['label'] }}</td><td class="cls-val">{{ $dr['amount']!==null ? $_fmtMoney($dr['amount']) : '—' }}</td></tr>
                        @empty
                        <tr><td class="cls-lbl" style="color:#aaa;font-style:italic;">{{ __('No deductions') }}</td><td></td></tr>
                        @endforelse
                        <tr class="cls-total"><td class="cls-lbl">{{ __('Total Deductions') }}</td><td class="cls-val">{{ $_fmtMoney($totalDeductions) }}</td></tr>
                    </table>
                </div>
            </div>

            {{-- Net Pay --}}
            <div class="cls-net">
                <label>{{ __('Net Pay') }}</label>
                <span>{{ $_fmtMoney($netSalary) }}</span>
            </div>

            {{-- Stamp --}}
            <div class="cls-stamp">
                <div style="font-size:9px;">{{ __('Authorized Digital Stamp') }}<br><span style="font-size:8px;color:#999;">{{ $companyName }}</span></div>
                <img src="{{ $_stampUrl ?? '' }}" alt="Stamp" style="{{ $_stampUrl ? 'width:56px;height:56px;border-radius:50%;object-fit:contain;border:2px solid '.$_themeColor.'44;' : 'display:none;' }}">
                @if(!$_stampUrl)<div class="cls-stamp-ph">{{ __('Stamp') }}</div>@endif
            </div>

            {{-- Signatures --}}
            <div class="cls-sig">
                <div class="cls-sig-item"><div class="cls-sig-line"></div><div class="cls-sig-lbl">{{ __('Employee Signature') }}</div></div>
                <div class="cls-sig-item"><div class="cls-sig-line"></div><div class="cls-sig-lbl">{{ __('Authorized Signatory') }}</div></div>
            </div>
        </div>
        <div class="cls-footer">
            @if($companyPhone)<strong>{{ __('Tel') }}:</strong> {{ $companyPhone }} &nbsp;|&nbsp; @endif
            @if($companyWeb)<strong>{{ __('Web') }}:</strong> {{ $companyWeb }} &nbsp;|&nbsp; @endif
            @if($companyEmail)<strong>{{ __('Email') }}:</strong> {{ $companyEmail }} @endif
        </div>
    </div>
</div>
<script>
window.addEventListener('message',function(e){if(e.data&&e.data.type==='payslip-stamp'&&e.data.dataUrl){var img=document.querySelector('.cls-stamp img');if(img){img.src=e.data.dataUrl;img.style.display='';}var ph=document.querySelector('.cls-stamp-ph');if(ph)ph.style.display='none';}});

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

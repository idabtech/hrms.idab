@php
    // All template data is pre-computed from
    // App\Http\Controllers\PaySlipController::preparePayslipTemplateData().
@endphp
<style>
.ctp-wrap { font-family:'Inter','Segoe UI',Arial,sans-serif; font-size:11px; color:#222; max-width:800px; margin:0 auto; background:#f0f2f5; padding:16px; }
.ctp-wrap * { box-sizing:border-box; }
.ctp-card { background:#fff; box-shadow:0 2px 20px rgba(0,0,0,0.05); position:relative; }
.ctp-accent-bar { position:absolute; left:0; top:0; bottom:0; width:4px; background:{{ $_themeColor }}; }
.ctp-header { padding:14px 20px 10px 24px; border-bottom:1px solid #e8eaee; display:flex; justify-content:space-between; align-items:center; }
.ctp-header h2 { font-size:14px; font-weight:700; color:#111; margin:0; letter-spacing:0; text-transform:uppercase; }
.ctp-header span { font-size:10px; color:#888; }
.ctp-header img { height:30px; }
.ctp-body { padding:12px 20px 14px 24px; }
.ctp-label { font-size:8px; letter-spacing:1.2px; text-transform:uppercase; color:#aaa; margin-bottom:3px; }
.ctp-grid { display:flex; gap:14px; margin-bottom:8px; }
.ctp-grid > div { flex:1; padding:6px 0; }
.ctp-table { width:100%; border-collapse:collapse; }
.ctp-table td { padding:2px 3px; font-size:10px; border-bottom:1px solid #f0f1f3; }
.ctp-lbl { color:#888; width:50%; font-size:9px; }
.ctp-val { text-align:right; font-weight:600; color:#222; }
.ctp-total td { font-weight:700; color:{{ $_themeColor }}; border-top:2px solid {{ $_themeColor }} !important; padding-top:4px; font-size:10.5px; }
.ctp-section-title { font-size:9px; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:{{ $_themeColor }}; border-bottom:2px solid {{ $_themeColor }}22; padding-bottom:3px; margin-bottom:5px; }
.ctp-net { display:flex; justify-content:space-between; align-items:center; padding:10px 0; margin:7px 0; border-top:2px solid #111; border-bottom:2px solid #111; }
.ctp-net label { font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:#111; }
.ctp-net span { font-size:18px; font-weight:700; color:{{ $_themeColor }}; }
.ctp-stamp { display:flex; justify-content:space-between; align-items:center; padding:5px 0; border-top:1px solid #e8eaee; margin-top:5px; }
.ctp-stamp img { width:50px; height:50px; border-radius:4px; object-fit:contain; border:1.5px solid {{ $_themeColor }}44; }
.ctp-stamp-ph { width:50px; height:50px; border-radius:4px; border:1.5px dashed #ddd; display:flex; align-items:center; justify-content:center; font-size:6px; color:#bbb; text-align:center; }
.ctp-sig { display:flex; justify-content:space-between; padding:8px 20px 2px; }
.ctp-sig-item { text-align:center; min-width:100px; }
.ctp-sig-line { border-top:1.5px solid #111; width:85px; margin:8px auto 2px; }
.ctp-sig-lbl { font-size:8px; color:#aaa; letter-spacing:0.5px; }
.ctp-badge { display:inline-block; padding:2px 8px; border-radius:3px; font-size:9px; font-weight:600; }
.ctp-paid { background:#111; color:#fff; }
.ctp-unpaid { background:#dc3545; color:#fff; }
.ctp-footer { background:#111; padding:6px 20px 6px 24px; font-size:8px; text-align:center; color:#fff; line-height:1.6; }
.ctp-footer strong { color:{{ $_themeColor }}; }
</style>
<div class="ctp-wrap">

    @if(!($previewMode ?? false))
    <div style="display:flex; justify-content:flex-end; gap:8px; margin-bottom:10px;">
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

    <div class="ctp-card">
        <div class="ctp-accent-bar"></div>
        <div class="ctp-header">
            <div><h2>{{ __('PAYSLIP') }}</h2><span>{{ $slipLabel }} | {{ $companyName }}</span></div>
            <img src="{{ $logoUrl }}" alt="logo">
        </div>
        <div class="ctp-body">

            {{-- Employee + Payment Grid --}}
            <div class="ctp-grid">
                <div>
                    <div class="ctp-section-title">{{ __('Employee') }}</div>
                    <table class="ctp-table">
                        <tr><td class="ctp-lbl">{{ __('Name') }}</td><td class="ctp-val" style="color:{{ $_themeColor }};">{{ $employee->name }}</td></tr>
                        <tr><td class="ctp-lbl">{{ __('Designation') }}</td><td class="ctp-val">{{ optional($employee->designation)->name ?? '—' }}</td></tr>
                        <tr><td class="ctp-lbl">{{ __('ID') }}</td><td class="ctp-val">{{ $employee->employee_id ?? '—' }}</td></tr>
                        <tr><td class="ctp-lbl">{{ __('Department') }}</td><td class="ctp-val">{{ optional($employee->department)->name ?? '—' }}</td></tr>
                        <tr><td class="ctp-lbl">{{ __('DOJ') }}</td><td class="ctp-val">{{ $employee->company_doj ?? '—' }}</td></tr>
                        @if($_isUkRequest)
                        <tr><td class="ctp-lbl">{{ __('NI Number') }}</td><td class="ctp-val">{{ $niNumber }}</td></tr>
                        <tr><td class="ctp-lbl">{{ __('Tax Code') }}</td><td class="ctp-val">{{ $taxCode }}</td></tr>
                        @endif
                    </table>
                </div>
                <div>
                    <div class="ctp-section-title">{{ __('Payment') }}</div>
                    <table class="ctp-table">
                        <tr><td class="ctp-lbl">{{ __('Bank') }}</td><td class="ctp-val">{{ $employee->bank_name ?? '—' }}</td></tr>
                        <tr><td class="ctp-lbl">{{ __('Account') }}</td><td class="ctp-val">{{ $employee->account_number ?? '—' }}</td></tr>
                        <tr><td class="ctp-lbl">{{ __(\App\Models\Utility::bankCodeLabel()) }}</td><td class="ctp-val">{{ $employee->bank_identifier_code ?? '—' }}</td></tr>
                        <tr><td class="ctp-lbl">{{ __('Holder') }}</td><td class="ctp-val">{{ $employee->account_holder_name ?? '—' }}</td></tr>
                        <tr><td class="ctp-lbl">{{ __('Period') }}</td><td class="ctp-val">{{ $slipLabel }}</td></tr>
                        <tr><td class="ctp-lbl">{{ __('Status') }}</td><td class="ctp-val"><span class="ctp-badge {{ $isPaid ? 'ctp-paid' : 'ctp-unpaid' }}">{{ $isPaid ? __('Paid') : __('Unpaid') }}</span></td></tr>
                    </table>
                </div>
            </div>

            {{-- Monthly/Hourly conditional --}}
            @if(!$isHourly)
            <div class="ctp-grid">
                <div>
                    <div class="ctp-section-title">{{ __('Days & Work') }}</div>
                    <table class="ctp-table">
                        <tr><td class="ctp-lbl">{{ __('Working') }}</td><td class="ctp-val" style="color:{{ $_themeColor }};">{{ $officeDays }}</td></tr>
                        <tr><td class="ctp-lbl">{{ __('Present') }}</td><td class="ctp-val" style="color:#198754;">{{ $presentDaysFmt }}</td></tr>
                        <tr><td class="ctp-lbl">{{ __('Leave') }}</td><td class="ctp-val" style="color:#e07900;">{{ $approvedLeaves }}</td></tr>
                        <tr><td class="ctp-lbl">{{ __('Absent') }}</td><td class="ctp-val" style="color:#dc3545;">{{ $absentDaysFmt }}</td></tr>
                        @if($extraDays>0)
                        <tr><td class="ctp-lbl" style="color:#856404;">{{ __('OT') }}</td><td class="ctp-val" style="color:#856404;">+{{ $extraDaysFmt }}</td></tr>
                        @endif
                        <tr><td class="ctp-lbl">{{ __('Hours') }}</td><td class="ctp-val">{{ $totalWorkHours }}h</td></tr>
                    </table>
                </div>
                <div>
                    <div class="ctp-section-title">{{ __('Leave') }}</div>
                    <table class="ctp-table">
                        <tr><td class="ctp-lbl" style="font-weight:700;">{{ __('This Month') }}</td><td class="ctp-val" style="font-weight:700;">{{ $approvedLeaves }}</td></tr>
                        <tr><td class="ctp-lbl">{{ __('Paid Leave') }}</td><td class="ctp-val" style="color:#198754;">{{ $paidLeaveDays }}</td></tr>
                        <tr><td class="ctp-lbl">{{ __('Unpaid Leave') }}</td><td class="ctp-val" style="color:#dc3545;">{{ $unpaidLeaveDays }}</td></tr>
                        <tr><td class="ctp-lbl" style="font-weight:700;">{{ __('Overall') }}</td><td class="ctp-val" style="font-weight:700;">{{ __('Allocated') }}: {{ $totalLeaveAlloc }}</td></tr>
                        <tr><td class="ctp-lbl">{{ __('Remaining') }}</td><td class="ctp-val" style="color:#0056b3;font-weight:700;">{{ $remainingLeaves }}</td></tr>
                    </table>
                </div>
            </div>
            @else
            <div class="ctp-grid">
                <div>
                    <div class="ctp-section-title">{{ __('Attendance') }}</div>
                    <table class="ctp-table">
                        <tr><td class="ctp-lbl">{{ __('Working') }}</td><td class="ctp-val" style="color:{{ $_themeColor }};">{{ $officeDays }}</td></tr>
                        <tr><td class="ctp-lbl">{{ __('Present') }}</td><td class="ctp-val" style="color:#198754;">{{ $presentDaysFmt }}</td></tr>
                        <tr><td class="ctp-lbl">{{ __('Absent') }}</td><td class="ctp-val" style="color:#dc3545;">{{ $absentDaysFmt }}</td></tr>
                        <tr><td class="ctp-lbl">{{ __('Hours') }}</td><td class="ctp-val">{{ $totalWorkHours }}h</td></tr>
                    </table>
                </div>
                <div>
                    <div class="ctp-section-title">{{ __('Rate') }}</div>
                    <table class="ctp-table">
                        <tr><td class="ctp-lbl">{{ __('Hourly') }} <b style="color:{{ $_themeColor }};">(A)</b></td><td class="ctp-val" style="color:{{ $_themeColor }};">{{ $_fmtMoney($perHourSalary) }}</td></tr>
                        <tr><td class="ctp-lbl">{{ __('Hours') }} <b style="color:#198754;">(B)</b></td><td class="ctp-val" style="color:#198754;">{{ $totalWorkHours }}</td></tr>
                        <tr style="background:{{ $_themeColor }}08;"><td class="ctp-lbl"><strong>{{ __('Gross') }} (A×B)</strong></td><td class="ctp-val" style="color:{{ $_themeColor }};"><strong>{{ $_fmtMoney($_storedSalary) }}</strong></td></tr>
                    </table>
                </div>
            </div>
            @endif

            {{-- Salary --}}
            <div class="ctp-grid" style="margin-bottom:6px;">
                <div>
                    <div class="ctp-section-title">{{ __('Salary') }}</div>
                    <table class="ctp-table">
                        <tr><td class="ctp-lbl">{{ __('Type') }}</td><td class="ctp-val">{{ $payslipTypeName }}</td></tr>
                        @if($isHourly)
                        <tr><td class="ctp-lbl">{{ __('Hourly Rate') }}</td><td class="ctp-val" style="color:{{ $_themeColor }};">{{ $_fmtMoney($perHourSalary) }}</td></tr>
                        <tr><td class="ctp-lbl">{{ __('Hours') }}</td><td class="ctp-val">{{ $totalWorkHours }}h</td></tr>
                        <tr><td class="ctp-lbl"><strong>{{ __('Gross Earned') }}</strong></td><td class="ctp-val" style="color:{{ $_themeColor }};"><strong>{{ $_fmtMoney($_storedSalary) }}</strong></td></tr>
                        @else
                        <tr><td class="ctp-lbl">{{ __('Gross') }}</td><td class="ctp-val" style="color:{{ $_themeColor }};">{{ $_fmtMoney($_storedSalary) }}</td></tr>
                        <tr><td class="ctp-lbl">{{ __('Per Day') }}</td><td class="ctp-val">{{ $_fmtMoney($perDaySalary) }}</td></tr>
                        <tr><td class="ctp-lbl">{{ __('Per Hour') }}</td><td class="ctp-val">{{ $_fmtMoney($perHourSalary) }}</td></tr>
                        <tr><td class="ctp-lbl">{{ __('Days Paid') }}</td><td class="ctp-val">{{ $daysPaid == floor($daysPaid) ? (int) $daysPaid : $daysPaid }} @if($paidLeaveDays>0)<span style="font-size:8px;color:#198754;">(+{{ $paidLeaveDays }})</span>@endif</td></tr>
                        @endif
                    </table>
                </div>
                <div>
                    <div class="ctp-section-title">{{ __('Summary') }}</div>
                    <table class="ctp-table">
                        <tr><td class="ctp-lbl">{{ __('Period') }}</td><td class="ctp-val">{{ $slipLabel }}</td></tr>
                        <tr><td class="ctp-lbl">{{ __('Earnings') }}</td><td class="ctp-val" style="color:{{ $_themeColor }};">{{ $_fmtMoney($totalEarnings) }}</td></tr>
                        <tr><td class="ctp-lbl">{{ __('Deductions') }}</td><td class="ctp-val" style="color:#dc3545;">{{ $_fmtMoney($totalDeductions) }}</td></tr>
                    </table>
                </div>
            </div>

            {{-- Earnings & Deductions --}}
            <div class="ctp-grid" style="margin-bottom:6px;">
                <div>
                    <div class="ctp-section-title">{{ __('Earnings') }}</div>
                    <table class="ctp-table">
                        @foreach($earRows as $er)
                        <tr><td class="ctp-lbl">{{ $er['label'] }}</td><td class="ctp-val">{{ $er['amount']!==null ? $_fmtMoney($er['amount']) : '' }}</td></tr>
                        @endforeach
                        <tr class="ctp-total"><td class="ctp-lbl">{{ __('Total') }}</td><td class="ctp-val">{{ $_fmtMoney($totalEarnings) }}</td></tr>
                    </table>
                </div>
                <div>
                    <div class="ctp-section-title">{{ __('Deductions') }}</div>
                    <table class="ctp-table">
                        @forelse($dedRows as $dr)
                        <tr><td class="ctp-lbl">{{ $dr['label'] }}</td><td class="ctp-val">{{ $dr['amount']!==null ? $_fmtMoney($dr['amount']) : '—' }}</td></tr>
                        @empty
                        <tr><td class="ctp-lbl" style="color:#aaa;font-style:italic;">{{ __('None') }}</td><td></td></tr>
                        @endforelse
                        <tr class="ctp-total"><td class="ctp-lbl">{{ __('Total') }}</td><td class="ctp-val">{{ $_fmtMoney($totalDeductions) }}</td></tr>
                    </table>
                </div>
            </div>

            {{-- Net Pay --}}
            <div class="ctp-net">
                <label>{{ __('Net Pay') }}</label>
                <span>{{ $_fmtMoney($netSalary) }}</span>
            </div>

            {{-- Stamp --}}
            <div class="ctp-stamp">
                <div><span style="font-size:8px;color:#999;">{{ __('Authorized Stamp') }}</span><br><span style="font-size:9px;font-weight:600;">{{ $companyName }}</span></div>
                <img src="{{ $_stampUrl ?? '' }}" alt="Stamp" style="{{ $_stampUrl ? 'width:50px;height:50px;border-radius:4px;object-fit:contain;border:1.5px solid '.$_themeColor.'44;' : 'display:none;' }}">
                @if(!$_stampUrl)<div class="ctp-stamp-ph">{{ __('Stamp') }}</div>@endif
            </div>

            {{-- Signatures --}}
            <div class="ctp-sig">
                <div class="ctp-sig-item"><div class="ctp-sig-line"></div><div class="ctp-sig-lbl">{{ __('Employee') }}</div></div>
                <div class="ctp-sig-item"><div class="ctp-sig-line"></div><div class="ctp-sig-lbl">{{ __('Authorized') }}</div></div>
            </div>
        </div>
        <div class="ctp-footer">
            @if($companyPhone)<strong>{{ __('Tel') }}:</strong> {{ $companyPhone }} &nbsp;|&nbsp; @endif
            @if($companyWeb)<strong>{{ __('Web') }}:</strong> {{ $companyWeb }} &nbsp;|&nbsp; @endif
            @if($companyEmail)<strong>{{ __('Email') }}:</strong> {{ $companyEmail }} @endif
        </div>
    </div>
</div>
<script>
window.addEventListener('message',function(e){if(e.data&&e.data.type==='payslip-stamp'&&e.data.dataUrl){var img=document.querySelector('.ctp-stamp img');if(img){img.src=e.data.dataUrl;img.style.display='';}var ph=document.querySelector('.ctp-stamp-ph');if(ph)ph.style.display='none';}});

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

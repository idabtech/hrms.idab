@php
    // All template data is pre-computed from
    // App\Http\Controllers\PaySlipController::preparePayslipTemplateData().
@endphp
<style>
.bld-wrap { font-family:'Arial Black','Impact','Segoe UI',sans-serif; font-size:11px; color:#1a1a1a; max-width:800px; margin:0 auto; background:#0d0d0d; padding:16px; }
.bld-wrap * { box-sizing:border-box; }
.bld-card { background:#fff; position:relative; overflow:hidden; }
.bld-strip { height:5px; background:repeating-linear-gradient(90deg,{{ $_themeColor }} 0px,{{ $_themeColor }} 20px,#111 20px,#111 40px); }
.bld-hero { background:#111; color:#fff; padding:12px 20px; display:flex; justify-content:space-between; align-items:center; }
.bld-hero h2 { font-size:22px; font-weight:900; margin:0; letter-spacing:3px; text-transform:uppercase; color:{{ $_themeColor }}; }
.bld-hero span { font-size:10px; opacity:0.7; letter-spacing:1px; }
.bld-hero img { height:32px; }
.bld-body { padding:14px 20px; }
.bld-grid { display:flex; gap:12px; margin-bottom:10px; }
.bld-grid > div { flex:1; }
.bld-title { font-size:9px; font-weight:900; text-transform:uppercase; letter-spacing:1.5px; color:#111; border-bottom:3px solid {{ $_themeColor }}; padding-bottom:2px; margin-bottom:5px; }
.bld-table { width:100%; border-collapse:collapse; }
.bld-table td { padding:3px 3px; font-size:10px; border-bottom:1px solid #e8e8e8; }
.bld-lbl { color:#666; font-weight:600; width:48%; }
.bld-val { text-align:right; font-weight:700; }
.bld-total td { font-weight:900; color:{{ $_themeColor }}; border-top:3px solid {{ $_themeColor }} !important; padding-top:5px; font-size:11px; }
.bld-net { background:#111; color:#fff; padding:10px 16px; display:flex; justify-content:space-between; align-items:center; margin:8px 0; border-left:4px solid {{ $_themeColor }}; }
.bld-net label { font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:1px; }
.bld-net span { font-size:18px; font-weight:900; color:{{ $_themeColor }}; }
.bld-stamp { display:flex; justify-content:space-between; align-items:center; padding:5px 0; border-top:2px solid #111; margin-top:5px; font-size:9px; font-weight:600; color:#111; }
.bld-stamp img { width:52px; height:52px; border-radius:50%; object-fit:contain; border:2px solid {{ $_themeColor }}; }
.bld-stamp-ph { width:52px; height:52px; border-radius:50%; border:2px dashed {{ $_themeColor }}66; display:flex; align-items:center; justify-content:center; font-size:6px; color:{{ $_themeColor }}88; text-align:center; }
.bld-sig { display:flex; justify-content:space-between; padding:8px 24px 2px; }
.bld-sig-item { text-align:center; min-width:100px; }
.bld-sig-line { border-top:2px solid #111; width:90px; margin:8px auto 2px; }
.bld-sig-lbl { font-size:8px; color:#888; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; }
.bld-badge { display:inline-block; padding:2px 10px; font-size:9px; font-weight:700; text-transform:uppercase; }
.bld-paid { background:#111; color:#fff; }
.bld-unpaid { background:#dc3545; color:#fff; }
.bld-footer { background:#111; padding:6px 20px; font-size:8px; text-align:center; color:#fff; line-height:1.6; letter-spacing:0.5px; }
.bld-footer strong { color:{{ $_themeColor }}; }
</style>
<div class="bld-wrap">

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

    <div class="bld-card">
        <div class="bld-strip"></div>
        <div class="bld-hero">
            <div><h2>{{ __('PAY SLIP') }}</h2><span>{{ $slipLabel }}</span></div>
            <img src="{{ $logoUrl }}" alt="logo">
        </div>
        <div class="bld-body">

            {{-- Employee + Payment Grid --}}
            @if($_showEmployeeDetails || $_showPaymentDetails)
            <div class="bld-grid">
                @if($_showEmployeeDetails)
                <div>
                    <div class="bld-title">{{ __('Employee') }}</div>
                    <table class="bld-table">
                        @if($_showName)
                        <tr><td class="bld-lbl">{{ __('Name') }}</td><td class="bld-val" style="color:{{ $_themeColor }};">{{ $employee->name }}</td></tr>
                        @endif
                        @if($_showDesignation)
                        <tr><td class="bld-lbl">{{ __('Designation') }}</td><td class="bld-val">{{ optional($employee->designation)->name ?? '—' }}</td></tr>
                        @endif
                        @if($_showEmployeeId)
                        <tr><td class="bld-lbl">{{ __('Employee ID') }}</td><td class="bld-val">{{ $employee->employee_id ?? '—' }}</td></tr>
                        @endif
                        @if($_showDepartment)
                        <tr><td class="bld-lbl">{{ __('Department') }}</td><td class="bld-val">{{ optional($employee->department)->name ?? '—' }}</td></tr>
                        @endif
                        @if($_showDateOfJoining)
                        <tr><td class="bld-lbl">{{ __('DOJ') }}</td><td class="bld-val">{{ $employee->company_doj ?? '—' }}</td></tr>
                        @endif
                        @if($_showPanNo)
                        <tr><td class="bld-lbl">{{ __('PAN No.') }}</td><td class="bld-val">{{ $employee->tax_payer_id ?? '—' }}</td></tr>
                        @endif
                        @if($_isUkRequest && $_showNiNumber)
                        <tr><td class="bld-lbl">{{ __('NI Number') }}</td><td class="bld-val">{{ $niNumber }}</td></tr>
                        @endif
                        @if($_isUkRequest && $_showTaxCode)
                        <tr><td class="bld-lbl">{{ __('Tax Code') }}</td><td class="bld-val">{{ $taxCode }}</td></tr>
                        @endif
                    </table>
                </div>
                @endif
                @if($_showPaymentDetails)
                <div>
                    <div class="bld-title">{{ __('Payment') }}</div>
                    <table class="bld-table">
                        @if($_showBankName)
                        <tr><td class="bld-lbl">{{ __('Bank Name') }}</td><td class="bld-val">{{ $employee->bank_name ?? '—' }}</td></tr>
                        @endif
                        @if($_showAccountNo)
                        <tr><td class="bld-lbl">{{ __('Account No.') }}</td><td class="bld-val">{{ $employee->account_number ?? '—' }}</td></tr>
                        @endif
                        @if($_showBankCode)
                        <tr><td class="bld-lbl">{{ __(\App\Models\Utility::bankCodeLabel()) }}</td><td class="bld-val">{{ $employee->bank_identifier_code ?? '—' }}</td></tr>
                        @endif
                        @if($_showAccountHolder)
                        <tr><td class="bld-lbl">{{ __('Account Holder') }}</td><td class="bld-val">{{ $employee->account_holder_name ?? '—' }}</td></tr>
                        @endif
                        @if($_showPayPeriod)
                        <tr><td class="bld-lbl">{{ __('Pay Period') }}</td><td class="bld-val">{{ $slipLabel }}</td></tr>
                        @endif
                        <tr><td class="bld-lbl">{{ __('Status') }}</td><td class="bld-val"><span class="bld-badge {{ $isPaid ? 'bld-paid' : 'bld-unpaid' }}">{{ $isPaid ? __('Paid') : __('Unpaid') }}</span></td></tr>
                    </table>
                </div>
                @endif
            </div>
            @endif

            {{-- Monthly/Hourly conditional section --}}
            @if(!$isHourly)
            <div class="bld-grid">
                <div>
                    <div class="bld-title">{{ __('Days & Work') }}</div>
                    <table class="bld-table">
                        <tr><td class="bld-lbl">{{ $workingDaysLabel ?? __('Working Days') }}</td><td class="bld-val" style="color:{{ $_themeColor }};">{{ $officeDays }}</td></tr>
                        <tr><td class="bld-lbl">{{ __('Present') }}</td><td class="bld-val" style="color:#198754;">{{ $presentDaysFmt }}</td></tr>
                        <tr><td class="bld-lbl">{{ __('Leave') }}</td><td class="bld-val" style="color:#e07900;">{{ $approvedLeaves }}</td></tr>
                        <tr><td class="bld-lbl">{{ __('Absent') }}</td><td class="bld-val" style="color:#dc3545;">{{ $absentDaysFmt }}</td></tr>
                        @if(!empty($isMonthWise) || !empty($dayOffDays))
                        <tr><td class="bld-lbl">{{ __('Day Off') }}</td><td class="bld-val" style="color:#6c757d;">{{ $dayOffDaysFmt ?? 0 }}</td></tr>
                        @endif
                        @if($extraDays>0)
                        <tr><td class="bld-lbl" style="color:#856404;">{{ __('Extra / OT') }}</td><td class="bld-val" style="color:#856404;">+{{ $extraDaysFmt }}</td></tr>
                        @endif
                        <tr><td class="bld-lbl">{{ __('Total Hours') }}</td><td class="bld-val">{{ $totalWorkHours }} hrs</td></tr>
                    </table>
                </div>
                <div>
                    <div class="bld-title">{{ __('Leave Summary') }}</div>
                    <table class="bld-table">
                        <tr><td class="bld-lbl" style="width:35%;font-weight:900;">{{ __('This Month') }}</td><td style="width:15%;text-align:right;font-weight:900;">{{ $approvedLeaves }}</td><td class="bld-lbl" style="width:50%;font-weight:900;">{{ __('Overall') }}</td></tr>
                        <tr><td class="bld-lbl">{{ __('Paid') }}</td><td style="text-align:right;color:#198754;">{{ $paidLeaveDays }}</td><td class="bld-lbl">{{ __('Allocated') }}: <strong>{{ $totalLeaveAlloc }}</strong></td></tr>
                        <tr><td class="bld-lbl">{{ __('Unpaid') }}</td><td style="text-align:right;color:#dc3545;">{{ $unpaidLeaveDays }}</td><td class="bld-lbl">{{ __('Remaining') }}: <strong style="color:#0056b3;">{{ $remainingLeaves }}</strong></td></tr>
                    </table>
                </div>
            </div>
            @else
            <div class="bld-grid">
                <div>
                    <div class="bld-title">{{ __('Attendance') }}</div>
                    <table class="bld-table">
                        <tr><td class="bld-lbl">{{ $workingDaysLabel ?? __('Working Days') }}</td><td class="bld-val" style="color:{{ $_themeColor }};">{{ $officeDays }}</td></tr>
                        <tr><td class="bld-lbl">{{ __('Present') }}</td><td class="bld-val" style="color:#198754;">{{ $presentDaysFmt }}</td></tr>
                        <tr><td class="bld-lbl">{{ __('Absent') }}</td><td class="bld-val" style="color:#dc3545;">{{ $absentDaysFmt }}</td></tr>
                        <tr><td class="bld-lbl">{{ __('Hours') }}</td><td class="bld-val">{{ $totalWorkHours }} hrs</td></tr>
                    </table>
                </div>
                <div>
                    <div class="bld-title">{{ __('Hourly Calculation') }}</div>
                    <table class="bld-table">
                        <tr><td class="bld-lbl">{{ __('Hourly Rate') }} <b style="color:{{ $_themeColor }};">(A)</b></td><td class="bld-val" style="color:{{ $_themeColor }};">{{ $_fmtMoney($perHourSalary) }}</td></tr>
                        <tr><td class="bld-lbl">{{ __('Hours Worked') }} <b style="color:#198754;">(B)</b></td><td class="bld-val" style="color:#198754;">{{ $totalWorkHours }} hrs</td></tr>
                        <tr style="background:{{ $_themeColor }}10;"><td class="bld-lbl"><strong>{{ __('Gross Earned') }} (A &times; B)</strong></td><td class="bld-val" style="color:{{ $_themeColor }};"><strong>{{ $_fmtMoney($_storedSalary) }}</strong></td></tr>
                    </table>
                </div>
            </div>
            @endif

            {{-- Salary Details --}}
            <div class="bld-grid">
                <div>
                    <div class="bld-title">{{ __('Salary') }}</div>
                    <table class="bld-table">
                        <tr><td class="bld-lbl">{{ __('Payslip Type') }}</td><td class="bld-val">{{ $payslipTypeName }}</td></tr>
                        @if($isHourly)
                        <tr><td class="bld-lbl">{{ __('Hourly Rate') }}</td><td class="bld-val" style="color:{{ $_themeColor }};">{{ $_fmtMoney($perHourSalary) }}</td></tr>
                        <tr><td class="bld-lbl">{{ __('Hours') }}</td><td class="bld-val">{{ $totalWorkHours }} hrs</td></tr>
                        <tr><td class="bld-lbl"><strong>{{ __('Gross Earned') }}</strong></td><td class="bld-val" style="color:{{ $_themeColor }};"><strong>{{ $_fmtMoney($_storedSalary) }}</strong></td></tr>
                        @else
                        <tr><td class="bld-lbl">{{ __('Gross Salary') }}</td><td class="bld-val" style="color:{{ $_themeColor }};">{{ $_fmtMoney($_storedSalary) }}</td></tr>
                        <tr><td class="bld-lbl">{{ __('Per Day Rate') }}</td><td class="bld-val">{{ $_fmtMoney($perDaySalary) }}</td></tr>
                        <tr><td class="bld-lbl">{{ __('Per Hour Rate') }}</td><td class="bld-val">{{ $_fmtMoney($perHourSalary) }}</td></tr>
                        @if(!empty($isMonthWise) || !empty($dayOffDays))
                        <tr><td class="bld-lbl">{{ __('Day Off') }}</td><td class="bld-val" style="color:#6c757d;">{{ $dayOffDaysFmt ?? 0 }}</td></tr>
                        @endif
                        <tr>
                            <td class="bld-lbl">{{ __('Days Paid') }}</td>
                            <td class="bld-val">
                                {{ $daysPaid == floor($daysPaid) ? (int) $daysPaid : $daysPaid }}
                                @if($paidLeaveDays > 0)
                                    <span style="font-size:8px;color:#198754;">
                                        (+{{ $paidLeaveDays }})
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @endif
                    </table>
                </div>
                <div>
                    <div class="bld-title">{{ __('Payslip') }}</div>
                    <table class="bld-table">
                        <tr><td class="bld-lbl">{{ __('Pay Period') }}</td><td class="bld-val">{{ $slipLabel }}</td></tr>
                        <tr><td class="bld-lbl">{{ __('Total Earnings') }}</td><td class="bld-val" style="color:{{ $_themeColor }};">{{ $_fmtMoney($totalEarnings) }}</td></tr>
                        <tr><td class="bld-lbl">{{ __('Total Deductions') }}</td><td class="bld-val" style="color:#dc3545;">{{ $_fmtMoney($totalDeductions) }}</td></tr>
                        <tr><td class="bld-lbl">{{ __('Status') }}</td><td class="bld-val"><span class="bld-badge {{ $isPaid ? 'bld-paid' : 'bld-unpaid' }}">{{ $isPaid ? __('Paid') : __('Unpaid') }}</span></td></tr>
                    </table>
                </div>
            </div>

            {{-- Earnings & Deductions --}}
            <div class="bld-grid" style="margin-bottom:8px;">
                <div>
                    <div class="bld-title">{{ __('Earnings') }}</div>
                    <table class="bld-table">
                        @foreach($earRows as $er)
                        <tr><td class="bld-lbl">{{ $er['label'] }}</td><td class="bld-val">{{ $er['amount']!==null ? $_fmtMoney($er['amount']) : '' }}</td></tr>
                        @endforeach
                        <tr class="bld-total"><td class="bld-lbl">{{ __('Total Earnings') }}</td><td class="bld-val">{{ $_fmtMoney($totalEarnings) }}</td></tr>
                    </table>
                </div>
                <div>
                    <div class="bld-title">{{ __('Deductions') }}</div>
                    <table class="bld-table">
                        @forelse($dedRows as $dr)
                        <tr><td class="bld-lbl">{{ $dr['label'] }}</td><td class="bld-val">{{ $dr['amount']!==null ? $_fmtMoney($dr['amount']) : '—' }}</td></tr>
                        @empty
                        <tr><td class="bld-lbl" style="color:#aaa;font-style:italic;">{{ __('No deductions') }}</td><td></td></tr>
                        @endforelse
                        <tr class="bld-total"><td class="bld-lbl">{{ __('Total Deductions') }}</td><td class="bld-val">{{ $_fmtMoney($totalDeductions) }}</td></tr>
                    </table>
                </div>
            </div>

            {{-- Net Pay --}}
            <div class="bld-net">
                <label>{{ __('Net Pay') }}</label>
                <span>{{ $_fmtMoney($netSalary) }}</span>
            </div>

            {{-- Stamp --}}
            <div class="bld-stamp">
                <div>{{ __('Authorized Stamp') }}<br><span style="font-size:8px;color:#888;">{{ $companyName }}</span></div>
                <img src="{{ $_stampUrl ?? '' }}" alt="Stamp" style="{{ $_stampUrl ? 'width:52px;height:52px;border-radius:50%;object-fit:contain;border:2px solid '.$_themeColor.';' : 'display:none;' }}">
                @if(!$_stampUrl)<div class="bld-stamp-ph">{{ __('Stamp') }}</div>@endif
            </div>

            {{-- Signatures --}}
            @if($_showSignatures)
            <div class="bld-sig">
                <div class="bld-sig-item"><div class="bld-sig-line"></div><div class="bld-sig-lbl">{{ __('Employee') }}</div></div>
                <div class="bld-sig-item"><div class="bld-sig-line"></div><div class="bld-sig-lbl">{{ __('Authorized') }}</div></div>
            </div>
            @endif
        </div>
        @if($_showFooter)
        <div class="bld-footer">
            @if($companyPhone)<strong>{{ __('Tel') }}:</strong> {{ $companyPhone }} &nbsp;|&nbsp; @endif
            @if($companyWeb)<strong>{{ __('Web') }}:</strong> {{ $companyWeb }} &nbsp;|&nbsp; @endif
            @if($companyEmail)<strong>{{ __('Email') }}:</strong> {{ $companyEmail }} @endif
        </div>
        @endif
    </div>
</div>
<script>
window.addEventListener('message',function(e){if(e.data&&e.data.type==='payslip-stamp'&&e.data.dataUrl){var img=document.querySelector('.bld-stamp img');if(img){img.src=e.data.dataUrl;img.style.display='';}var ph=document.querySelector('.bld-stamp-ph');if(ph)ph.style.display='none';}});

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

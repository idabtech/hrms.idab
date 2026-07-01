@extends('layouts.contractheader')
@section('page-title'){{ __('Payslip') }}@endsection

@section('content')
@php
    [$slipYear, $slipMonth] = explode('-', $payslip->salary_month);
    $logo         = asset(Storage::url('uploads/logo/'));
    $company_logo = \App\Models\Utility::get_company_logo();
    $logoUrl      = $logo . '/' . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo-dark.png');
    $downloadUrl  = route('payslip.download', [$employee->id, $payslip->salary_month]);
@endphp

@include('payslip.payslipMacro')

<style>
.ps-wrap { max-width:860px; margin:0 auto; padding:18px 20px; background:#fff; font-family:'Segoe UI',Arial,sans-serif; font-size:12.5px; color:#1c1c1c; }
.ps-wrap * { box-sizing:border-box; }
.ps-card { border:2px solid #26b5be; border-radius:4px; overflow:hidden; }
.ps-hdr { background:#f0fbfc; border-bottom:2px solid #26b5be; }
.ps-hdr table { width:100%; border-collapse:collapse; }
.ps-hdr td { border:none; padding:12px 16px; vertical-align:middle; }
.ps-hdr-right { text-align:right; font-size:11px; color:#555; line-height:1.7; }
.ps-hdr img { height:46px; object-fit:contain; }
.ps-title { background:#26b5be; color:#fff; text-align:center; font-size:18px; font-weight:700; letter-spacing:2px; padding:10px 0; }
.ps-band { background:#26b5be; color:#fff; font-weight:700; font-size:12px; text-align:center; padding:5px 10px; }
.ps-t { width:100%; border-collapse:collapse; }
.ps-t td, .ps-t th { border:1px solid #b2e0e3; padding:6px 10px; vertical-align:middle; font-size:12px; }
.ps-t th { background:#e0f7f8; color:#0e6e74; font-weight:700; text-align:center; font-size:11.5px; }
.ps-t tbody tr:nth-child(even) td { background:#f5fdfe; }
.lbl { font-weight:600; color:#333; white-space:nowrap; width:48%; }
.val { color:#444; }
.ra  { text-align:right; padding-right:12px !important; }
.v-green  { color:#198754; font-weight:700; }
.v-orange { color:#e07900; font-weight:700; }
.v-red    { color:#dc3545; font-weight:700; }
.v-blue   { color:#0d6efd; font-weight:700; }
.v-teal   { color:#0a5459; font-weight:700; }
.ps-total td { background:#d0f4f6 !important; font-weight:700; color:#0a5459; border-top:2px solid #26b5be !important; }
.ps-net-row { border-top:1px solid #b2e0e3; }
.ps-stamp-td { width:50%; text-align:center; padding:18px 10px; border-right:1px solid #b2e0e3; vertical-align:middle; }
.ps-stamp-ring { display:inline-block; width:72px; height:72px; border-radius:50%; border:3px solid #26b5be; color:#26b5be; font-size:8.5px; font-weight:700; text-align:center; line-height:1.3; padding:14px 6px; }
.ps-net-td { width:50%; vertical-align:middle; padding:0; }
.ps-net-inner { width:100%; border-collapse:collapse; height:100%; }
.ps-net-lbl { background:#d0f4f6; font-weight:700; font-size:13px; color:#0a5459; text-align:center; padding:16px 14px; border:1px solid #b2e0e3; width:55%; }
.ps-net-val { font-weight:700; font-size:20px; color:#0a5459; text-align:center; padding:16px 14px; border:1px solid #b2e0e3; }
.badge-paid   { background:#198754; color:#fff; padding:2px 8px; border-radius:10px; font-size:11px; font-weight:600; }
.badge-unpaid { background:#dc3545; color:#fff; padding:2px 8px; border-radius:10px; font-size:11px; font-weight:600; }
.ps-sig { border-top:1px solid #b2e0e3; padding:14px 20px 8px; }
.ps-sig table { width:100%; border-collapse:collapse; }
.ps-sig td { border:none; text-align:center; padding:0 10px; vertical-align:bottom; }
.ps-sig-line { border-top:1.5px solid #aaa; width:140px; margin:18px auto 5px; }
.ps-sig-label { font-size:10.5px; color:#666; }
.ps-footer { background:#26b5be; color:#fff; }
.ps-footer table { width:100%; border-collapse:collapse; }
.ps-footer td { border:none; padding:7px 14px; color:#fff; font-size:11px; }
</style>

<div class="main-content">
    <div class="text-end mb-3 me-3">
        <a href="{{ $downloadUrl }}" class="btn btn-primary btn-sm">
            <i class="fa fa-download me-1"></i>{{ __('Download PDF') }}
        </a>
    </div>

    <div class="ps-wrap">
        <div class="ps-card">

            <div class="ps-hdr">
                <table>
                    <tr>
                        <td style="width:40%;"><img src="{{ $logoUrl }}" alt="logo"></td>
                        <td class="ps-hdr-right" style="width:60%;">
                            <strong style="font-size:13px; color:#0e6e74;">{{ $companyName }}</strong><br>
                            {{ $companyAddr }}{{ $companyCity ? ', '.$companyCity : '' }}<br>
                            {{ $companyState }}{{ $companyZip ? ' - '.$companyZip : '' }}
                            @if($companyPhone)<br>{{ $companyPhone }}@endif
                        </td>
                    </tr>
                </table>
            </div>

            <div class="ps-title">PAY SLIP &mdash; {{ strtoupper($slipLabel) }}</div>

            {{-- A: Employee | Payment --}}
            <table class="ps-t" style="border:none;">
                <tr>
                    <td style="width:50%; padding:0; border:none; vertical-align:top;">
                        <div class="ps-band">{{ __('Employee Details') }}</div>
                        <table class="ps-t">
                            <tr><td class="lbl">{{ __('Name') }}</td><td class="val">{{ $employee->name }}</td></tr>
                            <tr><td class="lbl">{{ __('Designation') }}</td><td class="val">{{ optional($employee->designation)->name ?? '—' }}</td></tr>
                            <tr><td class="lbl">{{ __('Employee ID') }}</td><td class="val">{{ $employee->employee_id ?? '—' }}</td></tr>
                            <tr><td class="lbl">{{ __('Department') }}</td><td class="val">{{ optional($employee->department)->name ?? '—' }}</td></tr>
                            <tr><td class="lbl">{{ __('PAN No.') }}</td><td class="val">{{ $employee->tax_payer_id ?? '—' }}</td></tr>
                            <tr><td class="lbl">{{ __('Date of Joining') }}</td><td class="val">{{ $employee->company_doj ?? '—' }}</td></tr>
                        </table>
                    </td>
                    <td style="width:50%; padding:0; border-left:1px solid #b2e0e3; vertical-align:top;">
                        <div class="ps-band">{{ __('Payment Details') }}</div>
                        <table class="ps-t">
                            <tr><td class="lbl">{{ __('Bank Name') }}</td><td class="val">{{ $employee->bank_name ?? '—' }}</td></tr>
                            <tr><td class="lbl">{{ __('Account No.') }}</td><td class="val">{{ $employee->account_number ?? '—' }}</td></tr>
                            <tr><td class="lbl">{{ __(\App\Models\Utility::bankCodeLabel()) }}</td><td class="val">{{ $employee->bank_identifier_code ?? '—' }}</td></tr>
                            <tr><td class="lbl">{{ __('Account Holder') }}</td><td class="val">{{ $employee->account_holder_name ?? '—' }}</td></tr>
                            <tr><td class="lbl">{{ __('Transaction Mode') }}</td><td class="val">NEFT</td></tr>
                            <tr><td class="lbl">{{ __('Pay Period') }}</td><td class="val">{{ $slipLabel }}</td></tr>
                        </table>
                    </td>
                </tr>
            </table>

            {{-- B: Leave | Days & Work | Salary --}}
            <table class="ps-t" style="border:none; border-top:1px solid #b2e0e3;">
                <tr>
                    <td style="width:34%; padding:0; border:none; vertical-align:top;">
                        <div class="ps-band">{{ __('Leave Details') }}</div>
                        <table class="ps-t">
                            <tr>
                                <th style="width:50%;">{{ __('This Month') }}</th>
                                <th>{{ __('Overall') }}</th>
                            </tr>
                            <tr>
                                <td>{{ __('Approved') }}: <strong class="v-green">{{ $approvedLeaves }}</strong></td>
                                <td>{{ __('Allocated') }}: <strong>{{ $totalLeaveAlloc }}</strong></td>
                            </tr>
                            <tr>
                                <td>{{ __('Rejected') }}: <strong class="v-red">{{ $disapprovedLeaves }}</strong></td>
                                <td>{{ __('Remaining') }}: <strong class="v-blue">{{ $remainingLeaves }}</strong></td>
                            </tr>
                            <tr>
                                <td colspan="2" style="font-size:10.5px; color:#777; font-style:italic; padding:4px 10px;">
                                    {{ __('Used') }}: {{ $usedLeaves }} {{ __('days') }}
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td style="width:28%; padding:0; border-left:1px solid #b2e0e3; border-right:1px solid #b2e0e3; vertical-align:top;">
                        <div class="ps-band">{{ __('Days & Work Details') }}</div>
                        <table class="ps-t">
                            <tr>
                                <td class="lbl" style="width:60%;">{{ __('Working Days') }}</td>
                                <td class="ra val v-teal">{{ $officeDays }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">{{ __('Present Days') }}</td>
                                <td class="ra val v-green">{{ $presentDays }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">{{ __('Leave Days') }}</td>
                                <td class="ra val v-orange">{{ $approvedLeaves }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">{{ __('Absent Days') }}</td>
                                <td class="ra val v-red">{{ $absentDays }}</td>
                            </tr>
                            @if($extraDays > 0)
                            <tr style="background:#fff8e1;">
                                <td class="lbl" style="color:#856404;">{{ __('Extra / OT Days') }}</td>
                                <td class="ra" style="color:#856404; font-weight:700;">{{ $extraDays }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td class="lbl">{{ __('Total Hrs Worked') }}</td>
                                <td class="ra val">{{ $totalWorkHours }} hrs</td>
                            </tr>
                            <tr>
                                <td class="lbl">{{ __('Avg Hrs / Day') }}</td>
                                <td class="ra val">{{ $avgHrsPerDay }} hrs</td>
                            </tr>
                        </table>
                    </td>
                    <td style="width:38%; padding:0; vertical-align:top; border:none;">
                        <div class="ps-band">{{ __('Salary Details') }}</div>
                        <table class="ps-t">
                            <tr>
                                <td class="lbl" style="width:56%;">{{ __('Monthly Salary') }}</td>
                                <td class="ra val v-teal">{{ number_format($payslip->basic_salary) }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">{{ __('Per Day Rate') }}</td>
                                <td class="ra val">{{ number_format($perDaySalary, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">{{ __('Per Hour Rate') }}</td>
                                <td class="ra val">{{ number_format($perHourSalary, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">{{ __('Days Paid') }}</td>
                                <td class="ra val v-blue">{{ $daysPaid }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">{{ __('Pay Period') }}</td>
                                <td class="ra val" style="font-size:11px;">{{ $slipLabel }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">{{ __('Pay Status') }}</td>
                                <td class="ra val">
                                    @if($isPaid)
                                        <span class="badge-paid">{{ __('Paid') }}</span>
                                    @else
                                        <span class="badge-unpaid">{{ __('Unpaid') }}</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            {{-- C: Earnings | Deductions --}}
            <table class="ps-t" style="border:none; border-top:1px solid #b2e0e3;">
                <tr>
                    <td style="width:50%; padding:0; border:none; vertical-align:top;">
                        <div class="ps-band">{{ __('Earnings') }}</div>
                        <table class="ps-t">
                            <tr>
                                <th style="text-align:left; padding-left:10px; width:65%;">{{ __('Description') }}</th>
                                <th class="ra">{{ __('Amount') }}</th>
                            </tr>
                            @foreach($earRows as $er)
                            <tr>
                                <td style="padding-left:10px;">{{ $er['label'] }}</td>
                                <td class="ra">
                                    @if($er['amount'] !== null){{ number_format($er['amount']) }}@endif
                                </td>
                            </tr>
                            @endforeach
                            <tr class="ps-total">
                                <td style="padding-left:10px;"><strong>{{ __('Total Earnings') }}</strong></td>
                                <td class="ra"><strong>{{ number_format($totalEarnings) }}</strong></td>
                            </tr>
                        </table>
                    </td>
                    <td style="width:50%; padding:0; border-left:1px solid #b2e0e3; vertical-align:top;">
                        <div class="ps-band">{{ __('Deductions') }}</div>
                        <table class="ps-t">
                            <tr>
                                <th style="text-align:left; padding-left:10px; width:65%;">{{ __('Description') }}</th>
                                <th class="ra">{{ __('Amount') }}</th>
                            </tr>
                            @foreach($dedRows as $dr2)
                            <tr>
                                <td style="padding-left:10px;">{{ $dr2['label'] }}</td>
                                <td class="ra">
                                    @if($dr2['amount'] !== null)
                                        {{ number_format($dr2['amount']) }}
                                    @elseif(($dr2['label'] ?? '') !== '')
                                        &mdash;
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                            <tr class="ps-total">
                                <td style="padding-left:10px;"><strong>{{ __('Total Deductions') }}</strong></td>
                                <td class="ra"><strong>{{ number_format($totalDeductions) }}</strong></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            {{-- D: Stamp | Net Pay --}}
            <table class="ps-t ps-net-row" style="border:none;">
                <tr>
                    <td class="ps-stamp-td">
                        <div class="ps-stamp-ring">{{ $companyName }}</div>
                        <div style="font-size:10px; color:#888; margin-top:6px;">{{ __('Authorized Seal') }}</div>
                    </td>
                    <td class="ps-net-td">
                        <table class="ps-net-inner">
                            <tr>
                                <td class="ps-net-lbl">{{ __('Net Pay') }}</td>
                                <td class="ps-net-val">{{ number_format($netSalary) }}/-</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            {{-- E: Signatures --}}
            <div class="ps-sig">
                <table>
                    <tr>
                        <td>
                            <div class="ps-sig-line"></div>
                            <div class="ps-sig-label">{{ __('Employee Signature') }}</div>
                        </td>
                        <td>
                            <div class="ps-sig-line"></div>
                            <div class="ps-sig-label">{{ __('Authorized Signatory') }}</div>
                        </td>
                    </tr>
                </table>
            </div>

            {{-- Footer --}}
            <div class="ps-footer">
                <table>
                    <tr>
                        <td style="width:33%;">
                            @if($companyPhone)<strong>{{ __('Tel') }}:</strong> {{ $companyPhone }}@endif
                        </td>
                        <td style="width:34%; text-align:center;">
                            @if($companyWeb)<strong>{{ __('Web') }}:</strong> {{ $companyWeb }}@endif
                        </td>
                        <td style="width:33%; text-align:right;">
                            @if($companyEmail)<strong>{{ __('Email') }}:</strong> {{ $companyEmail }}@endif
                        </td>
                    </tr>
                </table>
            </div>

        </div>{{-- /.ps-card --}}
    </div>{{-- /.ps-wrap --}}
</div>
@endsection

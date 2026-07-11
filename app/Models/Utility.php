<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Mail;
use App\Mail\CommonEmailTemplate;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Twilio\Rest\Client;
use App\Models\Holiday as LocalHoliday;
use Spatie\GoogleCalendar\Event as GoogleEvent;

class Utility extends Model
{
    private static $getsettings = null;
    private static $languages = null;
    private static $settings = [];   // keyed by creatorId — isolates cache per user
    private static $colorset = null;
    private static $payments = null;
    private static $cookies = null;

    public static $arrShift = [
        '7' => 'Weekly',
        '15' => 'Biweekly',
        '30' => 'Monthly',
    ];

    public static function settings()
    {
        $key = \Auth::check() ? \Auth::user()->creatorId() : 0;

        if (!isset(self::$settings[$key])) {
            self::$settings[$key] = self::fetchSettings();
        }
        return self::$settings[$key];
    }

    /**
     * Bust the in-process static cache for the current user so the next
     * call to settings() re-reads fresh rows from the database.
     * Call this after any settings save operation.
     */
    public static function clearSettingsCache(): void
    {
        $key = \Auth::check() ? \Auth::user()->creatorId() : 0;
        unset(self::$settings[$key]);
        self::$colorset = null; // colorset also reads from settings table
    }

    public static function fetchSettings($user_id = null)
    {
        if ($user_id != null) {
            // Explicit user_id passed — load that user's settings and return immediately.
            $user = User::where('id', $user_id)->first();
            $data = $user
                ? DB::table('settings')->where('created_by', '=', $user_id)->get()
                : DB::table('settings')->where('created_by', '=', 1)->get();
        } elseif (\Auth::check()) {
            $data = DB::table('settings')->where('created_by', '=', \Auth::user()->creatorId())->get();
            if (count($data) == 0) {
                $data = DB::table('settings')->where('created_by', '=', 1)->get();
            }
        } else {
            $data = DB::table('settings')->where('created_by', '=', 1)->get();
        }

        $settings = [
            "site_currency" => "USD",
            "site_currency_symbol" => "$",
            "site_currency_symbol_position" => "pre",
            "site_date_format" => "M j, Y",
            "site_time_format" => "g:i A",
            "company_name" => "",
            "company_address" => "",
            "company_city" => "",
            "company_state" => "",
            "company_zipcode" => "",
            "company_country" => "",
            "company_telephone" => "",
            "company_email" => "",
            "company_email_from_name" => "",
            "employee_prefix" => "#EMP00",
            "footer_title" => "",
            "footer_notes" => "",
            "company_start_time" => "09:00",
            "company_end_time" => "18:00",
            "company_start_time1" => "",
            "company_end_time1" => "",
            "company_start_time2" => "",
            "company_end_time2" => "",
            "company_start_time3" => "",
            "company_end_time3" => "",
            'new_user' => '1',
            'new_employee' => '1',
            'new_payroll' => '1',
            'attendance_request' => '1',
            'new_ticket' => '1',
            'new_award' => '1',
            'employee_transfer' => '1',
            'employee_resignation' => '1',
            'employee_trip' => '1',
            'employee_promotion' => '1',
            'employee_complaints' => '1',
            'employee_warning' => '1',
            'employee_termination' => '1',
            'leave_status' => '1',
            'contract' => '1',
            'new_leave_request' => '1',
            'salary_revision_created'  => '1',
            'salary_revision_updated'  => '1',
            'salary_revision_approved' => '1',
            'salary_revision_applied'  => '1',
            'salary_revision_rejected' => '1',
            "default_language" => "en",
            "display_landing_page" => "on",
            "ip_restrict" => "on",
            "shift_change" => "",
            "login_deley_min" => "0",
            "logout_lead_time" => "0",
            "shift_turner" => "",
            "title_text" => "",
            "footer_text" => "",
            "gdpr_cookie" => "",
            "cookie_text" => "",
            "metakeyword" => "",
            "metadesc" => "",
            "zoom_account_id" => "",
            "zoom_client_id" => "",
            "zoom_client_secret" => "",
            'disable_signup_button' => "on",
            "theme_color" => "theme-2",
            "cust_theme_bg" => "on",
            "cust_darklayout" => "off",
            "SITE_RTL" => "off",
            "company_logo" => 'logo-dark.png',
            "company_logo_light" => 'logo-light.png',
            "company_stamp" => '',
            "dark_logo" => "logo-dark.png",
            "light_logo" => "logo-light.png",
            "contract_prefix" => "#CON",
            "storage_setting" => "local",
            "local_storage_validation" => "jpg,jpeg,png,xlsx,xls,csv,pdf",
            "local_storage_max_upload_size" => "2048000",
            "s3_key" => "",
            "s3_secret" => "",
            "s3_region" => "",
            "s3_bucket" => "",
            "s3_url" => "",
            "s3_endpoint" => "",
            "s3_max_upload_size" => "",
            "s3_storage_validation" => "",
            "wasabi_key" => "",
            "wasabi_secret" => "",
            "wasabi_region" => "",
            "wasabi_bucket" => "",
            "wasabi_url" => "",
            "wasabi_root" => "",
            "wasabi_max_upload_size" => "",
            "wasabi_storage_validation" => "",
            "google_clender_id" => "",
            "google_calender_json_file" => "",
            "is_enabled" => "",
            "email_verification" => "",
            // "seo_is_enabled" => "",
            "meta_title" => "",
            "meta_image" => "",
            "meta_description" => "",
            'enable_cookie' => 'on',
            'necessary_cookies' => 'on',
            'cookie_logging' => 'on',
            'cookie_title' => 'We use cookies!',
            'cookie_description' => 'Hi, this website uses essential cookies to ensure its proper operation and tracking cookies to understand how you interact with it',
            'strictly_cookie_title' => 'Strictly necessary cookies',
            'strictly_cookie_description' => 'These cookies are essential for the proper functioning of my website. Without these cookies, the website would not work properly',
            'more_information_description' => 'For any queries in relation to our policy on cookies and your choices, please contact us',
            'contactus_url' => '#',
            'chatgpt_key' => '',
            'chatgpt_model' => '',
            'enable_chatgpt' => '',
            'mail_driver' => '',
            'mail_host' => '',
            'mail_port' => '',
            'mail_username' => '',
            'mail_password' => '',
            'mail_encryption' => '',
            'mail_from_address' => '',
            'mail_from_name' => '',
            'timezone' => '',
            'pusher_app_id' => '',
            'pusher_app_key' => '',
            'pusher_app_secret' => '',
            'pusher_app_cluster' => '',
            'recaptcha_module' => '',
            'google_recaptcha_key' => '',
            'google_recaptcha_secret' => '',
            'google_recaptcha_version' => '',
            'color_flag' => 'false',
            'zkteco_api_url' => '',
            'username' => '',
            'user_password' => '',
            'auth_token' => '',
            'refreshtime' => '',
            'lunch_start' => '',
            'lunch_end' => '',
            'tea_start' => '',
            'tea_end' => '',
            'lunch_minutes' => '',
            'tea_minutes' => '',
            'company_shifts' => '',
            'rota_working_days' => '1,2,3,4,5',
            'saturday_pattern' => 'none',   // none | all | odd | even
            'working_hours_per_day' => '8', // default working hours per day
        ];

        foreach ($data as $row) {
            $settings[$row->name] = $row->value;
        }

        return $settings;
    }

    public static function getStorageSetting()
    {
        $data = DB::table('settings');
        $data = $data->where('created_by', '=', 1);
        $data = $data->get();
        $settings = [

            "storage_setting" => "local",
            "local_storage_validation" => "jpg,jpeg,png,xlsx,xls,csv,pdf",
            "local_storage_max_upload_size" => "2048000",
            "s3_key" => "",
            "s3_secret" => "",
            "s3_region" => "",
            "s3_bucket" => "",
            "s3_url" => "",
            "s3_endpoint" => "",
            "s3_max_upload_size" => "",
            "s3_storage_validation" => "",
            "wasabi_key" => "",
            "wasabi_secret" => "",
            "wasabi_region" => "",
            "wasabi_bucket" => "",
            "wasabi_url" => "",
            "wasabi_root" => "",
            "wasabi_max_upload_size" => "",
            "wasabi_storage_validation" => "",
        ];

        foreach ($data as $row) {
            $settings[$row->name] = $row->value;
        }
        return $settings;
    }


    // get date format
    public static function getDateFormated($date, $time = false)
    {
        if (!empty($date) && $date != '0000-00-00') {
            if ($time == true) {
                return date("d M Y H:i A", strtotime($date));
            } else {
                return date("d M Y", strtotime($date));
            }
        } else {
            return '';
        }
    }

    public static function languages()
    {
        if (self::$languages === null) {
            self::$languages = self::fetchlanguages();
        }
        return self::$languages;
    }

    public static function fetchlanguages()
    {
        $languages = Utility::langList();

        if (\Schema::hasTable('languages')) {
            $settings = self::settings();
            if (!empty($settings['disable_lang'])) {
                $disabledlang = explode(',', $settings['disable_lang']);
                $languages = Languages::whereNotIn('code', $disabledlang)->pluck('fullName', 'code');
            } else {
                $languages = Languages::pluck('fullName', 'code');
            }
        }

        return $languages;
    }

    public static function getValByName($key)
    {
        $setting = Utility::settings();
        if (!isset($setting[$key]) || empty($setting[$key])) {
            $setting[$key] = '';
        }
        return $setting[$key];
    }

    public static function setEnvironmentValue(array $values)
    {
        $envFile = app()->environmentFilePath();
        $str = file_get_contents($envFile);
        if (count($values) > 0) {
            foreach ($values as $envKey => $envValue) {
                $keyPosition = strpos($str, "{$envKey}=");
                $endOfLinePosition = strpos($str, "\n", $keyPosition);
                $oldLine = substr($str, $keyPosition, $endOfLinePosition - $keyPosition);
                // If key does not exist, add it
                if (!$keyPosition || !$endOfLinePosition || !$oldLine) {
                    $str .= "{$envKey}='{$envValue}'\n";
                } else {
                    $str = str_replace($oldLine, "{$envKey}='{$envValue}'", $str);
                }
            }
        }
        $str = substr($str, 0, -1);
        $str .= "\n";
        if (!file_put_contents($envFile, $str)) {
            return false;
        }

        return true;
    }

    public static $emailStatus = [
        'new_user' => 'New User',
        'new_employee' => 'New Employee',
        'new_payroll' => 'New Payroll',
        'attendance_request' => 'Attendance Request',
        'new_ticket' => 'New Ticket',
        'new_award' => 'New Award',
        'employee_transfer' => 'Employee Transfer',
        'employee_resignation' => 'Employee Resignation',
        'employee_trip' => 'Employee Trip',
        'employee_promotion' => 'Employee Promotion',
        'employee_complaints' => 'Employee Complaints',
        'employee_warning' => 'Employee Warning',
        'employee_termination' => 'Employee Termination',
        'leave_status' => 'Leave Status',
        'contract' => 'Contract',
        'new_leave_request' => 'New Leave Request',
        'salary_revision_created'  => 'Salary Revision Created',
        'salary_revision_updated'  => 'Salary Revision Updated',
        'salary_revision_approved' => 'Salary Revision Approved',
        'salary_revision_applied'  => 'Salary Revision Applied',
        'salary_revision_rejected' => 'Salary Revision Rejected',
    ];

    /**
     * Returns true if the given Saturday is a working day based on the pattern.
     * Patterns: 'all' = every Saturday, 'none' = no Saturday,
     *           'odd' = 1st/3rd/5th, 'even' = 2nd/4th
     */
    public static function isSaturdayWorking(Carbon $date, string $pattern): bool
    {
        if ($pattern === 'all')  return true;
        if ($pattern === 'none') return false;

        $occurrence = (int) ceil($date->day / 7);

        if ($pattern === 'odd')  return ($occurrence % 2) === 1;
        if ($pattern === 'even') return ($occurrence % 2) === 0;

        return false;
    }

    /**
     * Returns the correct label for the bank routing code field based on the
     * visitor's IP address country (detected via ip-api.com — free, no key needed).
     * Results are cached per IP for 24 hours to avoid repeated HTTP calls.
     *
     * Country → label mapping:
     *   GB (United Kingdom)  → 'Sort Code'
     *   AU (Australia)       → 'BSB Number'
     *   US / CA              → 'Routing Number'
     *   Most of Europe       → 'BIC / SWIFT'
     *   Everything else      → 'IFSC Code'
     */
    public static function bankCodeLabel(): string
    {
        try {
            $ip = request()->ip();

            // Treat localhost / private IPs as unknown — return default
            if (
                $ip === '127.0.0.1' ||
                $ip === '::1' ||
                str_starts_with($ip, '192.168.') ||
                str_starts_with($ip, '10.') ||
                str_starts_with($ip, '172.')
            ) {
                return 'IFSC Code';
            }

            $countryCode = \Cache::remember('ip_country_' . $ip, 86400, function () use ($ip) {
                try {
                    $client   = new \GuzzleHttp\Client(['timeout' => 3]);
                    $response = $client->get("http://ip-api.com/json/{$ip}?fields=countryCode");
                    $data     = json_decode($response->getBody()->getContents(), true);
                    return strtoupper($data['countryCode'] ?? '');
                } catch (\Throwable $e) {
                    return '';
                }
            });
        } catch (\Throwable $e) {
            $countryCode = '';
        }

        return match ($countryCode) {
            'GB', 'UK'              => 'Sort Code',
            'AU'                    => 'BSB Number',
            'US', 'CA'              => 'Routing Number',
            'DE', 'FR', 'ES', 'IT',
            'NL', 'BE', 'PT', 'AT',
            'CH', 'SE', 'DK', 'NO',
            'FI', 'IE', 'PL', 'CZ',
            'HU', 'RO', 'GR'       => 'BIC / SWIFT',
            default                 => 'IFSC Code',
        };
    }

    /**
     * Returns true if the current HTTP request originates from a UK IP address.
     * Reuses the same ip-api.com detection + 24h cache as bankCodeLabel().
     * Always returns false for localhost / private IPs.
     */
    public static function isUkRequest(): bool
    {
        try {
            $ip = request()->ip();

            // Localhost / private network — not UK
            if (
                $ip === '127.0.0.1' ||
                $ip === '::1' ||
                str_starts_with($ip, '192.168.') ||
                str_starts_with($ip, '10.') ||
                str_starts_with($ip, '172.')
            ) {
                return false;
            }

            $countryCode = \Cache::remember('ip_country_' . $ip, 86400, function () use ($ip) {
                try {
                    $client   = new \GuzzleHttp\Client(['timeout' => 3]);
                    $response = $client->get("http://ip-api.com/json/{$ip}?fields=countryCode");
                    $data     = json_decode($response->getBody()->getContents(), true);
                    return strtoupper($data['countryCode'] ?? '');
                } catch (\Throwable $e) {
                    return '';
                }
            });
        } catch (\Throwable $e) {
            return false;
        }

        return in_array($countryCode, ['GB', 'UK'], true);
    }

    /**
     * Count actual working days in a given month, excluding weekends (per
     * rota_working_days), alternate Saturdays (saturday_pattern), and
     * public holidays for the given creator.
     */
    public static function getWorkingDaysInMonth($year, $month, $createdBy)
    {
        $monthStart = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $monthEnd   = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        $settings        = self::settings();
        $wdRaw           = $settings['rota_working_days'] ?? '1,2,3,4,5';
        $workingDayNums  = array_map(
            'intval',
            array_filter(array_map('trim', explode(',', $wdRaw)), 'strlen')
        );
        $satPattern      = $settings['saturday_pattern'] ?? 'none';

        // Load holidays
        $holidayModels = Holiday::where('created_by', $createdBy)
            ->where('start_date', '<=', $monthEnd->format('Y-m-d'))
            ->where('end_date', '>=', $monthStart->format('Y-m-d'))
            ->get();

        $holidayDates = [];
        foreach ($holidayModels as $holiday) {
            foreach (CarbonPeriod::create($holiday->start_date, $holiday->end_date) as $day) {
                $holidayDates[$day->format('Y-m-d')] = true;
            }
        }

        $workingDays = 0;

        foreach (CarbonPeriod::create($monthStart, $monthEnd) as $date) {
            $dateStr = $date->format('Y-m-d');
            $dow     = (int) $date->dayOfWeek;

            $isHoliday = isset($holidayDates[$dateStr]);
            $isDayOff  = !empty($workingDayNums) && !in_array($dow, $workingDayNums);

            // Saturday pattern
            if ($dow === 6 && in_array(6, $workingDayNums)) {
                $occurrence = (int) ceil($date->day / 7);

                $isSaturdayWorking = match ($satPattern) {
                    'odd'  => $occurrence % 2 === 1,
                    'even' => $occurrence % 2 === 0,
                    'none' => false,
                    default => true,
                };

                $isDayOff = !$isSaturdayWorking;

                // Working Saturday should not be treated as holiday
                if ($isSaturdayWorking) {
                    $isHoliday = false;
                }
            }

            if (!$isHoliday && !$isDayOff) {
                $workingDays++;
            }
        }

        return $workingDays;
    }

    public static function employeePayslipDetail($employeeId, $month)
    {
        $employess = Employee::find($employeeId);

        // allowance
        $earning['allowance'] = PaySlip::where('employee_id', $employeeId)->where('salary_month', $month)->get();
        $totalAllowance = 0;

        $arrayJson = json_decode($earning['allowance']);
        foreach ($arrayJson as $earn) {
            $allowancejson = json_decode($earn->allowance);
            foreach ($allowancejson as $allowances) {
                if ($allowances->type == 'percentage') {
                    $empall = $allowances->amount * $earn->basic_salary / 100;
                } else {
                    $empall = $allowances->amount;
                }
                $totalAllowance += $empall;
            }
        }

        // commission
        $earning['commission'] = PaySlip::where('employee_id', $employeeId)->where('salary_month', $month)->get();
        $totalCommission = 0;

        $arrayJson = json_decode($earning['commission']);
        foreach ($arrayJson as $earn) {
            $commissionjson = json_decode($earn->commission);
            foreach ($commissionjson as $commissions) {
                if ($commissions->type == 'percentage') {
                    $empcom = $commissions->amount * $earn->basic_salary / 100;
                } else {
                    $empcom = $commissions->amount;
                }
                $totalCommission += $empcom;
            }
        }

        // bonous
        $earning['bonous'] = Bonous::where('employee_id', $employeeId)->get();
        $totalBonous = 0;
        foreach ($earning['bonous'] as $earn) {
            if ($earn->type == 'percentage') {
                $empbon = $earn->amount * $employess->salary / 100;
            } else {
                $empbon = $earn->amount;
            }
            $totalBonous += $empbon;
        }

        // Perk
        $earning['pearks'] = Peark::where('employee_id', $employeeId)->get();
        $totalPearks = 0;
        foreach ($earning['pearks'] as $peark) {
            $totalPearks += $peark->amount;
        }

        // otherpayment
        $earning['otherPayment'] = PaySlip::where('employee_id', $employeeId)->where('salary_month', $month)->get();
        $totalotherpayment = 0;

        $arrayJson = json_decode($earning['otherPayment']);
        foreach ($arrayJson as $earn) {
            $otherpaymentjson = json_decode($earn->other_payment);
            foreach ($otherpaymentjson as $otherpay) {
                if ($otherpay->type == 'percentage') {
                    $empotherpay = $otherpay->amount * $earn->basic_salary / 100;
                } else {
                    $empotherpay = $otherpay->amount;
                }
                $totalotherpayment += $empotherpay;
            }
        }
        //overtime
        $earning['overTime'] = Payslip::where('employee_id', $employeeId)->where('salary_month', $month)->get();
        $ot = 0;

        $arrayJson = json_decode($earning['overTime']);
        foreach ($arrayJson as $overtime) {
            $overtimes = json_decode($overtime->overtime);
            foreach ($overtimes as $overt) {
                $OverTime = $overt->number_of_days * $overt->hours * $overt->rate;
                $ot += $OverTime;
            }
        }
        // loan (earning — money given to employee)
        $earning['loan'] = PaySlip::where('employee_id', $employeeId)->where('salary_month', $month)->get();
        $totalloan = 0;

        $arrayJson = json_decode($earning['loan']);
        foreach ($arrayJson as $loan) {
            $loans = json_decode($loan->loan);
            foreach ($loans as $emploans) {
                if ($emploans->type == 'percentage') {
                    $emploan = $emploans->amount * $loan->basic_salary / 100;
                } else {
                    $emploan = $emploans->amount;
                }
                $totalloan += $emploan;
            }
        }

        // loan repayment (deduction — money employee pays back)
        $loanRepayments = LoanRepayment::where('employee_id', $employeeId)->get();
        $totalLoanRepayment = 0;
        foreach ($loanRepayments as $repay) {
            if ($repay->type == 'percentage') {
                $repayAmt = $repay->amount * $employess->salary / 100;
            } else {
                $repayAmt = $repay->amount;
            }
            $totalLoanRepayment += $repayAmt;
        }

        // saturation_deduction
        $deduction['saturation_deduction'] = PaySlip::where('employee_id', $employeeId)->where('salary_month', $month)->get();
        $totaldeduction = 0;
        $arrayJson = json_decode($deduction['saturation_deduction']);
        foreach ($arrayJson as $deductions) {
            $deduc = json_decode($deductions->saturation_deduction);
            foreach ($deduc as $deduction_option) {
                // Resolve the current DeductionOption name (reflects admin renames)
                if (!empty($deduction_option->deduction_option)) {
                    $currentOptName = DeductionOption::where('id', $deduction_option->deduction_option)->value('name');
                    if ($currentOptName) {
                        $deduction_option->title = $currentOptName;
                    }
                }
                $emp = Employee::find($employeeId);

                if ($deduction_option->type == 'percentage') {
                    if (strtolower($deduction_option->title) == 'epf') {
                        $empdeduction = ($emp->get_net_da() + $deductions->net_salary) * 12/ 100;
                    } elseif (strtolower($deduction_option->title) == 'gpf') {
                        $empdeduction = ($emp->get_net_da() + $deductions->net_salary) * 6   / 100;
                    } else {
                        $empdeduction = $deduction_option->amount * $deductions->basic_salary / 100;
                    }
                } else {
                    $empdeduction = $deduction_option->amount;
                }
                $totaldeduction += $empdeduction;
            }
        }
        // pansion
        $deduction['pansion'] = Pension::where('employee_id', $employeeId)->get();
        $totalPansion = 0;

        foreach ($deduction['pansion'] as $earn) {
            if ($earn->type == 'percentage') {
                $emppansion = $earn->amount * $employess->salary / 100;
            } else {
                $emppansion = $earn->amount;
            }
            $totalPansion += $emppansion;
        }

        // ── Parse the payslip month (format: YYYY-MM) ───────────────────────
        [$payYear, $payMonth] = explode('-', $month);
        $payYear  = (int) $payYear;
        $payMonth = (int) $payMonth;

        $monthStart = Carbon::createFromDate($payYear, $payMonth, 1)->startOfMonth();
        $monthEnd   = Carbon::createFromDate($payYear, $payMonth, 1)->endOfMonth();

        // Use the payslip month's salary snapshot from pay_slips table
        $payslipRecord = PaySlip::where('employee_id', $employeeId)
            ->where('salary_month', $month)
            ->first();
        $salary = $payslipRecord ? (float) $payslipRecord->basic_salary : (float) $employess->salary;
        $net_salary = $payslipRecord ? (float) $payslipRecord->net_salary : (float) $employess->salary;

        // Working days via shared utility (same logic as attendance report)
        $workingDaysInMonth = self::getWorkingDaysInMonth($payYear, $payMonth, $employess->created_by);

        // ── Hours per day: priority order ─────────────────────────────────────
        // 1. Explicit 'working_hours_per_day' setting (set by admin in Company Settings)
        // 2. Calculated from company_start_time / company_end_time shift times
        // 3. Fallback: 8 hours
        $companySettings = self::settings();

        $hoursPerDayShift = null;

        // Source 1 — explicit setting
        $explicitHpd = isset($companySettings['working_hours_per_day'])
            ? (float) $companySettings['working_hours_per_day']
            : 0;
        if ($explicitHpd > 0) {
            $hoursPerDayShift = $explicitHpd;
        }

        // Source 2 — derive from shift start/end times (only if explicit not set)
        if ($hoursPerDayShift === null) {
            $shiftStart = $companySettings['company_start_time'] ?? '';
            $shiftEnd   = $companySettings['company_end_time']   ?? '';
            if (!empty($shiftStart) && !empty($shiftEnd)) {
                try {
                    $shiftDiff = Carbon::parse($shiftStart)->diffInMinutes(Carbon::parse($shiftEnd));
                    if ($shiftDiff > 0) {
                        $hoursPerDayShift = round($shiftDiff / 60, 2);
                    }
                } catch (\Throwable $_e) {
                }
            }
        }

        // Source 3 — absolute fallback
        if ($hoursPerDayShift === null || $hoursPerDayShift <= 0) {
            $hoursPerDayShift = 8.0;
        }

        $totalShiftHoursInMonth = $workingDaysInMonth * $hoursPerDayShift;

        // ── Determine salary type: monthly or hourly ──────────────────────────
        // Use the salary_basis column (added migration 2026_06_17) — reliable enum.
        // Falls back to name-matching for legacy records that pre-date the column.
        $payslipTypeRecord = PayslipType::find($employess->salary_type);
        if ($payslipTypeRecord) {
            $isHourly = ($payslipTypeRecord->salary_basis ?? 'monthly') === 'hourly';
        } else {
            // Legacy fallback: detect from name string
            $isHourly = str_contains(strtolower(trim($payslipTypeRecord?->name ?? '')), 'hour');
        }
        $monthlySalaryGross = $salary;
        if ($isHourly) {
            // $salary stored IS the hourly rate directly (e.g. 140)
            // gross = hourly_rate × actual_hours_worked (calculated after attendance loop)
            $per_hour_amount    = $salary;
            $per_day_amount     = round($salary * $hoursPerDayShift, 4);
        } else {
            // $salary stored is the MONTHLY fixed salary

            $per_day_amount      = $workingDaysInMonth > 0
                ? round($salary / $workingDaysInMonth, 4)
                : 0;
            $per_hour_amount     = $hoursPerDayShift > 0
                ? round($per_day_amount / $hoursPerDayShift, 4)
                : 0;
        }

        // ── Attendance: effective present days with half-day support ───────
        // Fetch all clocked-in rows for the month grouped by date
        $attRows = AttendanceEmployee::where('employee_id', $employeeId)
            ->whereMonth('date', $payMonth)
            ->whereYear('date',  $payYear)
            ->whereIn('status', ['present', 'Present'])
            ->orderBy('date')
            ->get(['date', 'clock_in', 'clock_out']);

        // Deduplicate by date — earliest clock_in, latest clock_out per day
        $attByDate = [];
        foreach ($attRows as $r) {
            $dk = substr((string)$r->date, 0, 10);
            if (!isset($attByDate[$dk])) {
                $attByDate[$dk] = ['clock_in' => $r->clock_in, 'clock_out' => $r->clock_out];
            } else {
                if ($r->clock_in  && $r->clock_in  < $attByDate[$dk]['clock_in'])  $attByDate[$dk]['clock_in']  = $r->clock_in;
                if ($r->clock_out && $r->clock_out > $attByDate[$dk]['clock_out']) $attByDate[$dk]['clock_out'] = $r->clock_out;
            }
        }

        // ── Pre-load holiday and working-day settings for day-off detection ─
        // (needed both in the attendance loop and the leave loop below)
        $settingsEarly   = self::settings();
        $wdRawEarly      = $settingsEarly['rota_working_days'] ?? '1,2,3,4,5';
        $wdNumsEarly     = array_map('intval', array_filter(array_map('trim', explode(',', $wdRawEarly)), 'strlen'));
        $satPatternEarly = $settingsEarly['saturday_pattern'] ?? 'none';

        $holidayModelsEarly = Holiday::where('created_by', $employess->created_by)
            ->where('start_date', '<=', $monthEnd->format('Y-m-d'))
            ->where('end_date',   '>=', $monthStart->format('Y-m-d'))
            ->get();
        $holidayDatesEarly = [];
        foreach ($holidayModelsEarly as $hol) {
            foreach (CarbonPeriod::create($hol->start_date, $hol->end_date) as $hDay) {
                $holidayDatesEarly[$hDay->format('Y-m-d')] = true;
            }
        }

        // Helper: is a given date-string a working day (not holiday, not day-off)?
        $isWorkingDay = function (string $dateStr) use ($wdNumsEarly, $satPatternEarly, $holidayDatesEarly): bool {
            $carbonDay = Carbon::parse($dateStr);
            $dow       = (int) $carbonDay->dayOfWeek;
            $isHol     = isset($holidayDatesEarly[$dateStr]);
            $isDO      = !empty($wdNumsEarly) && !in_array($dow, $wdNumsEarly);

            if ($dow === 6 && in_array(6, $wdNumsEarly)) {
                $occurrence = (int) ceil($carbonDay->day / 7);
                $isSatWorking = match ($satPatternEarly) {
                    'odd'   => $occurrence % 2 === 1,
                    'even'  => $occurrence % 2 === 0,
                    'none'  => false,
                    default => true,
                };
                $isDO = !$isSatWorking;
                if ($isSatWorking) $isHol = false;
            }

            return !$isHol && !$isDO;
        };

        // Duration-based classification (mirrors ReportController thresholds exactly):
        //   < 2 hours  (< 120 min) → treat as full-day leave; don't count as present
        //   2–6 hours  (120–360 min) → half day: count as 0.5
        //   >= 6 hours (>= 360 min) → full present: count as 1.0
        //
        // IMPORTANT — matches ReportController behaviour:
        //   If employee clocked ≥ 6h AND has an approved leave → report marks it "L"
        //   (leave takes priority). So we must NOT count it as present in payslip either.
        //
        // Pre-load approved leave dates for cross-reference
        $leaveDatesForHalf = [];
        $leavesForHalf = Leave::where('employee_id', $employeeId)
            ->where('status', 'Approved')
            ->where('start_date', '<=', $monthEnd->format('Y-m-d'))
            ->where('end_date',   '>=', $monthStart->format('Y-m-d'))
            ->get(['start_date', 'end_date']);
        foreach ($leavesForHalf as $lh) {
            foreach (\Carbon\CarbonPeriod::create($lh->start_date, $lh->end_date) as $ld) {
                $ldStr = $ld->format('Y-m-d');
                // Only flag working days — leaves on day-offs/holidays don't affect present
                if ($isWorkingDay($ldStr)) {
                    $leaveDatesForHalf[$ldStr] = true;
                }
            }
        }

        $fullDays = 0;
        $halfDays = 0;
        // $fullDayLeaveFromAtt: present-status rows with no/minimal clock — treated as LOP
        $fullDayLeaveFromAtt = 0;
        foreach ($attByDate as $dk => $dr) {
            // Skip attendance rows that fall on a day-off or holiday —
            // those extra working days are bonuses and should not inflate or deflate counts.
            if (!$isWorkingDay($dk)) {
                continue;
            }

            $dayMins = 0;
            $ciRaw = $dr['clock_in']  ?? '';
            $coRaw = $dr['clock_out'] ?? '';
            // Normalise — treat '00:00:00' as empty
            if ($ciRaw === '00:00:00' || $ciRaw === '00:00') $ciRaw = '';
            if ($coRaw === '00:00:00' || $coRaw === '00:00') $coRaw = '';

            if (!empty($ciRaw) && !empty($coRaw)) {
                try {
                    $ci = Carbon::parse($ciRaw);
                    $co = Carbon::parse($coRaw);
                    if ($co->gt($ci)) $dayMins = $ci->diffInMinutes($co);
                    // Handle overnight shift
                    if ($dayMins <= 0) {
                        $co->addDay();
                        $dayMins = $ci->diffInMinutes($co);
                    }
                } catch (\Throwable $e) {
                }
            }

            $isOnLeaveDay = isset($leaveDatesForHalf[$dk]);

            // Detect "clock_in present but clock_out missing" — employee forgot to clock out.
            // The ReportController treats this as a full present day (status = 'P').
            // We must match that behaviour: count as full present regardless of $dayMins.
            $hasClockIn  = !empty($ciRaw);  // already normalised above
            $hasClockOut = !empty($coRaw);  // already normalised above
            $clockOutMissing = $hasClockIn && !$hasClockOut;

            if ($clockOutMissing) {
                // Clock-in recorded, clock-out missing → treat as full present day
                // (matches ReportController which marks status='Present' rows as 'P'
                //  when dayWorkedSeconds=0 and isHalfDay=false).
                if ($isOnLeaveDay) {
                    // On leave but clocked in without clock-out:
                    // report would mark as L (leave takes priority for full day).
                    // Leave loop handles total_leave_days for this day.
                    // No contribution to present count.
                } else {
                    $fullDays++;
                }
            } elseif ($dayMins === 0) {
                // No clock_in at all on a working day (both fields empty/zero).
                // If on an approved leave → leave loop below handles it.
                // If not on leave → absent/LOP day.
                if (!$isOnLeaveDay) {
                    $fullDayLeaveFromAtt++;
                }
            } elseif ($dayMins > 0 && $dayMins < 120) {
                // < 2h clocked → full-day leave, don't count as present
                $fullDayLeaveFromAtt++;
            } elseif ($dayMins >= 120 && $dayMins < 360) {
                // 2–6h → half day
                // If on leave: report shows "Half Day + Leave" → 0.5 present + 0.5 leave
                // Either way this day contributes 0.5 to present count.
                $halfDays++;
            } else {
                // >= 6h clocked
                if ($isOnLeaveDay) {
                    // Report marks this as "L" (leave takes priority over full-day clock).
                    // Do NOT count as present — leave loop will add it as a leave day.
                } else {
                    // Normal full present day
                    $fullDays++;
                }
            }
        }
        $attendance_count = $fullDays + ($halfDays * 0.5); // effective clocked present days

        // ── Approved leaves that OVERLAP with the payslip month only ────────
        // Settings and holiday map were already loaded above (reuse them).
        // $holidayDatesEarly, $wdNumsEarly, $satPatternEarly, $isWorkingDay() all available.

        $leaves = Leave::where('employee_id', $employeeId)
            ->where('status', 'Approved')
            ->where('start_date', '<=', $monthEnd->format('Y-m-d'))
            ->where('end_date',   '>=', $monthStart->format('Y-m-d'))
            ->with('dayDetails')
            ->get();

        $total_leave_days  = 0;
        $paid_leave_days   = 0.0;   // leave days that are PAID → no salary deduction
        $unpaid_leave_days = 0.0;   // leave days that are UNPAID → deduct from net pay

        foreach ($leaves as $leave) {
            $leaveStart   = Carbon::parse($leave->start_date);
            $leaveEnd     = Carbon::parse($leave->end_date);
            $overlapStart = $leaveStart->greaterThan($monthStart) ? $leaveStart : $monthStart->copy();
            $overlapEnd   = $leaveEnd->lessThan($monthEnd)        ? $leaveEnd   : $monthEnd->copy();

            if (!$overlapStart->lessThanOrEqualTo($overlapEnd)) continue;

            // Build a per-day lookup from leave_day_details for this leave record
            // so we know: is this specific day paid or unpaid, full or half?
            $dayDetailMap = [];
            foreach ($leave->dayDetails as $dd) {
                $dk = $dd->date instanceof Carbon
                    ? $dd->date->format('Y-m-d')
                    : Carbon::parse($dd->date)->format('Y-m-d');
                $dayDetailMap[$dk] = $dd;
            }

            $period = \Carbon\CarbonPeriod::create($overlapStart, $overlapEnd);
            foreach ($period as $leaveDay) {
                $dayStr = $leaveDay->format('Y-m-d');

                // Skip day-offs and holidays — leave on a non-working day costs nothing
                if (!$isWorkingDay($dayStr)) {
                    continue;
                }

                // Resolve paid/unpaid and duration for this specific day
                $dayDetail   = $dayDetailMap[$dayStr] ?? null;
                if ($dayDetail) {
                    $dayDuration  = $dayDetail->day_duration  ?? 'full_day';
                    $dayPayStatus = $dayDetail->day_status    ?? 'paid';
                } else {
                    // No per-day record → use leave-level flags (legacy or single-day)
                    $dayDuration  = $leave->leave_duration  ?? 'full_day';
                    $dayPayStatus = 'paid'; // legacy leaves default to paid
                }

                // Determine how many days this entry consumes (0.5 for half, 1.0 for full)
                // Cross-check with attendance to handle half-day clock-in overlap
                $daysThisEntry = ($dayDuration === 'half_day') ? 0.5 : 1.0;

                if (isset($attByDate[$dayStr])) {
                    $dr      = $attByDate[$dayStr];
                    $dayMins = 0;
                    $ciRaw2 = $dr['clock_in']  ?? '';
                    $coRaw2 = $dr['clock_out'] ?? '';
                    if ($ciRaw2 === '00:00:00' || $ciRaw2 === '00:00') $ciRaw2 = '';
                    if ($coRaw2 === '00:00:00' || $coRaw2 === '00:00') $coRaw2 = '';

                    $hasClockIn2      = !empty($ciRaw2);
                    $hasClockOut2     = !empty($coRaw2);
                    $clockOutMissing2 = $hasClockIn2 && !$hasClockOut2;

                    if (!empty($ciRaw2) && !empty($coRaw2)) {
                        try {
                            $ci = Carbon::parse($ciRaw2);
                            $co = Carbon::parse($coRaw2);
                            if ($co->gt($ci)) $dayMins = $ci->diffInMinutes($co);
                            if ($dayMins <= 0) {
                                $co->addDay();
                                $dayMins = $ci->diffInMinutes($co);
                            }
                        } catch (\Throwable $e) {
                        }
                    }

                    if ($clockOutMissing2) {
                        $daysThisEntry = 1.0; // Full leave day (leave takes priority)
                    } elseif ($dayMins === 0 || ($dayMins > 0 && $dayMins < 120)) {
                        $daysThisEntry = 1.0; // No/minimal clock → full leave day
                    } elseif ($dayMins >= 120 && $dayMins < 360) {
                        $daysThisEntry = 0.5; // Half-day clocked + half leave
                    } else {
                        $daysThisEntry = 1.0; // ≥6h but leave takes priority
                    }
                }

                // Accumulate into the correct bucket
                $total_leave_days += $daysThisEntry;
                if ($dayPayStatus === 'paid') {
                    $paid_leave_days   += $daysThisEntry;
                } else {
                    $unpaid_leave_days += $daysThisEntry;
                }
            }
        }

        // ── Unpaid leave deduction ───────────────────────────────────────────
        // Only UNPAID leave days reduce the salary.
        // Paid leave days are fully covered — no deduction.
        $unpaid_leave_deduction = round($unpaid_leave_days * $per_day_amount, 2);

        // Add unpaid leave as a visible deduction row (only when > 0)
        if ($unpaid_leave_deduction > 0) {
            $deduction['unpaid_leave'][] = (object) [
                'leave_reason'     => 'Unpaid Leave',
                'leave_type'       => 'Unpaid Leave',
                'total_leave_days' => $unpaid_leave_days . ' days',
                'amount'           => $unpaid_leave_deduction,
            ];
        } else {
            $deduction['unpaid_leave'] = [];
        }
        // present_days  = effectively clocked days + approved leave days (both "covered")
        // fullDayLeaveFromAtt = rows with no clock or < 2h — not in attendance_count,
        //   not in total_leave_days — they are the remaining uncovered days (absent/LOP)
        //
        // Formula:
        //   covered    = attendance_count + total_leave_days
        //   uncovered  = workingDays - covered   (these are truly absent days)
        //   LOP days   = uncovered + fullDayLeaveFromAtt
        //   (fullDayLeaveFromAtt days are already excluded from covered, so add them directly)
        $covered_days         = $attendance_count + $total_leave_days;
        $absent_days          = max($workingDaysInMonth - $covered_days, 0);
        $total_absent_for_lop = $absent_days + $fullDayLeaveFromAtt;
        $leave_deduction      = $total_absent_for_lop * $per_day_amount;

        $deduction['leave'][] = (object) [
            'leave_reason'     => 'Loss of Pay',
            'leave_type'       => 'Loss of Pay',
            'total_leave_days' => $total_absent_for_lop . ' days',
            'empleave'         => $leave_deduction,
        ];

        // Rejected leave count for the month (display only)
        $rejectedLeaves = Leave::where('employee_id', $employeeId)
            ->where('status', 'Reject')
            ->where('start_date', '<=', $monthEnd->format('Y-m-d'))
            ->where('end_date',   '>=', $monthStart->format('Y-m-d'))
            ->get();
        $total_rejected_days = 0;
        foreach ($rejectedLeaves as $rl) {
            $rlStart = Carbon::parse($rl->start_date);
            $rlEnd   = Carbon::parse($rl->end_date);
            $roStart = $rlStart->greaterThan($monthStart) ? $rlStart : $monthStart->copy();
            $roEnd   = $rlEnd->lessThan($monthEnd)        ? $rlEnd   : $monthEnd->copy();
            if ($roStart->lessThanOrEqualTo($roEnd)) {
                $total_rejected_days += $roStart->diffInDays($roEnd) + 1;
            }
        }

        // Leave allocation totals (for display on payslip)
        // Use per-employee assigned leave types if available, otherwise fallback to global
        $assignedLeaveTypesForPayslip = $employess->leaveTypes()->get();
        if ($assignedLeaveTypesForPayslip->isNotEmpty()) {
            $totalLeaveAlloc = 0;
            foreach ($assignedLeaveTypesForPayslip as $alt) {
                $pivotDays = $alt->pivot->total_days ?? 0;
                $totalLeaveAlloc += $pivotDays > 0 ? $pivotDays : $alt->days;
            }
            $totalLeaveAlloc = (int) $totalLeaveAlloc;
        } else {
            $totalLeaveAlloc = (int) LeaveType::where('created_by', $employess->created_by)->sum('days');
        }
        $totalUsedEver     = (int) Leave::where('employee_id', $employeeId)->where('status', 'Approved')->sum('total_leave_days');
        $remainingLeaves   = max(0, $totalLeaveAlloc - $totalUsedEver);

        // Per-type leave breakdown for payslip display
        $leaveBreakdown = [];
        $leaveTypesForBreakdown = $assignedLeaveTypesForPayslip->isNotEmpty()
            ? $assignedLeaveTypesForPayslip
            : LeaveType::where('created_by', $employess->created_by)->get();

        $annualCycle = self::AnnualLeaveCycle();
        foreach ($leaveTypesForBreakdown as $lt) {
            $pivotDays = ($lt->pivot->total_days ?? 0);
            $allocDays = $pivotDays > 0 ? $pivotDays : $lt->days;
            $isPaid = isset($lt->pivot) ? ($lt->pivot->is_paid ?? $lt->is_paid) : $lt->is_paid;

            $usedDays = (float) Leave::where('employee_id', $employeeId)
                ->where('leave_type_id', $lt->id)
                ->where('status', 'Approved')
                ->whereBetween('created_at', [$annualCycle['start_date'], $annualCycle['end_date']])
                ->sum('total_leave_days');

            $leaveBreakdown[] = [
                'title'     => $lt->title,
                'is_paid'   => $isPaid,
                'allocated' => $allocDays,
                'used'      => $usedDays,
                'remaining' => max(0, $allocDays - $usedDays),
            ];
        }

        // Total hours worked this month
        $totalWorkMins = 0;
        foreach ($attByDate as $dk => $dr) {
            if (!$isWorkingDay($dk)) continue;
            $ciR = $dr['clock_in']  ?? '';
            $coR = $dr['clock_out'] ?? '';
            if ($ciR === '00:00:00' || $ciR === '00:00') $ciR = '';
            if ($coR === '00:00:00' || $coR === '00:00') $coR = '';
            if (!empty($ciR) && !empty($coR)) {
                try {
                    $ci2 = Carbon::parse($ciR);
                    $co2 = Carbon::parse($coR);
                    $mins = $co2->gt($ci2) ? $ci2->diffInMinutes($co2) : 0;
                    if ($mins <= 0) {
                        $co2->addDay();
                        $mins = $ci2->diffInMinutes($co2);
                    }
                    $totalWorkMins += max(0, $mins);
                } catch (\Throwable $e) {
                }
            }
        }
        $workingDayRowCount = count(array_filter(array_keys($attByDate), $isWorkingDay));
        $totalWorkHours     = round($totalWorkMins / 60, 2);
        $avgHrsPerDay       = $workingDayRowCount > 0 ? round($totalWorkHours / $workingDayRowCount, 2) : 0;

        // ── Hourly gross: recalculate now that actual hours worked is known ──
        // For hourly employees the gross = hourly_rate × actual_hours_worked.
        // If no clock data exists, fall back to full shift hours (all days present).
        if ($isHourly) {
            $billableHours      = $totalWorkHours > 0
                ? $totalWorkHours
                : ($attendance_count * $hoursPerDayShift); // fallback: present days × shift hours
            $monthlySalaryGross = round($per_hour_amount * $billableHours, 2);

            // For hourly employees LOP is already embedded — you only get paid for hours
            // actually worked. Zero out the day-based LOP deduction to avoid double-penalising.
            $leave_deduction         = 0;
            $unpaid_leave_deduction  = 0;
            // Replace the leave deduction row with zero
            $deduction['leave'] = [(object)[
                'leave_reason'     => 'Loss of Pay',
                'leave_type'       => 'Loss of Pay',
                'total_leave_days' => '0 days',
                'empleave'         => 0,
            ]];
            $deduction['unpaid_leave'] = [];
            // Recalculate total deduction without LOP
            // (keep loan + saturation_deduction + pension)
        }
        // Extra days = days worked beyond the official working-day count
        $extra_days = max(0, $attendance_count - $workingDaysInMonth);

        // ── TEMPORARY DEBUG — remove after confirming ────────────────────
        \Log::debug('employeePayslipDetail', [
            'employeeId'           => $employeeId,
            'month'                => $month,
            'workingDaysInMonth'   => $workingDaysInMonth,
            'salary'               => $salary,
            'per_day_amount'       => $per_day_amount,
            'attByDate_count'      => count($attByDate),
            'attByDate_dates'      => array_keys($attByDate),
            'fullDays'             => $fullDays,
            'halfDays'             => $halfDays,
            'fullDayLeaveFromAtt'  => $fullDayLeaveFromAtt,
            'attendance_count'     => $attendance_count,
            'total_leave_days'     => $total_leave_days,
            'leave_dates_for_half' => array_keys($leaveDatesForHalf),
            'covered_days'         => $covered_days,
            'absent_days'          => $absent_days,
            'total_absent_for_lop' => $total_absent_for_lop,
            'leave_deduction'      => $leave_deduction,
            'totalloan'            => $totalloan,
            'totaldeduction'       => $totaldeduction,
            'totalPansion'         => $totalPansion,
        ]);


        $payslip['earning']              = $earning;
        $payslip['earning']              = $earning;
        $payslip['totalEarning']         = $totalAllowance + $totalCommission + $totalotherpayment + $ot + $totalBonous + $totalPearks + $totalloan;
        $payslip['deduction']            = $deduction;
        // For hourly employees leave_deduction and unpaid_leave_deduction are zeroed
        // above (actual hours already exclude unpaid time — no double-deduction needed)
        $payslip['totalDeduction']       = $totalLoanRepayment + $totaldeduction + $totalPansion
            + $leave_deduction + $unpaid_leave_deduction;

        $calcTotal = function ($items) use ($salary) {
            return $items->sum(function ($item) use ($salary) {
                return $item->type === 'percentage'
                    ? ($item->amount * $salary / 100)
                    : $item->amount;
            });
        };




        $payslip['hra']              = self::isUkRequest() ? 0 : $employess->get_net_hra();
        $payslip['da']              = self::isUkRequest() ? 0 : $employess->get_net_da();
        $payslip['earning']              = $earning;
        $payslip['totalEarning']         = $totalAllowance + $totalCommission
            + $totalotherpayment
            // + $ot
            + $totalBonous
            + $totalloan
            + $payslip['hra'] + $payslip['da'];
        // + $totalPearks;
        $payslip['deduction']            = $deduction;
        // For hourly employees leave_deduction and unpaid_leave_deduction are zeroed
        // above (actual hours already exclude unpaid time — no double-deduction needed)
        $payslip['totalDeduction']       = $totalLoanRepayment + $totaldeduction + $totalPansion;
        // + $leave_deduction + $unpaid_leave_deduction;
        $total_saturation_deduction = $totaldeduction;
        $total_bonus                = $calcTotal($employess->bonuses);
        $basic_salary = $monthlySalaryGross;

        $netSalary = $employess->basic_salary + $payslip['hra'] + $payslip['da'] + $totalAllowance + $totalCommission + $totalotherpayment
            + $totalloan - $totalLoanRepayment - $totalPansion + $total_bonus - $total_saturation_deduction;
        // Net salary:
        //   Monthly: fixed_salary + earnings - deductions (incl. LOP)
        //   Hourly:  hourly_rate × actual_hours_worked + earnings - deductions (no LOP)

        // $payslip['net_salary']           = $netSalary;

        $payslip['totalAllowance']           = $totalAllowance;
        $payslip['totalCommission']           = $totalCommission;
        $payslip['totalOtherPayment']    = $totalotherpayment;
        $payslip['totalPansion']           = $totalPansion;
        $payslip['totalLoan']           = $totalloan;
        $payslip['totalLoanRepayment']   = $totalLoanRepayment;
        $payslip['total_saturation_deduction']           = $total_saturation_deduction;
        $payslip['total_bonus']           = $total_bonus;



        // Net salary:
        //   Monthly: fixed_salary + earnings - deductions (incl. LOP)
        //   Hourly:  hourly_rate × actual_hours_worked + earnings - deductions (no LOP)
        // $payslip['net_salary']           = $monthlySalaryGross + $payslip['totalEarning'] - $payslip['totalDeduction'];


        $payslip['net_salary']           = $netSalary;
        $payslip['salary']           = $employess->basic_salary;
        $payslip['basic_salary']         = $basic_salary; // gross to display on slip
        $payslip['salary_rate']          = $salary;             // raw stored monthly reference amount
        $payslip['is_hourly']            = $isHourly;
        $payslip['per_day_amount']       = $per_day_amount;
        $payslip['per_hour_amount']      = $per_hour_amount;
        $payslip['hours_per_day']        = $hoursPerDayShift;
        $payslip['total_shift_hours']    = $totalShiftHoursInMonth;
        // Attendance & leave display values — single source of truth for all blades
        $payslip['office_days']          = $workingDaysInMonth;
        $payslip['present_days']         = $attendance_count;
        $payslip['leave_days']           = $total_leave_days;
        $payslip['paid_leave_days']      = $paid_leave_days;       // NEW
        $payslip['unpaid_leave_days']    = $unpaid_leave_days;     // NEW
        $payslip['unpaid_leave_deduction'] = $unpaid_leave_deduction; // NEW
        $payslip['absent_days']          = $absent_days;
        $payslip['extra_days']           = $extra_days;
        $payslip['lop_days']             = $total_absent_for_lop;
        $payslip['total_work_hours']     = $totalWorkHours;
        $payslip['avg_hrs_per_day']      = $avgHrsPerDay;
        $payslip['approved_leaves_month'] = $total_leave_days;
        $payslip['rejected_leaves_month'] = $total_rejected_days;
        $payslip['total_leave_alloc']    = $totalLeaveAlloc;
        $payslip['remaining_leaves']     = $remainingLeaves;
        $payslip['total_used_ever']      = $totalUsedEver;
        $payslip['leave_breakdown']      = $leaveBreakdown;   // per-type leave details
        // Days paid = clocked present days + paid leave days (both are compensated)
        $payslip['days_paid']            = $attendance_count + $paid_leave_days;
        // ── UK Year-To-Date (YTD) ─────────────────────────────────────────
        // UK tax year runs 6 April → 5 April.
        // We sum all payslips from the start of the current tax year up to and
        // including the current payslip month.
        // These keys are consumed by ukpdf.blade.php and ukPayslipDownload.blade.php.
        // ─────────────────────────────────────────────────────────────────────
        $taxYearStart = $payMonth >= 4
            ? Carbon::createFromDate($payYear, 4, 6)->startOfDay()
            : Carbon::createFromDate($payYear - 1, 4, 6)->startOfDay();

        // Collect all pay_slips rows from tax year start → current month
        $ytdSlips = PaySlip::where('employee_id', $employeeId)
            ->where('created_by', $employess->created_by)
            ->get()
            ->filter(function ($slip) use ($taxYearStart, $payYear, $payMonth) {
                [$sy, $sm] = explode('-', $slip->salary_month);
                $slipDate  = Carbon::createFromDate((int)$sy, (int)$sm, 6);
                return $slipDate->gte($taxYearStart)
                    && ($sy < $payYear || ((int)$sy === $payYear && (int)$sm <= $payMonth));
            });

        // Accumulate gross earnings across those months
        $ytdGross = 0.0;
        foreach ($ytdSlips as $_ys) {
            $ytdGross += (float) $_ys->basic_salary;
        }
        // Add the current month's allowances/bonuses/commissions/overtime
        $ytdGross += $payslip['totalEarning'];

        // Accumulate deduction categories across tax year months by reading
        // the saturation_deductions that have titles matching UK deduction names.
        // We key on lowercase title so "Income Tax", "income tax" etc. all match.
        $ytdIncomeTax    = 0.0;
        $ytdEmployeeNI   = 0.0;
        $ytdEmployerNI   = 0.0;
        $ytdStatutoryPay = 0.0;

        // ── Read deduction amounts from each payslip's stored JSON ────────
        // This is the single source of truth — same data the deduction list
        // in the blade shows. "Professional Tax", "Income Tax", "PAYE" etc.
        // all map to the income_tax YTD bucket via broad title matching.
        // We also capture the actual title used (first match per bucket)
        // so the blade can display matching labels in the YTD section.
        $ytdTitleIncomeTax    = '';
        $ytdTitleEmployeeNI   = '';
        $ytdTitleEmployerNI   = '';
        $ytdTitleStatutoryPay = '';

        foreach ($ytdSlips as $_ys) {
            $_basicForPct = (float) $_ys->basic_salary;
            $_satJson     = json_decode($_ys->saturation_deduction ?? '[]');
            if (!is_array($_satJson)) $_satJson = [];

            foreach ($_satJson as $_sd) {
                // Resolve current DeductionOption name (reflects admin renames)
                $_resolvedTitle = trim((string) ($_sd->title ?? ''));
                if (!empty($_sd->deduction_option)) {
                    $_currentName = DeductionOption::where('id', $_sd->deduction_option)->value('name');
                    if ($_currentName) {
                        $_resolvedTitle = $_currentName;
                    }
                }

                $_sdTitle = strtolower($_resolvedTitle);
                $_sdType  = strtolower(trim((string) ($_sd->type  ?? 'fixed')));
                $_sdAmt   = $_sdType === 'percentage'
                    ? round((float)$_sd->amount * $_basicForPct / 100, 2)
                    : (float) ($_sd->amount ?? 0);
                $_origTitle = $_resolvedTitle;

                if ($_sdAmt <= 0 || $_sdTitle === '') continue;

                // ── Employer NI — check first to avoid falling into generic NI ──
                if (
                    str_contains($_sdTitle, 'employer ni') ||
                    str_contains($_sdTitle, 'employer nic') ||
                    str_contains($_sdTitle, 'employer national') ||
                    str_contains($_sdTitle, 'employers ni')
                ) {
                    $ytdEmployerNI += $_sdAmt;
                    if ($ytdTitleEmployerNI === '') $ytdTitleEmployerNI = $_origTitle;
                }
                // ── Employee NI / National Insurance ──────────────────────────
                elseif (
                    str_contains($_sdTitle, 'national insurance') ||
                    str_contains($_sdTitle, 'employee ni') ||
                    str_contains($_sdTitle, 'employee nic') ||
                    $_sdTitle === 'ni' || $_sdTitle === 'nic'
                ) {
                    $ytdEmployeeNI += $_sdAmt;
                    if ($ytdTitleEmployeeNI === '') $ytdTitleEmployeeNI = $_origTitle;
                }
                // ── Statutory Pay (SSP / SMP / SPP) ───────────────────────────
                elseif (
                    str_contains($_sdTitle, 'statutory') ||
                    str_contains($_sdTitle, 'ssp') ||
                    str_contains($_sdTitle, 'smp') ||
                    str_contains($_sdTitle, 'maternity') ||
                    str_contains($_sdTitle, 'paternity') ||
                    str_contains($_sdTitle, 'sick pay')
                ) {
                    $ytdStatutoryPay += $_sdAmt;
                    if ($ytdTitleStatutoryPay === '') $ytdTitleStatutoryPay = $_origTitle;
                }
                // ── Any form of tax → Income Tax bucket ───────────────────────
                // Catches: "Income Tax", "Professional Tax", "PAYE", "Tax", etc.
                elseif (
                    str_contains($_sdTitle, 'tax') ||
                    str_contains($_sdTitle, 'paye') ||
                    str_contains($_sdTitle, 'p.a.y.e')
                ) {
                    $ytdIncomeTax += $_sdAmt;
                    if ($ytdTitleIncomeTax === '') $ytdTitleIncomeTax = $_origTitle;
                }
            }
        }

        // ── Pension YTD — each pension record tracked individually ───────────
        // Split by title: any pension record whose title contains "employer"
        // is treated as employer pension; everything else is employee pension.
        // This allows multiple pension records per employee to show as separate
        // YTD rows — exactly matching the deductions list on the right.
        $ytdPensionRows = []; // [ ['label'=>'...', 'amount'=>0.0], ... ]

        foreach ($deduction['pansion'] as $_pen) {
            $_penTitle  = trim((string) ($_pen->title ?? ''));
            $_penType   = strtolower(trim((string) ($_pen->type  ?? 'fixed')));
            $_penAmt    = $_penType === 'percentage'
                ? round((float) $_pen->amount * (float) $employess->salary / 100, 2)
                : (float) $_pen->amount;
            if ($_penAmt <= 0) continue;

            // Multiply by number of months in this tax year so far
            $_penYtd = round($_penAmt * ($ytdSlips->count() ?: 1), 2);

            $ytdPensionRows[] = [
                'label'  => $_penTitle ?: null,
                'amount' => $_penYtd,
            ];
        }

        // For backward-compat keys: sum all employee-type pensions
        $ytdEmployeePension = array_sum(array_column($ytdPensionRows, 'amount'));
        $ytdEmployerPension = 0.0; // no separate employer pension table yet

        // First employee/employer pension titles for single-row consumers
        $_firstEmpPen = collect($ytdPensionRows)->first();
        $ytdTitleEmployeePension = $_firstEmpPen['label'] ?? null;
        $ytdTitleEmployerPension = null;

        $payslip['ytd_taxable_gross']          = round($ytdGross, 2);
        $payslip['ytd_income_tax']             = round($ytdIncomeTax, 2);
        $payslip['ytd_employee_ni']            = round($ytdEmployeeNI, 2);
        $payslip['ytd_employer_ni']            = round($ytdEmployerNI, 2);
        $payslip['ytd_statutory_pay']          = round($ytdStatutoryPay, 2);
        $payslip['ytd_employee_pension']       = round($ytdEmployeePension, 2);
        $payslip['ytd_employer_pension']       = round($ytdEmployerPension, 2);
        // Individual pension rows — blades iterate this to show one row per pension
        $payslip['ytd_pension_rows']           = $ytdPensionRows;

        // Labels: actual titles from the stored deduction data.
        // Null when no matching deduction exists — blades apply their own fallback.
        $payslip['ytd_label_income_tax']       = $ytdTitleIncomeTax       ?: null;
        $payslip['ytd_label_employee_ni']      = $ytdTitleEmployeeNI      ?: null;
        $payslip['ytd_label_employer_ni']      = $ytdTitleEmployerNI      ?: null;
        $payslip['ytd_label_statutory_pay']    = $ytdTitleStatutoryPay    ?: null;
        $payslip['ytd_label_employee_pension'] = $ytdTitleEmployeePension ?: null;
        $payslip['ytd_label_employer_pension'] = $ytdTitleEmployerPension;
        $payslip['ytd_label_employer_pension'] = $ytdTitleEmployerPension; // always null until DB extended
        return $payslip;
    }

    public static function delete_directory($dir)
    {
        if (!file_exists($dir)) {
            return true;
        }
        if (!is_dir($dir)) {
            return unlink($dir);
        }
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }
            if (!self::delete_directory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }

        return rmdir($dir);
    }

    public static function addNewData()
    {
        \Artisan::call('cache:forget spatie.permission.cache');
        \Artisan::call('cache:clear');
        $usr = \Auth::user();
        $arrPermissions = [
            "Manage Job Category",
            "Create Job Category",
            "Edit Job Category",
            "Delete Job Category",
            "Manage Job Stage",
            "Create Job Stage",
            "Edit Job Stage",
            "Delete Job Stage",
            "Manage Job",
            "Create Job",
            "Edit Job",
            "Delete Job",
            "Show Job",
            "Manage Job Application",
            "Create Job Application",
            "Edit Job Application",
            "Delete Job Application",
            "Show Job Application",
            "Move Job Application",
            "Add Job Application Note",
            "Delete Job Application Note",
            "Add Job Application Skill",
            "Manage Job OnBoard",
            "Manage Custom Question",
            "Create Custom Question",
            "Edit Custom Question",
            "Delete Custom Question",
            "Manage Interview Schedule",
            "Create Interview Schedule",
            "Edit Interview Schedule",
            "Delete Interview Schedule",
            "Manage Career",
            "Manage Competencies",
            "Create Competencies",
            "Edit Competencies",
            "Delete Competencies",
            "Create Webhook",
            "Edit Webhook",
            "Delete Webhook",
            "Manage Biometric Attendance",
            "Biometric Attendance Synchronize",
        ];
        foreach ($arrPermissions as $ap) {
            // check if permission is not created then create it.
            $permission = Permission::where('name', 'LIKE', $ap)->first();
            if (empty($permission)) {
                Permission::create(['name' => $ap]);
            }
        }
        $companyRole = Role::where('name', 'LIKE', 'company')->where('created_by', '=', $usr->creatorId())->first();
        $companyPermissions = $companyRole->getPermissionNames()->toArray();
        $companyNewPermission = [
            "Manage Job Category",
            "Create Job Category",
            "Edit Job Category",
            "Delete Job Category",
            "Manage Job Stage",
            "Create Job Stage",
            "Edit Job Stage",
            "Delete Job Stage",
            "Manage Job",
            "Create Job",
            "Edit Job",
            "Delete Job",
            "Show Job",
            "Manage Job Application",
            "Create Job Application",
            "Edit Job Application",
            "Delete Job Application",
            "Show Job Application",
            "Move Job Application",
            "Add Job Application Note",
            "Delete Job Application Note",
            "Add Job Application Skill",
            "Manage Job OnBoard",
            "Manage Custom Question",
            "Create Custom Question",
            "Edit Custom Question",
            "Delete Custom Question",
            "Manage Interview Schedule",
            "Create Interview Schedule",
            "Edit Interview Schedule",
            "Delete Interview Schedule",
            "Manage Career",
            "Manage Competencies",
            "Create Competencies",
            "Edit Competencies",
            "Delete Competencies",
            "Create Webhook",
            "Edit Webhook",
            "Delete Webhook",
            "Manage Biometric Attendance",
            "Biometric Attendance Synchronize",
        ];
        foreach ($companyNewPermission as $op) {
            // check if permission is not assign to owner then assign.
            if (!in_array($op, $companyPermissions)) {
                $permission = Permission::findByName($op);
                $companyRole->givePermissionTo($permission);
            }
        }
        $employeeRole = Role::where('name', 'LIKE', 'employee')->first();
        $employeePermissions = $employeeRole->getPermissionNames()->toArray();
        $employeeNewPermission = [
            'Manage Career',
        ];
        foreach ($employeeNewPermission as $op) {
            // check if permission is not assign to owner then assign.
            if (!in_array($op, $employeePermissions)) {
                $permission = Permission::findByName($op);
                $employeeRole->givePermissionTo($permission);
            }
        }
    }

    public static function jobStage($id)
    {
        $stages = [
            'Applied',
            'Phone Screen',
            'Interview',
            'Hired',
            'Rejected',
        ];
        foreach ($stages as $stage) {

            JobStage::create(
                [
                    'title' => $stage,
                    'created_by' => $id,
                ]
            );
        }
    }

    public static function getSetting()
    {
        if (self::$getsettings == null) {
            $data = DB::table('settings');
            $data = $data->where('created_by', '=', 1)->get();
            if (count($data) == 0) {
                $data = DB::table('settings')->where('created_by', '=', 1)->get();
            }
            self::$getsettings = $data;
        }
        return self::$getsettings;
    }

    public static function sendEmailTemplate($emailTemplate, $mailTo, $obj)
    {
        $usr = \Auth::user();
        //Remove Current Login user Email don't send mail to them
        if ($usr) {
            if (is_array($mailTo)) {
                unset($mailTo[$usr->id]);

                $mailTo = array_values($mailTo);
            }
        }
        // find template is exist or not in our record
        $template = EmailTemplate::where('slug', $emailTemplate)->first();

        if (isset($template) && !empty($template)) {

            // check template is active or not by company
            $is_active = UserEmailTemplate::where('template_id', '=', $template->id)->first();
            if ($is_active->is_active == 1) {
                $settings = self::settings();

                $data = Utility::getSetting();

                $setting = [
                    'mail_driver' => '',
                    'mail_host' => '',
                    'mail_port' => '',
                    'mail_encryption' => '',
                    'mail_username' => '',
                    'mail_password' => '',
                    'mail_from_address' => '',
                    'mail_from_name' => '',

                ];
                foreach ($data as $row) {
                    $setting[$row->name] = $row->value;
                }

                // get email content language base
                if ($usr) {
                    $content = EmailTemplateLang::where('parent_id', '=', $template->id)->where('lang', 'LIKE', $usr->lang)->first();
                } else {
                    $content = EmailTemplateLang::where('parent_id', '=', $template->id)->where('lang', 'LIKE', 'en')->first();
                }
                $content['from'] = $template->from;

                if (!empty($content->content)) {
                    $content->content = self::replaceVariable($content->content, $obj);

                    try {
                        config(
                            [
                                'mail.driver' => $settings['mail_driver'] ? $settings['mail_driver'] : $setting['mail_driver'],
                                'mail.host' => $settings['mail_host'] ? $settings['mail_host'] : $setting['mail_host'],
                                'mail.port' => $settings['mail_port'] ? $settings['mail_port'] : $setting['mail_port'],
                                'mail.encryption' => $settings['mail_encryption'] ? $settings['mail_encryption'] : $setting['mail_encryption'],
                                'mail.username' => $settings['mail_username'] ? $settings['mail_username'] : $setting['mail_username'],
                                'mail.password' => $settings['mail_password'] ? $settings['mail_password'] : $setting['mail_password'],
                                'mail.from.address' => $settings['mail_from_address'] ? $settings['mail_from_address'] : $setting['mail_from_address'],
                                'mail.from.name' => $settings['mail_from_name'] ? $settings['mail_from_name'] : $setting['mail_from_name'],
                            ]
                        );
                        if (is_array($mailTo)) {
                            Mail::to($mailTo)->send(new CommonEmailTemplate($content, $settings, $mailTo));
                        } else {
                            Mail::to($mailTo)->send(new CommonEmailTemplate($content, $settings, $mailTo[0]));
                        }
                    } catch (\Exception $e) {
                        $error = __('E-Mail has been not sent due to SMTP configuration');
                    }

                    if (isset($error)) {
                        $arReturn = [
                            'is_success' => false,
                            'error' => $error,
                        ];
                    } else {
                        $arReturn = [
                            'is_success' => true,
                            'error' => false,
                        ];
                    }
                } else {
                    $arReturn = [
                        'is_success' => false,
                        'error' => __('Mail not send, email is empty'),
                    ];
                }

                return $arReturn;
            } else {
                return [
                    'is_success' => false,
                    'error' => __('Mail send is not Activate.'),
                ];
            }
        }
    }

    public static function replaceVariable($content, $obj)
    {

        $arrVariable = [
            '{email}',
            '{password}',
            '{passcode}',

            '{app_name}',
            '{app_url}',

            '{employee_name}',
            '{employee_email}',
            '{employee_password}',
            '{employee_passcode}',
            '{employee_branch}',
            '{employee_department}',
            '{employee_designation}',

            '{payslip_email}',
            '{name}',
            '{salary_month}',
            '{url}',

            '{ticket_title}',
            '{ticket_name}',
            '{ticket_code}',
            '{ticket_description}',
            '{award_name}',

            '{transfer_name}',
            '{transfer_date}',
            '{transfer_department}',
            '{transfer_branch}',
            '{transfer_description}',

            '{assign_user}',
            '{resignation_date}',

            '{employee_trip_name}',
            '{purpose_of_visit}',
            '{start_date}',
            '{end_date}',
            '{place_of_visit}',
            '{trip_description}',

            '{employee_promotion_name}',
            '{promotion_designation}',
            '{promotion_title}',
            '{promotion_date}',

            '{employee_complaints_name}',

            '{employee_warning_name}',
            '{warning_subject}',
            '{warning_description}',

            '{employee_termination_name}',
            '{notice_date}',
            '{termination_date}',
            '{termination_type}',

            '{leave_status_name}',
            '{leave_status}',
            '{leave_reason}',
            '{leave_start_date}',
            '{leave_end_date}',
            '{total_leave_days}',

            '{contract_subject}',
            '{contract_employee}',
            '{contract_start_date}',
            '{contract_end_date}',

            '{announcement_title}',
            '{branch_name}',

            '{year}',

            '{meeting_title}',
            '{date}',
            '{time}',
            '{reason}',

            '{occasion_name}',

            '{company_policy_name}',

            '{ticket_priority}',

            '{event_name}',

            '{contract_number}',
            '{contract_company_name}',

            '{company_name}',
            '{receiver_name}',

            '{type}',
            '{leave_type}',
            '{leave_start_end_time}',

            '{revision_employee_name}',
            '{revision_current_salary}',
            '{revision_revised_salary}',
            '{revision_type}',
            '{revision_value}',
            '{revision_effective_from}',
            '{revision_cycle}',
            '{revision_note}',
            '{revision_approved_by}',
        ];
        $arrValue = [
            'email' => '-',
            'password' => '-',
            'passcode' => '-',

            'app_name' => '-',
            'app_url' => '-',

            'employee_name' => '-',
            'employee_email' => '-',
            'employee_password' => '-',
            'employee_passcode' => '-',
            'employee_branch' => '-',
            'employee_department' => '-',
            'employee_designation' => '-',

            'payslip_email' => '-',
            'name' => '-',
            'salary_month' => '-',
            'url' => '-',

            'ticket_title' => '-',
            'ticket_name' => '-',
            'ticket_code' => '-',
            'ticket_description' => '-',
            'award_name' => '-',

            'transfer_name' => '-',
            'transfer_date' => '-',
            'transfer_department' => '-',
            'transfer_branch' => '-',
            'transfer_description' => '-',

            'assign_user' => '-',
            'resignation_date' => '-',

            'employee_trip_name' => '-',
            'purpose_of_visit' => '-',
            'start_date' => '-',
            'end_date' => '-',
            'place_of_visit' => '-',
            'trip_description' => '-',

            'employee_promotion_name' => '-',
            'promotion_designation' => '-',
            'promotion_title' => '-',
            'promotion_date' => '-',

            'employee_complaints_name' => '-',

            'employee_warning_name' => '-',
            'warning_subject' => '-',
            'warning_description' => '-',

            'employee_termination_name' => '-',
            'notice_date' => '-',
            'termination_date' => '-',
            'termination_type' => '-',

            'leave_status_name' => '-',
            'leave_status' => '-',
            'leave_reason' => '-',
            'leave_start_date' => '-',
            'leave_end_date' => '-',
            'total_leave_days' => '-',

            'contract_subject' => '-',
            'contract_employee' => '-',
            'contract_start_date' => '-',
            'contract_end_date' => '-',

            'announcement_title' => '-',
            'branch_name' => '-',

            'year' => '-',

            'meeting_title' => '-',
            'date' => '-',
            'time' => '-',
            'reason' => '-',

            'occasion_name' => '-',

            'company_policy_name' => '-',

            'ticket_priority' => '-',

            'event_name' => '-',

            'contract_number' => '-',
            'contract_company_name' => '-',

            'company_name' => '-',
            'receiver_name' => '-',

            'type' => '-',
            'leave_type' => '-',
            'leave_start_end_time' => '-',

            'revision_employee_name' => '-',
            'revision_current_salary' => '-',
            'revision_revised_salary' => '-',
            'revision_type' => '-',
            'revision_value' => '-',
            'revision_effective_from' => '-',
            'revision_cycle' => '-',
            'revision_note' => '-',
            'revision_approved_by' => '-',
        ];

        foreach ($obj as $key => $val) {
            $arrValue[$key] = $val;
        }
        $settings = Utility::settings();
        $company_name = !empty($settings['company_name']) ? $settings['company_name'] : 'IDAB TECH - (HRMS)';
        $arrValue['app_name'] = env('APP_NAME');
        $arrValue['company_name'] = $company_name;
        // $arrValue['company_name'] = self::settings()['company_name'];
        $arrValue['app_url'] = '<a href="' . env('APP_URL') . '" target="_blank">' . env('APP_URL') . '</a>';

        return str_replace($arrVariable, array_values($arrValue), $content);
    }
    public static function makeEmailLang($lang)
    {
        $template = EmailTemplate::all();
        foreach ($template as $t) {
            $default_lang = EmailTemplateLang::where('parent_id', '=', $t->id)->where('lang', 'LIKE', 'en')->first();
            $emailTemplateLang = new EmailTemplateLang();
            $emailTemplateLang->parent_id = $t->id;
            $emailTemplateLang->lang = $lang;
            $emailTemplateLang->subject = $default_lang->subject;
            $emailTemplateLang->content = $default_lang->content;
            $emailTemplateLang->save();
        }
    }

    public static function getAdminPaymentSetting()
    {
        if (self::$payments === null) {
            self::$payments = self::fetchAdminPaymentSetting();
        }
        return self::$payments;
    }

    public static function fetchAdminPaymentSetting()
    {
        $data = \DB::table('admin_payment_settings');

        $settings = [];
        if (\Auth::check()) {
            $user_id = 1;
            $data = $data->where('created_by', '=', $user_id);
        }
        $data = $data->get();
        foreach ($data as $row) {
            $settings[$row->name] = $row->value;
        }
        return $settings;
    }

    public static function error_res($msg = "", $args = array())
    {
        $msg = $msg == "" ? "error" : $msg;
        $msg_id = 'error.' . $msg;
        $converted = \Lang::get($msg_id, $args);
        $msg = $msg_id == $converted ? $msg : $converted;
        $json = array(
            'flag' => 0,
            'msg' => $msg,
        );

        return $json;
    }

    public static function success_res($msg = "", $args = array())
    {
        $msg = $msg == "" ? "success" : $msg;
        $msg_id = 'success.' . $msg;
        $converted = \Lang::get($msg_id, $args);
        $msg = $msg_id == $converted ? $msg : $converted;
        $json = array(
            'flag' => 1,
            'msg' => $msg,
        );

        return $json;
    }

    public static function getProgressColor($percentage)
    {
        $color = '';
        if ($percentage <= 20) {
            $color = 'danger';
        } elseif ($percentage > 20 && $percentage <= 40) {
            $color = 'warning';
        } elseif ($percentage > 40 && $percentage <= 60) {
            $color = 'info';
        } elseif ($percentage > 60 && $percentage <= 80) {
            $color = 'primary';
        } elseif ($percentage >= 80) {
            $color = 'success';
        }
        return $color;
    }

    public static function getselectedThemeColor()
    {
        $color = env('THEME_COLOR');
        if ($color == "" || $color == null) {
            $color = 'blue';
        }
        return $color;
    }

    public static function getAllThemeColors()
    {
        $colors = [
            'blue',
            'denim',
            'sapphire',
            'olympic',
            'violet',
            'black',
            'cyan',
            'dark-blue-natural',
            'gray-dark',
            'light-blue',
            'light-purple',
            'magenta',
            'orange-mute',
            'pale-green',
            'rich-magenta',
            'rich-red',
            'sky-gray'
        ];
        return $colors;
    }

    public static function send_slack_msg($slug, $obj)
    {
        $notification_template = NotificationTemplates::where('slug', $slug)->first();
        if (!empty($notification_template) && !empty($obj)) {
            $curr_noti_tempLang = NotificationTemplateLangs::where('parent_id', '=', $notification_template->id)->where('lang', \Auth::user()->lang)->where('created_by', '=', \Auth::user()->creatorId())->first();
            if (empty($curr_noti_tempLang)) {
                $curr_noti_tempLang = NotificationTemplateLangs::where('parent_id', '=', $notification_template->id)->where('lang', \Auth::user()->lang)->first();
            }
            if (empty($curr_noti_tempLang)) {
                $curr_noti_tempLang = NotificationTemplateLangs::where('parent_id', '=', $notification_template->id)->where('lang', 'en')->first();
            }
            if (!empty($curr_noti_tempLang) && !empty($curr_noti_tempLang->content)) {
                $msg = self::replaceVariable($curr_noti_tempLang->content, $obj);
            }
        }
        if (isset($msg)) {
            $settings = Utility::settings(\Auth::user()->creatorId());
            try {
                if (isset($settings['slack_webhook']) && !empty($settings['slack_webhook'])) {
                    $ch = curl_init();

                    curl_setopt($ch, CURLOPT_URL, $settings['slack_webhook']);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['text' => $msg]));

                    $headers = array();
                    $headers[] = 'Content-Type: application/json';
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                    $result = curl_exec($ch);
                    if (curl_errno($ch)) {
                        echo 'Error:' . curl_error($ch);
                    }
                    curl_close($ch);
                }
            } catch (\Exception $e) {
            }
        }
    }

    public static function send_telegram_msg($slug, $obj)
    {
        $notification_template = NotificationTemplates::where('slug', $slug)->first();
        if (!empty($notification_template) && !empty($obj)) {
            $curr_noti_tempLang = NotificationTemplateLangs::where('parent_id', '=', $notification_template->id)->where('lang', \Auth::user()->lang)->where('created_by', '=', \Auth::user()->creatorId())->first();
            if (empty($curr_noti_tempLang)) {
                $curr_noti_tempLang = NotificationTemplateLangs::where('parent_id', '=', $notification_template->id)->where('lang', \Auth::user()->lang)->first();
            }
            if (empty($curr_noti_tempLang)) {
                $curr_noti_tempLang = NotificationTemplateLangs::where('parent_id', '=', $notification_template->id)->where('lang', 'en')->first();
            }
            if (!empty($curr_noti_tempLang) && !empty($curr_noti_tempLang->content)) {
                $msg = self::replaceVariable($curr_noti_tempLang->content, $obj);
            }
        }
        if (isset($msg)) {
            $settings = Utility::settings(\Auth::user()->creatorId());

            try {
                $msg = $msg;
                // Set your Bot ID and Chat ID.
                $telegrambot = $settings['telegram_accestoken'];
                $telegramchatid = $settings['telegram_chatid'];
                // Function call with your own text or variable
                $url = 'https://api.telegram.org/bot' . $telegrambot . '/sendMessage';
                $data = array(
                    'chat_id' => $telegramchatid,
                    'text' => $msg,
                );
                $options = array(
                    'http' => array(
                        'method' => 'POST',
                        'header' => "Content-Type:application/x-www-form-urlencoded\r\n",
                        'content' => http_build_query($data),
                    ),
                );
                $context = stream_context_create($options);
                $result = file_get_contents($url, false, $context);
                $url = $url;
            } catch (\Exception $e) {
            }
        }
    }

    public static function send_twilio_msg($to, $slug, $obj)
    {
        $notification_template = NotificationTemplates::where('slug', $slug)->first();
        if (!empty($notification_template) && !empty($obj)) {
            $curr_noti_tempLang = NotificationTemplateLangs::where('parent_id', '=', $notification_template->id)->where('lang', \Auth::user()->lang)->where('created_by', '=', \Auth::user()->creatorId())->first();
            if (empty($curr_noti_tempLang)) {
                $curr_noti_tempLang = NotificationTemplateLangs::where('parent_id', '=', $notification_template->id)->where('lang', \Auth::user()->lang)->first();
            }
            if (empty($curr_noti_tempLang)) {
                $curr_noti_tempLang = NotificationTemplateLangs::where('parent_id', '=', $notification_template->id)->where('lang', 'en')->first();
            }
            if (!empty($curr_noti_tempLang) && !empty($curr_noti_tempLang->content)) {
                $msg = self::replaceVariable($curr_noti_tempLang->content, $obj);
            }
        }
        if (isset($msg)) {
            $settings = Utility::settings(\Auth::user()->creatorId());

            try {
                $account_sid = $settings['twilio_sid'];
                $auth_token = $settings['twilio_token'];
                $twilio_number = $settings['twilio_from'];
                $client = new Client($account_sid, $auth_token);
                $client->messages->create($to, [
                    'from' => $twilio_number,
                    'body' => $msg
                ]);
            } catch (\Exception $e) {
            }
        }
    }

    public static function get_superadmin_logo()
    {

        $is_dark_mode = DB::table('settings')->where('created_by', '1')->pluck('value', 'name')->toArray();

        if (!empty($is_dark_mode['dark_mode'])) {
            $is_dark_modes = $is_dark_mode['dark_mode'];

            if ($is_dark_modes == 'on') {
                return 'logo-light.png';
            } else {
                return 'logo-dark.png';
            }
        } else {
            return 'logo-dark.png';
        }
    }

    public static function get_company_logo()
    {

        $is_dark_mode = DB::table('settings')->where('created_by', Auth::user()->id)->pluck('value', 'name')->toArray();

        $is_dark_modes = !empty($is_dark_mode['dark_mode']) ? $is_dark_mode['dark_mode'] : 'off';
        if ($is_dark_modes == 'on') {
            return Utility::getValByName('company_logo_light');
        } else {
            return Utility::getValByName('company_logo');
        }
    }

    public static function colorset()
    {
        if (self::$colorset === null) {
            self::$colorset = self::fetchcolorset();
        }
        return self::$colorset;
    }

    public static function fetchcolorset()
    {
        if (\Auth::user()) {
            if (\Auth::user()->type == 'super admin') {
                $user = \Auth::user();

                $setting = DB::table('settings')->where('created_by', $user->id)->pluck('value', 'name')->toArray();
            } else {
                $setting = DB::table('settings')->where('created_by', \Auth::user()->creatorId())->pluck('value', 'name')->toArray();
            }
        } else {
            $user = User::where('type', 'super admin')->first();
            $setting = DB::table('settings')->where('created_by', $user->id)->pluck('value', 'name')->toArray();
        }
        if (!isset($setting['color'])) {
            $setting = Utility::settings();
        }
        return $setting;
    }

    public static function GetLogo()
    {
        $setting = Utility::colorset();
        if (\Auth::user() && \Auth::user()->type != 'super admin') {

            if (Utility::getValByName('cust_darklayout') == 'on') {

                return Utility::getValByName('company_logo_light');
            } else {
                return Utility::getValByName('company_logo');
            }
        } else {
            if (Utility::getValByName('cust_darklayout') == 'on') {
                return Utility::getValByName('light_logo');
            } else {
                return Utility::getValByName('dark_logo');
            }
        }
    }


    public static function GetLogolanding()
    {
        $setting = Utility::colorset();
        if (\Auth::user() && \Auth::user()->type != 'super admin') {

            if (Utility::getValByName('cust_darklayout') == 'on') {

                return Utility::getValByName('company_logo_light');
            } else {
                return Utility::getValByName('company_logo');
            }
        } else {



            return Utility::getValByName('light_logo');
        }
    }

    public static function getTargetrating($designationid, $competencyCount)
    {
        $indicator = Indicator::where('designation', $designationid)->first();
        if (!empty($indicator->rating) && ($competencyCount != 0)) {
            $rating = json_decode($indicator->rating, true);
            $starsum = array_sum($rating);

            $overallrating = $starsum / $competencyCount;
        } else {
            $overallrating = 0;
        }
        return $overallrating;
    }

    public static function upload_file($request, $key_name, $name, $path, $custom_validation = [])
    {
        try {
            $settings = Utility::getStorageSetting();
            if (!empty($settings['storage_setting'])) {

                if ($settings['storage_setting'] == 'wasabi') {

                    config(
                        [
                            'filesystems.disks.wasabi.key' => $settings['wasabi_key'],
                            'filesystems.disks.wasabi.secret' => $settings['wasabi_secret'],
                            'filesystems.disks.wasabi.region' => $settings['wasabi_region'],
                            'filesystems.disks.wasabi.bucket' => $settings['wasabi_bucket'],
                            'filesystems.disks.wasabi.endpoint' => 'https://s3.' . $settings['wasabi_region'] . '.wasabisys.com'
                        ]
                    );

                    $max_size = !empty($settings['wasabi_max_upload_size']) ? $settings['wasabi_max_upload_size'] : '2048';
                    $mimes = !empty($settings['wasabi_storage_validation']) ? $settings['wasabi_storage_validation'] : '';
                } else if ($settings['storage_setting'] == 's3') {
                    config(
                        [
                            'filesystems.disks.s3.key' => $settings['s3_key'],
                            'filesystems.disks.s3.secret' => $settings['s3_secret'],
                            'filesystems.disks.s3.region' => $settings['s3_region'],
                            'filesystems.disks.s3.bucket' => $settings['s3_bucket'],
                            'filesystems.disks.s3.use_path_style_endpoint' => false,
                        ]
                    );
                    $max_size = !empty($settings['s3_max_upload_size']) ? $settings['s3_max_upload_size'] : '2048';
                    $mimes = !empty($settings['s3_storage_validation']) ? $settings['s3_storage_validation'] : '';
                } else {
                    $max_size = !empty($settings['local_storage_max_upload_size']) ? $settings['local_storage_max_upload_size'] : '2048';

                    $mimes = !empty($settings['local_storage_validation']) ? $settings['local_storage_validation'] : '';
                }


                $file = $request->$key_name;

                $extension = strtolower($file->getClientOriginalExtension());
                $allowed_extensions = explode(',', $mimes);
                if (empty($extension) || !in_array($extension, $allowed_extensions)) {
                    return [
                        'flag' => 0,
                        'msg' => 'The ' . $key_name . ' must be a file of type: ' . implode(', ', $allowed_extensions) . '.',
                    ];
                }

                if (count($custom_validation) > 0) {
                    $validation = $custom_validation;
                } else {

                    $validation = [
                        'mimes:' . $mimes,
                        'max:' . $max_size,
                    ];
                }
                $validator = \Validator::make($request->all(), [
                    $key_name => $validation
                ]);
                if ($validator->fails()) {
                    $res = [
                        'flag' => 0,
                        'msg' => $validator->messages()->first(),
                    ];
                    return $res;
                } else {

                    $name = $name;

                    if ($settings['storage_setting'] == 'local') {

                        $request->$key_name->move(storage_path('app/public/' . $path), $name);

                        $path = $name;
                    } else if ($settings['storage_setting'] == 'wasabi') {

                        $path = \Storage::disk('wasabi')->putFileAs(
                            $path,
                            $file,
                            $name
                        );

                        // $path = $path.$name;

                    } else if ($settings['storage_setting'] == 's3') {

                        $path = \Storage::disk('s3')->putFileAs(
                            $path,
                            $file,
                            $name
                        );
                        // $path = $path.$name;
                    }


                    $res = [
                        'flag' => 1,
                        'msg' => 'success',
                        'url' => $path
                    ];
                    return $res;
                }
            } else {
                $res = [
                    'flag' => 0,
                    'msg' => __('Please set proper configuration for storage.'),
                ];
                return $res;
            }
        } catch (\Exception $e) {
            $res = [
                'flag' => 0,
                'msg' => $e->getMessage(),
            ];
            return $res;
        }
    }



    public static function upload_coustom_file($request, $key_name, $name, $path, $data_key, $custom_validation = [])
    {

        $multifile = [
            $key_name => $request->file($key_name)[$data_key],
        ];
        try {
            $settings = Utility::getStorageSetting();


            if (!empty($settings['storage_setting'])) {

                if ($settings['storage_setting'] == 'wasabi') {

                    config(
                        [
                            'filesystems.disks.wasabi.key' => $settings['wasabi_key'],
                            'filesystems.disks.wasabi.secret' => $settings['wasabi_secret'],
                            'filesystems.disks.wasabi.region' => $settings['wasabi_region'],
                            'filesystems.disks.wasabi.bucket' => $settings['wasabi_bucket'],
                            'filesystems.disks.wasabi.endpoint' => 'https://s3.' . $settings['wasabi_region'] . '.wasabisys.com'
                        ]
                    );

                    $max_size = !empty($settings['wasabi_max_upload_size']) ? $settings['wasabi_max_upload_size'] : '2048';
                    $mimes = !empty($settings['wasabi_storage_validation']) ? $settings['wasabi_storage_validation'] : '';
                } else if ($settings['storage_setting'] == 's3') {
                    config(
                        [
                            'filesystems.disks.s3.key' => $settings['s3_key'],
                            'filesystems.disks.s3.secret' => $settings['s3_secret'],
                            'filesystems.disks.s3.region' => $settings['s3_region'],
                            'filesystems.disks.s3.bucket' => $settings['s3_bucket'],
                            'filesystems.disks.s3.use_path_style_endpoint' => false,
                        ]
                    );
                    $max_size = !empty($settings['s3_max_upload_size']) ? $settings['s3_max_upload_size'] : '2048';
                    $mimes = !empty($settings['s3_storage_validation']) ? $settings['s3_storage_validation'] : '';
                } else {
                    $max_size = !empty($settings['local_storage_max_upload_size']) ? $settings['local_storage_max_upload_size'] : '2048';

                    $mimes = !empty($settings['local_storage_validation']) ? $settings['local_storage_validation'] : '';
                }


                $file = $request->$key_name;

                foreach ($multifile as $key => $value) {
                    $extension = strtolower($value->getClientOriginalExtension());
                    $allowed_extensions = explode(',', $mimes);
                    if (empty($extension) || !in_array($extension, $allowed_extensions)) {
                        return [
                            'flag' => 0,
                            'msg' => 'The ' . $key_name . ' must be a file of type: ' . implode(', ', $allowed_extensions) . '.',
                        ];
                    }
                }

                if (count($custom_validation) > 0) {
                    $validation = $custom_validation;
                } else {

                    $validation = [
                        'mimes:' . $mimes,
                        'max:' . $max_size,
                    ];
                }
                $validator = \Validator::make($multifile, [
                    $key_name => $validation
                ]);

                if ($validator->fails()) {
                    $res = [
                        'flag' => 0,
                        'msg' => $validator->messages()->first(),
                    ];
                    return $res;
                } else {

                    $name = $name;

                    if ($settings['storage_setting'] == 'local') {



                        \Storage::disk()->putFileAs(
                            $path,
                            $request->file($key_name)[$data_key],
                            $name
                        );


                        $path = $name;
                    } else if ($settings['storage_setting'] == 'wasabi') {

                        \Storage::disk('wasabi')->putFileAs(
                            $path,
                            $request->file($key_name)[$data_key],
                            $name
                        );
                        $path = $name;
                    } else if ($settings['storage_setting'] == 's3') {

                        \Storage::disk('s3')->putFileAs(
                            $path,
                            $request->file($key_name)[$data_key],
                            $name
                        );
                        $path = $name;
                    }

                    $res = [
                        'flag' => 1,
                        'msg' => 'success',
                        'url' => $path
                    ];
                    return $res;
                }
            } else {
                $res = [
                    'flag' => 0,
                    'msg' => __('Please set proper configuration for storage.'),
                ];
                return $res;
            }
        } catch (\Exception $e) {
            $res = [
                'flag' => 0,
                'msg' => $e->getMessage(),
            ];
            return $res;
        }
    }


    public static function get_file($path)
    {
        $settings = self::settings();

        try {
            if ($settings['storage_setting'] == 'wasabi') {
                config(
                    [
                        'filesystems.disks.wasabi.key' => $settings['wasabi_key'],
                        'filesystems.disks.wasabi.secret' => $settings['wasabi_secret'],
                        'filesystems.disks.wasabi.region' => $settings['wasabi_region'],
                        'filesystems.disks.wasabi.bucket' => $settings['wasabi_bucket'],
                        'filesystems.disks.wasabi.endpoint' => 'https://s3.' . $settings['wasabi_region'] . '.wasabisys.com'
                    ]
                );
            } elseif ($settings['storage_setting'] == 's3') {

                config(
                    [
                        'filesystems.disks.s3.key' => $settings['s3_key'],
                        'filesystems.disks.s3.secret' => $settings['s3_secret'],
                        'filesystems.disks.s3.region' => $settings['s3_region'],
                        'filesystems.disks.s3.bucket' => $settings['s3_bucket'],
                        'filesystems.disks.s3.use_path_style_endpoint' => false,
                    ]
                );
            }

            return \Storage::disk($settings['storage_setting'])->url('/' . $path);
        } catch (\Throwable $th) {
            return '';
        }
    }

    public static function colorCodeData($type)
    {
        if ($type == 'event') {
            return 1;
        } elseif ($type == 'zoom_meeting') {
            return 2;
        } elseif ($type == 'task') {
            return 3;
        } elseif ($type == 'appointment') {
            return 11;
        } elseif ($type == 'rotas') {
            return 3;
        } elseif ($type == 'holiday') {
            return 4;
        } elseif ($type == 'call') {
            return 10;
        } elseif ($type == 'meeting') {
            return 5;
        } elseif ($type == 'leave') {
            return 6;
        } elseif ($type == 'work_order') {
            return 7;
        } elseif ($type == 'lead') {
            return 7;
        } elseif ($type == 'deal') {
            return 8;
        } elseif ($type == 'interview_schedule') {
            return 9;
        } else {
            return 11;
        }
    }

    public static $colorCode = [
        1 => 'event-warning',
        2 => 'event-secondary',
        3 => 'event-success',
        4 => 'event-warning',
        5 => 'event-danger',
        6 => 'event-dark',
        7 => 'event-black',
        8 => 'event-info',
        9 => 'event-secondary',
        10 => 'event-success',
        11 => 'event-warning',
    ];

    public static function googleCalendarConfig()
    {
        $setting = Utility::settings();

        // $path = storage_path('app/google-calendar/' . $setting['google_calender_json_file']);
        $path = storage_path($setting['google_calender_json_file']);

        config([
            'google-calendar.default_auth_profile' => 'service_account',
            'google-calendar.auth_profiles.service_account.credentials_json' => $path,
            'google-calendar.auth_profiles.oauth.credentials_json' => $path,
            'google-calendar.auth_profiles.oauth.token_json' => $path,
            'google-calendar.calendar_id' => isset($setting['google_clender_id']) ? $setting['google_clender_id'] : '',
            'google-calendar.user_to_impersonate' => '',

        ]);
    }

    public static function addCalendarDataTime($request, $type)
    {
        self::googleCalendarConfig();
        $event = new GoogleEvent();
        $event->name = $request->title;
        $date = $request->start_date . $request->time;
        $event->startDateTime = Carbon::createFromFormat('Y-m-d H:i', $date);
        $event->endDateTime = Carbon::createFromFormat('Y-m-d H:i', $date);
        $event->colorId = self::colorCodeData($type);
        $event->save();
    }

    public static function addCalendarData($request, $type)
    {
        self::googleCalendarConfig();

        $event = new GoogleEvent();
        $event->name = $request->title;
        $event->startDateTime = Carbon::parse($request->start_date);
        $event->StartTime = Carbon::parse($request->time);
        $event->endDateTime = Carbon::parse($request->end_date);
        $event->colorId = self::colorCodeData($type);
        $event->save();
    }

    public static function getCalendarData($type)
    {
        self::googleCalendarConfig();

        $data = GoogleEvent::get();
        $type = self::colorCodeData($type);
        $arrayJson = [];
        foreach ($data as $val) {

            $end_date = date_create($val->endDateTime);

            date_add($end_date, date_interval_create_from_date_string("1 days"));

            if ($val->colorId == "$type") {
                $arrayJson[] = [
                    "id" => $val->id,
                    "title" => $val->summary,
                    "start" => $val->startDateTime,
                    "end" => date_format($end_date, "Y-m-d H:i:s"),
                    "className" => self::$colorCode[$type],
                    "allDay" => true,
                ];
            }
        }
        return $arrayJson;
    }

    public static function getSeoSetting()
    {
        $data = \DB::table('settings')->whereIn('name', ['meta_title', 'meta_description', 'meta_image'])->get();
        $settings = [];
        foreach ($data as $row) {
            $settings[$row->name] = $row->value;
        }
        return $settings;
    }

    public static function webhookSetting($module)
    {
        $webhook = Webhook::where('module', $module)->where('created_by', '=', \Auth::user()->creatorId())->first();
        if (!empty($webhook)) {
            $url = $webhook->url;
            $method = $webhook->method;
            $reference_url = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

            $data['method'] = $method;
            $data['reference_url'] = $reference_url;
            $data['url'] = $url;
            return $data;
        }
        return false;
    }

    public static function WebhookCall($url = null, $parameter = null, $method = 'POST')
    {

        if (!empty($url) && !empty($parameter)) {
            try {

                $curlHandle = curl_init($url);
                curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $parameter);
                curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, strtoupper($method));
                $curlResponse = curl_exec($curlHandle);
                curl_close($curlHandle);
                if (empty($curlResponse)) {
                    return true;
                } else {
                    return false;
                }
            } catch (\Throwable $th) {
                return false;
            }
        } else {
            return false;
        }
    }

    public static function getCookieSetting()
    {
        if (self::$cookies === null) {
            self::$cookies = self::fetchCookieSetting();
        }
        return self::$cookies;
    }

    public static function fetchCookieSetting()
    {
        $data = \DB::table('settings')->whereIn('name', [
            'enable_cookie',
            'cookie_logging',
            'cookie_title',
            'cookie_description',
            'necessary_cookies',
            'strictly_cookie_title',
            'strictly_cookie_description',
            'more_information_description',
            'contactus_url'
        ])->get();
        $settings = [
            'enable_cookie' => 'off',
            'necessary_cookies' => '',
            'cookie_logging' => '',
            'cookie_title' => '',
            'cookie_description' => '',
            'strictly_cookie_title' => '',
            'strictly_cookie_description' => '',
            'more_information_description' => '',
            'contactus_url' => '',
        ];
        foreach ($data as $row) {
            $settings[$row->name] = $row->value;
        }
        return $settings;
    }

    public static function get_device_type($user_agent)
    {
        $mobile_regex = '/(?:phone|windows\s+phone|ipod|blackberry|(?:android|bb\d+|meego|silk|googlebot) .+? mobile|palm|windows\s+ce|opera mini|avantgo|mobilesafari|docomo)/i';
        $tablet_regex = '/(?:ipad|playbook|(?:android|bb\d+|meego|silk)(?! .+? mobile))/i';

        if (preg_match_all($mobile_regex, $user_agent)) {
            return 'mobile';
        } else {

            if (preg_match_all($tablet_regex, $user_agent)) {
                return 'tablet';
            } else {
                return 'desktop';
            }
        }
    }

    public function extraKeyword()
    {
        $keyArr = [
            __('Branch name'),
            __('Award name'),
            __('Occasion name'),
            __('Company policy name'),
            __('Ticket priority'),
            __('Event name'),
            __('Purpose of visit'),
            __('Place of visit'),
            __('Contract number'),
            __('Contract company name'),
        ];
    }

    public static function AnnualLeaveCycle()
    {
        $start_date = '' . date('Y') . '-01-01';
        $end_date = '' . date('Y') . '-12-31';
        $start_date = date('Y-m-d', strtotime($start_date . ' -1 day'));
        $end_date = date('Y-m-d', strtotime($end_date . ' +1 day'));

        $date['start_date'] = $start_date;
        $date['end_date'] = $end_date;

        return $date;
    }

    // start for (plans) storage limit - for file upload size
    public static function updateStorageLimit($company_id, $image_size)
    {
        $image_size = number_format($image_size / 1048576, 2);

        $user = User::find($company_id);
        $plan = Plan::find($user->plan);
        $total_storage = $user->storage_limit + $image_size;

        if ($plan->storage_limit <= $total_storage && $plan->storage_limit != -1) {
            $error = __('Plan storage limit is over so please upgrade the plan.');
            return $error;
        } else {
            $user->storage_limit = $total_storage;
        }

        $user->save();
        return 1;
    }

    public static function changeStorageLimit($company_id, $file_path)
    {
        $files = \File::glob(storage_path($file_path));
        $fileSize = 0;
        foreach ($files as $file) {
            $fileSize += \File::size($file);
        }

        $image_size = number_format($fileSize / 1048576, 2);
        $user = User::find($company_id);
        $plan = Plan::find($user->plan);
        $total_storage = $user->storage_limit - $image_size;
        $user->storage_limit = $total_storage;
        $user->save();

        $status = false;
        foreach ($files as $key => $file) {
            if (\File::exists($file)) {
                $status = \File::delete($file);
            }
        }
        return true;
    }
    // end for (plans) storage limit - for file upload size

    public static function flagOfCountry()
    {
        $arr = [
            'ar' => '🇦🇪 ar',
            'da' => '🇩🇰 da',
            'de' => '🇩🇪 de',
            'es' => '🇪🇸 es',
            'fr' => '🇫🇷 fr',
            'it' => '🇮🇹 it',
            'ja' => '🇯🇵 ja',
            'nl' => '🇳🇱 nl',
            'pl' => '🇵🇱 pl',
            'ru' => '🇷🇺 ru',
            'pt' => '🇵🇹 pt',
            'en' => '🇮🇳 en',
            'tr' => '🇹🇷 tr',
            'pt-br' => '🇵🇹 pt-br',
        ];
        return $arr;
    }

    public static function getChatGPTSettings()
    {
        $user = User::find(\Auth::user()->creatorId());
        $plan = \App\Models\Plan::find($user->plan);
        return $plan;
    }

    public static function getChatGPTSetting()
    {
        $data = DB::table('settings');
        if (\Auth::check()) {
            $data = $data->where('created_by', '=', 1)->get();
            if (count($data) == 0) {
                $data = DB::table('settings')->where('created_by', '=', 1)->get();
            }
        } else {
            $data->where('created_by', '=', 1);
            $data = $data->get();
        }

        $settings = [
            'chatgpt_key' => '',
            'chatgpt_model' => '',
        ];

        foreach ($data as $row) {
            $settings[$row->name] = $row->value;
        }

        return $settings;
    }

    public static function languagecreate()
    {
        $languages = Utility::langList();
        foreach ($languages as $key => $lang) {
            $languageExist = Languages::where('code', $key)->first();
            if (empty($languageExist)) {
                $language = new Languages();
                $language->code = $key;
                $language->fullName = $lang;
                $language->save();
            }
        }
    }

    public static function langSetting()
    {
        $data = DB::table('settings');
        $data = $data->where('created_by', '=', 1)->get();
        if (count($data) == 0) {
            $data = DB::table('settings')->where('created_by', '=', 1)->get();
        }
        $settings = [];
        foreach ($data as $row) {
            $settings[$row->name] = $row->value;
        }
        return $settings;
    }

    public static function langList()
    {
        $languages = [
            "ar" => "Arabic",
            "zh" => "Chinese",
            "da" => "Danish",
            "de" => "German",
            "en" => "English",
            "es" => "Spanish",
            "fr" => "French",
            "he" => "Hebrew",
            "it" => "Italian",
            "ja" => "Japanese",
            "nl" => "Dutch",
            "pl" => "Polish",
            "pt" => "Portuguese",
            "ru" => "Russian",
            "tr" => "Turkish",
            "pt-br" => "Portuguese(Brazil)"
        ];
        return $languages;
    }

    public static function get_messenger_packages_migration()
    {
        $totalMigration = 0;
        $messengerPath = glob(base_path() . '/vendor/munafio/chatify/database/migrations' . DIRECTORY_SEPARATOR . '*.php');
        if (!empty($messengerPath)) {
            $messengerMigration = str_replace('.php', '', $messengerPath);
            $totalMigration = count($messengerMigration);
        }

        return $totalMigration;
    }

    //create company default roles
    public static function MakeRole($company_id)
    {
        $data = [];
        $hr_role_permission = [
            "Manage Language",
            "Manage User",
            "Create User",
            "Edit User",
            "Delete User",
            "Manage Award",
            "Create Award",
            "Edit Award",
            "Delete Award",
            "Manage Transfer",
            "Create Transfer",
            "Edit Transfer",
            "Delete Transfer",
            "Manage Resignation",
            "Create Resignation",
            "Edit Resignation",
            "Delete Resignation",
            "Manage Travel",
            "Create Travel",
            "Edit Travel",
            "Delete Travel",
            "Manage Promotion",
            "Create Promotion",
            "Edit Promotion",
            "Delete Promotion",
            "Manage Complaint",
            "Create Complaint",
            "Edit Complaint",
            "Delete Complaint",
            "Manage Warning",
            "Create Warning",
            "Edit Warning",
            "Delete Warning",
            "Manage Termination",
            "Create Termination",
            "Edit Termination",
            "Delete Termination",
            "Manage Department",
            "Create Department",
            "Edit Department",
            "Delete Department",
            "Manage Designation",
            "Create Designation",
            "Edit Designation",
            "Delete Designation",
            "Manage Document Type",
            "Create Document Type",
            "Edit Document Type",
            "Delete Document Type",
            "Manage Branch",
            "Create Branch",
            "Edit Branch",
            "Delete Branch",
            "Manage Award Type",
            "Create Award Type",
            "Edit Award Type",
            "Delete Award Type",
            "Manage Termination Type",
            "Create Termination Type",
            "Edit Termination Type",
            "Delete Termination Type",
            "Manage Employee",
            "Create Employee",
            "Edit Employee",
            "Delete Employee",
            "Show Employee",
            "Manage Payslip Type",
            "Create Payslip Type",
            "Edit Payslip Type",
            "Delete Payslip Type",
            "Manage Allowance Option",
            "Create Allowance Option",
            "Edit Allowance Option",
            "Delete Allowance Option",
            "Manage Loan Option",
            "Create Loan Option",
            "Edit Loan Option",
            "Delete Loan Option",
            "Manage Deduction Option",
            "Create Deduction Option",
            "Edit Deduction Option",
            "Delete Deduction Option",
            "Manage Set Salary",
            "Create Set Salary",
            "Edit Set Salary",
            "Delete Set Salary",
            "Manage Allowance",
            "Create Allowance",
            "Edit Allowance",
            "Delete Allowance",
            "Create Commission",
            "Create Loan",
            "Create Saturation Deduction",
            "Create Other Payment",
            "Create Overtime",
            "Edit Commission",
            "Delete Commission",
            "Edit Loan",
            "Delete Loan",
            "Edit Saturation Deduction",
            "Delete Saturation Deduction",
            "Edit Other Payment",
            "Delete Other Payment",
            "Edit Overtime",
            "Delete Overtime",
            "Manage Pay Slip",
            "Create Pay Slip",
            "Edit Pay Slip",
            "Delete Pay Slip",
            "Manage Event",
            "Create Event",
            "Edit Event",
            "Delete Event",
            "Manage Announcement",
            "Create Announcement",
            "Edit Announcement",
            "Delete Announcement",
            "Manage Leave Type",
            "Create Leave Type",
            "Edit Leave Type",
            "Delete Leave Type",
            "Manage Leave",
            "Create Leave",
            "Edit Leave",
            "Delete Leave",
            "Manage Meeting",
            "Create Meeting",
            "Edit Meeting",
            "Delete Meeting",
            "Manage Ticket",
            "Create Ticket",
            "Edit Ticket",
            "Delete Ticket",
            "Manage Attendance",
            "Create Attendance",
            "Edit Attendance",
            "Delete Attendance",
            "Manage TimeSheet",
            "Create TimeSheet",
            "Edit TimeSheet",
            "Delete TimeSheet",
            'Manage Assets',
            'Create Assets',
            'Edit Assets',
            'Delete Assets',
            'Manage Document',
            'Manage Employee Profile',
            'Show Employee Profile',
            'Manage Employee Last Login',
            'Manage Indicator',
            'Create Indicator',
            'Edit Indicator',
            'Delete Indicator',
            'Show Indicator',
            'Manage Appraisal',
            'Create Appraisal',
            'Edit Appraisal',
            'Delete Appraisal',
            'Show Appraisal',
            "Manage Goal Type",
            "Create Goal Type",
            "Edit Goal Type",
            "Delete Goal Type",
            "Manage Goal Tracking",
            "Create Goal Tracking",
            "Edit Goal Tracking",
            "Delete Goal Tracking",
            "Manage Company Policy",
            "Create Company Policy",
            "Edit Company Policy",
            "Delete Company Policy",
            "Manage Trainer",
            "Create Trainer",
            "Edit Trainer",
            "Delete Trainer",
            "Show Trainer",
            "Manage Training",
            "Create Training",
            "Edit Training",
            "Delete Training",
            "Show Training",
            "Manage Training Type",
            "Create Training Type",
            "Edit Training Type",
            "Delete Training Type",
            "Manage Holiday",
            "Create Holiday",
            "Edit Holiday",
            "Delete Holiday",
            "Manage Job Category",
            "Create Job Category",
            "Edit Job Category",
            "Delete Job Category",
            "Manage Job Stage",
            "Create Job Stage",
            "Edit Job Stage",
            "Delete Job Stage",
            "Manage Job",
            "Create Job",
            "Edit Job",
            "Delete Job",
            "Show Job",
            "Manage Job Application",
            "Create Job Application",
            "Edit Job Application",
            "Delete Job Application",
            "Show Job Application",
            "Move Job Application",
            "Add Job Application Note",
            "Delete Job Application Note",
            "Add Job Application Skill",
            "Manage Job OnBoard",
            "Manage Custom Question",
            "Create Custom Question",
            "Edit Custom Question",
            "Delete Custom Question",
            "Manage Interview Schedule",
            "Create Interview Schedule",
            "Edit Interview Schedule",
            "Delete Interview Schedule",
            "Manage Career",
            "Manage Performance Type",
            "Create Performance Type",
            "Edit Performance Type",
            "Delete Performance Type",
            "Manage Contract",
            "Create Contract",
            "Edit Contract",
            "Delete Contract",
            "Store Note",
            "Delete Note",
            "Store Comment",
            "Delete Comment",
            "Delete Attachment",
            "Manage Contract Type",
            "Create Contract Type",
            "Edit Contract Type",
            "Delete Contract Type",
            "Manage Employment Type",
            "Create Employment Type",
            "Edit Employment Type",
            "Delete Employment Type",
        ];

        $hr_permission = Role::where('name', 'hr')->where('created_by', $company_id)->where('guard_name', 'web')->first();

        if (empty($hr_permission)) {
            $hr_permission = new Role();
            $hr_permission->name = 'hr';
            $hr_permission->guard_name = 'web';
            $hr_permission->created_by = $company_id;
            $hr_permission->save();
            foreach ($hr_role_permission as $permission_s) {
                $permission = Permission::where('name', $permission_s)->first();
                $hr_permission->givePermissionTo($permission);
            }
        }

        $employee_role_permission = [
            "Manage Award",
            "Manage Transfer",
            "Manage Resignation",
            "Create Resignation",
            "Edit Resignation",
            "Delete Resignation",
            "Manage Travel",
            "Manage Promotion",
            "Manage Complaint",
            "Create Complaint",
            "Edit Complaint",
            "Delete Complaint",
            "Manage Warning",
            "Create Warning",
            "Edit Warning",
            "Delete Warning",
            "Manage Termination",
            "Manage Employee",
            "Edit Employee",
            "Show Employee",
            "Manage Allowance",
            "Manage Event",
            "Manage Announcement",
            "Manage Leave",
            "Create Leave",
            "Edit Leave",
            "Delete Leave",
            "Manage Meeting",
            "Manage Ticket",
            "Create Ticket",
            "Edit Ticket",
            "Delete Ticket",
            "Manage Language",
            "Manage TimeSheet",
            "Create TimeSheet",
            "Edit TimeSheet",
            "Delete TimeSheet",
            "Manage Attendance",
            'Manage Document',
            "Manage Holiday",
            "Manage Career",
            "Manage Contract",
            "Store Note",
            "Delete Note",
            "Store Comment",
            "Delete Comment",
            "Delete Attachment",
        ];

        $employee_permission = Role::where('name', 'employee')->where('created_by', $company_id)->where('guard_name', 'web')->first();

        if (empty($employee_permission)) {
            $employee_permission = new Role();
            $employee_permission->name = 'employee';
            $employee_permission->guard_name = 'web';
            $employee_permission->created_by = $company_id;
            $employee_permission->save();
            foreach ($employee_role_permission as $permission_s) {
                $permission = Permission::where('name', $permission_s)->first();
                $employee_permission->givePermissionTo($permission);
            }
        }

        $data['employee_permission'] = $employee_permission;

        return $data;
    }

    public static function getSMTPDetails($user_id)
    {
        $settings = Utility::settings($user_id);
        if ($settings) {
            config([
                'mail.default' => isset($settings['mail_driver']) ? $settings['mail_driver'] : '',
                'mail.mailers.smtp.host' => isset($settings['mail_host']) ? $settings['mail_host'] : '',
                'mail.mailers.smtp.port' => isset($settings['mail_port']) ? $settings['mail_port'] : '',
                'mail.mailers.smtp.encryption' => isset($settings['mail_encryption']) ? $settings['mail_encryption'] : '',
                'mail.mailers.smtp.username' => isset($settings['mail_username']) ? $settings['mail_username'] : '',
                'mail.mailers.smtp.password' => isset($settings['mail_password']) ? $settings['mail_password'] : '',
                'mail.from.address' => isset($settings['mail_from_address']) ? $settings['mail_from_address'] : '',
                'mail.from.name' => isset($settings['mail_from_name']) ? $settings['mail_from_name'] : '',
            ]);

            return $settings;
        } else {
            return redirect()->back()->with('Email SMTP settings does not configured so please contact to your site admin.');
        }
    }

    public static function getPusherDetails()
    {
        $data = DB::table('settings');
        if (\Auth::check()) {
            $data = $data->where('created_by', '=', 1)->get();
            if (count($data) == 0) {
                $data = DB::table('settings')->where('created_by', '=', 1)->get();
            }
        } else {
            $data->where('created_by', '=', 1);
            $data = $data->get();
        }

        $settings = [
            'pusher_app_id' => '',
            'pusher_app_key' => '',
            'pusher_app_secret' => '',
            'pusher_app_cluster' => '',
        ];

        foreach ($data as $row) {
            $settings[$row->name] = $row->value;
        }

        return $settings;
    }

    public static function getPusherSetting()
    {
        $settings = Utility::getPusherDetails();
        if ($settings) {
            config([
                'chatify.pusher.key' => isset($settings['pusher_app_key']) ? $settings['pusher_app_key'] : '',
                'chatify.pusher.secret' => isset($settings['pusher_app_secret']) ? $settings['pusher_app_secret'] : '',
                'chatify.pusher.app_id' => isset($settings['pusher_app_id']) ? $settings['pusher_app_id'] : '',
                'chatify.pusher.options.cluster' => isset($settings['pusher_app_cluster']) ? $settings['pusher_app_cluster'] : '',
            ]);
            return $settings;
        }
    }

    public static function emailTemplateLang($lang)
    {
        $defaultTemplate = [
            'new_user' => [
                'subject' => 'New User',
                'lang' => [
                    'en' => '<p>Hello,&nbsp;<br />Welcome to {app_name}.</p>
                    <p><strong>You are now user..</strong></p>
                    <p><strong>Email </strong>: {email}<br /><strong>Password</strong> : {password}<br /><strong>Passcode</strong> : {passcode}</p>
                    <p>{app_url}</p>
                    <p>Thanks,<br />{app_name}</p>',
                ],
            ],
            'new_employee' => [
                'subject' => 'New Employee',
                'lang' => [
                    'en' => '<p>Hello {employee_name},&nbsp;<br />Welcome to {app_name}.</p>
                    <p>You are now Employee..</p>
                    <p><strong>Email </strong>: {employee_email}</p>
                    <p><strong>Password</strong> : {employee_password}</p>
                    <p><strong>Passcode</strong> : {employee_passcode}</p>
                    <p>{app_url}</p>
                    <p>Thanks,<br />{app_name}</p>',
                ],
            ],
            'new_payroll' => [
                'subject' => 'New Payroll',
                'lang' => [
                    'en' => '<p><strong>Subject</strong>:-HR department/Company to send payslips by email at time of confirmation of payslip</p>
                    <p>Hi {name},</p>
                    <p>Hope this email ﬁnds you well! Please see attached payslip for {salary_month}.</p>
                    <p style="text-align: center;" align="center"><strong>simply click on the button below </strong></p>
                    <p style="text-align: center;" align="center"><span style="font-size: 18pt;"><a style="background: #6676ef; color: #ffffff; font-family: "Open Sans", Helvetica, Arial, sans-serif; font-weight: normal; line-height: 120%; margin: 0px; text-decoration: none; text-transform: none;" href="{url}" target="_blank" rel="noopener"> <strong style="color: white; font-weight: bold; text: white;">Payslip</strong> </a></span></p>
                    <p style="text-align: left;" align="center">Feel free to reach out if you have any questions.</p>
                    <p>Thank you</p>
                    <p><strong>Regards,</strong></p>
                    <p><strong>HR Department,</strong></p>
                    <p><span style="color: #000000; font-family: "Open Sans", sans-serif; font-size: 14px; background-color: #ffffff;"><strong>{app_name}</strong></span></p>',
                ],
            ],
            'attendance_request' => [
                'subject' => 'Attendance Request',
                'lang' => [
                    'en' => '<p>Hi {receiver_name} HR/Owner,</p>
                        <p>{employee_name} has submitted a request for <strong>{type}</strong> on {date} at {time}.</p>
                        <p><strong>Reason:</strong> {reason}</p>
                        <p style="text-align: center;" align="center">
                            <span style="font-size: 18pt;">
                                <a style="background: #6676ef; color: #ffffff; font-family: "Open Sans", Helvetica, Arial, sans-serif;
                                    font-weight: normal; line-height: 120%; margin: 0px; text-decoration: none;"
                                    href="{url}" target="_blank" rel="noopener">
                                    <strong style="color: white; font-weight: bold;">View Request</strong>
                                </a>
                            </span>
                        </p>
                        <p>Thank you</p>
                        <p><strong>Regards,</strong></p>
                        <p><strong>{app_name}</strong></p>
                    ',
                ],
            ],
            'new_ticket' => [
                'subject' => 'New Ticket',
                'lang' => [
                    'en' => '<p ><b>Subject:-HR department/Company to send ticket for {ticket_title}</b></p>
                    <p ><b>Hi {ticket_name},</b></p>
                    <p >Hope this email ﬁnds you well! , Your ticket code is {ticket_code}. </p></br>
                    {ticket_description},
                    <p >Feel free to reach out if you have any questions.</p></br>
                    <p><b>Thank you</b></p>
                    <p><b>Regards,</b></p>
                    <p><b>HR Department,</b></p>
                    <p><b>{app_name}</b></p>',
                ],
            ],
            'new_award' => [
                'subject' => 'New Award',
                'lang' => [
                    'en' => '<p ><b>Subject:-HR department/Company to send award letter to recognize an employee</b></p>
                    <p ><b>Hi {award_name},</b></p>
                    <p >I am much pleased to nominate {award_name}  </p>
                    <p >I am satisfied that (he/she) is the best employee for the award. I have realized that she is a goal-oriented person, efficient and very punctual. She is always ready to share her knowledge of details.</p>
                    <p>Additionally, (he/she) has occasionally solved conflicts and difficult situations within working hours. (he/she) has received some awards from the non-governmental organization within the country; this was because of taking part in charity activities to help the needy.</p>
                    <p>I believe these qualities and characteristics need to be appreciated. Therefore, (he/she) deserves the award hence nominating her.</p>
                    <p>Feel free to reach out if you have any questions.</p>
                    <p><b>Thank you</b></p>
                    <p><b>Regards,</b></p>
                    <p><b>HR Department,</b></p>
                    <p><b>{app_name}</b></p>',
                ],
            ],
            'employee_transfer' => [
                'subject' => 'Employee Transfer',
                'lang' => [
                    'en' => '<p ><b>Subject:-HR department/Company to send transfer letter to be issued to an employee from one location to another.</b></p>
                    <p ><b>Dear {transfer_name},</b></p>
                    <p >As per Management directives, your services are being transferred w.e.f.{transfer_date}. </p>
                    <p >Your new place of posting is {transfer_department} department of {transfer_branch} branch and date of transfer {transfer_date}. </p>
                    {transfer_description}.
                    <p>Feel free to reach out if you have any questions.</p>
                    <p><b>Thank you</b></p>
                    <p><b>Regards,</b></p>
                    <p><b>HR Department,</b></p>
                    <p><b>{app_name}</b></p>',
                ],
            ],
            'employee_resignation' => [
                'subject' => 'Employee Resignation',
                'lang' => [
                    'en' => '<p ><b>Subject:-HR department/Company to send resignation letter .</b></p>
                    <p ><b>Dear {assign_user},</b></p>
                    <p >It is with great regret that I formally acknowledge receipt of your resignation notice on {notice_date} to {resignation_date} is your final day of work. </p>
                    <p >It has been a pleasure working with you, and on behalf of the team, I would like to wish you the very best in all your future endeavors. Included with this letter, please find an information packet with detailed information on the resignation process. </p>
                    <p>Thank you again for your positive attitude and hard work all these years.</p>
                    <p>Feel free to reach out if you have any questions.</p>
                    <p>Thank you</p>
                    <p><b>Regards,</b></p>
                    <p><b>HR Department,</b></p>
                    <p><b>{app_name}</b></p>',
                ],
            ],
            'employee_trip' => [
                'subject' => 'Employee Trip',
                'lang' => [
                    'en' => '<p><strong>Subject:-HR department/Company to send trip letter .</strong></p>
                    <p><strong>Dear {employee_trip_name},</strong></p>
                    <p>Top of the morning to you! I am writing to your department office with a humble request to travel for a {purpose_of_visit} abroad.</p>
                    <p>It would be the leading climate business forum of the year and have been lucky enough to be nominated to represent our company and the region during the seminar.</p>
                    <p>My three-year membership as part of the group and contributions I have made to the company, as a result, have been symbiotically beneficial. In that regard, I am requesting you as my immediate superior to permit me to attend.</p>
                    <p>More detail about trip:{start_date} to {end_date}</p>
                    <p>Trip Duration:{start_date} to {end_date}</p>
                    <p>Purpose of Visit:{purpose_of_visit}</p>
                    <p>Place of Visit:{place_of_visit}</p>
                    <p>Description:{trip_description}</p>
                    <p>Feel free to reach out if you have any questions.</p>
                    <p>Thank you</p>
                    <p><strong>Regards,</strong></p>
                    <p><strong>HR Department,</strong></p>
                    <p><strong>{app_name}</strong></p>',
                ],
            ],
            'employee_promotion' => [
                'subject' => 'Employee Promotion',
                'lang' => [
                    'en' => '<p>&nbsp;</p>
                    <p><strong>Subject:-HR department/Company to send job promotion congratulation letter.</strong></p>
                    <p><strong>Dear {employee_promotion_name},</strong></p>
                    <p>Congratulations on your promotion to {promotion_designation} {promotion_title} effective {promotion_date}.</p>
                    <p>We shall continue to expect consistency and great results from you in your new role. We hope that you will set an example for the other employees of the organization.</p>
                    <p>We wish you luck for your future performance, and congratulations!.</p>
                    <p>Again, congratulations on the new position.</p>
                    <p>&nbsp;</p>
                    <p>Feel free to reach out if you have any questions.</p>
                    <p>Thank you</p>
                    <p><strong>Regards,</strong></p>
                    <p><strong>HR Department,</strong></p>
                    <p><strong>{app_name}</strong></p>',
                ],
            ],
            'employee_complaints' => [
                'subject' => 'Employee Complaints',
                'lang' => [
                    'en' => '<p><strong>Subject:-HR department/Company to send complaints letter.</strong></p>
                    <p><strong>Dear {employee_complaints_name},</strong></p>
                    <p>I would like to report a conflict between you and the other person.There have been several incidents over the last few days, and I feel that it is time to report a formal complaint against him/her.</p>
                    <p>&nbsp;</p>
                    <p>Feel free to reach out if you have any questions.</p>
                    <p>Thank you</p>
                    <p><strong>Regards,</strong></p>
                    <p><strong>HR Department,</strong></p>
                    <p><strong>{app_name}</strong></p>',
                ],
            ],
            'employee_warning' => [
                'subject' => 'Employee Warning',
                'lang' => [
                    'en' => '<p><strong>Subject:-HR department/Company to send warning letter.</strong></p>
                    <p><strong>Dear {employee_warning_name},</strong></p>
                    <p>{warning_subject}</p>
                    <p>{warning_description}</p>
                    <p>Feel free to reach out if you have any questions.</p>
                    <p>Thank you</p>
                    <p><strong>Regards,</strong></p>
                    <p><strong>HR Department,</strong></p>
                    <p><strong>{app_name}</strong></p>',
                ],
            ],
            'employee_termination' => [
                'subject' => 'Employee Termination',
                'lang' => [
                    'en' => '<p><strong>Subject:-HR department/Company to send termination letter.</strong></p>
                    <p><strong>Dear {employee_termination_name},</strong></p>
                    <p>This letter is written to notify you that your employment with our company is terminated.</p>
                    <p>More detail about termination:</p>
                    <p>Notice Date :{notice_date}</p>
                    <p>Termination Date:{termination_date}</p>
                    <p>Termination Type:{termination_type}</p>
                    <p>&nbsp;</p>
                    <p>Feel free to reach out if you have any questions.</p>
                    <p>Thank you</p>
                    <p><strong>Regards,</strong></p>
                    <p><strong>HR Department,</strong></p>
                    <p><strong>{app_name}</strong></p>',
                ],
            ],
            'leave_status' => [
                'subject' => 'Leave Status',
                'lang' => [
                    'en' => '<p><strong>Subject:-HR department/Company to send approval letter to {leave_status} a vacation or leave.</strong></p>
                    <p><strong>Dear {leave_status_name},</strong></p>
                    <p>I have {leave_status} your leave request for {leave_reason} from {leave_start_date} to {leave_end_date}.</p>
                    <p>{total_leave_days} days I have {leave_status}&nbsp; your leave request for {leave_reason}.</p>
                    <p>We request you to complete all your pending work or any other important issue so that the company does not face any loss or problem during your absence. We appreciate your thoughtfulness to inform us well in advance</p>
                    <p>&nbsp;</p>
                    <p>Feel free to reach out if you have any questions.</p>
                    <p>Thank you</p>
                    <p><strong>Regards,</strong></p>
                    <p><strong>HR Department,</strong></p>
                    <p><strong>{app_name}</strong></p>',
                ],
            ],
            'contract' => [
                'subject' => 'Contract',
                'lang' => [
                    'en' => '<p><span style="font-family: sans-serif;"><strong>Hi </strong>{contract_employee} </span></p>
                    <p><span style="font-family: sans-serif;"><strong>Contract Subject:</strong> {contract_subject} </span></p>
                    <p><strong><span style="font-family: sans-serif;">S</span></strong><span style="font-family: sans-serif;"><strong>tart Date</strong>: {contract_start_date} </span></p>
                    <p><span style="font-family: sans-serif;"><strong>End Date</strong>: {contract_end_date} </span></p>
                    <p><span style="font-family: sans-serif;">Looking forward to hear from you. </span></p>
                    <p><strong><span style="font-family: sans-serif;">Kind Regards, </span></strong></p>
                    <p><span style="font-family: sans-serif;">{company_name}</span></p>',
                ],
            ],
            'new_leave_request' => [
                'subject' => 'New Leave Request',
                'lang' => [
                    'en' => '<p><span style="font-family: sans-serif;"><strong>Subject:</strong> New Leave Request </span></p>
                    <p><span style="font-family: sans-serif;">Employee { employee_name } has applied for leave. Here are the details:</span></p>
                    <p><span style="font-family: sans-serif;"><strong>Leave Type:</strong> { leave_type }</span></p>
                    <p><span style="font-family: sans-serif;"><strong>Leave Duration:</strong> { leave_start_end_time }</span></p>
                    <p><span style="font-family: sans-serif;"><strong>Reason:</strong>{ leave_reason }</span></p>
                    <p><span style="font-family: sans-serif;">Thank you,</span></p>
                    <p><span style="font-family: sans-serif;">HR Department,</span></p>
                    <p><span style="font-family: sans-serif;">{ company_name }</span></p>',
                ],
            ],
            'salary_revision_created' => [
                'subject' => 'New Salary Revision Scheduled',
                'lang' => [
                    'en' => '<p><strong>Subject: New Salary Revision Scheduled for Review</strong></p>
                    <p>A salary revision has been scheduled for employee <strong>{revision_employee_name}</strong>. Please review and approve.</p>
                    <p><strong>Current Salary:</strong> {revision_current_salary}</p>
                    <p><strong>Revised Salary:</strong> {revision_revised_salary}</p>
                    <p><strong>Revision Type:</strong> {revision_type}</p>
                    <p><strong>Value:</strong> {revision_value}</p>
                    <p><strong>Effective From:</strong> {revision_effective_from}</p>
                    <p><strong>Cycle:</strong> {revision_cycle}</p>
                    <p><strong>Note:</strong> {revision_note}</p>
                    <p>Please log in to the system to approve this revision.</p>
                    <p>Thank you,</p>
                    <p>HR Department,</p>
                    <p>{company_name}</p>',
                ],
            ],
            'salary_revision_updated' => [
                'subject' => 'Salary Revision Updated',
                'lang' => [
                    'en' => '<p><strong>Subject: Salary Revision Has Been Updated</strong></p>
                    <p>The salary revision for employee <strong>{revision_employee_name}</strong> has been updated by <strong>{revision_updated_by}</strong>.</p>
                    <p><strong>Current Salary:</strong> {revision_current_salary}</p>
                    <p><strong>Revised Salary:</strong> {revision_revised_salary}</p>
                    <p><strong>Revision Type:</strong> {revision_type}</p>
                    <p><strong>Value:</strong> {revision_value}</p>
                    <p><strong>Effective From:</strong> {revision_effective_from}</p>
                    <p><strong>Cycle:</strong> {revision_cycle}</p>
                    <p><strong>Note:</strong> {revision_note}</p>
                    <p>Please log in to the system to review the updated revision.</p>
                    <p>Thank you,</p>
                    <p>HR Department,</p>
                    <p>{company_name}</p>',
                ],
            ],
            'salary_revision_approved' => [
                'subject' => 'Salary Revision Approved',
                'lang' => [
                    'en' => '<p><strong>Subject: Salary Revision Has Been Approved</strong></p>
                    <p>The salary revision for employee <strong>{revision_employee_name}</strong> has been approved by <strong>{revision_approved_by}</strong> and will be applied automatically on the effective date.</p>
                    <p><strong>Current Salary:</strong> {revision_current_salary}</p>
                    <p><strong>Revised Salary:</strong> {revision_revised_salary}</p>
                    <p><strong>Revision Type:</strong> {revision_type}</p>
                    <p><strong>Value:</strong> {revision_value}</p>
                    <p><strong>Effective From:</strong> {revision_effective_from}</p>
                    <p><strong>Cycle:</strong> {revision_cycle}</p>
                    <p><strong>Note:</strong> {revision_note}</p>
                    <p>The new salary will be applied automatically by the scheduled job on the effective date.</p>
                    <p>Thank you,</p>
                    <p>HR Department,</p>
                    <p>{company_name}</p>',
                ],
            ],
            'salary_revision_applied' => [
                'subject' => 'Salary Revision Applied',
                'lang' => [
                    'en' => '<p><strong>Subject: Salary Revision Has Been Applied</strong></p>
                    <p>The approved salary revision for employee <strong>{revision_employee_name}</strong> has been applied automatically by the scheduled job.</p>
                    <p><strong>Previous Salary:</strong> {revision_current_salary}</p>
                    <p><strong>New Salary:</strong> {revision_revised_salary}</p>
                    <p><strong>Revision Type:</strong> {revision_type}</p>
                    <p><strong>Value:</strong> {revision_value}</p>
                    <p><strong>Effective From:</strong> {revision_effective_from}</p>
                    <p><strong>Note:</strong> {revision_note}</p>
                    <p>Feel free to reach out if you have any questions.</p>
                    <p>Thank you,</p>
                    <p>HR Department,</p>
                    <p>{company_name}</p>',
                ],
            ],
            'salary_revision_rejected' => [
                'subject' => 'Salary Revision Rejected',
                'lang' => [
                    'en' => '<p><strong>Subject: Salary Revision Has Been Rejected</strong></p>
                    <p>The salary revision scheduled for employee <strong>{revision_employee_name}</strong> has been rejected by <strong>{revision_rejected_by}</strong>.</p>
                    <p><strong>Current Salary:</strong> {revision_current_salary}</p>
                    <p><strong>Proposed Revised Salary:</strong> {revision_revised_salary}</p>
                    <p><strong>Revision Type:</strong> {revision_type}</p>
                    <p><strong>Value:</strong> {revision_value}</p>
                    <p><strong>Effective From:</strong> {revision_effective_from}</p>
                    <p><strong>Note:</strong> {revision_note}</p>
                    <p>Please contact your administrator for further information.</p>
                    <p>Thank you,</p>
                    <p>HR Department,</p>
                    <p>{company_name}</p>',
                ],
            ],
        ];

        $email = EmailTemplate::all();
        foreach ($email as $e) {
            foreach ($defaultTemplate[$e->slug]['lang'] as $content) {
                $emailNoti = EmailTemplateLang::where('parent_id', $e->id)->where('lang', $lang)->count();
                if ($emailNoti == 0) {
                    EmailTemplateLang::create(
                        [
                            'parent_id' => $e->id,
                            'lang' => $lang,
                            'subject' => $defaultTemplate[$e->slug]['subject'],
                            'content' => $content,
                        ]
                    );
                }
            }
        }
    }

    public static function notificationTemplateLang($lang)
    {
        $defaultTemplate = [
            'new_monthly_payslip' => [
                'variables' => '{
                    "Year": "year"
                  }',
                'lang' => [
                    'en' => 'Payslip generated of {year}.',
                ]
            ],
            'new_announcement' => [
                'variables' => '{
                    "Announcement Title": "announcement_title",
                    "Branch name": "branch_name",
                    "Start Date": "start_date",
                    "End Date": "end_date"
                  }',
                'lang' => [
                    'en' => '{announcement_title} announcement created for branch {branch_name} from {start_date} to {end_date}',
                ],
            ],
            'new_meeting' => [
                'variables' => '{
                    "Meeting title": "meeting_title",
                    "Branch name": "branch_name",
                    "Date": "date",
                    "Time": "time"
                  }',
                'lang' => [
                    'en' => '{meeting_title} meeting created for {branch_name} from {date} at {time}.',
                ],
            ],
            'new_award' => [
                'variables' => '{
                    "Award name": "award_name",
                    "Employee Name": "employee_name",
                    "Date": "date"
                  }',
                'lang' => [
                    'en' => '{award_name} created for {employee_name} from {date}.',
                ],
            ],
            'new_holidays' => [
                'variables' => '{
                    "Occasion name": "occasion_name",
                    "Start Date": "start_date",
                    "End Date": "end_date"
                  }',
                'lang' => [
                    'en' => '{occasion_name} on {start_date} to {end_date}.',
                ],
            ],
            'new_company_policy' => [
                'variables' => '{
                    "Company policy name": "company_policy_name",
                    "Branch name": "branch_name"
                  }',
                'lang' => [
                    'en' => '{company_policy_name} for {branch_name} created.',
                ],
            ],
            'new_ticket' => [
                'variables' => '{
                    "Ticket priority": "ticket_priority",
                    "Employee Name": "employee_name"
                  }',
                'lang' => [
                    'en' => 'New Support ticket created of {ticket_priority} priority for {employee_name}.',
                ],
            ],
            'new_event' => [
                'variables' => '{
                    "Event name": "event_name",
                    "Branch name": "branch_name",
                    "Start Date": "start_date",
                    "End Date": "end_date"
                  }',
                'lang' => [
                    'en' => '{event_name} for branch {branch_name} from {start_date} to {end_date}',
                ],
            ],
            'leave_approve_reject' => [
                'variables' => '{
                    "Leave Status": "leave_status"
                  }',
                'lang' => [
                    'en' => 'Your leave has been {leave_status}.',
                ],
            ],
            'new_trip' => [
                'variables' => '{
                    "Purpose of visit": "purpose_of_visit",
                    "Place of visit": "place_of_visit",
                    "Employee Name": "employee_name",
                    "Start Date": "start_date",
                    "End Date": "end_date"
                  }',
                'lang' => [
                    'en' => '{purpose_of_visit} is created to visit {place_of_visit} for {employee_name} from {start_date} to {end_date}.',
                ],
            ],
            'contract_notification' => [
                'variables' => '{
                    "Contract number": "contract_number",
                    "Contract company name": "contract_company_name"
                  }',
                'lang' => [
                    'en' => 'New Invoice {contract_number} created by {contract_company_name}.',
                ],
            ],
        ];

        $notification = NotificationTemplates::all();
        foreach ($notification as $ntfy) {
            foreach ($defaultTemplate[$ntfy->slug]['lang'] as $content) {
                $emailNoti = NotificationTemplateLangs::where('parent_id', $ntfy->id)->where('lang', $lang)->count();
                if ($emailNoti == 0) {
                    NotificationTemplateLangs::create(
                        [
                            'parent_id' => $ntfy->id,
                            'lang' => $lang,
                            'variables' => $defaultTemplate[$ntfy->slug]['variables'],
                            'content' => $content,
                            'created_by' => 1,
                        ]
                    );
                }
            }
        }
    }

    public static function referralTransaction($plan, $company = '')
    {
        if ($company != '') {
            $objUser = $company;
        } else {
            $objUser = \Auth::user();
        }

        $user = ReferralTransaction::where('company_id', $objUser->id)->first();

        $referralSetting = ReferralSetting::where('created_by', 1)->first();

        if ($objUser->used_referral_code != 0 && $user == null && (isset($referralSetting) && $referralSetting->is_enable == 1)) {
            $transaction = new ReferralTransaction();
            $transaction->company_id = $objUser->id;
            $transaction->plan_id = $plan->id;
            $transaction->plan_price = $plan->price;
            $transaction->commission = $referralSetting->percentage;
            $transaction->referral_code = $objUser->used_referral_code;
            $transaction->save();

            $commissionAmount = ($plan->price * $referralSetting->percentage) / 100;
            $user = User::where('referral_code', $objUser->used_referral_code)->first();

            $user->commission_amount = $user->commission_amount + $commissionAmount;
            $user->save();
        }
    }

    public static function getTableFields($input, $extraFields = [])
    {
        try {
            if (Schema::hasTable($input)) {
                $tableFields = Schema::getColumnListing($input);
            } else {
                return [
                    'status' => true,
                    'message' => 'Table name not found',
                    'data' => null,
                ];
            }

            $filteredFields = array_diff($tableFields, $extraFields);

            return [
                'status' => true,
                'message' => 'success',
                'data' => $filteredFields,
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ];
        }
    }
}

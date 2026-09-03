<?php

namespace App\Http\Controllers;

use App\Models\AccountList;
use App\Models\Announcement;
use App\Models\AttendanceEmployee;
use App\Models\Employee;
use App\Models\Event;
use App\Models\Holiday;
use App\Models\LandingPageSection;
use App\Models\Meeting;
use App\Models\Job;
use App\Models\Order;
use App\Models\Payees;
use App\Models\Payer;
use App\Models\Plan;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Utility;
use AWS\CRT\HTTP\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;


use App\Models\EmployeeDocument;

class HomeController extends Controller
{

    public function __construct()
    {
        $this->middleware('2fa');
    }

    public function index()
    {
        if (Auth::check()) {
            $user = Auth::user();

            $transdate = date('Y-m-d');
            $holidays = Holiday::where('created_by', \Auth::user()->creatorId())
                ->whereDate('end_date', '>=', $transdate)
                ->get();

            $arrHolidays = [];
            foreach ($holidays as $holiday) {
                $arrHolidays[] = [
                    'id'        => $holiday->id,
                    'title'     => $holiday->occasion,
                    'start'     => $holiday->start_date,
                    'end'       => $holiday->end_date ?? $holiday->start_date,
                    'className' => 'event-primary',
                    'url'       => route('holiday.edit', $holiday->id),
                ];
            }
            if ($user->type == 'employee') {
                $emp = Employee::where('user_id', '=', $user->id)->first();
                $hasRequestedDocs = false;
                if ($emp) {
                    $hasRequestedDocs = EmployeeDocument::where('employee_id', $emp->id)
                        ->where('is_requested', 1)
                        ->where(function($q) {
                            $q->whereNull('document_value')->orWhere('document_value', '');
                        })
                        ->exists();
                }
                $today = (date('Y-m-d', time()));
                $announcements = Announcement::where('end_date', '>=', $today)->orderBy('announcements.id', 'desc')->take(5)->leftjoin('announcement_employees', 'announcements.id', '=', 'announcement_employees.announcement_id')->where('announcement_employees.employee_id', '=', $emp->id)->orWhere(
                    function ($q) {
                        $q->where('announcements.department_id', 0)->where('announcements.employee_id', 0);
                    }
                )->get();
                $employees = Employee::get();
                $meetings = Meeting::orderBy('meetings.id', 'desc')
                    ->take(5)
                    ->leftJoin('meeting_employees', 'meetings.id', '=', 'meeting_employees.meeting_id')
                    ->where('meetings.created_by', \Auth::user()->creatorId())
                    ->where('meeting_employees.employee_id', $emp->id)
                    ->get();
                $events    = Event::select('events.*', 'events.id as event_id', 'event_employees.*')->leftjoin('event_employees', 'events.id', '=', 'event_employees.event_id')->where('event_employees.employee_id', '=', $emp->id)->orWhere(
                    function ($q) {
                        $q->where('events.department_id', 0)->where('events.employee_id', 0);
                    }
                )->get();
                $arrEvents = [];

                foreach ($events as $event) {

                    $arr['id']              = $event['event_id'];
                    $arr['title']           = $event['title'];
                    $arr['start']           = $event['start_date'];
                    $arr['end']             = $event['end_date'];
                    $arr['className']       = $event['color'];
                    // $arr['borderColor']     = "#fff";
                    $arr['url']             = (!empty($event['event_id'])) ? route('eventsshow', $event['event_id']) : '0';
                    //  $arr['url']                = (!empty($event['event_id'])) ? route('eventsshow', $event['event_id']) : '0';

                    // $arr['textColor']       = "white";
                    $arrEvents[]            = $arr;
                }

                $allCalendarEvents = array_merge($arrEvents, $arrHolidays);

                $date               = date("Y-m-d");
                $time               = date("H:i:s");
                $employeeAttendance = AttendanceEmployee::orderBy('id', 'desc')->where('employee_id', '=', !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0)->where('date', '=', $date)->first();

                $officeTime['startTime'] = !empty(\Auth::user()->employee) && !empty(\Auth::user()->employee->company_start_time) ? \Auth::user()->employee->company_start_time : Utility::getValByName('company_start_time');
                $officeTime['endTime']   = !empty(\Auth::user()->employee) && !empty(\Auth::user()->employee->company_end_time) ? \Auth::user()->employee->company_end_time : Utility::getValByName('company_end_time');

                return view('dashboard.dashboard', compact('allCalendarEvents', 'arrHolidays', 'arrEvents', 'announcements', 'employees', 'meetings', 'employeeAttendance', 'officeTime', 'hasRequestedDocs', 'emp'));
            } else if ($user->isSuperAdminSideUser()) {
                $user                       = \Auth::user();
                $user['total_user']         = $user->countCompany();
                $user['total_paid_user']    = $user->countPaidCompany();
                $user['total_orders']       = Order::total_orders();
                $user['total_orders_price'] = Order::total_orders_price();
                $user['total_plan']         = Plan::total_plan();
                $user['most_purchese_plan'] = (!empty(Plan::most_purchese_plan()) ? Plan::most_purchese_plan()->name : '');

                $chartData = $this->getOrderChart(['duration' => 'week']);

                return view('dashboard.super_admin', compact('user', 'chartData'));
            } else {
                $events    = Event::where('created_by', '=', \Auth::user()->creatorId())->get();
                $arrEvents = [];

                foreach ($events as $event) {
                    $arr['id']    = $event['id'];
                    $arr['title'] = $event['title'];
                    $arr['start'] = $event['start_date'];
                    $arr['end']   = $event['end_date'];
                    $arr['className'] = $event['color'];
                    // $arr['borderColor']     = "#fff";
                    // $arr['textColor']       = "white";
                    $arr['url']             = route('event.edit', $event['id']);

                    $arrEvents[] = $arr;
                }

                $allCalendarEvents = array_merge($arrEvents, $arrHolidays);

                $today = (date('Y-m-d', time()));
                $announcements = Announcement::where('end_date', '>=', $today)->orderBy('announcements.id', 'desc')->take(5)->where('created_by', '=', \Auth::user()->creatorId())->get();

                $employees = User::where('type', '=', 'employee')->where('created_by', '=', \Auth::user()->creatorId())->get();
                $countEmployee = count($employees);

                $user      = User::where('type', '!=', 'employee')->where('created_by', '=', \Auth::user()->creatorId())->get();
                $countUser = count($user);
                $countTicket      = Ticket::where('created_by', '=', \Auth::user()->creatorId())->count();
                $countOpenTicket  = Ticket::where('status', '=', 'open')->where('created_by', '=', \Auth::user()->creatorId())->count();
                $countCloseTicket = Ticket::where('status', '=', 'close')->where('created_by', '=', \Auth::user()->creatorId())->count();

                $currentDate = date('Y-m-d');

                $notClockIn    = AttendanceEmployee::where('date', '=', $currentDate)->get()->pluck('employee_id');

                $notClockIns = Employee::where('created_by', '=', \Auth::user()->creatorId())
                    ->where('is_active', 1)
                    ->whereHas('user', function ($q) {
                        $q->where('is_active', 1);
                    })
                    ->whereNotIn('id', $notClockIn)
                    ->get();

                $accountBalance = AccountList::where('created_by', '=', \Auth::user()->creatorId())->sum('initial_balance');
                $activeJob   = Job::where('status', 'active')->where('created_by', '=', \Auth::user()->creatorId())->count();
                $inActiveJOb = Job::where('status', 'in_active')->where('created_by', '=', \Auth::user()->creatorId())->count();

                $totalPayee = Payees::where('created_by', '=', \Auth::user()->creatorId())->count();
                $totalPayer = Payer::where('created_by', '=', \Auth::user()->creatorId())->count();

                $meetings = Meeting::where('created_by', '=', \Auth::user()->creatorId())->limit(8)->get();

                $users = User::find(\Auth::user()->creatorId());
                $plan = $users ? Plan::find($users->plan) : null;
                $used_mb = (float)($users->storage_limit ?? 0);
                $total_limit = $users ? $users->total_storage_limit : ($plan ? (float)$plan->storage_limit : 0);
                if ($total_limit > 0) {
                    $storage_limit = min(100, round(($used_mb / $total_limit) * 100, 2));
                } else {
                    $storage_limit = 0;
                }

                // Attendance data for non-employee admin staff (hr, manager, company)
                $staffEmployee = Employee::where('user_id', \Auth::user()->id)->first();
                $employeeAttendance = $staffEmployee
                    ? AttendanceEmployee::where('employee_id', $staffEmployee->id)
                        ->where('date', date('Y-m-d'))
                        ->latest()
                        ->first()
                    : null;

                $officeTime = [
                    'startTime' => $staffEmployee && $staffEmployee->company_start_time
                        ? $staffEmployee->company_start_time
                        : Utility::getValByName('company_start_time'),
                    'endTime'   => $staffEmployee && $staffEmployee->company_end_time
                        ? $staffEmployee->company_end_time
                        : Utility::getValByName('company_end_time'),
                ];

                // Real-time Live Attendance & Break Status for all Active Company Employees
                $companyEmployees = Employee::with('department', 'designation')
                    ->where('created_by', \Auth::user()->creatorId())
                    ->where('is_active', 1)
                    ->whereHas('user', function ($q) {
                        $q->where('is_active', 1);
                    })
                    ->get();

                $companyEmployeeIds = $companyEmployees->pluck('id')->toArray();

                $todayAttendances = AttendanceEmployee::where('date', $currentDate)
                    ->whereIn('employee_id', $companyEmployeeIds)
                    ->get()
                    ->keyBy('employee_id');

                $liveStatusData = [
                    'on_break' => [],
                    'working' => [],
                    'clocked_out' => [],
                    'not_clocked_in' => [],
                ];

                foreach ($companyEmployees as $empItem) {
                    $attRecord = $todayAttendances->get($empItem->id);

                    if (!$attRecord || empty($attRecord->clock_in) || $attRecord->clock_in == '00:00:00' || $attRecord->clock_in == '00:00') {
                        $liveStatusData['not_clocked_in'][] = [
                            'employee' => $empItem,
                            'status' => 'not_clocked_in',
                            'label' => __('Not Clocked In'),
                        ];
                        continue;
                    }

                    // Check if clocked out
                    if (!empty($attRecord->clock_out) && $attRecord->clock_out != '00:00:00' && $attRecord->clock_out != '00:00') {
                        $liveStatusData['clocked_out'][] = [
                            'employee' => $empItem,
                            'attendance' => $attRecord,
                            'status' => 'clocked_out',
                            'label' => __('Clocked Out'),
                            'clock_out_time' => $attRecord->clock_out,
                        ];
                        continue;
                    }

                    // Check break status
                    $breaks = !empty($attRecord->breaks) ? json_decode($attRecord->breaks, true) : [];
                    $lastBreak = !empty($breaks) && is_array($breaks) ? end($breaks) : null;

                    if (!empty($lastBreak) && (empty($lastBreak['end']) || $lastBreak['end'] == null)) {
                        $breakTypeRaw = $lastBreak['type'] ?? 'break';
                        $breakTypeLabel = match($breakTypeRaw) {
                            'tea_break' => __('Tea Break'),
                            'lunch_break' => __('Lunch Break'),
                            default => __('Break'),
                        };

                        $liveStatusData['on_break'][] = [
                            'employee' => $empItem,
                            'attendance' => $attRecord,
                            'status' => 'on_break',
                            'break_type' => $breakTypeRaw,
                            'label' => $breakTypeLabel,
                            'start_time' => $lastBreak['start'] ?? '',
                        ];
                    } else {
                        $liveStatusData['working'][] = [
                            'employee' => $empItem,
                            'attendance' => $attRecord,
                            'status' => 'working',
                            'label' => __('Working / Clocked In'),
                            'clock_in_time' => $attRecord->clock_in,
                        ];
                    }
                }

                $allEmployeeStatuses = array_merge(
                    $liveStatusData['on_break'],
                    $liveStatusData['working'],
                    $liveStatusData['clocked_out'],
                    $liveStatusData['not_clocked_in']
                );

                $celebrationData = self::getBirthdayAndAnniversaryData(\Auth::user()->creatorId());
                $birthdayData = $celebrationData['birthdays'];
                $anniversaryData = $celebrationData['anniversaries'];
                $celebrationSummary = $celebrationData;

                return view('dashboard.dashboard', compact(
                    'allCalendarEvents', 'arrHolidays', 'arrEvents', 'announcements', 'employees',
                    'activeJob', 'inActiveJOb', 'meetings', 'countEmployee', 'countUser', 'countTicket',
                    'countOpenTicket', 'countCloseTicket', 'notClockIns', 'accountBalance', 'totalPayee',
                    'totalPayer', 'users', 'plan', 'storage_limit', 'employeeAttendance', 'officeTime',
                    'liveStatusData', 'allEmployeeStatuses', 'birthdayData', 'anniversaryData', 'celebrationSummary'
                ));
            }
        } else {
            if (!file_exists(storage_path() . "/installed")) {
                header('location:install');
                die;
            } else {
                return app(\App\Http\Controllers\Auth\AuthenticatedSessionController::class)->showLoginForm();
            }
        }
    }

    public function getOrderChart($arrParam)
    {
        $arrDuration = [];
        if ($arrParam['duration']) {
            if ($arrParam['duration'] == 'week') {
                $previous_week = strtotime("-2 week +1 day");
                for ($i = 0; $i < 14; $i++) {
                    $arrDuration[date('Y-m-d', $previous_week)] = date('d-M', $previous_week);
                    $previous_week                              = strtotime(date('Y-m-d', $previous_week) . " +1 day");
                }
            }
        }

        $arrTask          = [];
        $arrTask['label'] = [];
        $arrTask['data']  = [];
        foreach ($arrDuration as $date => $label) {

            $data               = Order::select(\DB::raw('count(*) as total'))->whereDate('created_at', '=', $date)->first();
            $arrTask['label'][] = $label;
            $arrTask['data'][]  = $data->total;
        }

        return $arrTask;
    }

    public static function getBirthdayAndAnniversaryData($creatorId)
    {
        $today = Carbon::today();
        $employees = Employee::with('department', 'designation')
            ->where('created_by', $creatorId)
            ->where('is_active', 1)
            ->whereHas('user', function ($q) {
                $q->where('is_active', 1);
            })
            ->get();

        $birthdays = [];
        $anniversaries = [];

        foreach ($employees as $emp) {
            // 1. Process DOB / Birthday
            if (!empty($emp->dob)) {
                try {
                    $dobClean = preg_replace('/[^0-9\-]/', '', $emp->dob);
                    if (strlen($dobClean) == 8) {
                        $dob = Carbon::createFromFormat('Ymd', $dobClean);
                    } else {
                        $dob = Carbon::parse($dobClean);
                    }

                    $bdayThisYear = Carbon::create($today->year, $dob->month, $dob->day);
                    if ($bdayThisYear->isPast() && !$bdayThisYear->isToday()) {
                        $nextBday = Carbon::create($today->year + 1, $dob->month, $dob->day);
                    } else {
                        $nextBday = $bdayThisYear;
                    }

                    $isToday = ($dob->month == $today->month && $dob->day == $today->day);
                    $isThisMonth = ($dob->month == $today->month);
                    $daysLeft = (int) $today->diffInDays($nextBday, false);
                    $age = $nextBday->year - $dob->year;

                    $birthdays[] = [
                        'employee' => $emp,
                        'dob' => $dob,
                        'next_date' => $nextBday,
                        'formatted_date' => $nextBday->format('d M'),
                        'full_date_label' => $dob->format('d M, Y'),
                        'is_today' => $isToday,
                        'is_this_month' => $isThisMonth,
                        'days_left' => $daysLeft,
                        'age' => $age,
                    ];
                } catch (\Exception $e) {}
            }

            // 2. Process Company DOJ / Work Anniversary
            if (!empty($emp->company_doj)) {
                try {
                    $dojClean = preg_replace('/[^0-9\-]/', '', $emp->company_doj);
                    if (strlen($dojClean) == 8) {
                        $doj = Carbon::createFromFormat('Ymd', $dojClean)->startOfDay();
                    } else {
                        $doj = Carbon::parse($dojClean)->startOfDay();
                    }

                    $annivThisYear = Carbon::create($today->year, $doj->month, $doj->day)->startOfDay();
                    if ($annivThisYear->isPast() && !$annivThisYear->isToday()) {
                        $nextAnniv = Carbon::create($today->year + 1, $doj->month, $doj->day)->startOfDay();
                    } else {
                        $nextAnniv = $annivThisYear;
                    }

                    $isJoinedToday = ($doj->toDateString() == $today->toDateString());
                    $isAnniversaryToday = ($doj->month == $today->month && $doj->day == $today->day && $doj->year < $today->year);
                    $isToday = $isJoinedToday || $isAnniversaryToday;
                    $isThisMonth = ($doj->month == $today->month);

                    if ($doj->gt($today)) {
                        $completedYears = 0;
                        $totalDays = 0;
                        $remainingDays = 0;
                    } else {
                        $completedYears = (int) $doj->diffInYears($today);
                        $totalDays = (int) $doj->diffInDays($today);
                        $remainingDays = (int) $doj->copy()->addYears($completedYears)->diffInDays($today);
                    }

                    $daysLeft = (int) $today->diffInDays($nextAnniv, false);
                    if ($daysLeft < 0) {
                        $daysLeft = 0;
                    }
                    $upcomingYears = $nextAnniv->year - $doj->year;

                    if ($completedYears >= 1) {
                        $yearStr = $completedYears . ' ' . ($completedYears == 1 ? __('Year') : __('Years'));
                        if ($remainingDays > 0) {
                            $experienceLabel = $yearStr . ', ' . $remainingDays . ' ' . ($remainingDays == 1 ? __('Day') : __('Days'));
                        } else {
                            $experienceLabel = $yearStr;
                        }
                    } else {
                        $experienceLabel = $totalDays . ' ' . ($totalDays == 1 ? __('Day') : __('Days'));
                    }

                    $anniversaries[] = [
                        'employee' => $emp,
                        'doj' => $doj,
                        'next_date' => $nextAnniv,
                        'formatted_date' => $nextAnniv->format('d M'),
                        'full_date_label' => $doj->format('d M, Y'),
                        'is_today' => $isToday,
                        'is_joined_today' => $isJoinedToday,
                        'is_anniversary_today' => $isAnniversaryToday,
                        'is_this_month' => $isThisMonth,
                        'days_left' => $daysLeft,
                        'years_completed' => $completedYears,
                        'total_days' => $totalDays,
                        'remaining_days' => $remainingDays,
                        'experience_label' => $experienceLabel,
                        'upcoming_years' => $upcomingYears,
                    ];
                } catch (\Exception $e) {}
            }
        }

        // Sort Birthdays: Today first, then remaining this month, then rest of year by days_left
        usort($birthdays, function ($a, $b) {
            if ($a['is_today'] !== $b['is_today']) {
                return $a['is_today'] ? -1 : 1;
            }
            if ($a['is_this_month'] !== $b['is_this_month']) {
                return $a['is_this_month'] ? -1 : 1;
            }
            return $a['days_left'] <=> $b['days_left'];
        });

        // Sort Anniversaries: Today first, then remaining this month, then rest of year by days_left
        usort($anniversaries, function ($a, $b) {
            if ($a['is_today'] !== $b['is_today']) {
                return $a['is_today'] ? -1 : 1;
            }
            if ($a['is_this_month'] !== $b['is_this_month']) {
                return $a['is_this_month'] ? -1 : 1;
            }
            return $a['days_left'] <=> $b['days_left'];
        });

        return [
            'birthdays' => $birthdays,
            'anniversaries' => $anniversaries,
            'today_birthdays_count' => count(array_filter($birthdays, fn($b) => $b['is_today'])),
            'today_anniversaries_count' => count(array_filter($anniversaries, fn($a) => $a['is_today'])),
            'this_month_birthdays_count' => count(array_filter($birthdays, fn($b) => $b['is_this_month'])),
            'this_month_anniversaries_count' => count(array_filter($anniversaries, fn($a) => $a['is_this_month'])),
        ];
    }
}

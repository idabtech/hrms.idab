<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Job;
use App\Models\JobCategory;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CareerApiController extends Controller
{
    /**
     * Get public career job listings filtered by keyword / branch name / category title with pagination support.
     * No raw IDs are exposed in the JSON response.
     *
     * @param Request $request
     * @param int|string $id Company ID or Company Name
     * @param string $lang Language code (default: 'en')
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCareerJobs(Request $request, $id, $lang = 'en')
    {
        try {
            $company = is_numeric($id) ? User::find($id) : User::where('company_name', $id)->first();

            if (!$company) {
                return response()->json([
                    'status' => false,
                    'message' => 'Company not found.',
                ], 404);
            }

            $companyId = $company->id;

            // Fetch jobs for company
            $sortOrder = strtolower($request->query('sort', $request->query('order', 'desc')));
            if (!in_array($sortOrder, ['asc', 'desc'])) {
                $sortOrder = 'desc';
            }

            $jobsQuery = Job::where('created_by', $companyId)
                ->where('status', 'active')
                ->with(['branches', 'categories', 'createdBy'])
                ->orderBy('created_at', $sortOrder);

            // Filter: General Search Keyword (Title, Position, Skill, Description)
            if ($request->filled('search')) {
                $search = $request->search;
                $jobsQuery->where(function ($query) use ($search) {
                    $query->where('title', 'LIKE', "%{$search}%")
                        ->orWhere('position', 'LIKE', "%{$search}%")
                        ->orWhere('skill', 'LIKE', "%{$search}%")
                        ->orWhere('description', 'LIKE', "%{$search}%");
                });
            }

            // Filter: Branch Name Keyword
            if ($request->filled('branch')) {
                $branchName = trim($request->branch);
                $jobsQuery->whereHas('branches', function ($query) use ($branchName) {
                    $query->where('name', 'LIKE', "%{$branchName}%");
                });
            }

            // Filter: Category Title Keyword
            if ($request->filled('category')) {
                $categoryTitle = trim($request->category);
                $jobsQuery->whereHas('categories', function ($query) use ($categoryTitle) {
                    $query->where('title', 'LIKE', "%{$categoryTitle}%");
                });
            }

            // Handle Pagination
            $perPageParam = $request->query('per_page', $request->query('limit'));
            if (strtolower($perPageParam) === 'all') {
                $jobsList = $jobsQuery->get();
                $totalJobs = $jobsList->count();
                $currentPage = 1;
                $lastPage = 1;
                $perPage = $totalJobs;
                $hasMorePages = false;
            } else {
                $perPage = (int) ($perPageParam ?? 5);
                if ($perPage <= 0) {
                    $perPage = 5;
                }

                $paginator = $jobsQuery->paginate($perPage);
                $jobsList = collect($paginator->items());
                $totalJobs = $paginator->total();
                $currentPage = $paginator->currentPage();
                $lastPage = $paginator->lastPage();
                $perPage = $paginator->perPage();
                $hasMorePages = $paginator->hasMorePages();
            }

            // Fetch settings for company
            $settingsRaw = DB::table('settings')->where('created_by', $companyId)->get()->pluck('value', 'name');

            $logoPath = rtrim(Utility::get_file('uploads/logo/'), '/');
            $companyLogo = isset($settingsRaw['company_logo_light']) && !empty($settingsRaw['company_logo_light'])
                ? $logoPath . '/' . ltrim($settingsRaw['company_logo_light'], '/')
                : $logoPath . '/logo-light.png';

            $companyFavicon = isset($settingsRaw['company_favicon']) && !empty($settingsRaw['company_favicon'])
                ? $logoPath . '/' . ltrim($settingsRaw['company_favicon'], '/')
                : $logoPath . '/favicon.png';

            $companyInfo = [
                'name' => !empty($company->company_name) ? $company->company_name : $company->name,
                'title_text' => isset($settingsRaw['title_text']) ? $settingsRaw['title_text'] : null,
                'email' => $company->email ?? null,
                'phone' => isset($settingsRaw['company_telephone']) && !empty($settingsRaw['company_telephone']) ? $settingsRaw['company_telephone'] : ($company->phone ?? null),
                'address' => isset($settingsRaw['company_address']) ? $settingsRaw['company_address'] : null,
                'city' => isset($settingsRaw['company_city']) ? $settingsRaw['company_city'] : null,
                'state' => isset($settingsRaw['company_state']) ? $settingsRaw['company_state'] : null,
                'zipcode' => isset($settingsRaw['company_zipcode']) ? $settingsRaw['company_zipcode'] : null,
                'country' => isset($settingsRaw['company_country']) ? $settingsRaw['company_country'] : null,
                'website' => $company->website ?? null,
                'logo' => $companyLogo,
                'favicon' => $companyFavicon,
            ];

            // Filter options (name/title only, no IDs)
            $branches = Branch::where('created_by', $companyId)
                ->pluck('name')
                ->map(function ($name) {
                    return ['name' => $name];
                })
                ->values();

            $categories = JobCategory::where('created_by', $companyId)
                ->pluck('title')
                ->map(function ($title) {
                    return ['title' => $title];
                })
                ->values();

            // Format job items (No IDs)
            $formattedJobs = $jobsList->map(function ($job) use ($lang) {
                $skills = !empty($job->skill) ? array_map('trim', explode(',', $job->skill)) : [];

                $readMoreUrl = url('/job/requirement/' . $job->code . '/' . $lang);
                $applyUrl = url('/job/apply/' . $job->code . '/' . $lang);

                return [
                    'title' => $job->title,
                    'code' => $job->code,
                    'position' => (int) $job->position,
                    'branch' => [
                        'name' => $job->branches ? $job->branches->name : null,
                    ],
                    'category' => [
                        'title' => $job->categories ? $job->categories->title : null,
                    ],
                    'skills' => $skills,
                    'description' => $job->description,
                    'requirement' => $job->requirement,
                    'start_date' => $job->start_date,
                    'end_date' => $job->end_date,
                    'status' => $job->status,
                    'read_more_url' => $readMoreUrl,
                    'apply_url' => $applyUrl,
                    'created_at' => $job->created_at ? $job->created_at->toDateTimeString() : null,
                ];
            });

            return response()->json([
                'status' => true,
                'message' => 'Career jobs retrieved successfully.',
                'pagination' => [
                    'total_jobs' => $totalJobs,
                    'per_page' => $perPage,
                    'current_page' => $currentPage,
                    'last_page' => $lastPage,
                    'has_more_pages' => $hasMorePages,
                ],
                'data' => [
                    'company' => $companyInfo,
                    'filters' => [
                        'branches' => $branches,
                        'categories' => $categories,
                    ],
                    'jobs' => $formattedJobs,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching career jobs: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all active system jobs without filters, with ASC/DESC sorting and pagination support.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllJobs(Request $request)
    {
        try {
            $lang = $request->query('lang', 'en');
            $sortOrder = strtolower($request->query('sort', $request->query('order', 'desc')));
            if (!in_array($sortOrder, ['asc', 'desc'])) {
                $sortOrder = 'desc';
            }

            $jobsQuery = Job::where('status', 'active')
                ->with(['branches', 'categories', 'createdBy'])
                ->orderBy('created_at', $sortOrder);

            // Handle Pagination
            $perPageParam = $request->query('per_page', $request->query('limit'));
            if (strtolower($perPageParam) === 'all') {
                $jobsList = $jobsQuery->get();
                $totalJobs = $jobsList->count();
                $currentPage = 1;
                $lastPage = 1;
                $perPage = $totalJobs;
                $hasMorePages = false;
            } else {
                $perPage = (int) ($perPageParam ?? 5);
                if ($perPage <= 0) {
                    $perPage = 5;
                }

                $paginator = $jobsQuery->paginate($perPage);
                $jobsList = collect($paginator->items());
                $totalJobs = $paginator->total();
                $currentPage = $paginator->currentPage();
                $lastPage = $paginator->lastPage();
                $perPage = $paginator->perPage();
                $hasMorePages = $paginator->hasMorePages();
            }

            $formattedJobs = $jobsList->map(function ($job) use ($lang) {
                $skills = !empty($job->skill) ? array_map('trim', explode(',', $job->skill)) : [];
                $jobLang = !empty($job->createdBy) && !empty($job->createdBy->lang) ? $job->createdBy->lang : $lang;

                $readMoreUrl = url('/job/requirement/' . $job->code . '/' . $jobLang);
                $applyUrl = url('/job/apply/' . $job->code . '/' . $jobLang);

                return [
                    'title' => $job->title,
                    'code' => $job->code,
                    'position' => (int) $job->position,
                    'branch' => [
                        'name' => $job->branches ? $job->branches->name : null,
                    ],
                    'category' => [
                        'title' => $job->categories ? $job->categories->title : null,
                    ],
                    'skills' => $skills,
                    'description' => $job->description,
                    'requirement' => $job->requirement,
                    'start_date' => $job->start_date,
                    'end_date' => $job->end_date,
                    'status' => $job->status,
                    'read_more_url' => $readMoreUrl,
                    'apply_url' => $applyUrl,
                    'created_at' => $job->created_at ? $job->created_at->toDateTimeString() : null,
                ];
            });

            return response()->json([
                'status' => true,
                'message' => 'All system jobs retrieved successfully.',
                'pagination' => [
                    'total_jobs' => $totalJobs,
                    'per_page' => $perPage,
                    'current_page' => $currentPage,
                    'last_page' => $lastPage,
                    'has_more_pages' => $hasMorePages,
                ],
                'jobs' => $formattedJobs,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching jobs: ' . $e->getMessage(),
            ], 500);
        }
    }
}

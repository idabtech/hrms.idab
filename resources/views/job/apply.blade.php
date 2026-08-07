<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $job->title }} - Apply</title>

    <!-- Google Fonts: Comfortaa & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@400;500;600;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}">

    <style>
        body {
            background-color: #F9FAFB;
            font-family: 'Comfortaa', cursive, 'Inter', system-ui, sans-serif;
            color: #1F2937;
        }

        .apply-header-title {
            font-family: 'Comfortaa', cursive, sans-serif;
            font-size: 32px;
            font-weight: 700;
            color: #111827;
        }

        .apply-header-title span {
            color: #441668;
        }

        .apply-header-subtitle {
            font-family: 'Comfortaa', cursive, sans-serif;
            font-size: 15px;
            color: #6B7280;
        }

        .main-form-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 32px;
            border: 1px solid #E5E7EB;
            box-shadow: 0px 4px 6px -1px rgba(0, 0, 0, 0.05), 0px 2px 4px -2px rgba(0, 0, 0, 0.05);
        }

        .section-heading {
            font-family: 'Comfortaa', cursive, sans-serif;
            font-size: 18px;
            font-weight: 700;
            color: #1F2937;
            margin-bottom: 20px;
            padding-bottom: 8px;
            border-bottom: 1px solid #F3F4F6;
        }

        .form-label-custom {
            font-family: 'Comfortaa', cursive, sans-serif;
            font-size: 14px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
        }

        .req-star {
            color: #EF4444;
            margin-left: 2px;
        }

        .form-control-custom {
            border: 1px solid #E5E7EB;
            border-radius: 8px;
            height: 44px;
            padding: 10px 14px;
            font-family: 'Comfortaa', cursive, sans-serif;
            font-size: 13px;
            color: #111827;
            background-color: #ffffff;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-control-custom:focus {
            border-color: #441668;
            box-shadow: 0 0 0 3px rgba(68, 22, 104, 0.1);
            outline: none;
        }

        textarea.form-control-custom {
            height: auto;
        }

        .btn-submit-app {
            background-color: #441668;
            color: #ffffff;
            font-family: 'Comfortaa', cursive, sans-serif;
            font-size: 16px;
            font-weight: 600;
            padding: 11px 28px;
            border-radius: 8px;
            border: none;
            box-shadow: 0px 4px 6px -4px rgba(68, 22, 104, 0.2), 0px 10px 15px -3px rgba(68, 22, 104, 0.2);
            transition: background-color 0.2s ease, transform 0.1s ease;
        }

        .btn-submit-app:hover {
            background-color: #5E2590;
            color: #ffffff;
            transform: translateY(-1px);
        }

        .btn-cancel-app {
            border: 1px solid #E5E7EB;
            color: #4B5563;
            font-family: 'Comfortaa', cursive, sans-serif;
            font-size: 16px;
            font-weight: 500;
            padding: 11px 24px;
            border-radius: 8px;
            background: #ffffff;
            text-decoration: none;
            transition: background 0.2s ease;
        }

        .btn-cancel-app:hover {
            background: #F3F4F6;
            color: #1F2937;
        }

        /* Sidebar Styling */
        .sidebar-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid #E5E7EB;
            box-shadow: 0px 4px 6px -1px rgba(0, 0, 0, 0.05), 0px 2px 4px -2px rgba(0, 0, 0, 0.05);
        }

        .sidebar-title {
            font-family: 'Comfortaa', cursive, sans-serif;
            font-size: 16px;
            font-weight: 700;
            color: #441668;
        }

        .job-pill-badge {
            background: #F0EBFF;
            color: #441668;
            font-family: 'Comfortaa', cursive, sans-serif;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 6px;
        }
    </style>
</head>

<body>

<div class="container py-5">
    <!-- Header Section -->
    <div class="mb-4">
        <h1 class="apply-header-title">Apply for this <span>Position</span></h1>
        <p class="apply-header-subtitle mb-0">Fill in the details below to apply for the position. We're excited to learn more about you!</p>
    </div>

    @if ($message = Session::get('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4 rounded-3 shadow-sm border-0 d-flex align-items-center" role="alert" style="background-color: #ECFDF5; color: #065F46; border-left: 4px solid #10B981 !important; padding: 16px 20px;">
            <i class="ti ti-circle-check fs-4 me-3" style="color: #10B981;"></i>
            <div class="flex-grow-1">
                <strong class="d-block mb-1" style="font-size: 15px;">{{ __('Application Submitted!') }}</strong>
                <span style="font-size: 14px;">{!! $message !!}</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($message = Session::get('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4 rounded-3 shadow-sm border-0 d-flex align-items-center" role="alert" style="background-color: #FEF2F2; color: #991B1B; border-left: 4px solid #EF4444 !important; padding: 16px 20px;">
            <i class="ti ti-alert-circle fs-4 me-3" style="color: #EF4444;"></i>
            <div class="flex-grow-1">
                <strong class="d-block mb-1" style="font-size: 15px;">{{ __('Submission Failed!') }}</strong>
                <span style="font-size: 14px;">{!! $message !!}</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Main Form Column (Left) -->
        <div class="col-lg-8">
            <div class="main-form-card">
                {{ Form::open(['route' => ['job.apply.data', $job->code], 'method' => 'post', 'enctype' => 'multipart/form-data']) }}

                <!-- Personal Information -->
                <div class="section-heading">Personal Information</div>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label-custom">Full Name <span class="req-star">*</span></label>
                        <input type="text" name="name" class="form-control form-control-custom" placeholder="Enter your full name" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-custom">Email Address <span class="req-star">*</span></label>
                        <input type="email" name="email" class="form-control form-control-custom" placeholder="Enter your email address" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-custom">Phone Number <span class="req-star">*</span></label>
                        <input type="text" name="phone" class="form-control form-control-custom" placeholder="Enter your phone number" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-custom">Date of Birth</label>
                        <input type="date" name="dob" class="form-control form-control-custom">
                    </div>
                </div>

                <!-- Custom Questions (If any) -->
                @if(isset($questions) && count($questions) > 0)
                    <div class="section-heading mt-4">Additional Questions</div>
                    <div class="row g-3 mb-4">
                        @foreach($questions as $question)
                            <div class="col-md-12">
                                <label class="form-label-custom">{{ $question->question }} @if($question->is_required == 'yes') <span class="req-star">*</span> @endif</label>
                                <input type="text" name="question[{{ $question->id }}]" class="form-control form-control-custom" placeholder="Enter your answer" @if($question->is_required == 'yes') required @endif>
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Documents -->
                <div class="section-heading mt-4">Documents</div>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label-custom">Profile Image <span class="req-star">*</span></label>
                        <input type="file" name="profile" class="form-control form-control-custom" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-custom">Resume / CV <span class="req-star">*</span></label>
                        <input type="file" name="resume" class="form-control form-control-custom" accept=".pdf,.doc,.docx" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label-custom">Cover Letter (Optional)</label>
                        <textarea name="cover_letter" class="form-control form-control-custom" rows="4" placeholder="Tell us why you want to be a part of our team..."></textarea>
                    </div>
                </div>

                <!-- Confirmation & Submit -->
                <div class="pt-3 border-top d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="confirm_info" required>
                        <label class="form-check-label fs-7 text-muted ms-1" for="confirm_info">
                            I confirm that the information provided is true and correct.
                        </label>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <a href="{{ route('job.requirement', [$job->code, !empty($job->createdBy->lang) ? $job->createdBy->lang : 'en']) }}" class="btn-cancel-app">Cancel</a>
                        <button type="submit" class="btn-submit-app">Submit Application</button>
                    </div>
                </div>

                {{ Form::close() }}
            </div>
        </div>

        <!-- Position Details Sidebar (Right) -->
        <div class="col-lg-4">
            <div class="sidebar-card">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <i class="ti ti-briefcase text-purple-brand fs-5" style="color: #441668;"></i>
                    <h5 class="sidebar-title mb-0">{{ __('Position Details') }}</h5>
                </div>

                <h4 class="fw-bold mb-3" style="font-size: 20px; color: #111827;">{{ $job->title }}</h4>

                <div class="d-flex flex-wrap gap-2 mb-3">
                    @if(!empty($job->categories))
                        <span class="job-pill-badge">{{ $job->categories->title }}</span>
                    @endif
                    @if(!empty($job->job_type))
                        <span class="job-pill-badge">{{ ucfirst($job->job_type) }}</span>
                    @endif
                </div>

                <div class="py-3 border-bottom mb-3 text-muted fs-7">
                    @if(!empty($job->branches))
                        <div class="mb-2"><i class="ti ti-map-pin me-2" style="color: #9CA3AF;"></i>{{ $job->branches->name }}</div>
                    @endif
                    @if(!empty($job->job_type))
                        <div class="mb-2"><i class="ti ti-clock me-2" style="color: #9CA3AF;"></i>{{ ucfirst($job->job_type) }}</div>
                    @else
                        <div class="mb-2"><i class="ti ti-clock me-2" style="color: #9CA3AF;"></i>{{ __('Full Time') }}</div>
                    @endif
                    @if(!empty($job->position))
                        <div><i class="ti ti-users me-2" style="color: #9CA3AF;"></i>{{ $job->position }} {{ __('Positions Available') }}</div>
                    @endif
                </div>

                @if(!empty($job->description))
                    <div class="mb-4">
                        <h6 class="fw-bold text-dark mb-2" style="font-size: 15px;">{{ __('Job Description') }}</h6>
                        <div class="text-muted fs-7 lh-base mb-0">{!! Str::limit(strip_tags($job->description), 250) !!}</div>
                    </div>
                @endif

                @if(!empty($job->requirement))
                    <div class="mb-4">
                        <h6 class="fw-bold text-dark mb-2" style="font-size: 15px;">{{ __('Requirements') }}</h6>
                        <div class="text-muted fs-7 lh-base mb-0">{!! Str::limit(strip_tags($job->requirement), 250) !!}</div>
                    </div>
                @elseif(!empty($job->skill))
                    <div class="mb-4">
                        <h6 class="fw-bold text-dark mb-2" style="font-size: 15px;">{{ __('Skills Required') }}</h6>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach(explode(',', $job->skill) as $skill)
                                <span class="job-pill-badge">{{ trim($skill) }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="pt-3 border-top">
                    <h6 class="fw-bold text-dark mb-1" style="font-size: 15px;">{{ __('Need help?') }}</h6>
                    <p class="text-muted fs-7 mb-2">{{ __('If you face any issues while applying, feel free to contact our HR team.') }}</p>
                    @php
                        $hrEmail = !empty($job->createdBy) && !empty($job->createdBy->email) ? $job->createdBy->email : 'hr@idabtech.com';
                    @endphp
                    <a href="mailto:{{ $hrEmail }}" class="text-decoration-none fw-bold" style="color: #441668; font-size: 14px;">
                        <i class="ti ti-mail me-1"></i> {{ $hrEmail }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/bootstrap-notify.min.js') }}"></script>
<script src="{{ asset('js/custom.js') }}"></script>

@if ($message = Session::get('success'))
    <script>
        if (typeof show_toastr === 'function') {
            show_toastr('Success', '{!! addslashes($message) !!}', 'success');
        }
    </script>
@endif
@if ($message = Session::get('error'))
    <script>
        if (typeof show_toastr === 'function') {
            show_toastr('Error', '{!! addslashes($message) !!}', 'error');
        }
    </script>
@endif

</body>
</html>
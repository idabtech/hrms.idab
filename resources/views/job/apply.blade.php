<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $job->title }} - Apply</title>

    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}">

    <style>
        body {
            background: #f5f7fb;
        }

        /* Clean Banner (No Image) */
        .job-banner {
            background: linear-gradient(135deg, #d0d4dd00, #e0eaf9);
            color: #fff;
            padding: 60px 20px;
            text-align: center;
        }

        .job-banner h2 {
            font-weight: 700;
        }

        .apply-card {
            margin-top: -40px;
            border-radius: 12px;
        }

        .form-control {
            border-radius: 8px;
            padding: 10px;
        }

        .form-label {
            font-weight: 600;
        }

        .btn-primary {
            padding: 10px 30px;
            border-radius: 8px;
        }
    </style>
</head>

<body>

<!--  Form -->
<div class="container">
    <h2 class="text-center mt-4">{{ $job->title }}</h2>

    <div class="mt-2 text-center mb-3">
        @foreach (explode(',', $job->skill) as $skill)
            <span class="badge bg-light text-dark">{{ $skill }}</span>
        @endforeach
    </div>

    @if (!empty($job->branches))
        <p class="mt-2 text-dark text-center">
            <i class="ti ti-map-pin text-dark"></i><label class="text-dark"> {{ $job->branches->name }} </label> 
        </p>
    @endif

    <div class="card shadow-lg border-0 apply-card p-4">

        <div class="text-center mb-4">
            <h3 class="fw-bold">Apply for this job</h3>
            <p class="text-muted">Fill the form and submit your application</p>
        </div>

        {{ Form::open(['route' => ['job.apply.data', $job->code], 'method' => 'post', 'enctype' => 'multipart/form-data']) }}

        <div class="row">

            <div class="col-md-6 mb-3">
                <label class="form-label">Name *</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Email *</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Phone *</label>
                <input type="text" name="phone" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Date of Birth</label>
                <input type="date" name="dob" class="form-control">
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Profile Image *</label>
                <input type="file" name="profile" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Resume *</label>
                <input type="file" name="resume" class="form-control" required>
            </div>

            <div class="col-md-12 mb-3">
                <label class="form-label">Cover Letter</label>
                <textarea name="cover_letter" class="form-control" rows="3"></textarea>
            </div>

            <div class="col-md-12 text-center mt-3">
                <button type="submit" class="btn btn-primary">
                    Submit Application
                </button>
            </div>

        </div>

        {{ Form::close() }}

    </div>
</div>

<script src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>

</body>
</html>
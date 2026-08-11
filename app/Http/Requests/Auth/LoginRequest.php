<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */

    // public function authenticate()
    // {
    //     $this->ensureIsNotRateLimited();

    //     if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
    //         RateLimiter::hit($this->throttleKey());

    //         throw ValidationException::withMessages([
    //             'email' => trans('auth.failed'),
    //         ]);
    //     }

    //     RateLimiter::clear($this->throttleKey());
    // }

    public function authenticate()
    {
        $superAdminUrl = config('app.super_admin_url') ?? env('SUPER_ADMIN_URL', 'https://admin.hrms.idabtech.com');
        $companyUrl    = config('app.company_url') ?? env('COMPANY_URL', 'https://hrms.idabtech.com');

        $parseHost = function ($url) {
            if (empty($url)) return '';
            $clean = preg_replace('#^https?://#i', '', trim($url));
            $clean = explode('/', $clean)[0];
            return strtolower(explode(':', $clean)[0]);
        };

        $adminHost   = $parseHost($superAdminUrl);
        $companyHost = $parseHost($companyUrl);

        $candidates = array_filter([
            $this->getHost(),
            $this->header('host'),
            $this->header('x-forwarded-host'),
            $_SERVER['HTTP_HOST'] ?? '',
            $_SERVER['HTTP_X_FORWARDED_HOST'] ?? '',
            $_SERVER['SERVER_NAME'] ?? '',
        ]);

        $currentHosts = array_map(function ($h) {
            $clean = preg_replace('#^https?://#i', '', trim($h));
            $clean = explode('/', $clean)[0];
            return strtolower(explode(':', $clean)[0]);
        }, $candidates);

        $isLocal = false;
        foreach ($currentHosts as $h) {
            if (in_array($h, ['127.0.0.1', 'localhost'])) {
                $isLocal = true;
                break;
            }
        }

        $isOnAdminDomain = false;
        if (!empty($adminHost)) {
            foreach ($currentHosts as $h) {
                if ($h === $adminHost) {
                    $isOnAdminDomain = true;
                    break;
                }
            }
        }

        // custom login
        $users = User::where('email', $this->email)->get();
        $id = 0;

        if (count($users) > 0) {
            foreach ($users as $key => $user) {
                if (password_verify($this->password, $user->password)) {

                    // Domain restriction enforcement when password matches
                    if (!empty($adminHost) && (!$isLocal || $isOnAdminDomain)) {
                        if ($user->type === 'super admin' && !$isOnAdminDomain) {
                            session()->flash('error', __('Access Denied: Super Admin can only log in through the Admin Portal: ') . $superAdminUrl);
                            throw ValidationException::withMessages([]);
                        }

                        if ($user->type !== 'super admin' && $isOnAdminDomain) {
                            session()->flash('error', __('Access Denied: Company and Employee users must log in through the Company Portal: ') . $companyUrl);
                            throw ValidationException::withMessages([]);
                        }
                    }

                    if ($user->is_active != 1 || ($user->is_disable != 1 && $user->type != "super admin")) {
                        throw ValidationException::withMessages([
                            'email' => __("Your Account is disable, please contact your Administrate."),
                        ]);
                    } elseif ($user->is_login_enable != 1) {
                        throw ValidationException::withMessages([
                            'email' => __("Your account is disabled from company."),
                        ]);
                    }
                    $id = $user->id;
                    break;
                }
            }
        } else {
            throw ValidationException::withMessages([
                'email' => __("this email doesn't match"),
            ]);
        }

        if (! Auth::attempt(['email' =>$this->email, 'password' =>$this->password,'id'=>$id], $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('These credentials do not match our records.'),
            ]);
        }
        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited()
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     *
     * @return string
     */
    public function throttleKey()
    {
        return Str::lower($this->input('email')).'|'.$this->ip();
    }
}

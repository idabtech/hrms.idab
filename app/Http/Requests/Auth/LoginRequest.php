<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Models\Utility;
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
        $superAdminUrl   = Utility::getSuperAdminUrl();
        $companyUrl      = Utility::getCompanyUrl();
        $isOnAdminDomain = Utility::isSuperAdminDomain();

        $users = User::where('email', $this->email)->get();
        if ($users->isEmpty()) {
            throw ValidationException::withMessages([
                'email' => __("This email doesn't match our records."),
            ]);
        }

        $matchedUser = null;
        foreach ($users as $user) {
            if (\Illuminate\Support\Facades\Hash::check($this->password, $user->password) || password_verify($this->password, $user->password)) {
                $matchedUser = $user;
                break;
            }
        }

        if (!$matchedUser) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'email' => __('These credentials do not match our records.'),
            ]);
        }

        // Domain restriction enforcement
        if ($matchedUser->type === 'super admin' && !$isOnAdminDomain) {
            $msg = __('Access Denied: Super Admin can only log in through the Admin Portal: ') . $superAdminUrl;
            session()->flash('error', $msg);
            throw ValidationException::withMessages([
                'email' => $msg,
            ]);
        }

        if ($matchedUser->type !== 'super admin' && $isOnAdminDomain) {
            $msg = __('Access Denied: Company and Employee users must log in through the Company Portal: ') . $companyUrl;
            session()->flash('error', $msg);
            throw ValidationException::withMessages([
                'email' => $msg,
            ]);
        }

        if ($matchedUser->is_active != 1 || ($matchedUser->is_disable != 1 && $matchedUser->type != "super admin")) {
            throw ValidationException::withMessages([
                'email' => __("Your Account is disable, please contact your Administrate."),
            ]);
        } elseif ($matchedUser->is_login_enable != 1) {
            throw ValidationException::withMessages([
                'email' => __("Your account is disabled from company."),
            ]);
        }

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password, 'id' => $matchedUser->id], $this->boolean('remember'))) {
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

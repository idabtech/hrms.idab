<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckDomainRestriction
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $superAdminUrl = env('SUPER_ADMIN_URL', config('app.super_admin_url', 'https://admin.hrms.idabtech.com'));
        $companyUrl    = env('COMPANY_URL', config('app.company_url', 'https://hrms.idabtech.com'));

        if (!empty($superAdminUrl)) {
            $adminHost = parse_url($superAdminUrl, PHP_URL_HOST) ?? $superAdminUrl;
            $adminHost = strtolower(explode(':', str_replace(['http://', 'https://'], '', $adminHost))[0]);

            $currentHost = strtolower($request->getHost());

            // Bypass on local environment unless running on the target domain
            $isLocal = in_array($currentHost, ['127.0.0.1', 'localhost']);

            if (!$isLocal || $currentHost === $adminHost) {
                $isOnAdminDomain = ($currentHost === $adminHost);

                if (Auth::check()) {
                    $user = Auth::user();

                    // Super Admin accessing non-admin domain
                    if ($user->type === 'super admin' && !$isOnAdminDomain) {
                        Auth::logout();
                        $request->session()->invalidate();
                        $request->session()->regenerateToken();

                        return redirect()->route('login')->with('error', __('Access Denied: Super Admin can only log in through the Admin Portal: ') . $superAdminUrl);
                    }

                    // Non-Super Admin accessing Admin domain
                    if ($user->type !== 'super admin' && $isOnAdminDomain) {
                        Auth::logout();
                        $request->session()->invalidate();
                        $request->session()->regenerateToken();

                        return redirect()->route('login')->with('error', __('Access Denied: Company and Employee users must log in through the Company Portal: ') . $companyUrl);
                    }
                }
            }
        }

        return $next($request);
    }
}

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

        if (!empty($adminHost)) {
            $candidates = array_filter([
                $request->getHost(),
                $request->header('host'),
                $request->header('x-forwarded-host'),
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
            foreach ($currentHosts as $h) {
                if ($h === $adminHost) {
                    $isOnAdminDomain = true;
                    break;
                }
            }

            if (!$isLocal || $isOnAdminDomain) {
                if (Auth::check()) {
                    $user = Auth::user();

                    if ($user->type === 'super admin' && !$isOnAdminDomain) {
                        Auth::logout();
                        $request->session()->invalidate();
                        $request->session()->regenerateToken();

                        return redirect()->route('login')->with('error', __('Access Denied: Super Admin can only log in through the Admin Portal: ') . $superAdminUrl);
                    }

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

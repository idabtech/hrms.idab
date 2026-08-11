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

        $parseHost = function ($url) {
            if (empty($url)) return '';
            $clean = preg_replace('#^https?://#i', '', trim($url));
            $clean = explode('/', $clean)[0];
            return strtolower(explode(':', $clean)[0]);
        };

        $adminHost   = $parseHost($superAdminUrl);
        $companyHost = $parseHost($companyUrl);

        if (!empty($adminHost)) {
            $currentHost = strtolower(explode(':', trim($request->getHost()))[0]);
            $httpHost    = isset($_SERVER['HTTP_HOST']) ? strtolower(explode(':', trim($_SERVER['HTTP_HOST']))[0]) : $currentHost;

            $isLocal = in_array($currentHost, ['127.0.0.1', 'localhost']) || in_array($httpHost, ['127.0.0.1', 'localhost']);

            if (!$isLocal || $currentHost === $adminHost || $httpHost === $adminHost) {
                $isOnAdminDomain = ($currentHost === $adminHost || $httpHost === $adminHost);

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

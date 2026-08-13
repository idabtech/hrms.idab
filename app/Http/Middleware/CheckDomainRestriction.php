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
        $superAdminUrl   = \App\Models\Utility::getSuperAdminUrl();
        $companyUrl      = \App\Models\Utility::getCompanyUrl();
        $isOnAdminDomain = \App\Models\Utility::isSuperAdminDomain();

        if (Auth::check()) {
            $user = Auth::user();

            if ($user->isSuperAdminSideUser() && !$isOnAdminDomain) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('error', __('Access Denied: Super Admin and Super Admin Staff can only log in through the Admin Portal: ') . $superAdminUrl);
            }

            if (!$user->isSuperAdminSideUser() && $isOnAdminDomain) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('error', __('Access Denied: Company and Employee users must log in through the Company Portal: ') . $companyUrl);
            }
        }

        return $next($request);
    }
}

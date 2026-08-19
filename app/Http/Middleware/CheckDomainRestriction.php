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

                return redirect()->route('login')->withErrors(['email' => __('User not found.')]);
            }

            if (!$user->isSuperAdminSideUser() && $isOnAdminDomain) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors(['email' => __('User not found.')]);
            }
        }

        return $next($request);
    }
}

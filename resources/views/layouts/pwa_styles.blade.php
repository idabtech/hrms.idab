@if(!Auth::check() || Auth::user()->type == 'super admin')
@php
    $iconPath = \Illuminate\Support\Facades\DB::table('settings')->where('name', 'pwa_icon')->value('value');
    $iconUrl = $iconPath ? asset(app()->environment('local') ? "storage/{$iconPath}" : "public/storage/{$iconPath}") : asset(app()->environment('local') ? 'logo.png' : 'public/logo.png');
@endphp

<style>
    #install-prompt {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 9999;
        display: flex;
        align-items: center;
        gap: 10px;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 10px 16px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        transition: all 0.3s ease-in-out;
    }
    #install-prompt-close {
        position: absolute;
        top: -8px;
        right: -8px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #ef4444;
        color: #fff;
        border: 2px solid #fff;
        font-size: 12px;
        line-height: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        transition: background-color 0.2s ease;
    }
    #install-prompt-close:hover { background: #dc2626; }
    #install-button {
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 48px;
        width: 48px;
        border-radius: 50%;
        background-color: #4f46e5;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        transition: background-color 0.3s ease;
    }
    #install-button:hover { background-color: #4338ca; }
    #install-button img { height: 28px; width: 28px; filter: brightness(0) invert(1); }
    #install-text { font-size: 14px; font-weight: 500; color: #111827; }
    @media (max-width: 640px) {
        #install-prompt { bottom: 10px; right: 10px; padding: 8px 12px; }
    }
</style>

<div id="install-prompt" class="box-icon" style="display: none;">
    <span id="install-prompt-close">&times;</span>
    <span id="install-button" class="circle">
        <img src="{{ $iconUrl }}" alt="Install App">
    </span>
</div>

<script src="{{ app()->environment('local') ? asset('sw.js') : asset('public/sw.js') }}"></script>
<script>
    "use strict";
    // Set a cookie when running as an installed PWA (standalone mode)
    if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true) {
        document.cookie = "pwa_mode=1; path=/; SameSite=Lax";
    } else {
        document.cookie = "pwa_mode=0; path=/; SameSite=Lax";
    }
    if ("serviceWorker" in navigator) {
        navigator.serviceWorker.register("{{ app()->environment('local') ? asset('sw.js') : asset('public/sw.js') }}")
            .catch(error => console.error("Service worker registration failed:", error));
    }
    let deferredPrompt;
    const installPrompt = document.getElementById("install-prompt");
    const installButton = document.getElementById("install-button");
    const installClose = document.getElementById("install-prompt-close");

    // Check if user dismissed in this session
    function isPwaDismissed() {
        return sessionStorage.getItem("pwa_install_dismissed") === "1";
    }

    window.addEventListener("load", () => {
        if (window.matchMedia("(display-mode: standalone)").matches) {
            installPrompt.style.display = "none";
        }
    });
    window.addEventListener("beforeinstallprompt", (e) => {
        e.preventDefault();
        deferredPrompt = e;
        // Only show if not dismissed in this session
        if (!isPwaDismissed()) {
            installPrompt.style.display = "flex";
        }
        installButton.addEventListener("click", () => {
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then(() => { deferredPrompt = null; });
        });
    });
    window.addEventListener("appinstalled", () => {
        installPrompt.style.display = "none";
    });
    // Close button - dismiss for this session
    installClose.addEventListener("click", () => {
        installPrompt.style.display = "none";
        sessionStorage.setItem("pwa_install_dismissed", "1");
    });
</script>
@endif

<x-guest-layout>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-slate-50 dark:bg-slate-900 px-4">
        <div class="w-full sm:max-w-md bg-white dark:bg-slate-800 shadow-xl rounded-2xl p-8 border border-slate-200/80 dark:border-slate-700/60">
            <!-- App Branding -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-sky-600 to-indigo-600 text-white shadow-lg shadow-sky-500/30 mb-4">
                    <svg class="w-9 h-9" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">SABIRA POSKESTREN</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Sistem Rekam Medis & Kesehatan Pesantren</p>
            </div>

            <!-- Flash Notifications -->
            @if (session('error'))
                <div class="mb-6 p-4 rounded-xl bg-rose-50 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-sm flex items-start gap-3">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>{{ session('error') }}</div>
                </div>
            @endif

            @if (session('info'))
                <div class="mb-6 p-4 rounded-xl bg-sky-50 dark:bg-sky-900/30 border border-sky-200 dark:border-sky-800 text-sky-700 dark:text-sky-300 text-sm flex items-start gap-3">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5 text-sky-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>{{ session('info') }}</div>
                </div>
            @endif

            <!-- Gate SSO Action -->
            <div class="space-y-4">
                <a href="{{ route('login', ['redirect' => 1]) }}"
                   class="w-full flex items-center justify-center gap-3 px-6 py-3.5 rounded-xl bg-sky-600 hover:bg-sky-500 active:bg-sky-700 text-white font-semibold shadow-lg shadow-sky-600/30 transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 dark:focus:ring-offset-slate-800">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                    <span>Masuk dengan SABIRA Gate SSO</span>
                </a>

                <div class="relative flex py-2 items-center">
                    <div class="flex-grow border-t border-slate-200 dark:border-slate-700"></div>
                    <span class="flex-shrink mx-4 text-xs uppercase font-medium text-slate-400">Pusat Autentikasi Terpadu</span>
                    <div class="flex-grow border-t border-slate-200 dark:border-slate-700"></div>
                </div>

                <div class="p-3.5 rounded-xl bg-slate-100 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 text-xs text-slate-500 dark:text-slate-400 space-y-1">
                    <div class="flex items-center justify-between font-medium">
                        <span>Layanan Gate SSO:</span>
                        <span class="inline-flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 font-semibold">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            Terhubung
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-400 dark:text-slate-500 pt-1">
                        Hak akses dan perizinan dikelola terpusat melalui SABIRA Gate Identity Provider.
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-8 text-center text-xs text-slate-400 dark:text-slate-500">
                &copy; {{ date('Y') }} POSKESTREN SABIRA. Seluruh Hak Cipta Dilindungi.
            </div>
        </div>
    </div>
</x-guest-layout>

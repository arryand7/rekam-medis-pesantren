<x-guest-layout>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-slate-50 dark:bg-slate-900 px-4">
        <div class="w-full sm:max-w-md bg-white dark:bg-slate-800 shadow-xl rounded-2xl p-8 border border-slate-200/80 dark:border-slate-700/60 text-center">
            <!-- Warning Icon -->
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800 mb-6">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>

            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Akses Tidak Diizinkan</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">
                Akun Gate Anda berhasil terverifikasi, namun Anda belum memiliki izin hak akses (<em>Application Entitlement</em>) untuk aplikasi <strong>POSKESTREN Health</strong>.
            </p>

            <div class="my-6 p-4 rounded-xl bg-slate-100 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 text-left text-xs text-slate-600 dark:text-slate-400 space-y-2">
                <div class="font-semibold text-slate-700 dark:text-slate-300">Status Hak Akses:</div>
                <div class="flex items-center justify-between">
                    <span>Aplikasi:</span>
                    <span class="font-mono text-slate-900 dark:text-slate-100">poskestren-health</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>Status:</span>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-100 dark:bg-rose-900/40 text-rose-700 dark:text-rose-300">
                        {{ strtoupper(session('entitlement_status', 'NOT_ASSIGNED')) }}
                    </span>
                </div>
            </div>

            <div class="space-y-3">
                <a href="{{ route('login') }}"
                   class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-medium shadow-md shadow-sky-600/20 transition duration-150">
                    <span>Kembali ke Halaman Login</span>
                </a>

                <p class="text-xs text-slate-400 dark:text-slate-500">
                    Hubungi Administrator IT Pesantren jika Anda memerlukan akses ke sistem rekam medis.
                </p>
            </div>
        </div>
    </div>
</x-guest-layout>

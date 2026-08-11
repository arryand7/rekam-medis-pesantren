<x-guest-layout>
    <div class="min-h-screen flex flex-col sm:justify-center items-center py-10 sm:py-12 bg-slate-50 dark:bg-slate-900 px-4 sm:px-6">
        <div class="w-full sm:max-w-md bg-white dark:bg-slate-800 shadow-2xl rounded-2xl p-8 border border-slate-200/80 dark:border-slate-700/60 backdrop-blur-sm">
            <!-- App Branding Header -->
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-sky-600 to-indigo-600 text-white shadow-lg shadow-sky-500/30 mb-3">
                    <svg class="w-9 h-9" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">SABIRA POSKESTREN</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Sistem Rekam Medis & Kesehatan Pesantren</p>
            </div>

            <!-- Flash Notifications -->
            @if (session('error'))
                <div class="mb-5 p-4 rounded-xl bg-rose-50 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-sm flex items-start gap-3">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>{{ session('error') }}</div>
                </div>
            @endif

            @if (session('info'))
                <div class="mb-5 p-4 rounded-xl bg-sky-50 dark:bg-sky-900/30 border border-sky-200 dark:border-sky-800 text-sky-700 dark:text-sky-300 text-sm flex items-start gap-3">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5 text-sky-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>{{ session('info') }}</div>
                </div>
            @endif

            @if (session('success'))
                <div class="mb-5 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-sm flex items-start gap-3">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            <!-- Direct Login Form -->
            <form method="POST" action="{{ route('login.attempt') }}" class="space-y-4" id="loginForm">
                @csrf

                <!-- Login / Username / Email Field -->
                <div>
                    <label for="login" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                        Email / Username / NIS / NIP
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <input type="text"
                               name="login"
                               id="login"
                               value="{{ old('login') }}"
                               required
                               autofocus
                               autocomplete="username"
                               placeholder="nama@sabira.id atau username"
                               class="block w-full pl-10 pr-3.5 py-2.5 bg-slate-50 dark:bg-slate-900/70 border @error('login') border-rose-500 focus:ring-rose-500 focus:border-rose-500 @else border-slate-300 dark:border-slate-700 focus:ring-sky-500 focus:border-sky-500 @enderror rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 transition-colors duration-150 focus:outline-none focus:ring-2">
                    </div>
                    @error('login')
                        <p class="mt-1.5 text-xs text-rose-600 dark:text-rose-400 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Field -->
                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                        Kata Sandi
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input type="password"
                               name="password"
                               id="password"
                               required
                               autocomplete="current-password"
                               placeholder="••••••••"
                               class="block w-full pl-10 pr-10 py-2.5 bg-slate-50 dark:bg-slate-900/70 border @error('password') border-rose-500 focus:ring-rose-500 focus:border-rose-500 @else border-slate-300 dark:border-slate-700 focus:ring-sky-500 focus:border-sky-500 @enderror rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 transition-colors duration-150 focus:outline-none focus:ring-2">
                        <button type="button"
                                onclick="togglePasswordVisibility()"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 focus:outline-none"
                                title="Tampilkan/Sembunyikan kata sandi">
                            <svg id="eyeIcon" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-rose-600 dark:text-rose-400 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me & Forgot Password Note -->
                <div class="flex items-center justify-between pt-1">
                    <label class="inline-flex items-center">
                        <input type="checkbox"
                               name="remember"
                               class="rounded border-slate-300 dark:border-slate-700 text-sky-600 shadow-sm focus:ring-sky-500 dark:bg-slate-900">
                        <span class="ml-2 text-xs text-slate-600 dark:text-slate-400">Ingat saya</span>
                    </label>
                    <span class="text-xs text-slate-400 dark:text-slate-500">
                        Akun aktif terdaftar
                    </span>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                        class="w-full flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-500 hover:to-indigo-500 active:from-sky-700 active:to-indigo-700 text-white font-semibold shadow-md shadow-sky-600/25 transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 dark:focus:ring-offset-slate-800">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                    <span>Masuk ke Aplikasi</span>
                </button>
            </form>

            <!-- Divider -->
            <div class="relative flex py-4 items-center my-1">
                <div class="flex-grow border-t border-slate-200 dark:border-slate-700"></div>
                <span class="flex-shrink mx-3 text-xs uppercase font-medium text-slate-400">atau</span>
                <div class="flex-grow border-t border-slate-200 dark:border-slate-700"></div>
            </div>

            <!-- Gate SSO Action -->
            <div class="space-y-3">
                <a href="{{ route('login', ['redirect' => 1]) }}"
                   class="w-full flex items-center justify-center gap-3 px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700/60 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-100 font-medium border border-slate-300/80 dark:border-slate-600 transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 dark:focus:ring-offset-slate-800 text-sm shadow-sm">
                    <svg class="w-4 h-4 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <span>Masuk dengan SABIRA Gate SSO</span>
                </a>

                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-800 text-xs text-slate-500 dark:text-slate-400 space-y-1">
                    <div class="flex items-center justify-between font-medium">
                        <span>Pusat Autentikasi:</span>
                        <span class="inline-flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 font-semibold text-[11px]">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            SSO & Langsung Aktif
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-400 dark:text-slate-500">
                        Anda dapat masuk menggunakan akun lokal POSKESTREN atau akun terpusat SABIRA Gate.
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-6 text-center text-[11px] text-slate-400 dark:text-slate-500">
                &copy; {{ date('Y') }} POSKESTREN SABIRA. Seluruh Hak Cipta Dilindungi.
            </div>
        </div>
    </div>

    <!-- Password Visibility Toggle Script -->
    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                `;
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                `;
            }
        }
    </script>
</x-guest-layout>

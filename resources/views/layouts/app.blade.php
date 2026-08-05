<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'SABIRA POSKESTREN Health') }}</title>

    <!-- Inline Anti-Flicker Theme Script (Runs BEFORE First Paint) -->
    <script>
        (function() {
            const key = 'sabira_theme_preference';
            const stored = localStorage.getItem(key) || 'system';
            const isDark = stored === 'dark' || (stored === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            if (isDark) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
            document.documentElement.setAttribute('data-theme', stored);
        })();
    </script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js script fallback if not bundled -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @livewireStyles
</head>
<body class="h-full antialiased font-sans flex flex-col min-h-screen bg-[var(--background)] text-[var(--foreground)]" x-data="{ sidebarOpen: false }">

    <!-- Top Navigation Bar -->
    <header class="sticky top-0 z-40 bg-[var(--surface)] border-b border-[var(--border)] shadow-xs no-print">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            
            <!-- Left: Brand & Mobile Sidebar Toggle -->
            <div class="flex items-center gap-3">
                <button 
                    @click="sidebarOpen = !sidebarOpen" 
                    type="button" 
                    class="lg:hidden p-2 rounded-md text-[var(--foreground-muted)] hover:text-[var(--foreground)] hover:bg-[var(--surface-muted)] focus:outline-hidden focus:ring-2 focus:ring-[var(--focus-ring)]"
                    aria-label="Toggle Sidebar Menu">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>

                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 group">
                    <div class="w-9 h-9 rounded-xl bg-[var(--primary)] text-white flex items-center justify-center font-bold text-lg shadow-sm group-hover:bg-[var(--primary-hover)] transition-colors">
                        +
                    </div>
                    <div>
                        <span class="font-bold text-base tracking-tight text-[var(--foreground)] block">SABIRA POSKESTREN</span>
                        <span class="text-[10px] uppercase font-semibold text-[var(--primary)] tracking-wider block">Health Services</span>
                    </div>
                </a>
            </div>

            <!-- Right: Theme Switcher & Status -->
            <div class="flex items-center gap-4">
                <x-theme-switcher />

                <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-full bg-[var(--surface-muted)] text-xs font-medium text-[var(--foreground-muted)] border border-[var(--border)]">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Sistem Aktif</span>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Container Layout (Sidebar + Content) -->
    <div class="flex-1 flex max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 gap-6">

        <!-- Sidebar Navigation -->
        <aside 
            :class="sidebarOpen ? 'block' : 'hidden lg:block'"
            class="w-64 shrink-0 no-print"
            aria-label="Navigasi Utama">
            
            <nav class="sticky top-22 bg-[var(--surface)] border border-[var(--border)] rounded-2xl p-4 shadow-xs space-y-1">
                <div class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-[var(--foreground-muted)]">
                    Navigasi Utama
                </div>

                <a href="{{ route('dashboard') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-xl transition-colors {{ request()->routeIs('dashboard') ? 'bg-[var(--primary)] text-white' : 'text-[var(--foreground-muted)] hover:bg-[var(--surface-muted)] hover:text-[var(--foreground)]' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 00-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    <span>Dashboard Shell</span>
                </a>

                <a href="{{ route('health') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-xl transition-colors {{ request()->routeIs('health') ? 'bg-[var(--primary)] text-white' : 'text-[var(--foreground-muted)] hover:bg-[var(--surface-muted)] hover:text-[var(--foreground)]' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Health Status</span>
                </a>

                <div class="pt-4 border-t border-[var(--border)] px-3 py-2 text-xs font-semibold uppercase tracking-wider text-[var(--foreground-muted)]">
                    Aturan Pelayanan Medis
                </div>
                
                <div class="px-3 py-2 text-xs text-[var(--foreground-muted)] leading-relaxed rounded-xl bg-[var(--surface-muted)] border border-[var(--border)]">
                    <p class="font-semibold text-[var(--foreground)] mb-1">📢 SOP Poskestren:</p>
                    Santri sakit wajib diantarkan/diarahkan ke Poskestren dan tidak diizinkan tetap di asrama.
                </div>
            </nav>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 min-w-0">
            {{ $slot }}
        </main>
    </div>

    <!-- Footer -->
    <footer class="mt-auto bg-[var(--surface)] border-t border-[var(--border)] py-4 text-center text-xs text-[var(--foreground-muted)] no-print">
        <div class="max-w-7xl mx-auto px-4">
            &copy; {{ date('Y') }} SABIRA POSKESTREN Health — Rekam Medis & Pelayanan Kesehatan Santri. All rights reserved.
        </div>
    </footer>

    @livewireScripts
</body>
</html>

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
                    <span>System Active</span>
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
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 00-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    <span>Dashboard</span>
                </a>

                <div class="pt-3 border-t border-[var(--border)] px-3 py-1 text-xs font-semibold uppercase tracking-wider text-[var(--foreground-muted)]">
                    Pelayanan Medis
                </div>

                <a href="{{ route('visits.index') }}" 
                   class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-xl transition-colors {{ request()->routeIs('visits.*') ? 'bg-[var(--primary)] text-white' : 'text-[var(--foreground-muted)] hover:bg-[var(--surface-muted)] hover:text-[var(--foreground)]' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    <span>Kunjungan Medis (Intake)</span>
                </a>

                <a href="{{ route('observations.index') }}" 
                   class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-xl transition-colors {{ request()->routeIs('observations.*') ? 'bg-[var(--primary)] text-white' : 'text-[var(--foreground-muted)] hover:bg-[var(--surface-muted)] hover:text-[var(--foreground)]' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    <span>Observasi Poskestren</span>
                </a>

                <div class="pt-3 border-t border-[var(--border)] px-3 py-1 text-xs font-semibold uppercase tracking-wider text-[var(--foreground-muted)]">
                    Farmasi & Inventaris
                </div>

                <a href="{{ route('pharmacy.medicines.index') }}" 
                   class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-xl transition-colors {{ request()->routeIs('pharmacy.medicines.*') ? 'bg-[var(--primary)] text-white' : 'text-[var(--foreground-muted)] hover:bg-[var(--surface-muted)] hover:text-[var(--foreground)]' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    <span>Master Data Obat</span>
                </a>

                <a href="{{ route('pharmacy.inventory.index') }}" 
                   class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-xl transition-colors {{ request()->routeIs('pharmacy.inventory.*') || request()->routeIs('pharmacy.receipt.*') || request()->routeIs('pharmacy.adjustments.*') ? 'bg-[var(--primary)] text-white' : 'text-[var(--foreground-muted)] hover:bg-[var(--surface-muted)] hover:text-[var(--foreground)]' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    <span>Stok & Batch Obat</span>
                </a>

                <div class="pt-3 border-t border-[var(--border)] px-3 py-1 text-xs font-semibold uppercase tracking-wider text-[var(--foreground-muted)]">
                    Identitas & Pasien
                </div>

                <a href="{{ route('people.index') }}" 
                   class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-xl transition-colors {{ request()->routeIs('people.*') ? 'bg-[var(--primary)] text-white' : 'text-[var(--foreground-muted)] hover:bg-[var(--surface-muted)] hover:text-[var(--foreground)]' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span>Direktori Person</span>
                </a>

                <a href="{{ route('patients.index') }}" 
                   class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-xl transition-colors {{ request()->routeIs('patients.*') ? 'bg-[var(--primary)] text-white' : 'text-[var(--foreground-muted)] hover:bg-[var(--surface-muted)] hover:text-[var(--foreground)]' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>Kelayakan Pasien</span>
                </a>

                <a href="{{ route('users.index') }}" 
                   class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-xl transition-colors {{ request()->routeIs('users.*') ? 'bg-[var(--primary)] text-white' : 'text-[var(--foreground-muted)] hover:bg-[var(--surface-muted)] hover:text-[var(--foreground)]' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <span>Akun Pengguna</span>
                </a>

                <a href="{{ route('roles.index') }}" 
                   class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-xl transition-colors {{ request()->routeIs('roles.*') ? 'bg-[var(--primary)] text-white' : 'text-[var(--foreground-muted)] hover:bg-[var(--surface-muted)] hover:text-[var(--foreground)]' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    <span>Roles & Permissions</span>
                </a>

                <div class="pt-3 border-t border-[var(--border)] px-3 py-1 text-xs font-semibold uppercase tracking-wider text-[var(--foreground-muted)]">
                    Integrasi & Audit
                </div>

                <a href="{{ route('gate-sync.preview') }}" 
                   class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-xl transition-colors {{ request()->routeIs('gate-sync.*') ? 'bg-[var(--primary)] text-white' : 'text-[var(--foreground-muted)] hover:bg-[var(--surface-muted)] hover:text-[var(--foreground)]' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    <span>Gate Dry-Run Sync</span>
                </a>

                <a href="{{ route('audit-logs.index') }}" 
                   class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-xl transition-colors {{ request()->routeIs('audit-logs.*') ? 'bg-[var(--primary)] text-white' : 'text-[var(--foreground-muted)] hover:bg-[var(--surface-muted)] hover:text-[var(--foreground)]' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    <span>Log Audit System</span>
                </a>
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

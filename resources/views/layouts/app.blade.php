<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) && trim((string) $title) !== '' ? trim((string) $title).' — '.$identity['application_name'] : $identity['application_name'] }}</title>
    <link rel="icon" href="{{ $identity['favicon_url'] }}">

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

    <!-- Alpine.js fallback -->
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

                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 group min-w-0">
                    <picture class="shrink-0">
                        <source media="(prefers-color-scheme: dark)" srcset="{{ $identity['logo_dark_url'] }}">
                        <img src="{{ $identity['logo_url'] }}" alt="Logo {{ $identity['application_name'] }}" class="h-10 w-auto max-w-44 object-contain group-hover:opacity-90 transition-opacity">
                    </picture>
                    <div class="hidden xl:block min-w-0">
                        <span class="font-bold text-sm tracking-tight text-[var(--foreground)] block truncate">{{ $identity['application_short_name'] }}</span>
                        <span class="text-[10px] font-semibold text-[var(--primary)] tracking-wide block truncate">{{ $identity['institution_name'] }}</span>
                    </div>
                </a>
            </div>

            <!-- Right: User Info, Theme Switcher & Logout -->
            <div class="flex items-center gap-3 sm:gap-4">
                <x-theme-switcher />

                @auth
                    <div class="flex items-center gap-3 pl-3 border-l border-[var(--border)]">
                        <div class="hidden sm:block text-right">
                            <div class="text-xs font-bold text-[var(--foreground)]">{{ Auth::user()->name }}</div>
                            <div class="text-[10px] text-[var(--foreground-muted)] font-medium">
                                {{ Auth::user()->roles->first()?->display_name ?? 'Pengguna' }}
                            </div>
                        </div>

                        <!-- Logout Button Form -->
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit"
                                    class="p-2 rounded-xl text-[var(--foreground-muted)] hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 border border-transparent hover:border-rose-200 dark:hover:border-rose-800 transition-colors text-xs font-semibold flex items-center gap-1.5"
                                    title="Keluar dari Aplikasi (Logout)">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                <span class="hidden md:inline">Keluar</span>
                            </button>
                        </form>
                    </div>
                @endauth
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
                   class="flex items-center gap-3 px-3 py-2.5 text-xs font-medium rounded-xl transition-colors {{ request()->routeIs('dashboard') || request()->routeIs('dashboards.*') ? 'bg-[var(--primary)] text-white' : 'text-[var(--foreground-muted)] hover:bg-[var(--surface-muted)] hover:text-[var(--foreground)]' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 00-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    <span>Dashboard Utama</span>
                </a>

                {{-- Pelayanan Medis Section --}}
                @canany(['view-clinical-dashboard', 'view-medical-visits', 'view-patients', 'view-observations', 'view-referrals', 'view-discharges'])
                    <div class="pt-3 border-t border-[var(--border)] px-3 py-1 text-xs font-semibold uppercase tracking-wider text-[var(--foreground-muted)]">
                        Pelayanan Medis
                    </div>

                    @can('view-clinical-dashboard')
                        <a href="{{ route('dashboards.clinical') }}"
                           class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-xl transition-colors {{ request()->routeIs('dashboards.clinical') ? 'bg-[var(--primary)] text-white' : 'text-[var(--foreground-muted)] hover:bg-[var(--surface-muted)] hover:text-[var(--foreground)]' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <span>Dashboard Klinis</span>
                        </a>
                    @endcan

                    @can('view-medical-visits')
                        <a href="{{ route('visits.index') }}"
                           class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-xl transition-colors {{ request()->routeIs('visits.*') ? 'bg-[var(--primary)] text-white' : 'text-[var(--foreground-muted)] hover:bg-[var(--surface-muted)] hover:text-[var(--foreground)]' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            <span>Kunjungan (Intake)</span>
                        </a>
                    @endcan

                    @can('view-patients')
                        <a href="{{ route('patients.index') }}"
                           class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-xl transition-colors {{ request()->routeIs('patients.*') ? 'bg-[var(--primary)] text-white' : 'text-[var(--foreground-muted)] hover:bg-[var(--surface-muted)] hover:text-[var(--foreground)]' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            <span>Data Rekam Medis</span>
                        </a>
                    @endcan

                    @can('view-observations')
                        <a href="{{ route('observations.index') }}"
                           class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-xl transition-colors {{ request()->routeIs('observations.*') ? 'bg-[var(--primary)] text-white' : 'text-[var(--foreground-muted)] hover:bg-[var(--surface-muted)] hover:text-[var(--foreground)]' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            <span>Ruang Observasi</span>
                        </a>
                    @endcan

                    @can('view-referrals')
                        <a href="{{ route('referrals.index') }}"
                           class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-xl transition-colors {{ request()->routeIs('referrals.*') ? 'bg-[var(--primary)] text-white' : 'text-[var(--foreground-muted)] hover:bg-[var(--surface-muted)] hover:text-[var(--foreground)]' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" /></svg>
                            <span>Rujukan Eksternal</span>
                        </a>
                    @endcan

                    @can('view-discharges')
                        <a href="{{ route('discharges.index') }}"
                           class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-xl transition-colors {{ request()->routeIs('discharges.*') ? 'bg-[var(--primary)] text-white' : 'text-[var(--foreground-muted)] hover:bg-[var(--surface-muted)] hover:text-[var(--foreground)]' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span>Kepulangan Medis</span>
                        </a>
                    @endcan
                @endcanany

                {{-- Farmasi Section --}}
                @canany(['manage-medicines', 'manage-medicine-master', 'view-pharmacy-inventory', 'view-pharmacy-dashboard'])
                    <div class="pt-3 border-t border-[var(--border)] px-3 py-1 text-xs font-semibold uppercase tracking-wider text-[var(--foreground-muted)]">
                        Farmasi & Obat
                    </div>

                    @canany(['view-pharmacy-dashboard', 'view-pharmacy-inventory'])
                        <a href="{{ route('dashboards.pharmacy') }}"
                           class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-xl transition-colors {{ request()->routeIs('dashboards.pharmacy') ? 'bg-[var(--primary)] text-white' : 'text-[var(--foreground-muted)] hover:bg-[var(--surface-muted)] hover:text-[var(--foreground)]' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                            <span>Dashboard Farmasi</span>
                        </a>
                    @endcanany

                    @canany(['manage-medicines', 'manage-medicine-master'])
                        <a href="{{ route('pharmacy.medicines.index') }}"
                           class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-xl transition-colors {{ request()->routeIs('pharmacy.medicines.*') ? 'bg-[var(--primary)] text-white' : 'text-[var(--foreground-muted)] hover:bg-[var(--surface-muted)] hover:text-[var(--foreground)]' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            <span>Master Data Obat</span>
                        </a>
                    @endcanany

                    @can('view-pharmacy-inventory')
                        <a href="{{ route('pharmacy.inventory.index') }}"
                           class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-xl transition-colors {{ request()->routeIs('pharmacy.inventory.*') || request()->routeIs('pharmacy.receipt.*') || request()->routeIs('pharmacy.adjustments.*') ? 'bg-[var(--primary)] text-white' : 'text-[var(--foreground-muted)] hover:bg-[var(--surface-muted)] hover:text-[var(--foreground)]' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            <span>Stok & Batch Obat</span>
                        </a>
                    @endcan
                @endcanany

                {{-- Operasional & Asrama Section --}}
                @canany(['view-operational-dashboard', 'view-operational-handoffs', 'view-follow-up-plans'])
                    <div class="pt-3 border-t border-[var(--border)] px-3 py-1 text-xs font-semibold uppercase tracking-wider text-[var(--foreground-muted)]">
                        Operasional Asrama
                    </div>

                    @can('view-operational-dashboard')
                        <a href="{{ route('dashboards.operational') }}"
                           class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-xl transition-colors {{ request()->routeIs('dashboards.operational') ? 'bg-[var(--primary)] text-white' : 'text-[var(--foreground-muted)] hover:bg-[var(--surface-muted)] hover:text-[var(--foreground)]' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
                            <span>Dashboard Operasional</span>
                        </a>
                    @endcan

                    @can('view-operational-handoffs')
                        <a href="{{ route('operational-handoffs.index') }}"
                           class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-xl transition-colors {{ request()->routeIs('operational-handoffs.*') ? 'bg-[var(--primary)] text-white' : 'text-[var(--foreground-muted)] hover:bg-[var(--surface-muted)] hover:text-[var(--foreground)]' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            <span>Handoff Asrama</span>
                        </a>
                    @endcan

                    @can('view-follow-up-plans')
                        <a href="{{ route('follow-up-plans.index') }}"
                           class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-xl transition-colors {{ request()->routeIs('follow-up-plans.*') ? 'bg-[var(--primary)] text-white' : 'text-[var(--foreground-muted)] hover:bg-[var(--surface-muted)] hover:text-[var(--foreground)]' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span>Rencana Kontrol</span>
                        </a>
                    @endcan
                @endcanany

                {{-- Laporan & Eksekutif Section --}}
                @canany(['view-reports', 'view-health-reports', 'view-management-dashboard'])
                    <div class="pt-3 border-t border-[var(--border)] px-3 py-1 text-xs font-semibold uppercase tracking-wider text-[var(--foreground-muted)]">
                        Laporan & Manajemen
                    </div>

                    @can('view-management-dashboard')
                        <a href="{{ route('dashboards.management') }}"
                           class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-xl transition-colors {{ request()->routeIs('dashboards.management') ? 'bg-[var(--primary)] text-white' : 'text-[var(--foreground-muted)] hover:bg-[var(--surface-muted)] hover:text-[var(--foreground)]' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
                            <span>Dashboard Manajemen</span>
                        </a>
                    @endcan

                    @canany(['view-reports', 'view-health-reports'])
                        <a href="{{ route('reports.index') }}"
                           class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-xl transition-colors {{ request()->routeIs('reports.*') ? 'bg-[var(--primary)] text-white' : 'text-[var(--foreground-muted)] hover:bg-[var(--surface-muted)] hover:text-[var(--foreground)]' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                            <span>Laporan Kesehatan</span>
                        </a>
                    @endcanany
                @endcanany

                {{-- Administrasi Sistem Section --}}
                @canany(['manage-users', 'manage-roles', 'manage-permissions', 'manage-system-settings', 'view-people', 'manage-gate-sync', 'view-gate-sync', 'view-audit-log', 'manage-healthcare-partners'])
                    <div class="pt-3 border-t border-[var(--border)] px-3 py-1 text-xs font-semibold uppercase tracking-wider text-[var(--foreground-muted)]">
                        Administrasi & Sistem
                    </div>

                    @can('view-people')
                        <a href="{{ route('people.index') }}"
                           class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-xl transition-colors {{ request()->routeIs('people.*') ? 'bg-[var(--primary)] text-white' : 'text-[var(--foreground-muted)] hover:bg-[var(--surface-muted)] hover:text-[var(--foreground)]' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            <span>Direktori Person</span>
                        </a>
                    @endcan

                    @can('manage-users')
                        <a href="{{ route('users.index') }}"
                           class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-xl transition-colors {{ request()->routeIs('users.*') ? 'bg-[var(--primary)] text-white' : 'text-[var(--foreground-muted)] hover:bg-[var(--surface-muted)] hover:text-[var(--foreground)]' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            <span>Akun Pengguna</span>
                        </a>
                    @endcan

                    @can('manage-roles')
                        <a href="{{ route('roles.index') }}"
                           class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-xl transition-colors {{ request()->routeIs('roles.*') ? 'bg-[var(--primary)] text-white' : 'text-[var(--foreground-muted)] hover:bg-[var(--surface-muted)] hover:text-[var(--foreground)]' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            <span>Roles & Permissions</span>
                        </a>
                    @endcan

                    @can('manage-system-settings')
                        <a href="{{ route('admin.system.application-identity.edit') }}"
                           class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-xl transition-colors {{ request()->routeIs('admin.system.application-identity.*') ? 'bg-[var(--primary)] text-white' : 'text-[var(--foreground-muted)] hover:bg-[var(--surface-muted)] hover:text-[var(--foreground)]' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7V4h3m10 0h3v3M4 17v3h3m10 0h3v-3M8 12a4 4 0 108 0 4 4 0 00-8 0Z" /></svg>
                            <span>Identitas Aplikasi</span>
                        </a>
                    @endcan

                    @can('manage-sso-settings')
                        <a href="{{ route('admin.system.sso-configuration.edit') }}"
                           class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-xl transition-colors {{ request()->routeIs('admin.system.sso-configuration.*') ? 'bg-[var(--primary)] text-white' : 'text-[var(--foreground-muted)] hover:bg-[var(--surface-muted)] hover:text-[var(--foreground)]' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-1V7a5 5 0 0 0-10 0v4H6a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2Zm3-10H9V7a3 3 0 0 1 6 0v4Z" /></svg>
                            <span>Pengaturan Gate SSO</span>
                        </a>
                    @endcan

                    @can('manage-healthcare-partners')
                        <a href="{{ route('healthcare-partners.index') }}"
                           class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-xl transition-colors {{ request()->routeIs('healthcare-partners.*') ? 'bg-[var(--primary)] text-white' : 'text-[var(--foreground-muted)] hover:bg-[var(--surface-muted)] hover:text-[var(--foreground)]' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                            <span>Mitra Faskes</span>
                        </a>
                    @endcan

                    @canany(['manage-gate-sync', 'view-gate-sync'])
                        <a href="{{ route('gate-sync.preview') }}"
                           class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-xl transition-colors {{ request()->routeIs('gate-sync.*') ? 'bg-[var(--primary)] text-white' : 'text-[var(--foreground-muted)] hover:bg-[var(--surface-muted)] hover:text-[var(--foreground)]' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            <span>Gate Sync Preview</span>
                        </a>
                    @endcanany

                    @can('view-audit-log')
                        <a href="{{ route('audit-logs.index') }}"
                           class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-xl transition-colors {{ request()->routeIs('audit-logs.*') ? 'bg-[var(--primary)] text-white' : 'text-[var(--foreground-muted)] hover:bg-[var(--surface-muted)] hover:text-[var(--foreground)]' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            <span>Log Audit System</span>
                        </a>
                    @endcan
                @endcanany

                <div class="mt-3 pt-3 border-t border-[var(--border)] px-3 flex items-center gap-2">
                    <img src="{{ $identity['mark_url'] }}" alt="" class="w-7 h-7 rounded-lg" aria-hidden="true">
                    <div class="min-w-0">
                        <div class="text-[11px] font-semibold text-[var(--foreground)] truncate">{{ $identity['application_short_name'] }}</div>
                        <div class="text-[10px] text-[var(--foreground-muted)] truncate">{{ $identity['institution_name'] }}</div>
                    </div>
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
            &copy; {{ date('Y') }} {{ $identity['footer_text'] ?: $identity['application_name'] }}
        </div>
    </footer>

    @livewireScripts
</body>
</html>

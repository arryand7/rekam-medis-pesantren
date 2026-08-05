<div x-data="{
        theme: window.SabiraTheme ? window.SabiraTheme.getTheme() : 'system',
        setTheme(mode) {
            this.theme = mode;
            if (window.SabiraTheme) {
                window.SabiraTheme.setTheme(mode);
            }
        }
    }" 
    class="relative inline-block text-left" 
    id="theme-switcher-component">
    
    <div class="inline-flex rounded-lg p-1 bg-[var(--surface-muted)] border border-[var(--border)]" role="group" aria-label="Pilih Tema Tampilan">
        <button 
            type="button"
            @click="setTheme('light')" 
            :class="theme === 'light' ? 'bg-[var(--primary)] text-white shadow-xs' : 'text-[var(--foreground-muted)] hover:text-[var(--foreground)]'"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-md transition-all focus:outline-hidden focus-visible:ring-2 focus-visible:ring-[var(--focus-ring)] cursor-pointer"
            aria-label="Mode Terang (Light)">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
            </svg>
            <span>Light</span>
        </button>

        <button 
            type="button"
            @click="setTheme('dark')" 
            :class="theme === 'dark' ? 'bg-[var(--primary)] text-white shadow-xs' : 'text-[var(--foreground-muted)] hover:text-[var(--foreground)]'"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-md transition-all focus:outline-hidden focus-visible:ring-2 focus-visible:ring-[var(--focus-ring)] cursor-pointer"
            aria-label="Mode Gelap (Dark)">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
            </svg>
            <span>Dark</span>
        </button>

        <button 
            type="button"
            @click="setTheme('system')" 
            :class="theme === 'system' ? 'bg-[var(--primary)] text-white shadow-xs' : 'text-[var(--foreground-muted)] hover:text-[var(--foreground)]'"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-md transition-all focus:outline-hidden focus-visible:ring-2 focus-visible:ring-[var(--focus-ring)] cursor-pointer"
            aria-label="Mode Sistem (Auto)">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
            <span>System</span>
        </button>
    </div>
</div>

<x-app-layout>
    <x-slot name="title">Konflik Identitas Gate</x-slot>

    <div class="space-y-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs">
            <div>
                <h1 class="text-2xl font-bold text-[var(--foreground)] tracking-tight">Resolusi Konflik Identitas</h1>
                <p class="text-sm text-[var(--foreground-muted)] mt-1">Halaman peninjauan manual untuk mendeteksi potensi duplikasi identitas berdasarkan NIS/NIP/NIK tanpa auto-merge.</p>
            </div>
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-900">
                    Manual Review Safeguard Active
                </span>
            </div>
        </div>

        <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-4">
            <div class="bg-[var(--surface-muted)] border-l-4 border-amber-500 p-4 rounded-xl">
                <h3 class="text-sm font-semibold text-[var(--foreground)]">Prinsip Keamanan Identitas:</h3>
                <p class="text-xs text-[var(--foreground-muted)] mt-1 leading-relaxed">
                    Sistem POSKESTREN dilarang keras melakukan *auto-merge* data identitas hanya berdasarkan kesamaan nama. Setiap potensi duplikasi harus diverifikasi melalui `gate_user_id` atau resolusi manual yang diaudit.
                </p>
            </div>

            <div class="text-center py-8 text-sm text-[var(--foreground-muted)]">
                Tidak ada konflik identitas yang menggantung saat ini. Semua data berada dalam kondisi sinkron.
            </div>
        </div>
    </div>
</x-app-layout>

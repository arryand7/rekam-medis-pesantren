<x-app-layout>
    <x-slot name="title">Mitra Layanan Kesehatan</x-slot>

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-[var(--foreground)]">Mitra Layanan Kesehatan</h1>
                <p class="text-xs text-[var(--foreground-muted)] mt-1">Direktori Puskesmas, Rumah Sakit, dan Klinik Mitra Rujukan & Konsultasi Poskestren.</p>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-emerald-500/10 border-l-4 border-emerald-500 p-4 rounded-xl text-xs text-emerald-700 dark:text-emerald-300 font-semibold">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Form Tambah Mitra -->
            <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-4">
                <h2 class="text-sm font-bold uppercase tracking-wider text-[var(--foreground-muted)]">Tambah Mitra Baru</h2>

                <form action="{{ route('healthcare-partners.store') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-[var(--foreground-muted)] mb-1">Kode Faskes <span class="text-rose-500">*</span></label>
                        <input type="text" name="code" required placeholder="FASKES-CONTOH" class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)] uppercase font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-[var(--foreground-muted)] mb-1">Nama Faskes Mitra <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" required placeholder="Fasilitas Kesehatan Mitra Fiktif" class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)]">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-[var(--foreground-muted)] mb-1">Tipe Faskes <span class="text-rose-500">*</span></label>
                        <select name="partner_type" required class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)]">
                            <option value="puskesmas">Puskesmas</option>
                            <option value="hospital">Rumah Sakit</option>
                            <option value="clinic">Klinik Pratama/Utama</option>
                            <option value="other">Lainnya</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-[var(--foreground-muted)] mb-1">Telepon Resmi</label>
                        <input type="text" name="phone" placeholder="Nomor kontak resmi" class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)]">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-[var(--foreground-muted)] mb-1">Alamat Email Resmi</label>
                        <input type="email" name="official_email" placeholder="partner@example.invalid" class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)]">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-[var(--foreground-muted)] mb-1">Nomor Referensi Kerjasama / MOU</label>
                        <input type="text" name="cooperation_reference" placeholder="MOU-SYNTHETIC-001" class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)]">
                    </div>

                    <div class="pt-2 text-right">
                        <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-[var(--primary)] text-white hover:bg-[var(--primary-hover)]">
                            Simpan Faskes Mitra
                        </button>
                    </div>
                </form>
            </div>

            <!-- List Mitra Healthcare Partners -->
            <div class="lg:col-span-2 bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-4">
                <h2 class="text-sm font-bold uppercase tracking-wider text-[var(--foreground-muted)]">Daftar Faskes Mitra Terdaftar</h2>

                <div class="divide-y divide-[var(--border)]">
                    @forelse($partners as $partner)
                        <div class="py-4 space-y-2">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="font-mono text-xs font-bold text-[var(--primary)]">{{ $partner->code }}</span>
                                    <h3 class="text-base font-bold text-[var(--foreground)]">{{ $partner->name }}</h3>
                                </div>
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300">
                                    {{ $partner->partner_type }}
                                </span>
                            </div>

                            <div class="text-xs text-[var(--foreground-muted)] space-y-1">
                                <div>Telepon: {{ $partner->phone ?? '-' }} • Email: {{ $partner->official_email ?? '-' }}</div>
                                <div>MOU: <strong class="font-mono text-[var(--foreground)]">{{ $partner->cooperation_reference ?? '-' }}</strong></div>
                            </div>

                            <!-- List Contacts -->
                            <div class="pt-2">
                                <div class="text-[11px] font-bold uppercase text-[var(--foreground-muted)] mb-1">Dokter / Kontak Medis Resmi:</div>
                                <div class="flex flex-wrap gap-2">
                                    @forelse($partner->contacts as $contact)
                                        <span class="px-2.5 py-1 rounded-lg text-xs bg-[var(--surface-muted)] border border-[var(--border)] text-[var(--foreground)]">
                                            <strong>{{ $contact->name }}</strong> ({{ $contact->profession }})
                                        </span>
                                    @empty
                                        <span class="text-xs text-[var(--foreground-muted)] italic">Belum ada kontak dokter terdaftar.</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-[var(--foreground-muted)] py-4">Belum ada mitra faskes terdaftar.</p>
                    @endforelse
                </div>

                <div class="pt-4">
                    {{ $partners->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

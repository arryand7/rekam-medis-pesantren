<x-app-layout>
    <x-slot name="title">Edit Role: {{ $role->display_name ?? $role->name }} — SABIRA POSKESTREN</x-slot>

    @php
        $initPerms = json_encode(old('permissions', $currentPermissions));
    @endphp

    <div class="space-y-6" x-data="{
        searchQuery: '',
        selectedPermissions: {{ $initPerms }},
        selectAllGroup(groupPerms) {
            groupPerms.forEach(p => {
                if (!this.selectedPermissions.includes(p)) {
                    this.selectedPermissions.push(p);
                }
            });
        },
        clearGroup(groupPerms) {
            this.selectedPermissions = this.selectedPermissions.filter(p => !groupPerms.includes(p));
        },
        matchesSearch(name, displayName) {
            if (!this.searchQuery) return true;
            const q = this.searchQuery.toLowerCase();
            return name.toLowerCase().includes(q) || displayName.toLowerCase().includes(q);
        }
    }">
        <!-- Top Breadcrumb & Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs">
            <div>
                <a href="{{ route('roles.show', $role->id) }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline mb-1 inline-flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Kembali ke Rincian Role
                </a>
                <h1 class="text-2xl font-bold text-[var(--foreground)] tracking-tight">
                    Edit Matrix Hak Akses: {{ $role->display_name ?? $role->name }}
                </h1>
                <p class="text-sm text-[var(--foreground-muted)] mt-1">
                    Perbarui nama tampilan, deskripsi, atau paket hak akses (permissions) untuk role ini. Perubahan langsung berlaku secara real-time.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold text-[var(--foreground-muted)]">Terpilih:</span>
                <span class="px-3 py-1 rounded-xl bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300 font-bold text-sm" x-text="selectedPermissions.length + ' Hak Akses'"></span>
            </div>
        </div>

        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 text-rose-800 dark:text-rose-300 text-xs space-y-1">
                <div class="font-bold flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    Terdapat kesalahan pengisian formulir:
                </div>
                <ul class="list-disc list-inside pl-2 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('roles.update', $role->id) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Role Identity Card -->
            <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-4">
                <h2 class="text-base font-bold text-[var(--foreground)]">1. Informasi Identitas Role</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-xs font-semibold uppercase text-[var(--foreground-muted)] mb-1">
                            Identifier Role (Unique Key) <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            value="{{ old('name', $role->name) }}"
                            {{ $role->isProtected() ? 'readonly' : 'required' }}
                            class="w-full text-xs font-mono p-2.5 rounded-xl border border-[var(--border)] bg-[var(--surface)] text-[var(--foreground)] focus:ring-2 focus:ring-blue-500 {{ $role->isProtected() ? 'opacity-60 bg-gray-100 dark:bg-gray-900 cursor-not-allowed' : '' }}">
                        @if($role->isProtected())
                            <p class="text-[11px] text-amber-600 dark:text-amber-400 mt-1">* Identifier role sistem inti bersifat permanen dan tidak dapat diubah.</p>
                        @endif
                    </div>

                    <div>
                        <label for="display_name" class="block text-xs font-semibold uppercase text-[var(--foreground-muted)] mb-1">
                            Nama Tampilan (Display Name) <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="display_name"
                            id="display_name"
                            value="{{ old('display_name', $role->display_name) }}"
                            required
                            class="w-full text-xs p-2.5 rounded-xl border border-[var(--border)] bg-[var(--surface)] text-[var(--foreground)] focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-xs font-semibold uppercase text-[var(--foreground-muted)] mb-1">
                        Deskripsi & Tugas Peran
                    </label>
                    <textarea
                        name="description"
                        id="description"
                        rows="2"
                        class="w-full text-xs p-2.5 rounded-xl border border-[var(--border)] bg-[var(--surface)] text-[var(--foreground)] focus:ring-2 focus:ring-blue-500">{{ old('description', $role->description) }}</textarea>
                </div>
            </div>

            <!-- Permission Matrix Card -->
            <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[var(--border)] pb-4">
                    <div>
                        <h2 class="text-base font-bold text-[var(--foreground)]">2. Matriks Hak Akses (Permissions Matrix)</h2>
                        <p class="text-xs text-[var(--foreground-muted)] mt-0.5">Centang atau hapus centang hak akses yang diizinkan untuk peran ini.</p>
                    </div>
                    <div class="w-full sm:w-72">
                        <input
                            type="text"
                            x-model="searchQuery"
                            placeholder="Saring hak akses..."
                            class="w-full text-xs p-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-[var(--foreground)] focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="space-y-6">
                    @foreach($groupedPermissions as $groupKey => $group)
                        @php
                            $permNames = $group['permissions']->pluck('name')->toArray();
                            $jsonPermNames = json_encode($permNames);
                        @endphp
                        <div class="p-5 rounded-2xl bg-[var(--surface-muted)]/60 border border-[var(--border)] space-y-3">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-[var(--border)] pb-2.5">
                                <div>
                                    <h3 class="text-sm font-bold text-[var(--foreground)] flex items-center gap-2">
                                        {{ $group['title'] }}
                                        <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full bg-[var(--surface)] text-[var(--foreground-muted)] border border-[var(--border)]">
                                            {{ $group['permissions']->count() }}
                                        </span>
                                    </h3>
                                    <p class="text-[11px] text-[var(--foreground-muted)]">{{ $group['description'] }}</p>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <button
                                        type="button"
                                        @click="selectAllGroup({{ $jsonPermNames }})"
                                        class="px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-[var(--surface)] hover:bg-[var(--border)] text-[var(--foreground)] border border-[var(--border)] transition-colors">
                                        Pilih Semua
                                    </button>
                                    <button
                                        type="button"
                                        @click="clearGroup({{ $jsonPermNames }})"
                                        class="px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-[var(--surface)] hover:bg-[var(--border)] text-[var(--foreground-muted)] border border-[var(--border)] transition-colors">
                                        Kosongkan
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2.5">
                                @foreach($group['permissions'] as $permission)
                                    @php
                                        $isProtected = $permission->isProtected();
                                        $isDisabled = $isProtected && ! $isSuperAdmin;
                                    @endphp
                                    <label
                                        x-show="matchesSearch('{{ $permission->name }}', '{{ addslashes($permission->display_name) }}')"
                                        class="flex items-start gap-2.5 p-2.5 rounded-xl border transition-colors cursor-pointer {{ $isDisabled ? 'opacity-50 cursor-not-allowed bg-gray-100 dark:bg-gray-900 border-gray-200 dark:border-gray-800' : 'bg-[var(--surface)] border-[var(--border)] hover:border-blue-400' }}">
                                        <input
                                            type="checkbox"
                                            name="permissions[]"
                                            value="{{ $permission->name }}"
                                            x-model="selectedPermissions"
                                            {{ $isDisabled ? 'disabled' : '' }}
                                            class="mt-0.5 rounded text-blue-600 focus:ring-blue-500 border-[var(--border)]">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="text-xs font-bold text-[var(--foreground)]">
                                                    {{ $permission->display_name }}
                                                </span>
                                                @if($isProtected)
                                                    <span class="px-1.5 py-0.2 text-[9px] font-bold rounded bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-900" title="Hak akses sensitif sistem">
                                                        PROTECTED
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="font-mono text-[10px] text-[var(--foreground-muted)] truncate">
                                                {{ $permission->name }}
                                            </div>
                                            @if($permission->description && $permission->description !== $permission->display_name)
                                                <div class="text-[10px] text-[var(--foreground-muted)] line-clamp-1 mt-0.5">
                                                    {{ $permission->description }}
                                                </div>
                                            @endif
                                            @if($isDisabled)
                                                <div class="text-[9px] text-amber-600 dark:text-amber-400 mt-0.5">
                                                    * Hanya Super Admin yang dapat mengubah hak akses ini.
                                                </div>
                                            @endif
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('roles.show', $role->id) }}" class="px-5 py-2.5 rounded-xl border border-[var(--border)] bg-[var(--surface)] text-[var(--foreground-muted)] hover:text-[var(--foreground)] text-xs font-semibold transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-xs transition-colors">
                    Simpan Perubahan Matrix Role
                </button>
            </div>
        </form>
    </div>
</x-app-layout>

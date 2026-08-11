---
id: DOC-PHASE-5A-VISIT-WORKSPACE
title: "Phase 5A Unified Visit Workspace Specification"
status: active
owner: "Ryand Arifriantoni"
last_updated: 2026-08-11
---

# Phase 5A Unified Visit Workspace Specification

Dokumen ini menjelaskan arsitektur antarmuka dan spesifikasi teknis komponen **Unified Visit Workspace** yang menyatukan seluruh interaksi pelayanan medis dalam satu ruang kerja yang kohesif.

---

## 1. Komponen Patient Context Header

Komponen *Patient Context Header* berada di bagian atas setiap halaman kunjungan:

```blade
<div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm mb-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-sky-100 dark:bg-sky-900/50 text-sky-600 dark:text-sky-400 flex items-center justify-center font-bold text-lg">
            {{ substr($patient->person->name, 0, 2) }}
        </div>
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ $patient->person->name }}</h2>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                    {{ $patient->patient_number }}
                </span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                {{ ucfirst($patient->person->user_type ?? 'santri') }} • {{ $patient->person->gender === 'male' ? 'Laki-laki' : 'Perempuan' }} • NIS/NIP: {{ $patient->person->nis_nip ?? '-' }}
            </p>
        </div>
    </div>
    
    @if($patient->activeAllergies->count() > 0)
        <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-rose-50 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-xs font-semibold">
            <svg class="w-4 h-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <span>Alergi: {{ $patient->activeAllergies->pluck('allergen')->join(', ') }}</span>
        </div>
    @endif
</div>
```

---

## 2. Navigasi Tahapan Kunjungan (*Stage Stepper Tabs*)

Tab tahapan klinis menghubungkan setiap fase pelayanan medis secara langsung tanpa perlu berpindah menu:

1. **Tab 1: Tanda Vital (`/visits/{id}`)**: Input & riwayat tekanan darah, denyut nadi, laju nafas, suhu badan, dan saturasi oksigen.
2. **Tab 2: Pengkajian SOAP (`/visits/{id}/assessment`)**: Input subjektif, objektif, asesmen diagnosis kerja, dan penentuan instruksi tindakan.
3. **Tab 3: Resep & Dispensing Obat (`/visits/{id}/medications`)**: Preskripsi obat, pemilihan nomor batch, dan administrasi obat langsung.
4. **Tab 4: Disposisi Khusus**:
   - Observasi Rawat Sementara (`/observations/{id}`)
   - Tele-Konsultasi Medis (`/visits/{id}/consultations/create`)
   - Rujukan Rumah Sakit (`/visits/{id}/referrals/create`)
5. **Tab 5: Kepulangan & Handoff (`/visits/{id}/discharge`)**: Resume medis kepulangan, rencana kontrol tindak lanjut, dan pesan pembatasan ke asrama.

---

## 3. Prioritas Tombol Aksi (*Action Hierarchy*)

- **Aksi Primer (Warna Dominan Sky / Indigo)**: Tombol untuk melanjutkan alur (misal: *Simpan Pengkajian*, *Resepkan Obat*, *Selesaikan Kunjungan*).
- **Aksi Sekunder (Warna Outline / Slate Neutral)**: Tombol pelengkap (misal: *Lihat Riwayat Medis*, *Cetak Resume*, *Kembali*).
- **Aksi Korektif / Destruktif (Warna Merah / Rose Subordinat)**: Ditempatkan di bagian bawah dengan dialog konfirmasi eksplisit (misal: *Batalkan Kunjungan Tidak Sah*, *Tandai Salah Input / Entered in Error*).

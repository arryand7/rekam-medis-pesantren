---
id: DOC-GATE-SSO-SECURITY
title: "Gate SSO Security Controls"
status: active
owner: "Ryand Arifriantoni"
last_updated: 2026-08-15
---

# Gate SSO Security Controls

## 1. Ikhtisar

Dokumen ini menjelaskan kontrol keamanan yang diterapkan pada integrasi Gate SSO di SABIRA POSKESTREN Health, mencakup autentikasi, otorisasi, perlindungan sesi, dan isolasi data medis.

## 2. Authentication Security

### 2.1 State Parameter (CSRF Protection)

- **Generasi**: `Str::random(40)` disimpan di session Laravel (`gate_auth_state`).
- **Validasi**: Menggunakan `hash_equals()` untuk mencegah timing attack.
- **Konsumsi**: State di-`pull()` dari session setelah validasi (one-time use), mencegah replay.
- **Gagal**: Jika state tidak cocok → audit log + redirect ke login.

### 2.2 Nonce Parameter (Replay Protection)

- **Generasi**: `Str::random(40)` disimpan di session (`gate_auth_nonce`).
- **Pengiriman**: Dikirim bersama authorization request.
- **Catatan**: Validasi penuh di sisi ID Token (issuer, audience, signature, expiry, nonce) dilakukan oleh Gate IdP. POSKESTREN memvalidasi state dan menerima entitlement setelah token exchange berhasil.

### 2.3 Code Exchange

- Authorization code ditukar server-to-server via `POST /oauth/token`.
- Client secret tidak pernah dikirim ke browser.
- Kegagalan exchange menghasilkan audit log (tanpa mengekspos detail teknis ke pengguna).

### 2.4 Session Security

| Kontrol | Implementasi |
|---|---|
| Session regeneration setelah login | `$request->session()->regenerate()` |
| Session invalidation saat logout | `$request->session()->invalidate()` |
| CSRF token regeneration saat logout | `$request->session()->regenerateToken()` |
| ID Token storage | Dalam session (untuk end-session URL, bukan untuk validasi lokal) |

### 2.5 Route Middleware Enforcement & Role-Aware Dashboard Dispatch

- **Group Middleware**: Seluruh rute aplikasi (Phase 0–4) dimasukkan ke dalam group `Route::middleware('auth')`.
- **Guest Protection**: Akses unauthenticated tanpa sesi ke rute manapun mengembalikan HTTP 302 redirect ke `/login`.
- **Role Resolver**: `DashboardController::index` merutekan pengguna terautentikasi sesuai permission peran (Klinis, Asrama, Manajemen, Admin).
- **Gate::before**: Memastikan hanya exact local permission yang diizinkan dan mendelegasikan pengecekan lainnya ke Policy model.

## 3. Application Entitlement Enforcement


### 3.1 Login-time Check

Setelah token exchange dan UserInfo berhasil, sistem **wajib** memeriksa application entitlement:

```text
allowed     → Login dilanjutkan
revoked     → Access denied, audit log
suspended   → Access denied, audit log
not_assigned → Access denied, audit log
unknown     → Default deny
```

### 3.2 Runtime Check

`EnforceGateApplicationEntitlement` middleware memeriksa `user->is_active` pada setiap request terautentikasi. Jika user dinonaktifkan:
- `Auth::logout()`
- Session invalidated
- CSRF regenerated
- Redirect ke login

### 3.3 Entitlement ≠ Clinical Permission

> [!IMPORTANT]
> Application entitlement **HANYA** menentukan apakah pengguna boleh mengakses aplikasi POSKESTREN Health.
> Entitlement **TIDAK** menentukan role klinis, izin medis, atau kelayakan pasien.

## 4. Identity Projection Security

### 4.1 Authoritative Fields (Boleh Di-update dari Gate)

- `gate_user_id`, `name`, `nik`, `nis_nip`, `email`, `phone`
- `user_type`, `gender`, `source_status`
- `checksum`, `source_version`, `source_updated_at`, `synced_at`

### 4.2 Protected Fields (TIDAK BOLEH Di-update dari Gate)

- Allergies, medical conditions, health profile
- Medical visits, observations, vital signs
- Clinical assessments, medication orders/administrations
- Referrals, discharges, follow-up plans
- Semua riwayat kesehatan

### 4.3 Non-Destructive Deactivation

- Deaktivasi akun: `is_active = false`, `source_status = 'deactivated'`
- **TIDAK PERNAH** menghapus Person, Patient, atau riwayat medis
- User yang dinonaktifkan tidak bisa login tapi datanya tetap tersimpan

### 4.4 Row Locking

Semua operasi proyeksi identitas menggunakan `lockForUpdate()` untuk mencegah race condition:
- `Person::where('gate_user_id', ...)->lockForUpdate()`
- `User::where('person_id', ...)->lockForUpdate()`
- Dalam `DB::transaction()` untuk atomicity

## 5. Role Mapping Security

### 5.1 Explicit Mapping Only

```php
// config/gate.php
'role_mapping' => [
    'health_officer' => 'petugas_kesehatan',
    'nurse'          => 'perawat',
    'doctor'         => 'dokter',
    'pharmacist'     => 'farmasi',
    'dorm_supervisor'=> 'pembina_asrama',
    'homeroom_teacher'=> 'wali_kelas',
    'school_admin'   => 'administrator',
],
```

### 5.2 Security Guards

- **Unknown roles**: Diabaikan (default deny). Tidak ada dynamic role creation.
- **Gate admin**: `school_admin` hanya di-map ke `administrator`. **TIDAK** otomatis mendapat permission klinis (`view-medical-record`, `view-clinical-dashboard`, dll).
- **syncWithoutDetaching**: Role hanya ditambahkan, tidak pernah dihapus otomatis dari Gate (mencegah privilege revocation yang tidak diinginkan).

## 6. Sync Apply Security

### 6.1 Authorization

- `execute-gate-sync-apply` permission diperlukan
- `GateSyncPolicy` memeriksa permission server-side
- `ApplyGateSyncRequest` melakukan validasi form

### 6.2 Idempotency

- Checksum comparison mencegah unnecessary writes (`unchanged` status)
- Row locks mencegah duplicate Person/User/Patient pada concurrent apply
- Patient number menggunakan suffix ULID (`substr($id, -10)`) untuk uniqueness

### 6.3 Conflict Handling

- NIS/NIP/NIK match tanpa `gate_user_id` → `conflict` status
- Conflict mapping dibuat sebagai `pending` → memerlukan manual approval
- **TIDAK ADA** auto-merge berdasarkan nama

## 7. Runtime Configuration & Feature Flags

| Flag | Default | Keterangan |
|---|---|---|
| Persistent `sso_enabled` | `false` | SSO aktif/nonaktif; hanya exact `super_admin` |
| `GATE_SYNC_APPLY_ENABLED` | `false` | Sync apply aktif/nonaktif |
| `GATE_WEBHOOK_ENABLED` | `false` | Webhook aktif/nonaktif |
| Persistent `driver` | `fake` | `fake` atau `http`; aktivasi SSO mensyaratkan `http` |
| `BREAK_GLASS_ENABLED` | `false` | Emergency local admin |

> [!WARNING]
> SSO default `false`/`fake`. Aktivasi memerlukan konfigurasi lengkap melalui UI Super Admin dan tidak boleh otomatis. Sync apply, webhook, dan break-glass tetap flag operasional terpisah.

## 8. Secret Management

| Item | Lokasi | Status |
|---|---|---|
| Gate client secret | Encrypted cast pada singleton `sso_configurations` | ✅ Ciphertext at rest; plaintext tidak masuk cache bersama/UI/audit/Git |
| OAuth tokens | Session only | ✅ Tidak di log/audit |
| ID Token | Session only | ✅ Untuk end-session URL saja |
| Random passwords | `bcrypt(Str::random(32))` | ✅ Opaque, tidak recoverable |
| Audit log | Tidak menyimpan token/secret | ✅ Data identitas saja |

Cache `SsoConfigurationService` menyimpan ciphertext dari raw database column. Dekripsi hanya dilakukan di memory pada saat client Gate membutuhkan credential. Field secret kosong pada update berarti mempertahankan secret lama; rotasi menghasilkan event audit tanpa nilainya.

Guard aktivasi menegakkan HTTPS (kecuali localhost pada local/testing), callback canonical `/auth/gate/callback`, scope `openid`, endpoint non-placeholder, Client ID, dan secret. Login lokal tetap tersedia untuk pemulihan jika SSO salah konfigurasi atau provider tidak tersedia.

## 9. Audit Trail

Semua kejadian keamanan menghasilkan audit log:

| Event | Action |
|---|---|
| Login dimulai | `gate_login.initiated` |
| Login gagal (provider error) | `gate_login.failed` |
| Login gagal (state mismatch) | `gate_login.failed` |
| Login gagal (token exchange) | `gate_login.failed` |
| Akses ditolak (entitlement) | `gate_login.access_denied` |
| Login berhasil | `gate_login.succeeded` |
| Logout | `gate_logout` |
| Proyeksi identitas dibuat | `gate_user.projection_created` |
| Proyeksi identitas diperbarui | `gate_user.projection_updated` |
| Konfigurasi SSO diperbarui | `SSO_CONFIGURATION_UPDATED` |
| Client secret disimpan/dirotasi | `SSO_CLIENT_SECRET_ROTATED` |
| Konfigurasi SSO direset | `SSO_CONFIGURATION_RESET` |
| Sync apply dimulai | `gate_sync.apply_started` |
| Sync item diterapkan | `gate_sync.item_applied` |
| Sync selesai | `gate_sync.completed` |
| Mapping disetujui | `gate_mapping.approved` |
| Mapping ditolak | `gate_mapping.rejected` |

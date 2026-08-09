---
id: DOC-DELIVERY-PHASE-4A-AUTH-PREFLIGHT
title: "Phase 4A Authentication Preflight & Architecture Gap Audit"
status: approved
phase: "4A"
created_at: 2026-08-09
owner: "Ryand Arifriantoni"
---

# Laporan Audit Pra-Penerbangan Autentikasi (Phase 4A Auth Preflight)

## 1. Status Autentikasi Eksisting (Current State)

- **Route `/login`**: Saat ini berupa stub respons teks sederhana (`Route::get('/login', fn () => response('Halaman login belum diimplementasikan.', 200))`).
- **Session & Guard**: Menggunakan web session guard default Laravel.
- **Model Pengguna (`User`)**: Berelasi dengan `Person` (1-to-1) melalui `person_id`, memiliki peran/izin Spatie-style (`roles`, `permissions`), dan flag status `is_active`.
- **Model Person (`Person`)**: Menyimpan `gate_user_id` (unique index), NIK, NIS/NIP, metadata versi sumber, checksum, dan metode `isHumanPatientEligible()`.
- **Gate Client**: Saat ini terikat ke `FakeGateClientService` via `AppServiceProvider`.
- **Sync Workflow**: `GateSyncDryRunService` telah memiliki logika klasifikasi (`new`, `changed`, `deactivated`, `unchanged`, `conflict`, `invalid_payload`), namun belum memiliki eksekusi *secure apply* transaksi nyata.

## 2. Alur Sasaran Gate SSO & Application Entitlement (Target Architecture)

```
[ Pengguna ] ──> GET /login ──> Redirect Gate OIDC / Authorization Endpoint
                                        │
                                        ▼ (OIDC / OAuth2 Flow)
                               [ Gate SSO Provider ]
                                        │
[ Pengguna ] <── Redirect Callback ──────┘ (code, state, PKCE verifier)
      │
      ▼
[ GateOidcController@callback ]
  ├── 1. Validasi State / Nonce / Replay Protection
  ├── 2. Tukar Code -> Access Token & ID Token (Signature, Audience, Expiry Validation)
  ├── 3. Ambil UserInfo & Application Entitlement (`poskestren-health`)
  ├── 4. Evaluasi Entitlement:
  │      - Jika 'allowed' -> Lanjut
  │      - Jika 'revoked' / 'not_assigned' -> Tolak login, catat audit, tampilkan access denied
  ├── 5. Proyeksi Identitas Person & User (Idempotent by gate_user_id, NO medical overwrite)
  ├── 6. Login Session Laravel (Regenerate Session ID, Cegah Fixation)
  ├── 7. Catat Audit Log (`GateLoginSucceeded`)
  └── 8. Redirect ke Dashboard
```

## 3. Rencana Migrasi & Strategi Rollback

1. **Konfigurasi Lingkungan (`config/gate.php`)**:
   - Menambahkan variabel konfigurasi OIDC (`GATE_BASE_URL`, `GATE_CLIENT_ID`, `GATE_CLIENT_SECRET`, `GATE_REDIRECT_URI`, `GATE_SCOPES`, `GATE_APP_CODE`, `GATE_SSO_ENABLED`, `GATE_SYNC_APPLY_ENABLED`).
2. **Abstraksi Kontrak & Adapter**:
   - `GateOidcClientContract` diimplementasikan oleh `HttpGateOidcClient` untuk runtime production dan `FakeGateOidcClient` untuk testing.
3. **Pemisahan Peran & Otorisasi Klinis**:
   - Klaim role dari Gate dimetakan secara eksplisit melalui config `gate.role_mapping`. Peran `admin` dari Gate **TIDAK PERNAH** otomatis memberikan izin rekam medis klinis (`view-medical-record` dll).
4. **Strategi Rollback**:
   - Seluruh mutasi identitas menggunakan transaksi database. Jika token exchange atau evaluasi entitlement gagal, transaksi di-rollback penuh dan sesi pengguna tidak terbentuk.

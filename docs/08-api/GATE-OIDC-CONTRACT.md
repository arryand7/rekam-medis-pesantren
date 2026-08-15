---
id: DOC-GATE-OIDC-CONTRACT
title: "Gate OIDC/OAuth2 Contract Specification"
status: active
owner: "Ryand Arifriantoni"
last_updated: 2026-08-09
---

# Gate OIDC / OAuth2 Contract Specification

## 1. Ikhtisar

SABIRA POSKESTREN Health menggunakan Gate sebagai Identity Provider (IdP) melalui protokol OAuth2 Authorization Code Flow. Sistem ini mengandalkan Gate untuk autentikasi pengguna dan verifikasi hak akses (application entitlement) ke aplikasi POSKESTREN Health.

## 2. Endpoints

| Endpoint | Method | URL | Deskripsi |
|---|---|---|---|
| Authorization | GET | `{GATE_BASE_URL}/oauth/authorize` | Redirect pengguna ke halaman login Gate |
| Token Exchange | POST | `{GATE_BASE_URL}/oauth/token` | Menukar authorization code dengan access/ID token |
| UserInfo | GET | `{GATE_BASE_URL}/oauth/userinfo` | Mengambil profil identitas pengguna terautentikasi |
| Application Entitlement | GET | `{GATE_BASE_URL}/api/v1/entitlements` | Memeriksa hak akses aplikasi per pengguna |
| End Session | GET | `{GATE_BASE_URL}/oauth/logout` | Mengakhiri sesi Gate (opsional) |
| Health | GET | `{GATE_BASE_URL}/health` | Probe status ketersediaan Gate |

## 3. Authorization Flow

```text
Browser                POSKESTREN                  Gate IdP
  |                       |                           |
  |--- GET /login ------->|                           |
  |                       |--- Generate state/nonce -->|
  |                       |   Store in session         |
  |<-- 302 to Gate auth --|                           |
  |                       |                           |
  |--- User login --------|-------------------------->|
  |                       |                           |
  |<-- 302 callback ------|<-- code + state ----------|
  |                       |                           |
  |                       |--- POST /oauth/token ----->|
  |                       |<-- access_token + id_token-|
  |                       |                           |
  |                       |--- GET /oauth/userinfo --->|
  |                       |<-- user profile -----------|
  |                       |                           |
  |                       |--- GET /api/v1/entitlements|
  |                       |<-- entitlement status -----|
  |                       |                           |
  |                       |--- Project identity ------>|
  |                       |   Person / User / Patient  |
  |                       |--- Auth::login ----------->|
  |                       |   Session regeneration     |
  |<-- 302 to dashboard --|                           |
```

## 4. Request Parameters

### 4.1 Authorization Request

| Parameter | Nilai | Keterangan |
|---|---|---|
| `response_type` | `code` | Authorization Code Flow |
| `client_id` | `{GATE_CLIENT_ID}` | Dari `config/gate.php` |
| `redirect_uri` | `{GATE_REDIRECT_URI}` | URL callback lokal |
| `scope` | `openid profile email phone offline_access poskestren_access` | Scope yang diminta |
| `state` | Random 40 karakter | CSRF protection, validasi di callback |
| `nonce` | Random 40 karakter | Replay protection (disimpan di session) |

### 4.2 Token Exchange Request

| Parameter | Nilai |
|---|---|
| `grant_type` | `authorization_code` |
| `code` | Authorization code dari callback |
| `client_id` | `{GATE_CLIENT_ID}` |
| `client_secret` | `{GATE_CLIENT_SECRET}` |
| `redirect_uri` | `{GATE_REDIRECT_URI}` |

## 5. Data Transfer Objects

### 5.1 GateOidcTokenResponseDTO

| Field | Type | Keterangan |
|---|---|---|
| `accessToken` | string | Bearer token untuk API calls |
| `idToken` | string | Identity token (untuk end-session) |
| `tokenType` | string | Selalu `Bearer` |
| `expiresIn` | int | Masa berlaku dalam detik |
| `refreshToken` | ?string | Refresh token opsional |
| `scope` | ?string | Scope yang disetujui |

### 5.2 GateUserInfoDTO

| Field | Type | Keterangan |
|---|---|---|
| `gateUserId` | string | Identifier unik stabil dari Gate |
| `name` | string | Nama lengkap pengguna |
| `email` | ?string | Alamat email |
| `phone` | ?string | Nomor telepon |
| `nik` | ?string | NIK (Nomor Induk Kependudukan) |
| `nisNip` | ?string | NIS/NIP sesuai kontrak Gate |
| `userType` | string | Tipe pengguna: `santri`, `tenaga_kesehatan`, `staf`, `pengajar`, `service_account`, `bot` |
| `gender` | ?string | Jenis kelamin (dinormalisasi ke L/P) |
| `sourceStatus` | string | Status sumber: `active`, `deactivated`, `suspended` |
| `appRoles` | array | Role claims dari Gate |
| `checksum` | ?string | Checksum untuk deteksi perubahan |
| `sourceVersion` | ?string | Versi data sumber |
| `sourceUpdatedAt` | ?string | Timestamp update sumber |

### 5.3 GateApplicationEntitlementDTO

| Field | Type | Keterangan |
|---|---|---|
| `gateUserId` | string | Gate user identifier |
| `appCode` | string | Kode aplikasi (`poskestren-health`) |
| `status` | string | `allowed`, `revoked`, `suspended`, `not_assigned` |
| `roles` | array | Role tambahan dari entitlement |

## 6. Environment Configuration

| Variable | Default | Keterangan |
|---|---|---|
| `GATE_BASE_URL` | `https://gate.example.invalid` | Base URL Gate server |
| `GATE_CLIENT_ID` | *(kosong)* | OAuth2 Client ID, wajib diisi per environment |
| `GATE_CLIENT_SECRET` | *(kosong)* | OAuth2 Client Secret |
| `GATE_REDIRECT_URI` | `{APP_URL}/auth/gate/callback` | Callback URL |
| `GATE_SCOPES` | `openid profile email phone offline_access poskestren_access` | Scope |
| `GATE_APP_CODE` | `poskestren-health` | Kode aplikasi untuk entitlement |
| `GATE_SSO_ENABLED` | `false` | Flag SSO aktif |
| `GATE_CLIENT_DRIVER` | `fake` | Driver: `fake` atau `http` |

> [!CAUTION]
> JANGAN commit `GATE_CLIENT_SECRET` atau token ke repositori. Simpan di `.env` lokal dan secret manager.

## 7. Client Implementations

| Class | Driver | Digunakan Pada |
|---|---|---|
| `FakeGateOidcClient` | `fake` | Testing dan development |
| `HttpGateOidcClient` | `http` | Production |
| `FakeGateClientService` | `fake` | Testing sync |
| `HttpGateClient` | `http` | Production sync |

Binding dikelola di `AppServiceProvider::register()` berdasarkan `config('gate.driver')`.

## 8. Error Handling

| Kondisi | Respons |
|---|---|
| Gate mengembalikan `error` parameter | Redirect ke login dengan pesan error yang di-sanitize |
| State validation gagal | Redirect ke login, audit log sebagai CSRF/replay risk |
| Authorization code kosong | Redirect ke login |
| Token exchange gagal | Redirect ke login, exception message di-audit (bukan di-expose ke user) |
| Entitlement `not_assigned`/`revoked`/`suspended` | Redirect ke access-denied page |

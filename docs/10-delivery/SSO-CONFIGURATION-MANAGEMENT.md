---
id: DOC-SSO-CONFIG-MANAGEMENT
title: "Super Admin SSO Configuration Management"
status: implemented-local
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-15
---

# Super Admin SSO Configuration Management

## Tujuan

Menghilangkan kebutuhan konfigurasi OIDC Gate melalui variabel `.env` khusus SSO. Super Admin mengelola konfigurasi dari UI, sedangkan aplikasi tetap memiliki fallback source-controlled yang aman: SSO nonaktif, fake driver, endpoint `.invalid`, dan secret kosong.

## Arsitektur

```text
Super Admin form
  -> server-side validation + exact-role authorization
  -> singleton sso_configurations (client_secret encrypted)
  -> SsoConfigurationService (cached ciphertext + immediate invalidation)
  -> OIDC/auth/sync/health consumers
```

Client secret tidak pernah dikirim kembali ke browser. Input kosong mempertahankan secret; reset menghapus row dan ciphertext. Audit mencatat actor, field aman yang berubah, dan status secret tanpa plaintext.

## Fail-closed activation

SSO hanya dapat aktif jika driver `http`, endpoint bukan placeholder, transport aman, Client ID/secret tersedia, callback menggunakan `/auth/gate/callback`, scopes memuat `openid`, dan application code terisi. Login lokal tetap tersedia sebagai recovery path.

## Scope implementasi

- Route: `GET|PUT /admin/system/sso-configuration`, `POST .../reset`.
- Authorization: exact role `super_admin`; delegated admin dengan `manage-system-settings` tetap 403.
- Fields: enabled, driver, base URL, client ID/secret, redirect URI, scopes, app code, timeout, retry, backoff, dan entitlement TTL.
- Consumers: login/button/callback/logout, Gate OIDC client, Gate sync client/dashboard, dan readiness metadata.
- Tidak ada staging/production deployment atau panggilan provider nyata pada implementasi lokal ini.

## Manual staging acceptance

Masih diperlukan nilai resmi provider, callback registration, issuer/audience/JWKS validation contract, entitlement behavior, logout, error handling, serta browser Light/Dark/System dan responsive smoke pada staging.

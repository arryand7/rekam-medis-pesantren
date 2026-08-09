---
id: DOC-GATE-LOGIN-AND-ACCESS
title: "Alur Login Gate dan Kontrol Akses"
status: active
owner: "Ryand Arifriantoni"
last_updated: 2026-08-09
---

# Alur Login Gate dan Kontrol Akses

## 1. Alur Login Gate SSO

### 1.1 Prasyarat

- Gate SSO dikonfigurasi di `config/gate.php`
- Feature flag `GATE_SSO_ENABLED` diset `true` untuk production
- `GATE_CLIENT_DRIVER` diset `http` untuk production
- Client ID dan Client Secret terkonfigurasi di `.env`

### 1.2 Langkah-langkah Login

```mermaid
sequenceDiagram
    participant B as Browser
    participant P as POSKESTREN
    participant G as Gate IdP

    B->>P: GET /login
    alt SSO Enabled atau ?redirect=1
        P->>P: Generate state + nonce
        P->>P: Simpan di session
        P->>B: 302 → Gate /oauth/authorize
        B->>G: User login di Gate
        G->>B: 302 → /auth/gate/callback?code=...&state=...
        B->>P: GET /auth/gate/callback
        P->>P: Validasi state (hash_equals)
        P->>G: POST /oauth/token (code exchange)
        G->>P: access_token + id_token
        P->>G: GET /oauth/userinfo
        G->>P: User profile (GateUserInfoDTO)
        P->>G: GET /api/v1/entitlements
        G->>P: Entitlement status
        alt Entitlement: allowed
            P->>P: Proyeksi identitas (Person/User/Patient)
            P->>P: Auth::login + session regeneration
            P->>B: 302 → Dashboard
        else Entitlement: denied
            P->>P: Audit log access_denied
            P->>B: 302 → /auth/gate/access-denied
        end
    else SSO Disabled
        P->>B: Halaman login.blade.php (informasi konfigurasi)
    end
```

### 1.3 Error Handling

| Kondisi | Aksi |
|---|---|
| Gate mengembalikan error param | Audit log + redirect ke login |
| State mismatch | Audit log (CSRF risk) + redirect ke login |
| Code kosong | Redirect ke login |
| Token exchange gagal | Audit log + redirect ke login (pesan sanitized) |
| Entitlement ditolak | Audit log + redirect ke access-denied |

## 2. Alur Logout

```mermaid
sequenceDiagram
    participant B as Browser
    participant P as POSKESTREN
    participant G as Gate IdP

    B->>P: POST /logout
    P->>P: Audit log gate_logout
    P->>P: Auth::logout()
    P->>P: Session::invalidate()
    P->>P: Session::regenerateToken() (CSRF)
    alt Gate end-session URL tersedia
        P->>B: 302 → Gate /oauth/logout
    else
        P->>B: 302 → /login
    end
```

## 3. Alur Proyeksi Identitas

### 3.1 Login-time Projection

Setiap kali pengguna login melalui Gate, sistem memproyeksikan identitas Gate ke model lokal:

```mermaid
flowchart TD
    A[UserInfo dari Gate] --> B{Person dengan gate_user_id ada?}
    B -->|Ya| D[Lock Person, update authoritative fields]
    B -->|Tidak| C{Approved mapping ada?}
    C -->|Ya| D
    C -->|Tidak| E[Buat Person baru]
    E --> D
    D --> F{User dengan person_id ada?}
    F -->|Ya| G[Update User name/email/is_active]
    F -->|Tidak| H{User dengan email ada?}
    H -->|Ya| G
    H -->|Tidak| I[Buat User baru, random password]
    I --> G
    G --> J{Person human-eligible?}
    J -->|Ya| K{Patient ada?}
    K -->|Ya| L[Selesai]
    K -->|Tidak| M[Buat Patient baru]
    M --> L
    J -->|Tidak| L
```

### 3.2 Fields yang Di-update (Authoritative)

- `name`, `nik`, `nis_nip`, `email`, `phone`, `gender`
- `user_type`, `source_status`, `checksum`, `source_version`
- `source_updated_at`, `synced_at`

### 3.3 Fields yang TIDAK PERNAH Di-update

- Allergies, medical conditions, health profile
- Medical visits, observations, vital signs, assessments
- Medication orders, referrals, discharges, follow-ups

## 4. Alur Sync Apply

### 4.1 Dry-Run (Preview)

```mermaid
flowchart LR
    A[Admin klik Dry-Run] --> B[Fetch users dari Gate API]
    B --> C[Klasifikasi: new/matched/changed/unchanged/deactivated]
    C --> D[Tampilkan preview, ZERO mutation database]
```

### 4.2 Apply (Eksekusi)

```mermaid
flowchart TD
    A[Admin klik Apply] --> B[Buat GateSyncRun record]
    B --> C[Fetch users dari Gate API]
    C --> D[Untuk setiap user]
    D --> E{Validasi payload}
    E -->|Invalid| F[Catat sebagai failed]
    E -->|Valid| G{Person match?}
    G -->|gate_user_id| H[Lock + update]
    G -->|approved mapping| H
    G -->|NIS/NIK candidate| I[Conflict, pending mapping]
    G -->|Tidak ada| J[Create new Person/User/Patient]
    H --> K{Checksum sama?}
    K -->|Ya| L[unchanged]
    K -->|Tidak| M[Update authoritative fields]
    M --> N{source_status}
    N -->|active| O[changed]
    N -->|deactivated| P[deactivated, is_active=false]
    J --> Q[new]
    F --> R[Update GateSyncRun summary]
    I --> R
    L --> R
    O --> R
    P --> R
    Q --> R
```

### 4.3 Otorisasi Sync

| Aksi | Permission | Policy |
|---|---|---|
| View sync dashboard | `view-gate-sync` | `GateSyncPolicy` |
| Execute dry-run | `view-gate-sync` | `GateSyncPolicy` |
| Execute apply | `execute-gate-sync-apply` | `GateSyncPolicy` |
| View run detail | `view-gate-sync` | `GateSyncPolicy` |

## 5. Alur Rekonsiliasi

### 5.1 Identifikasi Konflik

Saat sync apply menemukan NIS/NIP/NIK match tanpa `gate_user_id`:
1. Record `gate_identity_mappings` dibuat dengan `status = 'pending'`
2. Muncul di halaman rekonsiliasi
3. Admin meninjau dan menyetujui/menolak

### 5.2 Approval Flow

```mermaid
flowchart LR
    A[Mapping pending] --> B{Admin review}
    B -->|Approve| C[Status → approved, Person.gate_user_id diset]
    B -->|Reject| D[Status → rejected, tidak ada perubahan]
    C --> E[Audit log]
    D --> E
```

### 5.3 Otorisasi Rekonsiliasi

| Aksi | Permission |
|---|---|
| View reconciliation | `view-gate-reconciliation` |
| Approve mapping | `manage-identity-mappings` |
| Reject mapping | `manage-identity-mappings` |

## 6. Kontrol Akses Entitlement

### 6.1 Login-time

```text
Gate Entitlement Status → Aksi POSKESTREN
─────────────────────────────────────────
allowed       → Login diizinkan
revoked       → Access denied, audit log
suspended     → Access denied, audit log
not_assigned  → Access denied, audit log
unknown       → Default deny
```

### 6.2 Runtime

Middleware `EnforceGateApplicationEntitlement` memeriksa `user->is_active` pada setiap request:
- `is_active = false` → force logout + redirect ke login

### 6.3 Pemisahan Entitlement dan Permission Klinis

> [!IMPORTANT]
> **Entitlement** menentukan: boleh/tidak mengakses aplikasi POSKESTREN.
> **Permission klinis** ditentukan terpisah melalui role lokal dan permission server-side.
> Gate admin role **TIDAK** memberikan akses ke data medis.

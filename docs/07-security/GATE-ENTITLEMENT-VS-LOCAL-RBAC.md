---
id: DOC-GATE-ENTITLEMENT-VS-RBAC
title: "Gate Entitlement versus Local RBAC"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-15
---

# Gate Entitlement versus Local RBAC

Gate menjawab **siapa orangnya, tipe/status akun, dan apakah aplikasi ini boleh diakses**. Local RBAC menjawab **capability apa yang boleh dilakukan setelah masuk**.

```text
Gate identity + active entitlement
            |
            v
local User/Person projection ----> local Role + Permission ----> Policy/Gate
```

- Entitlement allowed tidak otomatis menjadikan admin atau pasien.
- Permission admin tidak menentukan apakah manusia boleh mempunyai patient profile.
- Form lokal tidak boleh mengubah `gate_user_id` atau field authoritative Gate.
- Sync tidak menghapus local roles/direct permission secara diam-diam.
- Revocation/deactivation menutup login tanpa menghapus person, patient atau history.
- Konflik identity diparkir untuk reconciliation dan audit, bukan overwrite.

Kontrak mapping claim/entitlement nyata harus diverifikasi di Gate staging checklist.

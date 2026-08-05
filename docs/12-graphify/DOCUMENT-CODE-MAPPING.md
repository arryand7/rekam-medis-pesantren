---
id: DOC-DOC-CODE-MAP
title: "Pemetaan Dokumen ke Kode"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-05
---

# Pemetaan Dokumen ke Kode

## Phase 1 — Identity, Access Control, Gate Contract & Dry-Run Sync Mapping

```text
ADR-006 (Person Patient Separation) & PERSON-PATIENT-IDENTITY.md
  -> IDENTITY-AND-PATIENT-MODEL.md
  -> GATE-USER-SYNC-CONTRACT.md
  -> ACCESS-CONTROL-MATRIX.md & AUDIT-LOG.md
  -> database/migrations/0000_01_01_000000_create_people_table.php
  -> database/migrations/0001_01_01_000000_create_users_table.php
  -> database/migrations/2026_08_05_000300_create_patients_table.php
  -> database/migrations/2026_08_05_000400_create_roles_and_permissions_tables.php
  -> database/migrations/2026_08_05_000500_create_audit_logs_table.php
  -> app/Models/Person.php
  -> app/Models/User.php
  -> app/Models/Patient.php
  -> app/Models/Role.php
  -> app/Models/Permission.php
  -> app/Models/AuditLog.php
  -> app/Policies/UserPolicy.php
  -> app/Policies/PersonPolicy.php
  -> app/Policies/PatientPolicy.php
  -> app/Policies/GateSyncPolicy.php
  -> app/Policies/AuditLogPolicy.php
  -> app/Services/AuditLogService.php
  -> app/Contracts/GateClientContract.php
  -> app/DTOs/GateUserDTO.php
  -> app/Services/Gate/FakeGateClientService.php
  -> app/Services/Gate/GateSyncDryRunService.php
  -> tests/Feature/Identity/PersonUserPatientTest.php
  -> tests/Feature/Authorization/PolicyAccessTest.php
  -> tests/Feature/GateSync/DryRunSyncTest.php
  -> tests/Feature/Audit/AuditLogTest.php
```

## Phase 0 — Foundation & Theme Engine Mapping

```text
ADR-005 (Theme Strategy) & LIGHT-DARK-THEME.md
  -> DESIGN-SYSTEM.md
  -> resources/css/app.css (Semantic Tokens)
  -> resources/js/theme-switcher.js (Anti-Flicker & Persistence)
  -> resources/views/components/theme-switcher.blade.php
  -> resources/views/layouts/app.blade.php (App Shell)
  -> app/View/Components/AppLayout.php
  -> app/Http/Controllers/HealthController.php
  -> tests/Feature/ThemePreferenceTest.php
  -> tests/Feature/DashboardTest.php
  -> tests/Feature/HealthCheckTest.php
```

## Gate mapping

```text
BR-031
  -> FR-GATE-001
  -> WF-GATE-SYNC-001
  -> GateSyncDryRunService
  -> Person
  -> Patient
  -> GateUserDTO
  -> DryRunSyncTest
```

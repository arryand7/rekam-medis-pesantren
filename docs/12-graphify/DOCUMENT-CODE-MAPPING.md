---
id: DOC-DOC-CODE-MAP
title: "Pemetaan Dokumen ke Kode"
status: active
owner: "Ryand Arifriantoni"
last_updated: 2026-08-09
---

# Pemetaan Dokumen ke Kode

## Production Auth Hotfix & Protection Hardening Mapping

```text
PRODUCTION-AUTH-HOTFIX-ROLLOUT.md & PRODUCTION-AUTH-HOTFIX-VERIFICATION.md & PRODUCTION-AUTH-EXPOSURE-REVIEW.md & PRODUCTION-AUTH-RUNTIME-INCIDENT.md
  -> app/Http/Controllers/Dashboard/DashboardController.php (role-aware index method)
  -> app/Providers/AppServiceProvider.php (typed Gate::before local permission hook)
  -> routes/web.php (Route::middleware('auth') complete group & route-level Gate::authorize)
  -> tests/Feature/Auth/AuthenticationRuntimeAuditAndProtectionTest.php
```

## Phase 4C2 — Controlled Production Cutover, Canary Activation, Post-Go-Live Validation & Rollback Guard Mapping


```text
PHASE-4C2-CUTOVER-EXECUTION.md & PHASE-4C2-POST-CUTOVER-UAT.md & PHASE-4C2-FINAL-STATUS.md
  -> app/Http/Controllers/HealthController.php
  -> app/Services/Integration/HttpAttendanceSandboxIntegration.php (public assertPayloadCompliant)
  -> tests/Feature/Production/Phase4C2ProductionCutoverTest.php
  -> docs/10-delivery/PHASE-4C-DEPLOYMENT-RUNBOOK.md
  -> docs/10-delivery/INCIDENT-ROLLBACK-RUNBOOK.md
  -> docs/10-delivery/PRODUCTION-GO-LIVE-CHECKLIST.md
```


## Phase 4C — Production Deployment Hardening, Controlled Cutover, Rollback, Observability, and Go-Live Validation Mapping


```text
PHASE-4C-PRODUCTION-PREFLIGHT.md & PHASE-4C-BACKUP-AND-ROLLBACK.md & PHASE-4C-DEPLOYMENT-RUNBOOK.md & PHASE-4C-PRODUCTION-UAT.md & PHASE-4C-CLOSURE.md & PRODUCTION-GO-LIVE-CHECKLIST.md & INCIDENT-ROLLBACK-RUNBOOK.md
  -> app/Http/Controllers/HealthController.php (liveness /health & readiness /health/ready endpoints)
  -> config/filesystems.php (private document disks hardening)
  -> routes/web.php (health.ready route)
  -> tests/Feature/HealthCheckTest.php (liveness & readiness feature test suite)
```

## Phase 4B — Staging Integration, End-to-End UAT, Gate SSO Activation & Attendance Sandbox Mapping


```text
PHASE-4B-STAGING-PREFLIGHT.md & PHASE-4B-GATE-SSO-UAT.md & PHASE-4B-UAT-EVIDENCE.md & PHASE-4B-CLOSURE.md
  -> app/Models/Patient.php (collision hardening methods)
  -> app/Services/Integration/HttpAttendanceSandboxIntegration.php
  -> config/integration.php (sandbox & http drivers)
  -> app/Providers/AppServiceProvider.php (driver binding & explicit policy registration)
  -> app/Policies/ClinicalOperationalHandoffPolicy.php (acknowledge permission visibility)
  -> tests/Feature/Patient/PatientNumberCollisionHardeningTest.php
  -> tests/Feature/Integration/AttendanceSandboxIntegrationTest.php
  -> tests/Feature/UAT/Phase4BEndToEndUatTest.php
  -> tests/Feature/Integration/IntegrationOutboxFailureAndRetryTest.php
  -> tests/Feature/UAT/Phase4BRoleMatrixPrivacyUatTest.php
```

## Phase 4A — Gate SSO, Secure Sync Apply, Application Entitlement & Identity Hardening Mapping


```text
GATE-OIDC-CONTRACT.md & GATE-SSO-SECURITY.md & GATE-LOGIN-AND-ACCESS.md & ACCESS-CONTROL-MATRIX.md
  -> config/gate.php
  -> database/migrations/2026_08_05_005300_create_gate_identity_mappings_table.php
  -> database/migrations/2026_08_05_005400_create_gate_sync_runs_table.php
  -> database/migrations/2026_08_05_005500_add_phase_4a_permissions.php
  -> app/Contracts/GateOidcClientContract.php
  -> app/DTOs/GateOidcTokenResponseDTO.php
  -> app/DTOs/GateUserInfoDTO.php
  -> app/DTOs/GateApplicationEntitlementDTO.php
  -> app/Models/GateIdentityMapping.php
  -> app/Models/GateSyncRun.php
  -> app/Services/Gate/FakeGateOidcClient.php
  -> app/Services/Gate/HttpGateOidcClient.php
  -> app/Services/Gate/HttpGateClient.php
  -> app/Services/Gate/GateAuthenticationService.php
  -> app/Services/Gate/GateSyncApplyService.php
  -> app/Services/Gate/GateIdentityReconciliationService.php
  -> app/Http/Middleware/EnforceGateApplicationEntitlement.php
  -> app/Http/Controllers/Auth/GateOidcAuthController.php
  -> app/Http/Controllers/Gate/GateSyncController.php
  -> app/Http/Controllers/Gate/GateReconciliationController.php
  -> app/Http/Requests/Gate/ApplyGateSyncRequest.php
  -> app/Http/Requests/Gate/ApproveIdentityMappingRequest.php
  -> app/Policies/GateSyncPolicy.php
  -> app/Policies/GateMappingPolicy.php
  -> app/View/Components/GuestLayout.php
  -> resources/views/layouts/guest.blade.php
  -> resources/views/pages/auth/login.blade.php
  -> resources/views/pages/auth/access-denied.blade.php
  -> resources/views/pages/gate/sync.blade.php
  -> resources/views/pages/gate/dry-run-preview.blade.php
  -> resources/views/pages/gate/run-detail.blade.php
  -> resources/views/pages/gate/reconciliation.blade.php
  -> tests/Feature/Auth/GateSsoAuthenticationTest.php
  -> tests/Feature/Auth/GateApplicationEntitlementTest.php
  -> tests/Feature/Gate/GateIdentityProjectionTest.php
  -> tests/Feature/Gate/GateSyncApplyTest.php
  -> tests/Feature/Gate/GateMariaDBSyncConcurrencyTest.php
  -> tests/Feature/Gate/GateReconciliationTest.php
```

## Phase 3C1 — Visit Discharge, Follow-Up, Return-to-Activity & Operational Handoff Mapping


```text
DISCHARGE-AND-RETURN.md & VISIT-STATUS-LIFECYCLE.md & ACCESS-CONTROL-MATRIX.md
  -> database/migrations/2026_08_05_004100_create_visit_discharges_table.php
  -> database/migrations/2026_08_05_004200_create_visit_discharge_versions_table.php
  -> database/migrations/2026_08_05_004300_create_visit_follow_up_plans_table.php
  -> database/migrations/2026_08_05_004400_create_activity_restrictions_table.php
  -> database/migrations/2026_08_05_004500_create_clinical_operational_handoffs_table.php
  -> database/migrations/2026_08_05_004600_add_phase_3c1_permissions.php
  -> app/Models/VisitDischarge.php
  -> app/Models/VisitDischargeVersion.php
  -> app/Models/VisitFollowUpPlan.php
  -> app/Models/ActivityRestriction.php
  -> app/Models/ClinicalOperationalHandoff.php
  -> app/Actions/Discharge/EvaluateVisitDischargeReadinessAction.php
  -> app/Services/VisitDischargeService.php
  -> app/Services/VisitDischargeDocumentService.php
  -> app/Policies/VisitDischargePolicy.php
  -> app/Policies/VisitFollowUpPlanPolicy.php
  -> app/Policies/ActivityRestrictionPolicy.php
  -> app/Policies/ClinicalOperationalHandoffPolicy.php
  -> app/Http/Controllers/Discharge/VisitDischargeController.php
  -> app/Http/Controllers/Discharge/VisitFollowUpPlanController.php
  -> app/Http/Controllers/Discharge/ActivityRestrictionController.php
  -> app/Http/Controllers/Discharge/ClinicalOperationalHandoffController.php
  -> app/Http/Controllers/Discharge/VisitDischargeDocumentController.php
  -> app/Http/Requests/Discharge/StoreVisitDischargeRequest.php
  -> app/Http/Requests/Discharge/FinalizeVisitDischargeRequest.php
  -> app/Http/Requests/Discharge/AmendVisitDischargeRequest.php
  -> app/Http/Requests/Discharge/StoreFollowUpPlanRequest.php
  -> app/Http/Requests/Discharge/StoreActivityRestrictionRequest.php
  -> app/Http/Requests/Discharge/StoreOperationalHandoffRequest.php
  -> app/Http/Requests/Discharge/AcknowledgeOperationalHandoffRequest.php
  -> resources/views/pages/discharges/workspace.blade.php
  -> resources/views/pages/discharges/index.blade.php
  -> resources/views/pages/discharges/show.blade.php
  -> resources/views/pages/discharges/handoffs.blade.php
  -> resources/views/pages/discharges/follow-ups.blade.php
  -> tests/Feature/Discharge/VisitDischargeReadinessTest.php
  -> tests/Feature/Discharge/VisitDischargeCreationAndFinalizationTest.php
  -> tests/Feature/Discharge/VisitFollowUpPlanTest.php
  -> tests/Feature/Discharge/ActivityRestrictionTest.php
  -> tests/Feature/Discharge/ClinicalOperationalHandoffTest.php
  -> tests/Feature/Discharge/VisitDischargeDocumentTest.php
  -> tests/Feature/Discharge/VisitDischargeAuthorizationTest.php
```

## Phase 3B — Hospital Referral Execution, Transportation, Handover & Hardening Mapping


```text
HOSPITAL-REFERRAL.md & STATE-MACHINES.md & ACCESS-CONTROL-MATRIX.md
  -> database/migrations/2026_08_05_003300_create_referrals_table.php
  -> database/migrations/2026_08_05_003400_create_referral_versions_table.php
  -> database/migrations/2026_08_05_003500_create_referral_transports_table.php
  -> database/migrations/2026_08_05_003600_create_referral_companions_table.php
  -> database/migrations/2026_08_05_003700_create_referral_handovers_table.php
  -> database/migrations/2026_08_05_003800_create_referral_returns_table.php
  -> database/migrations/2026_08_05_003900_create_referral_return_reviews_table.php
  -> database/migrations/2026_08_05_004000_create_referral_status_events_table.php
  -> app/Models/Referral.php
  -> app/Models/ReferralVersion.php
  -> app/Models/ReferralTransport.php
  -> app/Models/ReferralCompanion.php
  -> app/Models/ReferralHandover.php
  -> app/Models/ReferralReturn.php
  -> app/Models/ReferralReturnReview.php
  -> app/Models/ReferralStatusEvent.php
  -> app/Services/ReferralService.php
  -> app/Services/ReferralDocumentService.php
  -> app/Policies/ReferralPolicy.php
  -> app/Http/Controllers/Referral/ReferralController.php
  -> app/Http/Controllers/Referral/ReferralTransportController.php
  -> app/Http/Controllers/Referral/ReferralCompanionController.php
  -> app/Http/Controllers/Referral/ReferralDepartureController.php
  -> app/Http/Controllers/Referral/ReferralHandoverController.php
  -> app/Http/Controllers/Referral/ReferralStatusController.php
  -> app/Http/Controllers/Referral/ReferralReturnController.php
  -> app/Http/Controllers/Referral/ReferralReturnReviewController.php
  -> app/Http/Controllers/Referral/ReferralDocumentController.php
  -> app/Http/Requests/Referral/StoreReferralRequest.php
  -> app/Http/Requests/Referral/StoreReferralTransportRequest.php
  -> app/Http/Requests/Referral/StoreReferralCompanionRequest.php
  -> app/Http/Requests/Referral/StoreReferralStatusEventRequest.php
  -> app/Http/Requests/Referral/StoreReferralReturnRequest.php
  -> app/Http/Requests/Referral/StoreReferralReturnReviewRequest.php
  -> resources/views/pages/referrals/index.blade.php
  -> resources/views/pages/referrals/create.blade.php
  -> resources/views/pages/referrals/show.blade.php
  -> tests/Feature/Referral/ReferralCreationTest.php
  -> tests/Feature/Referral/ReferralLogisticsTest.php
  -> tests/Feature/Referral/ReferralHandoffTest.php
  -> tests/Feature/Referral/ReferralReturnTest.php
  -> tests/Feature/Referral/ReferralAuthorizationTest.php
  -> tests/Feature/Referral/ReferralDocumentTest.php
  -> tests/Feature/Referral/ReferralMariaDBConcurrencyTest.php
  -> tests/scripts/concurrency-referral-test.sh
```

## Phase 3A — External Clinical Consultation & Healthcare Partner Integration Mapping


```text
REMOTE-CLINICAL-CONSULTATION.md & INTEGRATION-CONTRACTS.md & ADR-007
  -> database/migrations/2026_08_05_002600_create_healthcare_partners_table.php
  -> database/migrations/2026_08_05_002700_create_healthcare_partner_contacts_table.php
  -> database/migrations/2026_08_05_002800_create_clinical_consultations_table.php
  -> database/migrations/2026_08_05_002900_create_clinical_consultation_versions_table.php
  -> database/migrations/2026_08_05_003000_create_clinical_consultation_transmissions_table.php
  -> database/migrations/2026_08_05_003100_create_external_clinical_advices_table.php
  -> database/migrations/2026_08_05_003200_create_consultation_local_decisions_table.php
  -> app/Models/HealthcarePartner.php
  -> app/Models/HealthcarePartnerContact.php
  -> app/Models/ClinicalConsultation.php
  -> app/Models/ClinicalConsultationVersion.php
  -> app/Models/ClinicalConsultationTransmission.php
  -> app/Models/ExternalClinicalAdvice.php
  -> app/Models/ConsultationLocalDecision.php
  -> app/Models/MedicalVisit.php
  -> app/Contracts/ClinicalConsultationTransportContract.php
  -> app/Services/Transport/FakeClinicalConsultationTransport.php
  -> app/Services/ClinicalConsultationService.php
  -> app/Policies/HealthcarePartnerPolicy.php
  -> app/Policies/ClinicalConsultationPolicy.php
  -> resources/views/pages/healthcare-partners/index.blade.php
  -> resources/views/pages/consultations/index.blade.php
  -> resources/views/pages/consultations/create.blade.php
  -> resources/views/pages/consultations/show.blade.php
  -> tests/Feature/Consultation/HealthcarePartnerTest.php
  -> tests/Feature/Consultation/ClinicalConsultationTest.php
  -> tests/Feature/Consultation/ConsultationTransmissionTest.php
  -> tests/Feature/Consultation/ExternalAdviceAndLocalDecisionTest.php
```

## Phase 2D2 — Medication Orders, Medication Administration & Atomic Stock Issue Mapping

```text
MEDICATION-ADMINISTRATION.md & SAFETY-ACKNOWLEDGEMENTS.md & STATE-MACHINES.md
  -> database/migrations/2026_08_05_002300_create_medication_orders_table.php
  -> database/migrations/2026_08_05_002400_create_medication_safety_acknowledgements_table.php
  -> database/migrations/2026_08_05_002500_create_medication_administrations_table.php
  -> app/Models/MedicationOrder.php
  -> app/Models/MedicationSafetyAcknowledgement.php
  -> app/Models/MedicationAdministration.php
  -> app/Services/MedicationService.php
  -> app/Policies/MedicationOrderPolicy.php
  -> app/Policies/MedicationAdministrationPolicy.php
  -> resources/views/pages/visits/medications.blade.php
  -> tests/Feature/Medication/MedicationOrderTest.php
  -> tests/Feature/Medication/MedicationAdministrationTest.php
  -> tests/Feature/Medication/MedicationStatusTest.php
  -> tests/Feature/Medication/MedicationReversalTest.php
```

## Phase 2D1 — Pharmacy Inventory Foundation & Stock Ledger Mapping

```text
MEDICATION-ADMINISTRATION.md & FUNCTIONAL-REQUIREMENTS.md & DATA-DICTIONARY.md
  -> database/migrations/2026_08_05_001900_create_medicines_table.php
  -> database/migrations/2026_08_05_002000_create_stock_locations_table.php
  -> database/migrations/2026_08_05_002100_create_medicine_batches_table.php
  -> database/migrations/2026_08_05_002200_create_stock_movements_table.php
  -> app/Models/Medicine.php
  -> app/Models/StockLocation.php
  -> app/Models/MedicineBatch.php
  -> app/Models/StockMovement.php
  -> app/Services/PharmacyService.php
  -> app/Policies/MedicinePolicy.php
  -> app/Policies/MedicineBatchPolicy.php
  -> app/Policies/StockMovementPolicy.php
  -> app/Policies/StockLocationPolicy.php
  -> resources/views/pages/pharmacy/medicines/index.blade.php
  -> resources/views/pages/pharmacy/inventory/index.blade.php
  -> resources/views/pages/pharmacy/receipt/create.blade.php
  -> resources/views/pages/pharmacy/adjustments/create.blade.php
  -> tests/Feature/Pharmacy/MedicineMasterTest.php
  -> tests/Feature/Pharmacy/StockReceiptTest.php
  -> tests/Feature/Pharmacy/StockAdjustmentTest.php
  -> tests/Feature/Pharmacy/StockReversalTest.php
```

## Phase 2C — POSKESTREN Observation, Periodic Monitoring & Shift Handover Mapping

```text
OBSERVATION-AND-CARE.md & VISIT-STATUS-LIFECYCLE.md & STATE-MACHINES.md
  -> database/migrations/2026_08_05_001600_create_observation_episodes_table.php
  -> database/migrations/2026_08_05_001700_create_observation_records_table.php
  -> database/migrations/2026_08_05_001800_create_observation_handovers_table.php
  -> app/Models/ObservationEpisode.php
  -> app/Models/ObservationRecord.php
  -> app/Models/ObservationHandover.php
  -> app/Services/ObservationService.php
  -> app/Policies/ObservationEpisodePolicy.php
  -> app/Policies/ObservationRecordPolicy.php
  -> app/Policies/ObservationHandoverPolicy.php
  -> resources/views/pages/observations/index.blade.php
  -> resources/views/pages/observations/show.blade.php
  -> tests/Feature/Observation/ObservationEpisodeTest.php
  -> tests/Feature/Observation/ObservationMonitoringTest.php
  -> tests/Feature/Observation/ObservationHandoverTest.php
  -> tests/Feature/Observation/ObservationOutcomeTest.php
```

## Phase 2B — Vital Signs, Clinical Assessment & Disposition Recommendation Mapping

```text
ADR-006 & INITIAL-ASSESSMENT.md & VISIT-STATUS-LIFECYCLE.md & STATE-MACHINES.md
  -> database/migrations/2026_08_05_001200_refactor_patient_allergies_split_status_fields.php
  -> database/migrations/2026_08_05_001300_create_vital_signs_table.php
  -> database/migrations/2026_08_05_001400_create_clinical_assessments_table.php
  -> database/migrations/2026_08_05_001500_create_clinical_actions_table.php
  -> app/Models/VitalSign.php
  -> app/Models/ClinicalAssessment.php
  -> app/Models/ClinicalAction.php
  -> app/Services/VitalSignService.php
  -> app/Services/ClinicalAssessmentService.php
  -> app/Policies/VitalSignPolicy.php
  -> app/Policies/ClinicalAssessmentPolicy.php
  -> app/Policies/ClinicalActionPolicy.php
  -> resources/views/pages/visits/assessment.blade.php
  -> tests/Feature/VitalSign/VitalSignTest.php
  -> tests/Feature/ClinicalAssessment/ClinicalAssessmentTest.php
  -> tests/Feature/ClinicalAction/ClinicalActionTest.php
```

## Phase 2A — Patient Health Profile & Medical Visit Intake Mapping

```text
ADR-006 & PERSON-PATIENT-IDENTITY.md & HEALTH-PROFILES & POSKESTREN-ADMISSION.md
  -> database/migrations/2026_08_05_000600_create_patient_health_profiles_table.php
  -> database/migrations/2026_08_05_000700_create_patient_allergies_table.php
  -> database/migrations/2026_08_05_000800_create_patient_medical_conditions_table.php
  -> database/migrations/2026_08_05_000900_create_patient_emergency_contacts_table.php
  -> database/migrations/2026_08_05_001100_create_medical_visits_table.php
  -> app/Models/PatientHealthProfile.php
  -> app/Models/PatientAllergy.php
  -> app/Models/PatientMedicalCondition.php
  -> app/Models/PatientEmergencyContact.php
  -> app/Models/MedicalVisit.php
  -> app/Services/MedicalVisitService.php
  -> app/Policies/PatientHealthProfilePolicy.php
  -> app/Policies/PatientAllergyPolicy.php
  -> app/Policies/MedicalVisitPolicy.php
  -> resources/views/pages/patients/show.blade.php
  -> resources/views/pages/visits/index.blade.php
  -> resources/views/pages/visits/create.blade.php
  -> resources/views/pages/visits/show.blade.php
  -> tests/Feature/HealthProfile/PatientHealthProfileTest.php
  -> tests/Feature/MedicalVisit/MedicalVisitIntakeTest.php
```

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

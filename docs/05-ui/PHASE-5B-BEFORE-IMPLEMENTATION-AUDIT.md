---
id: DOC-UI-PHASE-5B-BEFORE-AUDIT
title: "Phase 5B Before Implementation Audit — Clinical Workflow Continuity"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-11
---

# Phase 5B Before Implementation Audit — Clinical Workflow Continuity

## 1. Executive Context & Baseline State

- **Baseline Version**: `0.19.3` (`PHASE-5A-FINAL-ACCEPTED`, commit `931f26a`)
- **Environment Reality**: `LOCAL-DEVELOPMENT` (Developer Workstation macOS, MariaDB 10.4.28)
- **Deployment Status**: `NOT_DEPLOYED` (Pre-production operational readiness validated)
- **Objective**: Unify all clinical episode stages (Intake $\rightarrow$ Vitals $\rightarrow$ SOAP $\rightarrow$ Observation $\rightarrow$ Consultation $\rightarrow$ Referral $\rightarrow$ Discharge $\rightarrow$ Follow-Up $\rightarrow$ Operational Handoff $\rightarrow$ Pharmacy) into one continuous, contextual, and role-governed user experience.

---

## 2. Pre-Implementation Module Status Matrix

| Clinical / Operational Module | Routes / Controllers | Current UI State | Integration Level | Planned Phase 5B Enhancement |
| :--- | :--- | :--- | :--- | :--- |
| **Observation Room** | `observations.index`, `observations.show`, `observations.monitoring.store`, `observations.handover.store`, `observations.complete` | Standalone view with modals | `PARTIALLY-INTEGRATED` | Add Patient Context Header, Stage Stepper, Chronological Timeline, and completed episode mutation locks. |
| **External Clinical Consultation** | `visits.consultations.create`, `consultations.show`, `consultations.transmit` | Separate show view | `PARTIALLY-INTEGRATED` | Add Patient Context Header, Stage Stepper, clear distinction of External Advice vs. Local Clinical Decision, and simulated transport label. |
| **Referral Management** | `visits.referrals.create`, `referrals.index`, `referrals.show`, `referrals.depart.store`, `referrals.handover.store`, `referrals.return.store` | Detailed show view | `PARTIALLY-INTEGRATED` | Add Patient Context Header, Stage Stepper, Chronological Timeline Stepper, and lifecycle-grouped action buttons. |
| **Discharge & Readiness** | `visits.discharge`, `visits.discharge.store`, `discharges.show`, `discharges.finalize` | Detailed workspace view | `PARTIALLY-INTEGRATED` | Add Patient Context Header, Stage Stepper, structured Readiness Evaluation checklist (blockers vs. warnings), and privacy-preserving handoffs. |
| **Follow-Up Plans** | `follow-up-plans.index`, `follow-up-plans.complete`, `follow-up-plans.cancel` | List view | `PARTIALLY-INTEGRATED` | Link follow-up status directly in Visit Overview cards and enforce due date discovery. |
| **Operational Handoff** | `operational-handoffs.index`, `operational-handoffs.acknowledge` | Operational list view | `PARTIALLY-INTEGRATED` | Verify minimum necessary privacy: 0 diagnosis, 0 clinical SOAP narrative, 0 vitals exposed to dorm supervisors. |
| **Unified Visit Overview** | `visits.show` | Overview grid | `PARTIALLY-INTEGRATED` | Upgrade overview cards to render real live states for Observation, Consultation, Referral, Discharge, and Follow-Up. |
| **Pharmacy Inventory & Safety** | `pharmacy.inventory.index`, `pharmacy.medicines.index`, `pharmacy.receipt.create`, `pharmacy.adjustments.create` | Inventory index | `PARTIALLY-INTEGRATED` | Add status filter tags (normal, expiring, expired, quarantined, depleted) and audit append-only stock safety. |

---

## 3. Key Design & Clinical Safety Principles

1. **Patient Safety & Identity Continuity**: `<x-patient-context-header>` must appear on every clinical screen to guarantee unambiguous patient identification and immediate visibility of active drug allergies.
2. **Workflow Progression**: `<x-visit-stage-nav>` must provide seamless horizontal navigation between clinical stages without breaking server authorization.
3. **External Advice vs. Local Order**: External consultation recommendations are advisory and must never automatically mutate local prescriptions, diagnoses, or discharge orders.
4. **Referral Distinct Events**: Referral departure, physical arrival, clinical handover, and partner acceptance are distinct operational events.
5. **Operational Privacy Protection**: Operational handoff data shared with non-medical staff (asrama/dorm) must adhere to the *Minimum Necessary* rule (activity restrictions only, no medical diagnoses).

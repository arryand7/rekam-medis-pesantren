<?php

use App\Http\Controllers\Auth\GateOidcAuthController;
use App\Http\Controllers\Gate\GateReconciliationController;
use App\Http\Controllers\Gate\GateSyncController;
use App\Http\Controllers\HealthController;
use App\Models\AuditLog;
use App\Models\ClinicalConsultation;
use App\Models\HealthcarePartner;
use App\Models\MedicalVisit;
use App\Models\MedicationAdministration;
use App\Models\MedicationOrder;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\ObservationEpisode;
use App\Models\ObservationHandover;
use App\Models\Patient;
use App\Models\Permission;
use App\Models\Person;
use App\Models\Referral;
use App\Models\Role;
use App\Models\StockLocation;
use App\Models\User;
use App\Services\ClinicalAssessmentService;
use App\Services\ClinicalConsultationService;
use App\Services\Gate\GateSyncDryRunService;
use App\Services\MedicalVisitService;
use App\Services\MedicationService;
use App\Services\ObservationService;
use App\Services\PharmacyService;
use App\Services\VitalSignService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/health', HealthController::class)->name('health');
Route::get('/health/ready', [HealthController::class, 'ready'])->name('health.ready');

// Gate SSO Authentication Routes
Route::get('/login', [GateOidcAuthController::class, 'login'])->name('login');
Route::get('/auth/gate/callback', [GateOidcAuthController::class, 'callback'])->name('auth.gate.callback');
Route::get('/auth/gate/access-denied', [GateOidcAuthController::class, 'accessDenied'])->name('auth.gate.access_denied');
Route::post('/logout', [GateOidcAuthController::class, 'logout'])->name('logout');

// Phase 1 Management Shell Routes

Route::get('/people', function () {
    $people = Person::latest()->paginate(15);

    return view('pages.people.index', compact('people'));
})->name('people.index');

Route::get('/patients', function () {
    $patients = Patient::with('person')->latest()->paginate(15);

    return view('pages.patients.index', compact('patients'));
})->name('patients.index');

Route::get('/patients/{id}', function (string $id) {
    $patient = Patient::with(['person', 'healthProfile', 'allergies', 'activeAllergies', 'medicalConditions', 'emergencyContacts'])->findOrFail($id);

    return view('pages.patients.show', compact('patient'));
})->name('patients.show');

Route::get('/users', function () {
    $users = User::with(['person', 'roles'])->latest()->paginate(15);

    return view('pages.users.index', compact('users'));
})->name('users.index');

Route::get('/roles', function () {
    $roles = Role::with('permissions')->get();
    $permissions = Permission::all();

    return view('pages.roles.index', compact('roles', 'permissions'));
})->name('roles.index');

Route::get('/gate-sync/preview', function (Request $request, GateSyncDryRunService $dryRunService) {
    $report = null;
    if ($request->has('run')) {
        $report = $dryRunService->execute();
    }

    return view('pages.gate-sync.preview', compact('report'));
})->name('gate-sync.preview');

Route::get('/gate-sync/conflicts', function () {
    return view('pages.gate-sync.conflicts');
})->name('gate-sync.conflicts');

Route::get('/audit-logs', function () {
    $auditLogs = AuditLog::latest()->paginate(20);

    return view('pages.audit-logs.index', compact('auditLogs'));
})->name('audit-logs.index');

// Phase 2A & 2B Medical Visit & Assessment Routes

Route::get('/visits', function () {
    $visits = MedicalVisit::with(['patient.person', 'receivingOfficer'])->latest()->paginate(15);

    return view('pages.visits.index', compact('visits'));
})->name('visits.index');

Route::get('/visits/create', function () {
    $patients = Patient::with('person')->where('is_eligible', true)->get();

    return view('pages.visits.create', compact('patients'));
})->name('visits.create');

Route::post('/visits', function (Request $request, MedicalVisitService $visitService) {
    $validated = $request->validate([
        'patient_id' => 'required|exists:patients,id',
        'chief_complaint' => 'required|string|min:3|max:1000',
        'reporting_type' => 'nullable|string',
        'reporting_name' => 'nullable|string|max:255',
        'origin_location' => 'nullable|string|max:255',
        'override_active' => 'nullable|boolean',
        'override_reason' => 'nullable|string|max:500',
    ]);

    try {
        $visit = $visitService->registerVisit($validated);

        return redirect()->route('visits.assessment', $visit->id)->with('success', 'Kunjungan medis berhasil didaftarkan. Silakan lakukan pengkajian.');
    } catch (Exception $e) {
        return redirect()->back()->withInput()->with('error', $e->getMessage());
    }
})->name('visits.store');

Route::get('/visits/{id}', function (string $id) {
    $visit = MedicalVisit::with(['patient.person', 'receivingOfficer', 'assignedOfficer', 'vitalSigns', 'latestAssessment', 'actions'])->findOrFail($id);

    return view('pages.visits.show', compact('visit'));
})->name('visits.show');

Route::post('/visits/{id}/cancel', function (string $id, Request $request, MedicalVisitService $visitService) {
    $request->validate([
        'cancellation_reason' => 'required|string|min:3|max:500',
    ]);

    $visit = MedicalVisit::findOrFail($id);

    try {
        $visitService->cancelVisit($visit, $request->input('cancellation_reason'));

        return redirect()->route('visits.show', $visit->id)->with('success', 'Kunjungan medis telah dibatalkan.');
    } catch (Exception $e) {
        return redirect()->back()->with('error', $e->getMessage());
    }
})->name('visits.cancel');

// Phase 2B Clinical Assessment Workspace Routes

Route::get('/visits/{id}/assessment', function (string $id) {
    $visit = MedicalVisit::with(['patient.person', 'patient.activeAllergies', 'vitalSigns', 'latestAssessment', 'actions'])->findOrFail($id);

    return view('pages.visits.assessment', compact('visit'));
})->name('visits.assessment');

Route::post('/visits/{id}/vital-signs', function (string $id, Request $request, VitalSignService $vitalService) {
    $visit = MedicalVisit::findOrFail($id);

    $validated = $request->validate([
        'temperature_c' => 'nullable|numeric|between:30,45',
        'systolic_bp' => 'nullable|integer|between:40,250',
        'diastolic_bp' => 'nullable|integer|between:30,150',
        'pulse_bpm' => 'nullable|integer|between:30,220',
        'respiratory_rate' => 'nullable|integer|between:5,60',
        'spo2_percent' => 'nullable|integer|between:50,100',
        'weight_kg' => 'nullable|numeric|between:5,300',
        'height_cm' => 'nullable|numeric|between:50,250',
        'pain_score' => 'nullable|integer|between:0,10',
        'consciousness_level' => 'nullable|string',
        'notes' => 'nullable|string',
        'finalize' => 'nullable|boolean',
    ]);

    try {
        $vitalService->record($visit, $validated);

        return redirect()->route('visits.assessment', $visit->id)->with('success', 'Data tanda vital berhasil dicatat.');
    } catch (Exception $e) {
        return redirect()->back()->withInput()->with('error', $e->getMessage());
    }
})->name('visits.vital-signs.store');

Route::post('/visits/{id}/assessment', function (string $id, Request $request, ClinicalAssessmentService $assessmentService, ObservationService $obsService) {
    $visit = MedicalVisit::findOrFail($id);

    $validated = $request->validate([
        'history_current_illness' => 'required|string|min:3',
        'relevant_history' => 'nullable|string',
        'examination_findings' => 'required|string|min:3',
        'assessment_summary' => 'required|string|min:3',
        'working_diagnosis' => 'nullable|string',
        'disposition_recommendation' => 'required|string',
        'finalize' => 'nullable|boolean',
    ]);

    try {
        $assessment = $assessmentService->saveDraft($visit, $validated);

        if (! empty($request->input('finalize')) && $request->input('finalize') == '1') {
            $assessmentService->finalizeAssessment($assessment);

            // Auto start observation episode if disposition recommendation is observation_required or rest_at_poskestren
            if (in_array($validated['disposition_recommendation'], ['observation_required', 'rest_at_poskestren'])) {
                $episode = $obsService->startEpisode($visit, [
                    'reason' => 'Rekomendasi observasi dari pengkajian medis: '.$validated['assessment_summary'],
                    'location_label' => 'Ruang Observasi Poskestren',
                ]);

                return redirect()->route('observations.show', $episode->id)->with('success', 'Pengkajian medis selesai & episode observasi Poskestren telah dimulai.');
            }

            return redirect()->route('visits.show', $visit->id)->with('success', 'Pengkajian klinis medis telah difinalisasi.');
        }

        return redirect()->route('visits.assessment', $visit->id)->with('success', 'Draft pengkajian klinis medis disimpan.');
    } catch (Exception $e) {
        return redirect()->back()->withInput()->with('error', $e->getMessage());
    }
})->name('visits.assessment.store');

Route::post('/visits/{id}/actions', function (string $id, Request $request, ClinicalAssessmentService $assessmentService) {
    $visit = MedicalVisit::findOrFail($id);

    $validated = $request->validate([
        'action_type' => 'required|string',
        'description' => 'required|string|min:3',
        'notes' => 'nullable|string',
    ]);

    try {
        $assessmentService->recordAction($visit, $validated);

        return redirect()->route('visits.assessment', $visit->id)->with('success', 'Tindakan awal non-obat berhasil dicatat.');
    } catch (Exception $e) {
        return redirect()->back()->withInput()->with('error', $e->getMessage());
    }
})->name('visits.actions.store');

// Phase 2C Observation Workspace Routes

Route::get('/observations', function () {
    $episodes = ObservationEpisode::with(['medicalVisit.patient.person', 'responsibleOfficer'])->latest()->paginate(15);

    return view('pages.observations.index', compact('episodes'));
})->name('observations.index');

Route::get('/observations/{id}', function (string $id) {
    $episode = ObservationEpisode::with(['medicalVisit.patient.person', 'medicalVisit.patient.activeAllergies', 'responsibleOfficer', 'records.recordedBy', 'handovers.fromUser', 'handovers.toUser'])->findOrFail($id);
    $medicalUsers = User::where('is_active', true)->get();

    return view('pages.observations.show', compact('episode', 'medicalUsers'));
})->name('observations.show');

Route::post('/observations/{id}/monitoring', function (string $id, Request $request, ObservationService $obsService) {
    $episode = ObservationEpisode::findOrFail($id);

    $validated = $request->validate([
        'condition_summary' => 'required|string|min:3',
        'symptom_changes' => 'nullable|string',
        'general_condition' => 'nullable|string',
    ]);

    try {
        $obsService->recordMonitoring($episode, $validated);

        return redirect()->route('observations.show', $episode->id)->with('success', 'Lembar pemantauan berkala berhasil dicatat.');
    } catch (Exception $e) {
        return redirect()->back()->withInput()->with('error', $e->getMessage());
    }
})->name('observations.monitoring.store');

Route::post('/observations/{id}/handover', function (string $id, Request $request, ObservationService $obsService) {
    $episode = ObservationEpisode::findOrFail($id);

    $validated = $request->validate([
        'to_user_id' => 'nullable|exists:users,id',
        'summary' => 'required|string|min:3',
        'current_condition' => 'required|string|min:3',
        'pending_tasks' => 'nullable|string',
    ]);

    try {
        $obsService->submitHandover($episode, $validated);

        return redirect()->route('observations.show', $episode->id)->with('success', 'Handover shift jaga berhasil diajukan.');
    } catch (Exception $e) {
        return redirect()->back()->withInput()->with('error', $e->getMessage());
    }
})->name('observations.handover.store');

Route::post('/handovers/{id}/acknowledge', function (string $id, ObservationService $obsService) {
    $handover = ObservationHandover::findOrFail($id);

    try {
        $obsService->acknowledgeHandover($handover);

        return redirect()->route('observations.show', $handover->observation_episode_id)->with('success', 'Handover shift disetujui & penanggung jawab observasi berhasil dialihkan.');
    } catch (Exception $e) {
        return redirect()->back()->with('error', $e->getMessage());
    }
})->name('observations.handover.acknowledge');

Route::post('/observations/{id}/complete', function (string $id, Request $request, ObservationService $obsService) {
    $episode = ObservationEpisode::findOrFail($id);

    $validated = $request->validate([
        'outcome' => 'required|string',
        'outcome_reason' => 'required|string|min:3',
    ]);

    try {
        $obsService->completeEpisode($episode, $validated['outcome'], $validated['outcome_reason']);

        return redirect()->route('observations.show', $episode->id)->with('success', 'Episode observasi Poskestren telah diselesaikan.');
    } catch (Exception $e) {
        return redirect()->back()->withInput()->with('error', $e->getMessage());
    }
})->name('observations.complete');

// Phase 2D1 Pharmacy Foundation Routes

Route::get('/pharmacy/medicines', function () {
    $medicines = Medicine::latest()->paginate(15);

    return view('pages.pharmacy.medicines.index', compact('medicines'));
})->name('pharmacy.medicines.index');

Route::post('/pharmacy/medicines', function (Request $request, PharmacyService $pharmacyService) {
    $validated = $request->validate([
        'code' => 'required|string|unique:medicines,code',
        'generic_name' => 'required|string|min:2',
        'brand_name' => 'nullable|string',
        'dosage_form' => 'required|string',
        'strength_text' => 'nullable|string',
        'base_unit' => 'required|string',
        'minimum_stock' => 'required|integer|min:1',
    ]);

    try {
        $pharmacyService->createMedicine($validated);

        return redirect()->route('pharmacy.medicines.index')->with('success', 'Master obat baru berhasil didaftarkan.');
    } catch (Exception $e) {
        return redirect()->back()->withInput()->with('error', $e->getMessage());
    }
})->name('pharmacy.medicines.store');

Route::get('/pharmacy/inventory', function () {
    $batches = MedicineBatch::with(['medicine', 'location'])->where('status', '!=', 'entered_in_error')->latest()->paginate(15);

    return view('pages.pharmacy.inventory.index', compact('batches'));
})->name('pharmacy.inventory.index');

Route::get('/pharmacy/receipt/create', function () {
    $medicines = Medicine::where('is_active', true)->get();
    $locations = StockLocation::where('is_active', true)->get();

    return view('pages.pharmacy.receipt.create', compact('medicines', 'locations'));
})->name('pharmacy.receipt.create');

Route::post('/pharmacy/receipt', function (Request $request, PharmacyService $pharmacyService) {
    $validated = $request->validate([
        'medicine_id' => 'required|exists:medicines,id',
        'stock_location_id' => 'required|exists:stock_locations,id',
        'batch_number' => 'required|string|min:2',
        'expiry_date' => 'nullable|date',
        'quantity' => 'required|integer|min:1',
        'supplier_name' => 'nullable|string',
        'reason' => 'nullable|string',
    ]);

    try {
        $pharmacyService->receiveStock($validated);

        return redirect()->route('pharmacy.inventory.index')->with('success', 'Penerimaan stok obat baru berhasil dicatat.');
    } catch (Exception $e) {
        return redirect()->back()->withInput()->with('error', $e->getMessage());
    }
})->name('pharmacy.receipt.store');

Route::get('/pharmacy/adjustments/create', function () {
    $batches = MedicineBatch::with(['medicine', 'location'])->where('status', 'active')->get();

    return view('pages.pharmacy.adjustments.create', compact('batches'));
})->name('pharmacy.adjustments.create');

Route::post('/pharmacy/adjustments', function (Request $request, PharmacyService $pharmacyService) {
    $validated = $request->validate([
        'medicine_batch_id' => 'required|exists:medicine_batches,id',
        'movement_type' => 'required|in:adjustment_in,adjustment_out',
        'quantity' => 'required|integer|min:1',
        'reason' => 'required|string|min:3',
    ]);

    try {
        $pharmacyService->adjustStock($validated);

        return redirect()->route('pharmacy.inventory.index')->with('success', 'Penyesuaian stok opname berhasil dicatat.');
    } catch (Exception $e) {
        return redirect()->back()->withInput()->with('error', $e->getMessage());
    }
})->name('pharmacy.adjustments.store');

// Phase 2D2 Medication Administration Routes

Route::get('/visits/{id}/medications', function (string $id) {
    $visit = MedicalVisit::with(['patient.person', 'patient.activeAllergies', 'medicationOrders.medicine.batches', 'medicationOrders.orderedBy', 'medicationAdministrations.medicine', 'medicationAdministrations.batch', 'medicationAdministrations.administeredBy'])->findOrFail($id);
    $medicines = Medicine::with('batches')->where('is_active', true)->get();

    return view('pages.visits.medications', compact('visit', 'medicines'));
})->name('visits.medications.index');

Route::post('/visits/{id}/medications/orders', function (string $id, Request $request, MedicationService $medicationService) {
    $visit = MedicalVisit::findOrFail($id);

    $validated = $request->validate([
        'medicine_id' => 'required|exists:medicines,id',
        'dose_value' => 'required|string',
        'dose_unit' => 'required|string',
        'frequency_text' => 'required|string|min:2',
        'instructions' => 'nullable|string',
        'allergy_acknowledgement_reason' => 'nullable|string',
    ]);

    try {
        $medicationService->createOrder($visit, $validated);

        return redirect()->route('visits.medications.index', $visit->id)->with('success', 'Instruksi obat baru berhasil disimpan.');
    } catch (Exception $e) {
        return redirect()->back()->withInput()->with('error', $e->getMessage());
    }
})->name('visits.medications.orders.store');

Route::post('/medication-orders/{id}/administer', function (string $id, Request $request, MedicationService $medicationService) {
    $order = MedicationOrder::findOrFail($id);

    $request->validate([
        'medicine_batch_id' => 'required|exists:medicine_batches,id',
    ]);

    $batch = MedicineBatch::findOrFail($request->input('medicine_batch_id'));

    try {
        $admin = $medicationService->scheduleAdministration($order, []);
        $medicationService->administerMedication($admin, $batch);

        return redirect()->route('visits.medications.index', $order->medical_visit_id)->with('success', 'Pemberian obat ke pasien selesai & stok batch berhasil dipotong secara atomik.');
    } catch (Exception $e) {
        return redirect()->back()->with('error', $e->getMessage());
    }
})->name('visits.medications.administer.store');

Route::post('/medication-administrations/{id}/correct', function (string $id, Request $request, MedicationService $medicationService) {
    $admin = MedicationAdministration::findOrFail($id);

    $request->validate([
        'reason' => 'required|string|min:3',
    ]);

    try {
        $medicationService->correctAdministration($admin, $request->input('reason'));

        return redirect()->route('visits.medications.index', $admin->medical_visit_id)->with('success', 'Pemberian obat dibatalkan & stok batch berhasil dikembalikan secara atomik.');
    } catch (Exception $e) {
        return redirect()->back()->with('error', $e->getMessage());
    }
})->name('visits.medications.administer.correct');

// Phase 3A Clinical Consultation & Healthcare Partners Routes

Route::get('/healthcare-partners', function () {
    $partners = HealthcarePartner::with('contacts')->latest()->paginate(15);

    return view('pages.healthcare-partners.index', compact('partners'));
})->name('healthcare-partners.index');

Route::post('/healthcare-partners', function (Request $request, ClinicalConsultationService $consultationService) {
    $validated = $request->validate([
        'code' => 'required|string|unique:healthcare_partners,code',
        'name' => 'required|string|min:2',
        'partner_type' => 'required|string',
        'phone' => 'nullable|string',
        'official_email' => 'nullable|email',
        'cooperation_reference' => 'nullable|string',
    ]);

    try {
        $consultationService->createPartner($validated);

        return redirect()->route('healthcare-partners.index')->with('success', 'Faskes mitra baru berhasil didaftarkan.');
    } catch (Exception $e) {
        return redirect()->back()->withInput()->with('error', $e->getMessage());
    }
})->name('healthcare-partners.store');

Route::get('/consultations', function () {
    $consultations = ClinicalConsultation::with(['medicalVisit.patient.person', 'partner', 'recipientContact'])->latest()->paginate(15);

    return view('pages.consultations.index', compact('consultations'));
})->name('consultations.index');

Route::get('/visits/{id}/consultations/create', function (string $id) {
    $visit = MedicalVisit::with(['patient.person', 'latestAssessment', 'latestVitalSign'])->findOrFail($id);
    $partners = HealthcarePartner::with('contacts')->where('is_active', true)->where('consultation_enabled', true)->get();

    return view('pages.consultations.create', compact('visit', 'partners'));
})->name('visits.consultations.create');

Route::post('/visits/{id}/consultations', function (string $id, Request $request, ClinicalConsultationService $consultationService) {
    $visit = MedicalVisit::findOrFail($id);

    $validated = $request->validate([
        'healthcare_partner_id' => 'required|exists:healthcare_partners,id',
        'recipient_contact_id' => 'nullable|exists:healthcare_partner_contacts,id',
        'purpose' => 'required|string|min:3',
        'clinical_question' => 'required|string|min:5',
        'urgency' => 'required|in:routine,urgent,emergency',
        'redaction_notes' => 'nullable|string',
    ]);

    try {
        $consultation = $consultationService->createConsultation($visit, $validated);

        return redirect()->route('consultations.show', $consultation->id)->with('success', 'Pengajuan ringkasan konsultasi eksternal berhasil dibuat.');
    } catch (Exception $e) {
        return redirect()->back()->withInput()->with('error', $e->getMessage());
    }
})->name('visits.consultations.store');

Route::get('/consultations/{id}', function (string $id) {
    $consultation = ClinicalConsultation::with(['medicalVisit.patient.person', 'partner', 'recipientContact', 'latestVersion', 'transmissions', 'externalAdvices', 'latestAdvice', 'latestDecision'])->findOrFail($id);

    return view('pages.consultations.show', compact('consultation'));
})->name('consultations.show');

Route::post('/consultations/{id}/transmit', function (string $id, ClinicalConsultationService $consultationService) {
    $consultation = ClinicalConsultation::findOrFail($id);

    try {
        $consultationService->transmitConsultation($consultation);

        return redirect()->route('consultations.show', $consultation->id)->with('success', 'Ringkasan konsultasi berhasil dikirim ke mitra faskes.');
    } catch (Exception $e) {
        return redirect()->back()->with('error', $e->getMessage());
    }
})->name('consultations.transmit');

Route::post('/consultations/{id}/advice', function (string $id, Request $request, ClinicalConsultationService $consultationService) {
    $consultation = ClinicalConsultation::findOrFail($id);

    $validated = $request->validate([
        'clinician_name' => 'required|string|min:2',
        'clinician_profession' => 'required|string|min:2',
        'advice_text' => 'required|string|min:5',
        'recommended_next_step' => 'nullable|string',
    ]);

    try {
        $consultationService->recordExternalAdvice($consultation, $validated);

        return redirect()->route('consultations.show', $consultation->id)->with('success', 'Jawaban/advice klinis eksternal berhasil dicatat.');
    } catch (Exception $e) {
        return redirect()->back()->withInput()->with('error', $e->getMessage());
    }
})->name('consultations.advice.store');

Route::post('/consultations/{id}/decision', function (string $id, Request $request, ClinicalConsultationService $consultationService) {
    $consultation = ClinicalConsultation::findOrFail($id);

    $validated = $request->validate([
        'decision_type' => 'required|string',
        'rationale' => 'required|string|min:3',
    ]);

    try {
        $consultationService->recordLocalDecision($consultation, $validated);

        return redirect()->route('consultations.show', $consultation->id)->with('success', 'Keputusan klinis lokal Poskestren telah difinalisasi.');
    } catch (Exception $e) {
        return redirect()->back()->withInput()->with('error', $e->getMessage());
    }
})->name('consultations.decision.store');

// ─── Phase 3B — Referral Module Routes (Controller-based, Policy-enforced) ───────

use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Discharge\ActivityRestrictionController;
use App\Http\Controllers\Discharge\ClinicalOperationalHandoffController;
use App\Http\Controllers\Discharge\VisitDischargeController;
use App\Http\Controllers\Discharge\VisitDischargeDocumentController;
use App\Http\Controllers\Discharge\VisitFollowUpPlanController;
use App\Http\Controllers\Integration\AttendanceIntegrationController;
use App\Http\Controllers\Integration\IntegrationOutboxController;
use App\Http\Controllers\Notification\OperationalNotificationController;
use App\Http\Controllers\Notification\UserNotificationController;
use App\Http\Controllers\Referral\ReferralCompanionController;
use App\Http\Controllers\Referral\ReferralController;
use App\Http\Controllers\Referral\ReferralDepartureController;
use App\Http\Controllers\Referral\ReferralDocumentController;
use App\Http\Controllers\Referral\ReferralHandoverController;
use App\Http\Controllers\Referral\ReferralReturnController;
use App\Http\Controllers\Referral\ReferralReturnReviewController;
use App\Http\Controllers\Referral\ReferralStatusController;
use App\Http\Controllers\Referral\ReferralTransportController;
use App\Http\Controllers\Reporting\HealthReportController;

Route::middleware('auth')->group(function () {
    // Referral main CRUD
    Route::get('/referrals', [ReferralController::class, 'index'])->name('referrals.index');
    Route::get('/referrals/{id}', [ReferralController::class, 'show'])->name('referrals.show');
    Route::get('/visits/{id}/referrals/create', [ReferralController::class, 'create'])->name('visits.referrals.create');
    Route::post('/visits/{id}/referrals', [ReferralController::class, 'store'])->name('visits.referrals.store');

    // Transport
    Route::post('/referrals/{id}/transport', [ReferralTransportController::class, 'store'])
        ->name('referrals.transport.store');

    // Companion
    Route::post('/referrals/{id}/companion', [ReferralCompanionController::class, 'store'])
        ->name('referrals.companion.store');

    // Departure (server timestamp, no client time accepted)
    Route::post('/referrals/{id}/depart', [ReferralDepartureController::class, 'store'])
        ->name('referrals.depart.store');

    // Handover (idempotent)
    Route::post('/referrals/{id}/handover', [ReferralHandoverController::class, 'store'])
        ->name('referrals.handover.store');

    // Destination status events (handoff ≠ acceptance)
    Route::post('/referrals/{id}/status-event', [ReferralStatusController::class, 'store'])
        ->name('referrals.status-event.store');

    // Return from referral
    Route::post('/referrals/{id}/return', [ReferralReturnController::class, 'store'])
        ->name('referrals.return.store');

    // Return review (no auto-discharge)
    Route::post('/referral-returns/{id}/review', [ReferralReturnReviewController::class, 'store'])
        ->name('referrals.return-review.store');

    // Private referral document (download + generate)
    Route::get('/referrals/{referralId}/versions/{versionId}/document', [ReferralDocumentController::class, 'show'])
        ->name('referrals.document.download')
        ->middleware('throttle:30,1'); // rate limiting: 30 downloads/minute

    Route::post('/referrals/{referralId}/versions/{versionId}/document/generate', [ReferralDocumentController::class, 'generate'])
        ->name('referrals.document.generate');

    // Phase 3C1 Discharge & Clinical Closure Routes
    Route::get('/discharges', [VisitDischargeController::class, 'index'])->name('discharges.index');
    Route::get('/visits/{id}/discharge', [VisitDischargeController::class, 'workspace'])->name('visits.discharge');
    Route::post('/visits/{id}/discharge', [VisitDischargeController::class, 'store'])->name('visits.discharge.store');
    Route::get('/discharges/{id}', [VisitDischargeController::class, 'show'])->name('discharges.show');
    Route::post('/discharges/{id}/finalize', [VisitDischargeController::class, 'finalize'])->name('discharges.finalize');
    Route::post('/discharges/{id}/amend', [VisitDischargeController::class, 'amend'])->name('discharges.amend');

    // Follow-up Plans
    Route::get('/follow-up-plans', [VisitFollowUpPlanController::class, 'index'])->name('follow-up-plans.index');
    Route::post('/discharges/{id}/follow-up-plans', [VisitFollowUpPlanController::class, 'store'])->name('discharges.follow-up-plans.store');
    Route::post('/follow-up-plans/{id}/complete', [VisitFollowUpPlanController::class, 'complete'])->name('follow-up-plans.complete');
    Route::post('/follow-up-plans/{id}/cancel', [VisitFollowUpPlanController::class, 'cancel'])->name('follow-up-plans.cancel');

    // Activity Restrictions
    Route::post('/discharges/{id}/activity-restrictions', [ActivityRestrictionController::class, 'store'])->name('discharges.activity-restrictions.store');
    Route::post('/activity-restrictions/{id}/cancel', [ActivityRestrictionController::class, 'cancel'])->name('activity-restrictions.cancel');

    // Operational Handoffs
    Route::get('/operational-handoffs', [ClinicalOperationalHandoffController::class, 'index'])->name('operational-handoffs.index');
    Route::post('/discharges/{id}/operational-handoffs', [ClinicalOperationalHandoffController::class, 'store'])->name('discharges.operational-handoffs.store');
    Route::post('/operational-handoffs/{id}/acknowledge', [ClinicalOperationalHandoffController::class, 'acknowledge'])->name('operational-handoffs.acknowledge');

    // Private Discharge Summary Document (download + generate)
    Route::get('/discharges/{dischargeId}/versions/{versionId}/document', [VisitDischargeDocumentController::class, 'show'])
        ->name('discharges.document.download')
        ->middleware('throttle:30,1');

    Route::post('/discharges/{dischargeId}/versions/{versionId}/document/generate', [VisitDischargeDocumentController::class, 'generate'])
        ->name('discharges.document.generate');

    // Phase 3C2 Integration Outbox & Identity Conflicts
    Route::get('/integration/outbox', [IntegrationOutboxController::class, 'index'])->name('integration.outbox.index');
    Route::get('/integration/outbox/{id}', [IntegrationOutboxController::class, 'show'])->name('integration.outbox.show');
    Route::post('/integration/outbox/{id}/retry', [IntegrationOutboxController::class, 'retry'])->name('integration.outbox.retry');
    Route::get('/integration/attendance/status', [AttendanceIntegrationController::class, 'status'])->name('integration.attendance.status');
    Route::get('/integration/conflicts', [AttendanceIntegrationController::class, 'conflicts'])->name('integration.conflicts.index');
    Route::post('/integration/conflicts/{id}/resolve', [AttendanceIntegrationController::class, 'resolveConflict'])->name('integration.conflicts.resolve');

    // Phase 3C2 Operational Notifications & Inbox
    Route::get('/notifications/operational', [OperationalNotificationController::class, 'index'])->name('notifications.operational.index');
    Route::post('/notifications/operational/{id}/acknowledge', [OperationalNotificationController::class, 'acknowledge'])->name('notifications.operational.acknowledge');
    Route::get('/notifications/inbox', [UserNotificationController::class, 'index'])->name('notifications.inbox.index');
    Route::post('/notifications/inbox/{id}/read', [UserNotificationController::class, 'markAsRead'])->name('notifications.inbox.read');
    Route::post('/notifications/inbox/read-all', [UserNotificationController::class, 'markAllAsRead'])->name('notifications.inbox.read-all');

    // Phase 3C2 Role-aware Dashboards
    Route::get('/dashboards/clinical', [DashboardController::class, 'clinical'])->name('dashboards.clinical');
    Route::get('/dashboards/management', [DashboardController::class, 'management'])->name('dashboards.management');
    Route::get('/dashboards/operational', [DashboardController::class, 'operational'])->name('dashboards.operational');

    // Phase 3C2 Health Reports Foundation
    Route::get('/reports', [HealthReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/view', [HealthReportController::class, 'show'])->name('reports.show');

    // Phase 4A Gate User Synchronization & Reconciliation
    Route::prefix('gate')->name('gate.')->group(function () {
        Route::get('/sync', [GateSyncController::class, 'index'])->name('sync.index');
        Route::get('/sync/dry-run', [GateSyncController::class, 'dryRun'])->name('sync.dry_run');
        Route::post('/sync/apply', [GateSyncController::class, 'apply'])->name('sync.apply');
        Route::get('/sync/runs/{run}', [GateSyncController::class, 'showRun'])->name('sync.show');

        Route::get('/reconciliation', [GateReconciliationController::class, 'index'])->name('reconciliation.index');
        Route::post('/reconciliation/{mapping}/approve', [GateReconciliationController::class, 'approveMapping'])->name('reconciliation.approve');
        Route::post('/reconciliation/{mapping}/reject', [GateReconciliationController::class, 'rejectMapping'])->name('reconciliation.reject');
    });
});

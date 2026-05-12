<?php $title = 'Intake Form'; include __DIR__ . '/../shared/header.php'; ?>

<div class="container my-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1">Patient Intake Form</h1>
            <p class="text-muted mb-0">Please complete your intake information.</p>
        </div>
        <div class="btn-group">
            <a class="btn btn-outline-secondary" href="/clinic/controllers/patient_run.php?action=dashboard">Back to Dashboard</a>
        </div>
    </div>

    <section class="card shadow-sm">
        <div class="card-header bg-white">
            <h2 class="h5 mb-0">Medical History</h2>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label" for="medical_history">Medical history</label>
                    <textarea
                        class="form-control"
                        id="medical_history"
                        name="medical_history"
                        rows="6"
                        placeholder="Conditions, allergies, medications, past treatments, etc."
                    ><?= htmlspecialchars((string)($patient['MedicalHistory'] ?? '')) ?></textarea>
                    <div class="form-text">This will be saved to your patient profile.</div>
                </div>

                <button type="submit" class="btn btn-primary">Save Intake Form</button>
            </form>
        </div>
    </section>
</div>

<?php include __DIR__ . '/../shared/footer.php'; ?>
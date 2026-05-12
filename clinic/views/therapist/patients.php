<?php $title = 'Patients'; include __DIR__ . '/../shared/header.php'; ?>
<div class="container my-4">
    <h1 class="h3">Patients</h1>
    <?php if (($_GET['msg'] ?? '') === 'note_sent'): ?>
        <div class="alert alert-success">Note sent to managers successfully.</div>
    <?php endif; ?>
    <table class="table table-striped">
        <thead><tr><th>Name</th><th>Email</th></tr></thead>
        <tbody>
        <?php foreach ($patients as $patient): ?>
            <tr>
                <td><?= htmlspecialchars($patient['FullName']) ?></td>
                <td><?= htmlspecialchars($patient['Email']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <h2 class="h5 mt-4">Shared Journals</h2>
    <?php foreach ($sharedJournals as $journal): ?>
        <div class="border rounded p-2 mb-2">
            <strong><?= htmlspecialchars($journal['patient_name'] ?? '') ?></strong>
            <div><?= nl2br(htmlspecialchars($journal['Content'] ?? '')) ?></div>
        </div>
    <?php endforeach; ?>
    <a href="/clinic/controllers/therapist_run.php?action=dashboard">Back to Dashboard</a>

    <section class="card shadow-sm mt-4">
        <div class="card-header bg-white"><h2 class="h5 mb-0">Send Note To Manager</h2></div>
        <div class="card-body">
            <form method="post" action="/clinic/controllers/therapist_run.php?action=sendManagerNote">
                <div class="mb-3">
                    <label class="form-label" for="patient_id">Patient</label>
                    <select class="form-select" id="patient_id" name="patient_id" required>
                        <option value="">Select patient</option>
                        <?php foreach ($patients as $patient): ?>
                            <option value="<?= (int)$patient['Id'] ?>"><?= htmlspecialchars($patient['FullName']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="content">Note</label>
                    <textarea class="form-control" id="content" name="content" rows="3" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Send To Manager</button>
            </form>
        </div>
    </section>
</div>
<?php include __DIR__ . '/../shared/footer.php'; ?>

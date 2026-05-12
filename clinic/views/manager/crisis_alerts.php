<?php $title = 'Crisis Alerts'; include __DIR__ . '/../shared/header.php'; ?>
<div class="container my-4">
    <h1 class="h4 mb-3">Open Crisis Alerts</h1>
    <section class="card shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($alerts)): ?>
                <p class="p-3 text-muted mb-0">No open crisis alerts.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead><tr><th>Patient</th><th>Therapist</th><th>Severity</th><th>Created At</th><th>Action</th></tr></thead>
                        <tbody>
                        <?php foreach ($alerts as $a): ?>
                            <tr>
                                <td><?= htmlspecialchars((string)$a['PatientName']) ?></td>
                                <td><?= htmlspecialchars((string)$a['TherapistName']) ?></td>
                                <td><span class="badge bg-danger"><?= htmlspecialchars((string)$a['Severity']) ?></span></td>
                                <td><?= htmlspecialchars((string)$a['CreatedAt']) ?></td>
                                <td>
                                    <form method="post" action="/clinic/controllers/manager_run.php?action=crisisAlerts" onsubmit="return confirm('Mark this alert as resolved?')">
                                        <input type="hidden" name="action" value="resolve">
                                        <input type="hidden" name="alert_id" value="<?= (int)($a['AlertId'] ?? 0) ?>">
                                        <button type="submit" class="btn btn-sm btn-success">Resolve</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>
<?php include __DIR__ . '/../shared/footer.php'; ?>

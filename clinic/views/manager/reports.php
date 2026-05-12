<?php $title = 'Manager Reports'; include __DIR__ . '/../shared/header.php'; ?>
<div class="container my-4">
    <h1 class="h4 mb-3">Reports</h1>
    <div class="alert alert-info d-flex justify-content-between align-items-center">
        <span>Open Crisis Alerts: <strong><?= (int)$openCrisisAlerts ?></strong></span>
        <a class="btn btn-sm btn-outline-danger" href="/clinic/controllers/manager_run.php?action=crisisAlerts">Open Crisis Center</a>
    </div>

    <section class="card shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h2 class="h5 mb-0">Recent Crisis Alerts</h2>
            <a class="btn btn-sm btn-outline-danger" href="/clinic/controllers/manager_run.php?action=crisisAlerts">Manage Alerts</a>
        </div>
        <div class="card-body">
            <?php if (empty($recentCrisisAlerts)): ?>
                <p class="text-muted mb-0">No open crisis alerts right now.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead><tr><th>Patient</th><th>Therapist</th><th>Severity</th><th>Time</th></tr></thead>
                        <tbody>
                        <?php foreach ($recentCrisisAlerts as $a): ?>
                            <tr>
                                <td><?= htmlspecialchars((string)($a['PatientName'] ?? '')) ?></td>
                                <td><?= htmlspecialchars((string)($a['TherapistName'] ?? 'Not assigned')) ?></td>
                                <td><span class="badge bg-danger"><?= htmlspecialchars((string)($a['Severity'] ?? 'high')) ?></span></td>
                                <td><?= htmlspecialchars((string)($a['CreatedAt'] ?? '')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="card shadow-sm">
        <div class="card-header bg-white"><h2 class="h5 mb-0">Weekly Mood Reports</h2></div>
        <div class="card-body">
            <?php if (empty($weeklyMoodReports)): ?>
                <p class="text-muted mb-0">No mood logs found in last 7 days.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead><tr><th>Patient</th><th>Average Mood</th><th>Logs</th></tr></thead>
                        <tbody>
                        <?php foreach ($weeklyMoodReports as $r): ?>
                            <tr>
                                <td><?= htmlspecialchars((string)($r['patient_name'] ?? '')) ?></td>
                                <td><?= htmlspecialchars(number_format((float)($r['avg_mood'] ?? 0), 1)) ?></td>
                                <td><?= (int)($r['logs_count'] ?? 0) ?></td>
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

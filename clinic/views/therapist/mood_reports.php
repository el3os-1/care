<?php $title = 'Reports'; include __DIR__ . '/../shared/header.php'; ?>
<div class="container my-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1">Weekly Mood Reports</h1>
            <p class="text-muted mb-0">Summary for the last 7 days.</p>
        </div>
        <div class="btn-group">
            <a class="btn btn-outline-secondary" href="/clinic/controllers/therapist_run.php?action=dashboard">Back</a>
        </div>
    </div>

    <section class="card shadow-sm">
        <div class="card-header bg-white">
            <h2 class="h5 mb-0">Patients</h2>
        </div>
        <div class="card-body">
            <?php if (empty($weeklyMoodReports)): ?>
                <p class="text-muted mb-0">No mood logs found for your assigned patients in the last 7 days.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Average Mood</th>
                                <th>Logs</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($weeklyMoodReports as $report): ?>
                                <tr>
                                    <td><?= htmlspecialchars($report['patient_name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars(number_format((float)($report['avg_mood'] ?? 0), 1)) ?></td>
                                    <td><?= (int)($report['logs_count'] ?? 0) ?></td>
                                    <td class="text-end">
                                        <a class="btn btn-sm btn-primary"
                                           href="/clinic/controllers/therapist_run.php?action=moodReport&patient_id=<?= (int)($report['PatientId'] ?? 0) ?>">
                                            View
                                        </a>
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


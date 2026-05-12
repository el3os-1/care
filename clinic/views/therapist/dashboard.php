<?php $title = 'Therapist Dashboard'; include __DIR__ . '/../shared/header.php'; ?>
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Therapist Dashboard</h1>
        <div class="btn-group">
            <a class="btn btn-outline-primary" href="/clinic/controllers/therapist_run.php?action=availability">Availability</a>
            <a class="btn btn-outline-primary" href="/clinic/controllers/therapist_run.php?action=profile">Profile</a>
            <a class="btn btn-outline-primary" href="/clinic/controllers/therapist_run.php?action=notes">Notes</a>
            <a class="btn btn-outline-primary" href="/clinic/controllers/therapist_run.php?action=patients">Patients</a>
            <a class="btn btn-outline-primary" href="/clinic/controllers/therapist_run.php?action=moodReports">Reports</a>
        </div>
    </div>

    <h2 class="h5">Upcoming Appointments</h2>
    <table class="table table-striped">
        <thead><tr><th>Patient</th><th>Date</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($appointments as $appointment): ?>
            <tr>
                <td><?= htmlspecialchars($appointment['patient_name'] ?? '') ?></td>
                <td><?= htmlspecialchars($appointment['date'] ?? '') ?></td>
                <td><?= htmlspecialchars($appointment['status'] ?? '') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h2 class="h5 mt-4">Today's Sessions</h2>
    <table class="table table-striped">
        <thead><tr><th>Patient</th><th>Date</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($sessions as $session): ?>
            <tr>
                <td><?= htmlspecialchars($session['patient_name'] ?? '') ?></td>
                <td><?= htmlspecialchars($session['date'] ?? '') ?></td>
                <td><?= htmlspecialchars($session['status'] ?? '') ?></td>
                <td><a class="btn btn-sm btn-primary" href="/clinic/controllers/therapist_run.php?action=session&id=<?= (int)$session['id'] ?>">Open</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h2 class="h5 mt-4">Weekly Mood Summary</h2>
    <table class="table table-striped">
        <thead><tr><th>Patient</th><th>Average Mood</th><th>Logs</th><th></th></tr></thead>
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
<?php include __DIR__ . '/../shared/footer.php'; ?>

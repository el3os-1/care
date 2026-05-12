<?php $title = 'Mood Report'; include __DIR__ . '/../shared/header.php'; ?>
<div class="container my-4">
    <h1 class="h3">Mood Report</h1>
    <table class="table table-striped">
        <thead><tr><th>Date</th><th>Mood</th><th>Sleep</th><th>Notes</th></tr></thead>
        <tbody>
        <?php foreach ($moodLogs as $log): ?>
            <tr>
                <td><?= htmlspecialchars($log['LogDate']) ?></td>
                <td><?= htmlspecialchars((string)$log['MoodScore']) ?></td>
                <td><?= htmlspecialchars((string)$log['SleepHours']) ?></td>
                <td><?= htmlspecialchars($log['Notes'] ?? '') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <a href="/clinic/controllers/therapist_run.php?action=dashboard">Back to Dashboard</a>
</div>
<?php include __DIR__ . '/../shared/footer.php'; ?>

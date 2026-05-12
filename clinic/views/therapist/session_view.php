<?php $title = 'Session'; include __DIR__ . '/../shared/header.php'; ?>
<div class="container my-4">
    <h1 class="h3">Session #<?= (int)$session['id'] ?></h1>
    <p><strong>Patient:</strong> <?= htmlspecialchars($patient['FullName'] ?? $session['patient_name'] ?? '') ?></p>
    <p><strong>Date:</strong> <?= htmlspecialchars($session['date'] ?? '') ?></p>
    <p><strong>Status:</strong> <?= htmlspecialchars($session['Status'] ?? $session['status'] ?? '') ?></p>
    <div class="mb-3">
        <a class="btn btn-success" href="/clinic/controllers/therapist_run.php?action=startSession&id=<?= (int)$session['id'] ?>">Start</a>
        <a class="btn btn-secondary" href="/clinic/controllers/therapist_run.php?action=endSession&id=<?= (int)$session['id'] ?>">End</a>
    </div>
    <form method="post" action="/clinic/controllers/therapist_run.php?action=saveNote&id=<?= (int)$session['id'] ?>" class="mb-4">
        <input type="hidden" name="session_id" value="<?= (int)$session['id'] ?>">
        <label class="form-label" for="content">New Note</label>
        <textarea class="form-control" id="content" name="content" rows="4" required></textarea>
        <button class="btn btn-primary mt-2" type="submit">Save Note</button>
    </form>
    <h2 class="h5">Notes</h2>
    <?php foreach ($notes as $note): ?>
        <div class="border rounded p-2 mb-2">
            <div class="text-muted"><?= htmlspecialchars($note['timestamp'] ?? '') ?></div>
            <div><?= nl2br(htmlspecialchars($note['content'] ?? '')) ?></div>
        </div>
    <?php endforeach; ?>
    <a href="/clinic/controllers/therapist_run.php?action=dashboard">Back to Dashboard</a>
</div>
<?php include __DIR__ . '/../shared/footer.php'; ?>

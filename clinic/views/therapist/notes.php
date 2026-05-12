<?php $title = 'Therapist Notes'; include __DIR__ . '/../shared/header.php'; ?>
<div class="container my-4">
    <h1 class="h3">Clinical Notes</h1>
    <?php if (isset($_GET['saved'])): ?><div class="alert alert-success">Note saved.</div><?php endif; ?>
    <form method="post" action="/clinic/controllers/therapist_run.php?action=notes" class="mb-4">
        <div class="mb-2">
            <label class="form-label" for="session_id">Session ID</label>
            <input class="form-control" type="number" id="session_id" name="session_id" required>
        </div>
        <div class="mb-2">
            <label class="form-label" for="content">Note</label>
            <textarea class="form-control" id="content" name="content" rows="4" required></textarea>
        </div>
        <button class="btn btn-primary" type="submit">Save Note</button>
    </form>
    <table class="table table-striped">
        <thead><tr><th>ID</th><th>Session</th><th>Content</th><th>Created</th></tr></thead>
        <tbody>
        <?php foreach ($notes as $note): ?>
            <tr>
                <td><?= (int)$note['id'] ?></td>
                <td><?= htmlspecialchars((string)($note['session_id'] ?? '')) ?></td>
                <td><?= htmlspecialchars((string)$note['content']) ?></td>
                <td><?= htmlspecialchars((string)$note['timestamp']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <a href="/clinic/controllers/therapist_run.php?action=dashboard">Back to Dashboard</a>
</div>
<?php include __DIR__ . '/../shared/footer.php'; ?>

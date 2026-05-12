<?php $title = 'Manager Notes'; include __DIR__ . '/../shared/header.php'; ?>
<div class="container my-4">
    <h1 class="h4 mb-3">Notes Sent To Manager</h1>
    <section class="card shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($notes)): ?>
                <p class="p-3 text-muted mb-0">No notes yet.</p>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($notes as $note): ?>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <span class="badge bg-<?= ($note['Type'] ?? '') === 'admin_manager_note' ? 'primary' : 'success' ?>">
                                    <?= ($note['Type'] ?? '') === 'admin_manager_note' ? 'From Admin' : 'From Therapist' ?>
                                </span>
                                <small class="text-muted"><?= htmlspecialchars((string)($note['CreatedAt'] ?? '')) ?></small>
                            </div>
                            <p class="mb-0 mt-2"><?= nl2br(htmlspecialchars((string)($note['Message'] ?? ''))) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>
<?php include __DIR__ . '/../shared/footer.php'; ?>

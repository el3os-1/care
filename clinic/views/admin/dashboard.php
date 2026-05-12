<?php
$title = 'Admin Dashboard';
require __DIR__ . '/../shared/header.php';
?>

<div class="container">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <h2 class="h4 mb-0">Welcome, Admin</h2>

            <?php if (($_GET['msg'] ?? '') === 'note_sent'): ?>
                <div class="alert alert-success mt-3 mb-0">Note sent to all managers successfully.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../shared/footer.php'; ?>

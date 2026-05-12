<?php
$title = 'Emergency';
require __DIR__ . '/../shared/header.php';
?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-md-9 col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h4 mb-3 text-danger">Emergency Alert</h1>
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
                    <?php endif; ?>
                    <div class="alert alert-warning">
                        If this is an immediate emergency, contact local emergency services first.
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <a class="btn btn-danger" href="/clinic/controllers/patient_run.php?action=emergency&trigger=1">Send Crisis Alert</a>
                        <a class="btn btn-outline-secondary" href="/clinic/controllers/patient_run.php?action=dashboard">Back to Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../shared/footer.php'; ?>

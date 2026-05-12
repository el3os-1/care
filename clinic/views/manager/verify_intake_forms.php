<?php $title = 'Verify Intake Forms'; include __DIR__ . '/../shared/header.php'; ?>
<div class="container my-4">
    <h1 class="h4 mb-3">Verify Intake Forms</h1>
    <?php if (!empty($message)): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Form</th><th>Patient</th><th>Therapist</th><th>Responses</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php foreach ($forms as $form): ?>
                        <tr>
                            <td>#<?= (int)$form['FormId'] ?></td>
                            <td><?= htmlspecialchars((string)($form['PatientName'] ?? '')) ?></td>
                            <td><?= htmlspecialchars((string)($form['TherapistName'] ?? 'Not assigned')) ?></td>
                            <td><?= htmlspecialchars((string)($form['Responses'] ?? '')) ?></td>
                            <td><span class="badge bg-<?= !empty($form['isVerified']) ? 'success' : 'warning text-dark' ?>"><?= !empty($form['isVerified']) ? 'Verified' : 'Pending' ?></span></td>
                            <td>
                                <?php if (empty($form['isVerified'])): ?>
                                    <form method="post" action="/clinic/controllers/manager_run.php?action=verifyIntakeForms" onsubmit="return confirm('Verify this intake form?')">
                                        <input type="hidden" name="action" value="verify">
                                        <input type="hidden" name="id" value="<?= (int)$form['FormId'] ?>">
                                        <button class="btn btn-sm btn-success">Verify</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted">Done</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../shared/footer.php'; ?>

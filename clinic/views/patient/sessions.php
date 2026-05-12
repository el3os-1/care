<?php $title = 'My Sessions'; include __DIR__ . '/../shared/header.php'; ?>
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">My Sessions</h1>
        <a class="btn btn-outline-primary" href="/clinic/controllers/patient_run.php?action=dashboard">Dashboard</a>
    </div>
    <?php if (($_GET['msg'] ?? '') === 'paid'): ?>
        <div class="alert alert-success">Payment completed successfully.</div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <?php if (empty($sessions)): ?>
                <p class="text-muted mb-0">No sessions found.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Therapist</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th>Notes</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sessions as $session): ?>
                                <tr>
                                    <td><?= htmlspecialchars($session['therapist_name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($session['date'] ?? '') ?></td>
                                    <td><span class="badge bg-primary-subtle text-primary"><?= htmlspecialchars($session['status'] ?? '') ?></span></td>
                                    <td>
                                        <?php $ps = strtolower((string)($session['payment_status'] ?? 'pending')); ?>
                                        <div>
                                            <span class="badge bg-<?= $ps === 'paid' ? 'success' : ($ps === 'fine' ? 'warning text-dark' : 'secondary') ?>">
                                                <?= htmlspecialchars(ucfirst($ps)) ?>
                                            </span>
                                        </div>
                                        <div class="small text-muted"><?= number_format((float)($session['payment_amount'] ?? 0), 2) ?> EGP</div>
                                    </td>
                                    <td><?= htmlspecialchars($session['notes'] ?? '') ?></td>
                                    <td>
                                        <?php if (in_array(strtolower((string)($session['payment_status'] ?? 'pending')), ['pending', 'fine'], true) && !empty($session['payment_id'])): ?>
                                            <form method="POST" action="/clinic/controllers/patient_run.php?action=paySession">
                                                <input type="hidden" name="payment_id" value="<?= (int)$session['payment_id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-success">Pay Now</button>
                                            </form>
                                        <?php elseif (strtolower((string)$session['status']) === 'scheduled'): ?>
                                            <span class="text-muted">Contact clinic to cancel</span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../shared/footer.php'; ?>

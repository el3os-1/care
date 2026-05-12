<?php include __DIR__ . '/../shared/header.php'; ?>
<div class="container-fluid px-4 py-4">
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="/clinic/controllers/manager_run.php?action=dashboard" class="btn btn-outline-secondary btn-sm">← Back</a>
        <h3 class="fw-bold mb-0">Verify Therapist Licenses</h3>
    </div>
    <?php if (!empty($message)): ?><div class="alert alert-success alert-dismissible fade show">✅ <?= htmlspecialchars($message) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="alert alert-danger alert-dismissible fade show">❌ <?= htmlspecialchars($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
    <?php
    $pendingCount = count(array_filter($therapists, fn($t) => ($t['LicStatus'] ?? '') === 'pending'));
    $badCount     = count(array_filter($therapists, fn($t) => in_array($t['LicStatus'] ?? '', ['expired', 'revoked'])));
    $tabs = ['tabAll' => $therapists, 'tabPending' => array_filter($therapists, fn($t) => ($t['LicStatus'] ?? '') === 'pending'), 'tabBad' => array_filter($therapists, fn($t) => in_array($t['LicStatus'] ?? '', ['expired', 'revoked']))];
    ?>
    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabAll">All</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabPending">Pending <?php if ($pendingCount): ?><span class="badge bg-warning text-dark"><?= $pendingCount ?></span><?php endif; ?></a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabBad">Expired/Revoked <?php if ($badCount): ?><span class="badge bg-danger"><?= $badCount ?></span><?php endif; ?></a></li>
    </ul>
    <div class="tab-content">
        <?php foreach ($tabs as $tabId => $rows): ?>
            <div class="tab-pane fade <?= $tabId === 'tabAll' ? 'show active' : '' ?>" id="<?= $tabId ?>">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <?php if (empty($rows)): ?><p class="text-muted p-3 mb-0">No therapists in this category.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Therapist</th>
                                            <th>Contact</th>
                                            <th>Specialization</th>
                                            <th>Profile</th>
                                            <th>License No.</th>
                                            <th>Issuer</th>
                                            <th>Expiry</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($rows as $t):
                                            $lic = $t['LicStatus'] ?? ($t['LicenseStatus'] ?? 'unknown');
                                            $cls = match ($lic) {
                                                'valid' => 'success',
                                                'active' => 'success',
                                                'pending' => 'warning',
                                                'expired' => 'danger',
                                                'revoked' => 'dark',
                                                default => 'secondary'
                                            };
                                            $effectiveExpiry = $t['ExpiryDate'] ?? ($t['LicenseExpiry'] ?? null);
                                            $exp = !empty($effectiveExpiry) && strtotime((string)$effectiveExpiry) < time();
                                        ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-semibold"><?= htmlspecialchars($t['FullName']) ?></div>
                                                    <div class="text-muted small"><?= htmlspecialchars($t['Email']) ?></div>
                                                </td>
                                                <td>
                                                    <div class="small"><?= htmlspecialchars((string)($t['Phone'] ?? '—')) ?></div>
                                                </td>
                                                <td><?= htmlspecialchars($t['Specialization'] ?? '—') ?></td>
                                                <td>
                                                    <div class="small">Rating: <?= htmlspecialchars(number_format((float)($t['Rating'] ?? 0), 1)) ?></div>
                                                    <div class="small">Availability: <?= !empty($t['IsSnoozed']) ? 'Snoozed' : 'Available' ?></div>
                                                    <div class="small text-muted">Profile Status: <?= htmlspecialchars((string)($t['LicenseStatus'] ?? '—')) ?></div>
                                                </td>
                                                <td><?= htmlspecialchars($t['LicenseNumber'] ?? '—') ?></td>
                                                <td><?= htmlspecialchars($t['Issuer'] ?? '—') ?></td>
                                                <td><?= $effectiveExpiry ? date('d M Y', strtotime((string)$effectiveExpiry)) : '—' ?><?php if ($exp): ?> <span class="badge bg-danger">Expired</span><?php endif; ?></td>
                                                <td><span class="badge bg-<?= $cls ?>"><?= ucfirst($lic) ?></span></td>
                                                <td>
                                                    <?php if (!empty($t['LicenseId'])): ?>
                                                        <div class="d-flex gap-1 flex-wrap">
                                                            <?php if ($lic !== 'valid'): ?>
                                                                <form method="POST" action="/clinic/controllers/manager_run.php?action=verifyTherapists"><input type="hidden" name="action" value="verify"><input type="hidden" name="license_id" value="<?= $t['LicenseId'] ?>"><button class="btn btn-sm btn-success">Verify</button></form>
                                                            <?php endif; ?>
                                                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#renewModal" data-license-id="<?= $t['LicenseId'] ?>" data-therapist="<?= htmlspecialchars($t['FullName']) ?>">Renew</button>
                                                            <?php if ($lic !== 'revoked'): ?>
                                                            <form method="POST" action="/clinic/controllers/manager_run.php?action=verifyTherapists" onsubmit="return confirm('Revoke this license?')"><input type="hidden" name="action" value="revoke"><input type="hidden" name="license_id" value="<?= $t['LicenseId'] ?>"><button class="btn btn-sm btn-outline-danger">Revoke</button></form>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php else: ?><span class="text-muted small">No license on file</span><?php endif; ?>
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
        <?php endforeach; ?>
    </div>
</div>
<div class="modal fade" id="renewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/clinic/controllers/manager_run.php?action=verifyTherapists">
                <input type="hidden" name="action" value="renew"><input type="hidden" name="license_id" id="renewLicenseId">
                <div class="modal-header">
                    <h5 class="modal-title">Renew License — <span id="renewTherapistName"></span></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body"><label class="form-label fw-semibold">New Expiry Date</label><input type="date" name="new_expiry" class="form-control" required min="<?= date('Y-m-d') ?>"></div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-warning">Renew</button></div>
            </form>
        </div>
    </div>
</div>
<script>
    document.getElementById('renewModal').addEventListener('show.bs.modal', function(e) {
        const btn = e.relatedTarget;
        document.getElementById('renewLicenseId').value = btn.dataset.licenseId;
        document.getElementById('renewTherapistName').textContent = btn.dataset.therapist;
    });
</script>
<?php include __DIR__ . '/../shared/footer.php'; ?>
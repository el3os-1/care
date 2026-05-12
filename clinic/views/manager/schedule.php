<?php include __DIR__ . '/../shared/header.php'; ?>
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-2">
            <a href="manager_run.php?action=dashboard" class="btn btn-outline-secondary btn-sm">← Back</a>
            <h3 class="fw-bold mb-0">Appointment Schedule</h3>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newApptModal">+ New Appointment</button>
    </div>
    <?php if (!empty($message)): ?><div class="alert alert-success alert-dismissible fade show">✅ <?= htmlspecialchars($message) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="alert alert-danger alert-dismissible fade show">❌ <?= htmlspecialchars($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2 d-flex gap-2 flex-wrap align-items-center">
            <span class="fw-semibold me-1">Filter:</span>
            <button class="btn btn-sm btn-outline-secondary filter-btn active" data-status="all">All</button>
            <button class="btn btn-sm btn-outline-warning  filter-btn" data-status="scheduled">Scheduled</button>
            <button class="btn btn-sm btn-outline-success  filter-btn" data-status="live">Live</button>
            <button class="btn btn-sm btn-outline-primary  filter-btn" data-status="completed">Completed</button>
            <button class="btn btn-sm btn-outline-danger   filter-btn" data-status="cancelled">Cancelled</button>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($appointments)): ?><p class="text-muted p-3 mb-0">No appointments found.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="apptTable">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Patient</th>
                                <th>Therapist</th>
                                <th>Scheduled At</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($appointments as $a):
                                $status = strtolower($a['Status']);
                                $cls = match ($status) {
                                    'scheduled' => 'warning',
                                    'live' => 'success',
                                    'completed' => 'primary',
                                    'cancelled' => 'danger',
                                    default => 'secondary'
                                };
                            ?>
                                <tr data-status="<?= $status ?>">
                                    <td><?= $a['AppointmentId'] ?></td>
                                    <td><?= htmlspecialchars($a['PatientName']) ?></td>
                                    <td><?= htmlspecialchars($a['TherapistName']) ?></td>
                                    <td><?= date('d M Y', strtotime($a['ScheduledAt'])) ?><div class="text-muted small"><?= date('h:i A', strtotime($a['ScheduledAt'])) ?></div>
                                    </td>
                                    <td><span class="badge bg-<?= $cls ?> <?= $status === 'scheduled' ? 'text-dark' : '' ?>"><?= ucfirst($status) ?></span></td>
                                    <td>
                                        <?php if ($status === 'scheduled'): ?>
                                            <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModal"
                                                data-appt-id="<?= $a['AppointmentId'] ?>"
                                                data-scheduled="<?= date('d M Y, h:i A', strtotime($a['ScheduledAt'])) ?>">Cancel</button>
                                        <?php elseif ($status === 'live'): ?><span class="text-success fw-semibold">🔴 Live</span>
                                        <?php else: ?><span class="text-muted small">—</span><?php endif; ?>
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
<div class="modal fade" id="newApptModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="schedule_run.php?action=createAppointment">
                <div class="modal-header">
                    <h5 class="modal-title">New Appointment</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label fw-semibold">Patient</label>
                        <select name="patient_id" class="form-select" required>
                            <option value="">— Select —</option>
                            <?php foreach ($patients ?? [] as $p): ?><option value="<?= $p['Id'] ?>"><?= htmlspecialchars($p['FullName']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3"><label class="form-label fw-semibold">Therapist</label>
                        <select name="therapist_id" class="form-select" required>
                            <option value="">— Select —</option>
                            <?php foreach ($therapists ?? [] as $t): ?><option value="<?= $t['Id'] ?>"><?= htmlspecialchars($t['FullName']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3"><label class="form-label fw-semibold">Date &amp; Time</label>
                        <input type="datetime-local" name="scheduled_at" class="form-control" required min="<?= date('Y-m-d\TH:i') ?>">
                        <div class="form-text text-info"> Double-booking prevented automatically </div>
                    </div>
                    <div class="mb-3"><label class="form-label fw-semibold">Fee (EGP)</label>
                        <input type="number" name="amount" class="form-control" value="200" min="0" step="0.01">
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary">Book</button></div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="schedule_run.php?action=cancelAppointment">
                <input type="hidden" name="appointment_id" id="cancelApptId">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">Cancel Appointment</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Scheduled: <strong id="cancelScheduled"></strong></p>
                    <div class="alert alert-warning">⚠️ Cancellations within <strong>24 hours</strong> incur a <strong>50% fine</strong> </div>
                    <div class="mb-3"><label class="form-label fw-semibold">Reason</label><textarea name="cancel_reason" class="form-control" rows="3" required></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Back</button><button type="submit" class="btn btn-danger">Confirm Cancel</button></div>
            </form>
        </div>
    </div>
</div>
<script>
    document.getElementById('cancelModal').addEventListener('show.bs.modal', function(e) {
        const btn = e.relatedTarget;
        document.getElementById('cancelApptId').value = btn.dataset.apptId;
        document.getElementById('cancelScheduled').textContent = btn.dataset.scheduled;
    });
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const status = this.dataset.status;
            document.querySelectorAll('#apptTable tbody tr').forEach(row => {
                row.style.display = (status === 'all' || row.dataset.status === status) ? '' : 'none';
            });
        });
    });
</script>
<?php include __DIR__ . '/../shared/footer.php'; ?>
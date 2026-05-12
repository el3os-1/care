<?php include __DIR__ . '/../shared/header.php'; ?>
<div class="container-fluid px-4 py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center gap-2">
      <a href="manager_run.php?action=dashboard" class="btn btn-outline-secondary btn-sm">← Back</a>
      <h3 class="fw-bold mb-0"><?= isset($filter) && $filter==='cancelled' ? '⚠️ Cancelled Sessions' : '🗂️ All Sessions' ?></h3>
    </div>
    <div class="d-flex gap-2">
      <a href="session_run.php?action=listSessions" class="btn btn-sm <?= ($filter??'')!=='cancelled'?'btn-primary':'btn-outline-secondary' ?>">All Sessions</a>
      <a href="session_run.php?action=cancelledSessions" class="btn btn-sm <?= ($filter??'')==='cancelled'?'btn-danger':'btn-outline-danger' ?>">Cancelled</a>
    </div>
  </div>
  <?php
  $flashMap = ['started'=>['success','✅ Session started — status set to Live.'],'completed'=>['success','✅ Session completed and billed.'],'refunded'=>['success','✅ Refund processed.'],'fine_applied'=>['warning','⚠️ Late cancellation fine applied (REQ 23).']];
  $msgKey = $_GET['msg'] ?? '';
  if (isset($flashMap[$msgKey])): ?>
    <div class="alert alert-<?= $flashMap[$msgKey][0] ?> alert-dismissible fade show"><?= $flashMap[$msgKey][1] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
  <?php endif; ?>
  <?php if (!empty($_GET['err'])): ?><div class="alert alert-danger alert-dismissible fade show">❌ <?= htmlspecialchars($_GET['err']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
  <div class="card border-0 shadow-sm">
    <div class="card-body p-0">
      <?php if (empty($sessions)): ?><p class="text-muted p-4 mb-0">No sessions found.</p>
      <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Session #</th><th>Patient</th><th>Therapist</th><th>Scheduled</th><th>Status</th><th>Payment</th>
              <?php if (($filter??'')==='cancelled'): ?><th>Reason</th><th>Refund</th><?php endif; ?>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($sessions as $s):
              $sessionId  = $s['SessionId'] ?? null;
              $apptId     = $s['AppointmentId'] ?? null;
              $sessStatus = strtolower($s['Status'] ?? 'unknown');
              $apptStatus = strtolower($s['AppointmentStatus'] ?? $sessStatus);
              $badgeCls = match($sessStatus) { 'pending'=>'secondary','live'=>'success','completed'=>'primary','cancelled'=>'danger',default=>'secondary' };
              $payCls   = match($s['PaymentStatus']??'') { 'paid'=>'success','fine'=>'warning','refunded'=>'info',default=>'secondary' };
            ?>
            <tr>
              <td><?= $sessionId ? "#$sessionId" : "Appt #$apptId" ?></td>
              <td><div class="fw-semibold"><?= htmlspecialchars($s['PatientName']) ?></div><div class="text-muted small"><?= htmlspecialchars($s['PatientEmail']??'') ?></div></td>
              <td><?= htmlspecialchars($s['TherapistName']) ?></td>
              <td><?= date('d M Y', strtotime($s['ScheduledAt'])) ?><div class="text-muted small"><?= date('h:i A', strtotime($s['ScheduledAt'])) ?></div></td>
              <td><span class="badge bg-<?= $badgeCls ?>"><?= ucfirst($sessStatus) ?></span></td>
              <td><span class="badge bg-<?= $payCls ?>"><?= ucfirst($s['PaymentStatus']??'pending') ?></span><div class="text-muted small"><?= number_format((float)($s['Amount']??0),2) ?> EGP</div></td>
              <?php if (($filter??'')==='cancelled'): ?>
              <td class="text-muted small" style="max-width:150px"><?= htmlspecialchars($s['CancelReason']??'—') ?></td>
              <td><span class="badge bg-<?= ($s['RefundStatus']??'')==='refunded'?'success':'secondary' ?>"><?= ucfirst($s['RefundStatus']??'none') ?></span></td>
              <?php endif; ?>
              <td>
                <div class="d-flex gap-1 flex-wrap">
                  <?php if ($sessStatus==='pending' && $sessionId): ?>
                  <form method="POST" action="session_run.php?action=startSession"><input type="hidden" name="session_id" value="<?= $sessionId ?>"><button class="btn btn-sm btn-success" onclick="return confirm('Start this session?')">▶ Start</button></form>
                  <?php elseif ($sessStatus==='live' && $sessionId): ?>
                  <form method="POST" action="session_run.php?action=endSession"><input type="hidden" name="session_id" value="<?= $sessionId ?>"><button class="btn btn-sm btn-primary" onclick="return confirm('Mark as completed and bill?')">✅ Complete &amp; Bill</button></form>
                  <?php elseif ($apptStatus==='cancelled' && !empty($s['PaymentId'])): ?>
                    <?php if (($s['RefundStatus']??'')?'refunded'!==($s['RefundStatus']??''):true): ?>
                    <form method="POST" action="session_run.php?action=processRefund"><input type="hidden" name="payment_id" value="<?= $s['PaymentId'] ?>"><button class="btn btn-sm btn-outline-success" onclick="return confirm('Process refund?')">💰 Refund</button></form>
                    <?php endif; ?>
                    <?php if (($s['PaymentStatus']??'')?'fine'!==($s['PaymentStatus']??''):true): ?>
                    <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#fineModal" data-payment-id="<?= $s['PaymentId'] ?>" data-patient="<?= htmlspecialchars($s['PatientName']) ?>">⚠️ Fine</button>
                    <?php endif; ?>
                  <?php else: ?><span class="text-muted small">—</span><?php endif; ?>
                </div>
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
<div class="modal fade" id="fineModal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <form method="POST" action="session_run.php?action=applyFine">
      <input type="hidden" name="payment_id" id="finePaymentId">
      <div class="modal-header"><h5 class="modal-title text-warning">Apply Late Cancellation Fine</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <p>Patient: <strong id="finePatientName"></strong></p>
        <div class="alert alert-warning">Applied when patient cancelled within 24 hours </div>
        <label class="form-label fw-semibold">Fine Amount (EGP)</label>
        <input type="number" name="fine_amount" class="form-control" value="100" min="0" step="0.01">
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-warning">Apply Fine</button></div>
    </form>
  </div></div>
</div>
<script>
document.getElementById('fineModal').addEventListener('show.bs.modal', function(e) {
  const btn = e.relatedTarget;
  document.getElementById('finePaymentId').value = btn.dataset.paymentId;
  document.getElementById('finePatientName').textContent = btn.dataset.patient;
});
</script>
<?php include __DIR__ . '/../shared/footer.php'; ?>

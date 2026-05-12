<?php

$title = 'Manager Dashboard';
include __DIR__ . '/../shared/header.php';
?>

<div class="container-fluid px-4">


  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-speedometer2 me-2 text-primary"></i>Manager Dashboard</h4>
    <span class="text-muted">Welcome back, <strong><?= htmlspecialchars($_SESSION['name'] ?? 'Manager') ?></strong></span>
  </div>


  <div class="row g-3 mb-4">
    <?php
    $cards = [
      ['Total Appointments', 'total',     'primary', 'bi-calendar-check'],
      ['Scheduled',          'scheduled', 'warning',  'bi-clock'],
      ['Live Now',           'live',      'success',  'bi-camera-video-fill'],
      ['Cancelled',          'cancelled', 'danger',   'bi-x-circle'],
    ];
    foreach ($cards as [$label, $key, $color, $icon]):
    ?>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="rounded-circle d-flex align-items-center justify-content-center bg-<?= $color ?> bg-opacity-10"
               style="width:52px;height:52px;flex-shrink:0;">
            <i class="bi <?= $icon ?> fs-4 text-<?= $color ?>"></i>
          </div>
          <div>
            <div class="fs-2 fw-bold text-<?= $color ?> lh-1"><?= (int)($stats[$key] ?? 0) ?></div>
            <div class="text-muted small"><?= $label ?></div>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-md-3"><a class="btn btn-outline-primary w-100" href="/clinic/controllers/manager_run.php?action=verifyIntakeForms">Verify Intake Forms</a></div>
    <div class="col-md-3"><a class="btn btn-outline-danger w-100" href="/clinic/controllers/manager_run.php?action=crisisAlerts">Crisis Alerts</a></div>
    <div class="col-md-3"><a class="btn btn-outline-secondary w-100" href="/clinic/controllers/manager_run.php?action=reports">Reports</a></div>
  </div>


  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <a href="/clinic/controllers/manager_run.php?action=assignTherapist"
         class="card border-0 shadow-sm text-decoration-none h-100 card-hover">
        <div class="card-body d-flex align-items-center gap-3 p-3">
          <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width:48px;height:48px;flex-shrink:0;">
            <i class="bi bi-calendar-plus fs-5 text-primary"></i>
          </div>
          <div>
            <div class="fw-semibold text-dark">Assign Therapist</div>
            <div class="text-muted small">Book a session for a patient</div>
          </div>
          <i class="bi bi-chevron-right ms-auto text-muted"></i>
        </div>
      </a>
    </div>

    <div class="col-md-4">
      <a href="/clinic/controllers/manager_run.php?action=verifyTherapists"
         class="card border-0 shadow-sm text-decoration-none h-100 card-hover">
        <div class="card-body d-flex align-items-center gap-3 p-3">
          <div class="rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center" style="width:48px;height:48px;flex-shrink:0;">
            <i class="bi bi-patch-check fs-5 text-warning"></i>
          </div>
          <div>
            <div class="fw-semibold text-dark">
              Verify Licenses
              <?php if (!empty($unverified)): ?>
                <span class="badge bg-danger ms-1"><?= $unverified ?></span>
              <?php endif; ?>
            </div>
            <div class="text-muted small">Review &amp; renew therapist licenses</div>
          </div>
          <i class="bi bi-chevron-right ms-auto text-muted"></i>
        </div>
      </a>
    </div>

    <div class="col-md-4">
      <a href="/clinic/controllers/schedule_run.php?action=viewSchedule"
         class="card border-0 shadow-sm text-decoration-none h-100 card-hover">
        <div class="card-body d-flex align-items-center gap-3 p-3">
          <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center" style="width:48px;height:48px;flex-shrink:0;">
            <i class="bi bi-calendar3 fs-5 text-success"></i>
          </div>
          <div>
            <div class="fw-semibold text-dark">View Schedule</div>
            <div class="text-muted small">Full appointment calendar</div>
          </div>
          <i class="bi bi-chevron-right ms-auto text-muted"></i>
        </div>
      </a>
    </div>
  </div>


  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center pt-3">
      <h6 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>Upcoming Appointments</h6>
      <a href="/clinic/controllers/schedule_run.php?action=viewSchedule" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="card-body p-0">
      <?php if (empty($upcoming)): ?>
        <p class="text-muted p-3 mb-0">No upcoming appointments.</p>
      <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr><th>#</th><th>Patient</th><th>Therapist</th><th>Scheduled At</th><th>Status</th></tr>
          </thead>
          <tbody>
            <?php foreach ($upcoming as $a): ?>
            <tr>
              <td class="text-muted small">#<?= $a['AppointmentId'] ?></td>
              <td><i class="bi bi-person me-1 text-muted"></i><?= htmlspecialchars($a['PatientName']) ?></td>
              <td><?= htmlspecialchars($a['TherapistName']) ?></td>
              <td>
                <div><?= date('d M Y', strtotime($a['ScheduledAt'])) ?></div>
                <div class="text-muted small"><?= date('h:i A', strtotime($a['ScheduledAt'])) ?></div>
              </td>
              <td><span class="badge bg-warning text-dark">Scheduled</span></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>


  <?php if (!empty($cancelled)): ?>
  <div class="card border-0 shadow-sm border-start border-danger border-3">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center pt-3">
      <h6 class="fw-bold mb-0 text-danger">
        <i class="bi bi-exclamation-triangle me-2"></i>Cancelled Sessions Needing Review (<?= count($cancelled) ?>)
      </h6>
      <a href="/clinic/controllers/session_run.php?action=cancelledSessions" class="btn btn-sm btn-outline-danger">View All</a>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr><th>Patient</th><th>Was Scheduled</th><th>Reason</th><th>Refund Status</th><th></th></tr>
          </thead>
          <tbody>
            <?php foreach (array_slice($cancelled, 0, 5) as $c): ?>
            <tr>
              <td><?= htmlspecialchars($c['PatientName']) ?></td>
              <td><?= date('d M Y', strtotime($c['ScheduledAt'])) ?></td>
              <td class="text-muted small"><?= htmlspecialchars($c['CancelReason'] ?? '—') ?></td>
              <td>
                <?php
                  $rs = $c['RefundStatus'] ?? 'none';
                  $rc = match($rs) { 'refunded'=>'success','partial'=>'warning','pending_refund'=>'info', default=>'secondary' };
                ?>
                <span class="badge bg-<?= $rc ?>"><?= ucfirst($rs) ?></span>
              </td>
              <td>
                <a href="/clinic/controllers/session_run.php?action=cancelledSessions"
                   class="btn btn-sm btn-outline-danger">Review</a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <?php endif; ?>

</div>

<?php include __DIR__ . '/../shared/footer.php'; ?>

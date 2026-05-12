<?php include __DIR__ . '/../shared/header.php'; ?>

<div class="container py-4" style="max-width:860px">

  <div class="d-flex align-items-center gap-2 mb-4">

    <a href="/clinic/controllers/manager_run.php?action=dashboard"
       class="btn btn-outline-secondary btn-sm">
       ← Back
    </a>

    <h3 class="fw-bold mb-0">
      Assign Therapist to Patient
    </h3>

  </div>

  <?php if (!empty($message)): ?>

    <div class="alert alert-success alert-dismissible fade show">

      ✅ <?= htmlspecialchars($message) ?>

      <button type="button"
              class="btn-close"
              data-bs-dismiss="alert"></button>

    </div>

  <?php endif; ?>

  <?php if (!empty($error)): ?>

    <div class="alert alert-danger alert-dismissible fade show">

      ❌ <?= htmlspecialchars($error) ?>

      <button type="button"
              class="btn-close"
              data-bs-dismiss="alert"></button>

    </div>

  <?php endif; ?>

  <div class="card border-0 shadow-sm">

    <div class="card-body p-4">

      <form method="POST"
            action="/clinic/controllers/manager_run.php?action=assignTherapist">

        <div class="mb-3">

          <label class="form-label fw-semibold">
            Patient
          </label>

          <select name="patient_id"
                  class="form-select"
                  required>

            <option value="">
              — Select patient —
            </option>

            <?php foreach ($patients as $p): ?>

              <option value="<?= $p['Id'] ?>">

                <?= htmlspecialchars($p['FullName']) ?>

                (<?= htmlspecialchars($p['Email']) ?>)

              </option>

            <?php endforeach; ?>

          </select>

        </div>

        <div class="mb-3">

          <label class="form-label fw-semibold">
            Therapist
          </label>

          <select name="therapist_id"
                  id="therapistSelect"
                  class="form-select"
                  required>

            <option value="">
              — Select therapist —
            </option>

            <?php foreach ($therapists as $t): ?>

              <option value="<?= $t['Id'] ?>"
                      data-snoozed="<?= $t['IsSnoozed'] ? '1' : '0' ?>">

                <?= htmlspecialchars($t['FullName']) ?>

                <?= $t['Specialization']
                    ? ' — ' . htmlspecialchars($t['Specialization'])
                    : '' ?>

                <?= $t['IsSnoozed']
                    ? ' (Snoozed)'
                    : '' ?>

              </option>

            <?php endforeach; ?>

          </select>

          <div id="snoozeWarning"
               class="form-text text-warning d-none">

            ⚠️ This therapist is snoozed and not accepting new patients.

          </div>

        </div>

        <div class="mb-3">

          <label class="form-label fw-semibold">
            Date &amp; Time
          </label>

          <input type="datetime-local"
                 name="scheduled_at"
                 class="form-control"
                 required
                 min="<?= date('Y-m-d\TH:i') ?>">

          <div class="form-text">
            Double-booking is prevented automatically
          </div>

        </div>

        <div class="mb-4">

          <label class="form-label fw-semibold">
            Session Fee (EGP)
          </label>

          <input type="number"
                 name="amount"
                 class="form-control"
                 value="200"
                 min="0"
                 step="0.01">

        </div>

        <div class="d-flex gap-2">

          <button type="submit"
                  class="btn btn-primary px-4">

            Book Appointment

          </button>

          <a href="/clinic/controllers/manager_run.php?action=dashboard"
             class="btn btn-outline-secondary">

            Cancel

          </a>

        </div>

      </form>

    </div>

  </div>

</div>

<script>

document.getElementById('therapistSelect')
.addEventListener('change', function () {

    document.getElementById('snoozeWarning')
    .classList.toggle(
        'd-none',
        this.options[this.selectedIndex].dataset.snoozed !== '1'
    );

});

</script>

<?php include __DIR__ . '/../shared/footer.php'; ?>
<?php $title = 'Therapist Profile'; include __DIR__ . '/../shared/header.php'; ?>
<div class="container my-4">
    <h1 class="h3">Profile</h1>
    <?php if (isset($_GET['saved'])): ?><div class="alert alert-success">Profile saved.</div><?php endif; ?>
    <form method="post" action="/clinic/controllers/therapist_run.php?action=profile">
        <label class="form-label" for="specialization">Specialization</label>
        <input class="form-control mb-2" id="specialization" name="specialization" value="<?= htmlspecialchars($profile['Specialization'] ?? '') ?>">
        <label class="form-label" for="license_status">License Status</label>
        <input class="form-control mb-2" id="license_status" name="license_status" value="<?= htmlspecialchars($profile['LicenseStatus'] ?? 'pending') ?>">
        <label class="form-label" for="license_expiry">License Expiry</label>
        <input class="form-control mb-2" type="date" id="license_expiry" name="license_expiry" value="<?= htmlspecialchars($profile['LicenseExpiry'] ?? '') ?>">
        <label class="form-check-label mb-3">
            <input class="form-check-input" type="checkbox" name="is_snoozed" value="1" <?= !empty($profile['IsSnoozed']) ? 'checked' : '' ?>>
            Snoozed
        </label>
        <br>
        <button class="btn btn-primary" type="submit">Save</button>
    </form>
    <a href="/clinic/controllers/therapist_run.php?action=dashboard">Back to Dashboard</a>
</div>
<?php include __DIR__ . '/../shared/footer.php'; ?>

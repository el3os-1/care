<?php $title = 'Availability'; include __DIR__ . '/../shared/header.php'; ?>
<div class="container my-4">
    <h1 class="h3">Availability</h1>
    <?php if (isset($_GET['saved'])): ?><div class="alert alert-success">Availability saved.</div><?php endif; ?>
    <form method="post" action="/clinic/controllers/therapist_run.php?action=availability" class="row g-3 mb-4">
        <div class="col-md-3">
            <label class="form-label" for="day">Day</label>
            <select class="form-select" id="day" name="day">
                <option value="1">Monday</option>
                <option value="2">Tuesday</option>
                <option value="3">Wednesday</option>
                <option value="4">Thursday</option>
                <option value="5">Friday</option>
                <option value="6">Saturday</option>
                <option value="7">Sunday</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label" for="start">Start</label>
            <input class="form-control" type="time" id="start" name="start" required>
        </div>
        <div class="col-md-3">
            <label class="form-label" for="end">End</label>
            <input class="form-control" type="time" id="end" name="end" required>
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <label class="form-check-label">
                <input class="form-check-input" type="checkbox" name="is_snoozed" value="1" <?= !empty($profile['IsSnoozed']) ? 'checked' : '' ?>>
                Snoozed
            </label>
        </div>
        <div class="col-12">
            <button class="btn btn-primary" type="submit">Save</button>
        </div>
    </form>
    <table class="table table-striped">
        <thead><tr><th>Day</th><th>Start</th><th>End</th></tr></thead>
        <tbody>
        <?php foreach ($availability as $slot): ?>
            <tr>
                <td><?= (int)$slot['day'] ?></td>
                <td><?= htmlspecialchars($slot['start_time']) ?></td>
                <td><?= htmlspecialchars($slot['end_time']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <a href="/clinic/controllers/therapist_run.php?action=dashboard">Back to Dashboard</a>
</div>
<?php include __DIR__ . '/../shared/footer.php'; ?>

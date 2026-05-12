<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'CalmSpace Platform') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/clinic/assets/style.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark" style="background:#00b2e4;">
    <div class="container-fluid px-4">
        <a class="navbar-brand fw-bold" href="#">CalmSpace</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <?php if (isset($_SESSION['role'])): ?>
            <ul class="navbar-nav me-auto">
                <?php if ($_SESSION['role'] === 'manager'): ?>
                    <li class="nav-item"><a class="nav-link" href="/clinic/controllers/manager_run.php?action=dashboard"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="/clinic/controllers/manager_run.php?action=assignTherapist"><i class="bi bi-calendar-plus me-1"></i>Assign</a></li>
                    <li class="nav-item"><a class="nav-link" href="/clinic/controllers/manager_run.php?action=verifyTherapists"><i class="bi bi-patch-check me-1"></i>Licenses</a></li>
                    <li class="nav-item"><a class="nav-link" href="/clinic/controllers/manager_run.php?action=verifyIntakeForms"><i class="bi bi-ui-checks-grid me-1"></i>Intake</a></li>
                    <li class="nav-item"><a class="nav-link" href="/clinic/controllers/manager_run.php?action=reports"><i class="bi bi-bar-chart me-1"></i>Reports</a></li>
                    <li class="nav-item"><a class="nav-link" href="/clinic/controllers/manager_run.php?action=crisisAlerts"><i class="bi bi-exclamation-triangle me-1"></i>Crisis</a></li>
                    <li class="nav-item"><a class="nav-link" href="/clinic/controllers/manager_run.php?action=notes"><i class="bi bi-journal-text me-1"></i>Notes</a></li>
                    <li class="nav-item"><a class="nav-link" href="/clinic/controllers/schedule_run.php?action=viewSchedule"><i class="bi bi-calendar3 me-1"></i>Schedule</a></li>
                    <li class="nav-item"><a class="nav-link" href="/clinic/controllers/session_run.php?action=listSessions"><i class="bi bi-camera-video me-1"></i>Sessions</a></li>
                <?php elseif ($_SESSION['role'] === 'admin'): ?>
                    <li class="nav-item"><a class="nav-link" href="/clinic/controllers/admin_run.php?action=dashboard">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="/clinic/controllers/admin_run.php?action=users">Users</a></li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#sendManagerNoteModal">
                            <i class="bi bi-send me-1"></i>Send Note
                        </a>
                    </li>
                <?php elseif ($_SESSION['role'] === 'therapist'): ?>
                    <li class="nav-item"><a class="nav-link" href="/clinic/controllers/therapist_run.php?action=dashboard">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="/clinic/controllers/therapist_run.php?action=notes">Notes</a></li>
                    <li class="nav-item"><a class="nav-link" href="/clinic/controllers/therapist_run.php?action=availability">Availability</a></li>
                    <li class="nav-item"><a class="nav-link" href="/clinic/controllers/therapist_run.php?action=profile">Profile</a></li>
                <?php elseif ($_SESSION['role'] === 'patient'): ?>
                    <li class="nav-item"><a class="nav-link" href="/clinic/controllers/patient_run.php?action=dashboard">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="/clinic/controllers/patient_run.php?action=intakeForm">Intake Form</a></li>
                    <li class="nav-item"><a class="nav-link" href="/clinic/controllers/patient_run.php?action=sessions">Sessions</a></li>
                    <li class="nav-item"><a class="nav-link" href="/clinic/controllers/patient_run.php?action=favorites">Therapists</a></li>
                <?php endif; ?>
            </ul>
            <?php endif; ?>
            <ul class="navbar-nav ms-auto align-items-center">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <span class="navbar-text text-white me-3">
                            <i class="bi bi-person-circle me-1"></i>
                            <?= htmlspecialchars($_SESSION['name'] ?? 'User') ?>
                            <span class="badge bg-white text-primary ms-1"><?= ucfirst($_SESSION['role'] ?? '') ?></span>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-light btn-sm" href="/clinic/controllers/auth_run.php?action=logout">
                            <i class="bi bi-box-arrow-right me-1"></i>Logout
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
<div class="modal fade" id="sendManagerNoteModal" tabindex="-1" aria-labelledby="sendManagerNoteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/clinic/controllers/admin_run.php?action=sendManagerNote" method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="sendManagerNoteModalLabel">Send Note To Managers</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label" for="manager-message-nav">Message</label>
                    <textarea class="form-control" id="manager-message-nav" name="message" rows="4" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send Note</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<main class="py-3">

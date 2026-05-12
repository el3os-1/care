<?php
require_once __DIR__ . '/../../controllers/AdminController.php';

$roles = AdminController::GetAllRoles();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    AdminController::UpdateUser(
        $id,
        trim($_POST['username'] ?? ''),
        trim($_POST['email'] ?? ''),
        trim($_POST['FullName'] ?? ''),
        trim($_POST['phone'] ?? ''),
        (int)($_POST['role'] ?? 3)
    );
    header('Location: /clinic/controllers/admin_run.php?action=users&msg=updated');
    exit;
}

$id = (int)($_GET['id'] ?? 0);
$user = AdminController::GetUserById($id);
if (!$user) {
    echo 'User not found.';
    exit;
}
$currentUserRoleName = AdminController::GetUserRole((int)$user['Id']);
?>
<?php
$title = 'Update User';
require __DIR__ . '/../shared/header.php';
?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-md-9 col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h2 class="h5 mb-0">Update User</h2>
                </div>
                <div class="card-body">
                    <form action="/clinic/controllers/admin_run.php?action=updateUser" method="post" class="row g-3">
                        <input type="hidden" name="id" value="<?= (int)$user['Id'] ?>">
                        <div class="col-md-6">
                            <label class="form-label" for="edit-username">Username</label>
                            <input class="form-control" type="text" id="edit-username" name="username" value="<?= htmlspecialchars($user['Username'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="edit-email">Email</label>
                            <input class="form-control" type="email" id="edit-email" name="email" value="<?= htmlspecialchars($user['Email'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="edit-phone">Phone</label>
                            <input class="form-control" type="text" id="edit-phone" name="phone" value="<?= htmlspecialchars($user['Phone'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="edit-fullname">Full Name</label>
                            <input class="form-control" type="text" id="edit-fullname" name="FullName" value="<?= htmlspecialchars($user['FullName'] ?? '') ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="edit-role">Role</label>
                            <select class="form-select" id="edit-role" name="role">
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= (int)$role['RoleId'] ?>" <?= ($currentUserRoleName === $role['RoleName']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($role['RoleName']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button class="btn btn-primary" type="submit">Update User</button>
                            <a class="btn btn-outline-secondary" href="/clinic/controllers/admin_run.php?action=users">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../shared/footer.php'; ?>

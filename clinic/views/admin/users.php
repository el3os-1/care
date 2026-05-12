<?php
require_once __DIR__ . '/../../controllers/AdminController.php';
require_once __DIR__ . '/../../config/encryption.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = Encryption::hashPassword((string)($_POST['password'] ?? ''));
        $fullname = trim($_POST['fullname'] ?? '');
        $roleId = (int)($_POST['role'] ?? 3);
        $phone = trim($_POST['phone'] ?? '');

        if ($username && $email && $fullname) {
            AdminController::CreateUser($username, $email, $password, $fullname, $roleId, $phone);
            header('Location: /clinic/controllers/admin_run.php?action=users&msg=added');
            exit;
        }
    }

    if ($action === 'delete') {
        AdminController::DeleteUser((int)($_POST['id'] ?? 0));
        header('Location: /clinic/controllers/admin_run.php?action=users&msg=deleted');
        exit;
    }
    if ($action === 'ban') {
        AdminController::GiveBan((int)($_POST['id'] ?? 0));
        header('Location: /clinic/controllers/admin_run.php?action=users&msg=banned');
        exit;
    }
    if ($action === 'unban') {
        AdminController::ActivateUser((int)($_POST['id'] ?? 0));
        header('Location: /clinic/controllers/admin_run.php?action=users&msg=unbanned');
        exit;
    }
}

$users = AdminController::GetAllUsers();
$roles = AdminController::GetAllRoles();
?>
<?php
$title = 'Manage Users';
require __DIR__ . '/../shared/header.php';
?>

<div class="container">
    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h2 class="h5 mb-0">Users</h2>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Phone</th>
                            <th>IsActive</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars((string)$user['Id']) ?></td>
                                <td><?= htmlspecialchars((string)$user['Username']) ?></td>
                                <td><?= htmlspecialchars((string)$user['Email']) ?></td>
                                <td><?= htmlspecialchars((string)($user['RoleName'] ?? '')) ?></td>
                                <td><?= htmlspecialchars((string)($user['Phone'] ?? '')) ?></td>
                                <td>
                                    <span class="badge <?= ((int)$user['IsActive'] === 1) ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?>">
                                        <?= ((int)$user['IsActive'] === 1) ? 'Yes' : 'No' ?>
                                    </span>
                                </td>
                                <td class="d-flex gap-2 flex-wrap">
                                    <a href="/clinic/controllers/admin_run.php?action=updateUser&id=<?= (int)$user['Id'] ?>" class="btn btn-sm btn-outline-primary">Update</a>
                                    <?php if ((int)$user['IsActive'] === 1): ?>
                                        <form action="/clinic/controllers/admin_run.php?action=users" method="POST" onsubmit="return confirm('Ban this user?')">
                                            <input type="hidden" name="action" value="ban">
                                            <input type="hidden" name="id" value="<?= (int)$user['Id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-warning">Ban</button>
                                        </form>
                                    <?php else: ?>
                                        <form action="/clinic/controllers/admin_run.php?action=users" method="POST">
                                            <input type="hidden" name="action" value="unban">
                                            <input type="hidden" name="id" value="<?= (int)$user['Id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-success">Unban</button>
                                        </form>
                                    <?php endif; ?>
                                    <form action="/clinic/controllers/admin_run.php?action=users" method="POST" onsubmit="return confirm('Delete this user?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= (int)$user['Id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h2 class="h5 mb-0">Add New User</h2>
        </div>
        <div class="card-body">
            <form action="/clinic/controllers/admin_run.php?action=users" method="post" class="row g-3">
                <input type="hidden" name="action" value="add">
                <div class="col-md-6">
                    <label class="form-label" for="username">Username</label>
                    <input class="form-control" type="text" id="username" name="username" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="password">Password</label>
                    <input class="form-control" type="password" id="password" name="password" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="email">Email</label>
                    <input class="form-control" type="email" id="email" name="email" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="phone">Phone</label>
                    <input class="form-control" type="text" id="phone" name="phone">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="fullname">Full Name</label>
                    <input class="form-control" type="text" id="fullname" name="fullname" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="role">Role</label>
                    <select class="form-select" id="role" name="role">
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= (int)$role['RoleId'] ?>"><?= htmlspecialchars($role['RoleName']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <button class="btn btn-primary" type="submit">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../shared/footer.php'; ?>

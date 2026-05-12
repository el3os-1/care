<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/clinic/assets/style.css">
</head>
<body class="auth-page">
    <div class="container py-4">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-12 col-sm-10 col-md-8 col-lg-5 col-xl-4">
                <div class="card shadow-lg border-0 rounded-4">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <h2 class="h3 fw-bold mb-1">Welcome Back</h2>
                            <p class="text-muted mb-0">Sign in to your account</p>
                        </div>

                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger py-2 px-3"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>

                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success py-2 px-3"><?= htmlspecialchars($success) ?></div>
                        <?php endif; ?>

                        <form action="/clinic/controllers/auth_run.php?action=loginPost" method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold">Email</label>
                                <input id="email" class="form-control form-control-lg" type="email" name="email" placeholder="Enter your email" required>
                            </div>
                            <div class="mb-4">
                                <label for="password" class="form-label fw-semibold">Password</label>
                                <input id="password" class="form-control form-control-lg" type="password" name="password" placeholder="Enter your password" required>
                            </div>
                            <button class="btn btn-primary btn-lg w-100" type="submit">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Login
                            </button>
                        </form>

                        <p class="text-center mt-4 mb-0 text-muted">
                            Don't have an account?
                            <a class="fw-semibold text-decoration-none" href="/clinic/controllers/auth_run.php?action=register&switch=1">Register</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
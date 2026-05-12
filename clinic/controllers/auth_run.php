<?php

session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/encryption.php';

require_once __DIR__ . '/../core/BaseController.php';

require_once __DIR__ . '/../models/User.php';

require_once __DIR__ . '/AuthController.php';

$controller = new AuthController();

$action = $_GET['action'] ?? 'login';

switch ($action) {

    case 'login':

        $controller->login();
        break;

    case 'loginPost':

        $controller->loginPost();
        break;

    case 'register':

        $controller->register();
        break;

    case 'registerPost':

        $controller->registerPost();
        break;

    case 'logout':

        $controller->logout();
        break;

    default:

        $controller->login();
        break;
}
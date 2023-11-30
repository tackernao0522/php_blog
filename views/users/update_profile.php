<?php
require_once(__DIR__ . '/../../controllers/UserController.php');

$userController = new UserController();

// プロフィールの更新処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userController->updateProfile();
}

require_once(__DIR__ . '/../form/update_profile_form.php');

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once(__DIR__ . '/../../controllers/UserController.php');
require_once(__DIR__ . '/../../validation/profile_validation.php');

// HTTPヘッダーのセキュリティ対策
header('X-Frame-Options: DENY');

$userController = new UserController();

// ユーザーがログインしていない場合、ログインページにリダイレクト
if (!$userController->isUserLoggedIn()) {
    $_SESSION['toast_message'] = 'ログインしてください。';
    header("Location: http://localhost:3000/views/users/auth/login.php");
    exit;
}

// プロフィールの更新処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 入力値のバリデーション
    $errors = validateProfileInput($_POST['full_name'], $_POST['bio']);

    if (count($errors) > 0) {
        // バリデーションエラーがある場合、エラーメッセージと入力値をセッションに保存
        $_SESSION['validation_errors'] = $errors;
        $_SESSION['old_input'] = $_POST;

        // フォームにリダイレクト
        header("Location: update_profile.php");
        exit;
    } else {
        $userController->updateProfile();
    }
}

require_once(__DIR__ . '/../form/update_profile_form.php');

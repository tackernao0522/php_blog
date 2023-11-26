<?php
// 必要なファイルをインクルード
require_once(__DIR__ . '/../../../models/User.php');
require_once(__DIR__ . '/../../../services/UserManager.php');
require_once(__DIR__ . '/../../../services/UserManagerImpl.php');
require_once(__DIR__ . '/../../../services/PasswordHasher.php');
require_once(__DIR__ . '/../../../database/config.php');
require_once(__DIR__ . '/../../../services/UserImpl.php');
require_once(__DIR__ . '/../../../controllers/UserController.php');
require_once(__DIR__ . '/../../../validation/login_validation.php');
require_once(__DIR__ . '/../../component/header.php');

// エラーレポーティングを設定
error_reporting(E_ALL);
ini_set('display_errors', 1);

// HTTPヘッダーのセキュリティ対策
header('X-Frame-Options: DENY');

// セッション開始
// session_start();

// セッションIDの再生成
session_regenerate_id(true);

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// UserController クラスのインスタンスを作成
$userController = new UserController();

if ($userController->isUserLoggedIn()) {
    // ログイン済みの場合、プロフィールページにリダイレクト
    header("Location: http://localhost:3000/views/users/profile.php");
    exit;
}

$errors = $_SESSION['errors'] ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRFトークンの検証
    if (
        isset($_SESSION['csrf_token']) &&
        isset($_POST['csrf_token']) &&
        hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        // POSTデータからユーザー名とパスワードを取得
        $username = $_POST['username'];
        $password = $_POST['password'];

        // ユーザーが入力した値をセッションに保存
        $_SESSION['old']['username'] = $username;

        // バリデーションを追加
        $errors = validateLogin($_POST);
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            session_write_close();
            header("Location: http://localhost:3000/views/users/auth/login.php");
            exit;
        }

        // ユーザー認証
        $userManager = new UserManagerImpl();
        $user = $userManager->getUserByUsername($username);

        if ($user && $user->verifyPassword($password)) {
            // ログインが成功した場合、セッションにuser_idを設定
            $_SESSION['user_id'] = $user->getId();

            // プロフィールが登録されていない場合、プロフィール登録画面にリダイレクト
            $userProfile = getUserProfile($user->getId());
            if (!$userProfile) {
                if ((isset($_SESSION['logout_message']))) {
                    session_destroy();
                }
                $_SESSION['toast_profile_input_message'] = 'プロフィールを登録してください。';
                header("Location: http://localhost:3000/views/users/profile_input.php");
                exit;
            }

            // プロフィール情報をセッションに保存
            $_SESSION['userProfile'] = $userProfile;

            header("Location: http://localhost:3000/views/users/profile.php"); // プロフィールページにリダイレクト
            unset($_SESSION['auth_error']);
            exit;
        } else {
            $errors['password'] = 'パスワードが一致しません。';
            $_SESSION['errors'] = $errors;
            session_write_close();
            header("Location: http://localhost:3000/views/users/auth/login.php");
            exit;
        }
    } else {
        die("CSRF攻撃を検知");
    }
}

// CSRFトークンの生成
$csrfToken = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrfToken;

require_once(__DIR__ . '/../../form/login_form.php');

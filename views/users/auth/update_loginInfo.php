<?php
// セッション開始
session_start();

// データベースとUserクラスを読み込む
require_once(__DIR__ . '/../../../database/db.php');
require_once(__DIR__ . '/../../../models/User.php');
require_once(__DIR__ . '/../../../controllers/UserController.php');
require_once(__DIR__ . '/../../../services/PasswordHasher.php');
require_once(__DIR__ . '/../../../services/UserManagerImpl.php');
require_once(__DIR__ . '/../../../validation/update_login_validation.php');

// エラーレポーティングを設定
error_reporting(E_ALL);
ini_set('display_errors', 1);

// HTTPヘッダーのセキュリティ対策
header('X-Frame-Options: DENY');

// セッションIDの再生成
session_regenerate_id(true);

$userController = new UserController();

// ログインしていない場合、ログインページにリダイレクト
if (!$userController->isUserLoggedIn()) {

    $_SESSION['toast_message'] = 'ログインしてください。';

    header("Location: login.php");
    exit;
}

// ログインしているユーザーの情報を取得
$userManager = new UserManagerImpl();
$user = $userManager->getUserById($_SESSION['user_id']);

// プロフィールが登録されていない場合、プロフィール登録画面にレダイレクト
$existingProfile = getUserProfile($_SESSION['user_id']);
if (!$existingProfile) {
    $_SESSION['toast_profile_input_message'] = 'プロフィールを登録してください。';
    header("Location: http://localhost:3000/views/users/profile_input.php");
    exit;
}

$errors = [];
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRFトークンの検証
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("CSRF攻撃を検知");
    }

    // バリデーションエラーを取得
    $errors = validateUpdateLoginInfo($_POST);

    // メールアドレスが既に存在するか確認
    $email = $_POST['email'];
    if ($email !== $user->getEmail() && $userManager->getUserByEmail($email)) {
        $errors['email'] = 'このメールアドレスは既に使用されています。';
    }

    // バリデーションエラーがない場合、UserControllerのupdateLoginInfoメソッドを呼び出す。
    if (empty($errors)) {
        $userController->updateLoginInfo();
        header("Location: http://localhost:3000/views/users/profile.php");
        exit;
    } else {
        // バリデーションエラーがある場合、エラーメッセージとフォームの値をセッションに保存し、フォームページにリダイレクトします。
        $_SESSION['errors'] = $errors;
        $_SESSION['old'] = $_POST;
        header("Location: http://localhost:3000/views/users/auth/update_loginInfo.php");
        exit;
    }
} else {
    // GETリクエストの場合、セッションからエラーメッセージとフォームの値を取得し、それを表示します。
    $errors = $_SESSION['errors'] ?? [];
    $old = $_SESSION['old'] ?? [];
    unset($_SESSION['errors']);  // エラーメッセージをセッションから削除します。
    unset($_SESSION['old']);  // フォームの値をセッションから削除します。
}

// CSRFトークンの生成
$csrfToken = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrfToken;

require_once(__DIR__ . '/../../form/update_loginInfo_form.php');

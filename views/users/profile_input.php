<?php
// UserController.phpをインクルード
require_once(__DIR__ . '/../../controllers/UserController.php');
require_once(__DIR__ . '/../../models/UserProfile.php');
require_once(__DIR__ . '/../../validation/profile_validation.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

// HTTPヘッダーのセキュリティ対策
header('X-Frame-Options: DENY');

ob_start(); // 出力バッファリングを開始

// ユーザーコントローラークラスのインスタンスを作成
$userController = new UserController();

// ログインしていない場合、ログインページにリダイレクト
if (!$userController->isUserLoggedIn()) {
    $_SESSION['toast_message'] = 'ログインしてください。';
    header("Location: http://localhost:3000/views/users/auth/login.php");
    exit;
}

// ユーザープロフィールが既に登録済みの場合、profile.phpにリダイレクト
$existingProfile = getUserProfile($_SESSION['user_id']);
if ($existingProfile) {
    header("Location: profile.php");
    exit;
}

// ユーザーがフォームを提出した場合
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRFトークンの検証
    if (isset($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        // バリデーションを行う
        $errors = validateProfileInput(
            htmlspecialchars($_POST['full_name'], ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($_POST['bio'], ENT_QUOTES, 'UTF-8')
        );

        if (empty($errors)) {
            // バリデーションエラーがない場合、プロフィールを保存
            // editProfileメソッドを呼び出し
            $userController->storeProfile();
            $_SESSION['profile_create_message'] = 'プロフィールが登録しました。';
            // storeProfileメソッド内で新しいプロフィールが作成された場合、profile.phpにリダイレクト
            // var_dump($_SESSION['profile_create_message']);
            header("Location: profile.php");
            ob_end_flush(); // 出力バッファリングを終了
            exit;
        } else {
            // バリデーションエラーがある場合、エラーメッセージをセッションに保存
            $_SESSION['validation_errors'] = $errors;
            $_SESSION['old_input'] = $_POST;

            // バリデーションエラーがある場合でもリダイレクト
            header("Location: http://localhost:3000/views/users/profile_input.php");
            ob_end_flush(); // 出力バッファリングを終了
            exit;
        }
    } else {
        die("不正なリクエストが検出されました。");
    }
}

$errors = isset($_SESSION['validation_errors']) ? $_SESSION['validation_errors'] : [];
$oldInput = isset($_SESSION['old_input']) ? $_SESSION['old_input'] : [];

// CSRFトークンの生成
$csrfToken = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrfToken;

require_once(__DIR__ . '/../form/profile_input_form.php');

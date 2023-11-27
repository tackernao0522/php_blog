<?php
session_start(); // セッションを開始
require_once(__DIR__ . '/../../../sendMail/ResetRequestHelper.php');
require_once(__DIR__ . '/../../../database/db.php');
require_once(__DIR__ . '/../../../services/UserManagerImpl.php');
require_once(__DIR__ . '/../../../controllers/UserController.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

// HTTPヘッダーのセキュリティ対策
header('X-Frame-Options: DENY');

use function sendMail\sendResetRequestEmail;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];

    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    if ($email === false) {
        // メールアドレスが入力されていない、または無効な形式の場合はエラーメッセージを表示
        $errors['email'] = '有効なメールアドレスを入力して下さい。';
    } else {
        // UserManagerImplのインスタンスを作成
        $userManager = new UserManagerImpl();

        // ユーザーが存在するか確認
        $user = $userManager->getUserByEmail($email);

        if ($user) {
            // ユーザーが存在する場合はトークンを生成してデータベースに保存
            $token = bin2hex(random_bytes(16)); // 32文字のランダムなトークンを生成
            $expires = time() + 600; // トークンの有効期限を現在時刻から10分後に設定

            $stmt = $db->prepare('INSERT INTO password_reset_requests(email, token, expires) VALUES (:email, :token, :expires)');

            $stmt->execute([
                ':email' => $email,
                ':token' => $token,  // ハッシュ化せずにトークンを保存
                ':expires' => date('Y-m-d H:i:s', $expires),
            ]);

            // その後、トークンを含むリセットリンクをメールで送信
            sendResetRequestEmail($email, $token);
        } else {
            // ユーザーが存在しない場合はエラーメッセージを表示
            $errors['user'] = '該当のメールアドレスを持つユーザーが存在しません。';
        }
    }

    if (!empty($errors) || isset($_SESSION['reset_request_message'])) {
        // エラーがある場合は、エラーメッセージをセッションに保存してリダイレクト
        $_SESSION['errors'] = $errors;
        header("Location: http://localhost:3000/views/users/auth/reset_request.php");
        exit;
    }
} else {
    // GETリクエストの場合、セッションからエラーメッセージを取得して表示
    $errors = $_SESSION['errors'] ?? [];
    unset($_SESSION['errors']);  // エラーメッセージをセッションから削除
}

require_once(__DIR__ . '/../../form/reset_request_form.php');

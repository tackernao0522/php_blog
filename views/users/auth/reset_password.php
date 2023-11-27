<?php
session_start();
require_once(__DIR__ . '/../../../database/db.php');
require_once(__DIR__ . '/../../../services/UserManagerImpl.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

// HTTPヘッダーのセキュリティ対策
header('X-Frame-Options: DENY');

$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']);

$token = $_SESSION['token'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = isset($_POST['token']) ? htmlspecialchars($_POST['token']) : $token; // サニタイズ
    $_SESSION['token'] = $token;
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // バリデーション
    if (empty($password)) {
        $errors['password'] = 'パスワードを入力してください。';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'パスワードは少なくとも8文字以上で入力してください。';
    }
    if (empty($password_confirm) || $password !== $password_confirm) {
        $errors['password_confirm'] = 'パスワードが一致しません。';
    }

    if (empty($errors)) {
        /// 対応するユーザーのパスワードを新しいものに更新
        $stmt = $db->prepare('SELECT * FROM password_reset_requests WHERE token = :token');
        $stmt->execute([':token' => $token]);  // ハッシュ化せずにトークンを検索
        $resetRequest = $stmt->fetch();

        if ($resetRequest) {
            if (strtotime($resetRequest['expires']) > time()) {
                // 対応するユーザーのパスワードを新しいものに更新
                $stmt = $db->prepare('UPDATE users SET password = :password WHERE email = :email');
                $stmt->execute([
                    ':password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
                    ':email' => $resetRequest['email'],
                ]);

                // パスワードリセットリクエストを削除
                $stmt = $db->prepare('DELETE FROM password_reset_requests WHERE token = :token');
                $stmt->execute([':token' => $token]);

                // 成功メッセージを表示
                $_SESSION['reset_password_message'] = 'パスワードが正常にリセットされました。';
                session_regenerate_id(true);  // セッションIDを再生成
                header("Location: http://localhost:3000/views/users/auth/login.php");
                exit;
            } else {
                $errors['token'] = 'トークンの有効期限が切れています。';
            }
        } else {
            $errors['token'] = '無効なトークンです。';
        }
    }

    // バリデーションエラーがある場合、エラーメッセージをセッションに保存し、フォームページにリダイレクトします。
    $_SESSION['errors'] = $errors;
    header("Location: http://localhost:3000/views/users/auth/reset_password.php");
    exit;
}

require_once(__DIR__ . '/../../form/reset_password_form.php');

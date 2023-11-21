<?php
session_start();  // Move this to the top of your script

require_once(__DIR__ . '/../../../database/config.php');
require_once(__DIR__ . '/../../../validation/signup_validation.php');
require_once(__DIR__ . '/../../../services/UserImpl.php');
require(__DIR__ . '/../../../vendor/autoload.php');

use SendGrid\Mail\Mail;

$email = new Mail();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// HTTPヘッダーのセキュリティ対策
header('X-Frame-Options: DENY');

// アクセス制御: ログイン済みの場合、プロフィールページにリダイレクト
if (isset($_SESSION['user_id'])) {
    header("Location: localhost:3000/views/users/profile.php");
    exit;
}

$errors = [];
$old = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $csrfToken;

    $errors = validateRegistration($_POST);

    if (empty($errors)) {
        $passwordHasher = new PasswordHasher();
        $hashedPassword = $passwordHasher->hashPassword($_POST['password']);

        $userId = 1; // ユーザーIDを適切な方法で設定
        $fullName = 'ユーザーのフルネーム'; // ユーザーのフルネームを設定
        $bio = 'ユーザーのバイオ'; // ユーザーのバイオを設定

        $user = new UserImpl(0, $_POST['username'], $_POST['email'], $hashedPassword, $userId, $fullName, $bio);
        $userManager = new UserManagerImpl();

        if ($userManager->createUser($user)) {
            // ログイン成功後にセッションIDを再生成
            session_regenerate_id(true);

            require_once(__DIR__ . '/../../../sendMail/EmailHelper.php');
            // メール送信
            \sendMail\sendRegistrationEmail($user->getEmail());

            $_SESSION['toast_message'] = 'ログインしてください。';

            header("Location: http://localhost:3000/views/users/auth/login.php");
            exit;
        } else {
            echo 'ユーザー登録に失敗しました。';
        }
    } else {
        // バリデーションエラーがある場合、エラーメッセージとフォームの値をセッションに保存し、フォームページにリダイレクトします。
        $_SESSION['errors'] = $errors;
        $_SESSION['old'] = $_POST;
        header("Location: http://localhost:3000/views/users/auth/register.php");
        exit;
    }
} else {
    // GETリクエストの場合、セッションからエラーメッセージとフォームの値を取得し、それを表示します。
    $errors = $_SESSION['errors'] ?? [];
    $old = $_SESSION['old'] ?? [];
    unset($_SESSION['errors']);  // エラーメッセージをセッションから削除します。
    unset($_SESSION['old']);  // フォームの値をセッションから削除します。
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ユーザー登録</title>
    <link rel="stylesheet" type="text/css" href="../../../frontend/style.css">
</head>

<body>
    <?php require_once(__DIR__ . '/../../component/header.php') ?>
    <h1>ユーザー登録</h1>
    <div class="content">
        <form id="register-form" method="POST">
            <!-- CSRFトークンをフォーム内に追加 -->
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <label for="username">ユーザー名:</label>
            <input type="text" id="username" name="username" value="<?php echo isset($old['username']) ? htmlspecialchars($old['username'], ENT_QUOTES, 'UTF-8') : ''; ?>">
            <?php if (isset($errors['username'])) echo '<span class="error">' . htmlspecialchars($errors['username'], ENT_QUOTES, 'UTF-8') . '</span>'; ?><br>

            <label for="email">メールアドレス:</label>
            <input type="email" id="email" name="email" value="<?php echo isset($old['email']) ? htmlspecialchars($old['email'], ENT_QUOTES, 'UTF-8') : ''; ?>">
            <?php if (isset($errors['email'])) echo '<span class="error">' . htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8') . '</span>'; ?>

            <label for="password">パスワード:</label>
            <input type="password" id="password" name="password">
            <?php if (isset($errors['password'])) echo '<span class="error">' . htmlspecialchars($errors['password'], ENT_QUOTES, 'UTF-8') . '</span>'; ?>

            <label for="password_confirm">パスワード確認</label>
            <input type="password" id="password_confirm" name="password_confirm">
            <?php if (isset($errors['password_confirm'])) echo '<span class="errors">' . htmlspecialchars($errors['password_confirm'], ENT_QUOTES, 'UTF-8') . '</span>'; ?>

            <button type="submit">登録</button>
        </form>
    </div>
    <p id="register-message"></p>
</body>

</html>
<?php
require_once(__DIR__ . '/../../../database/config.php');
require_once(__DIR__ . '/../../../validation/signup_validation.php');
require_once(__DIR__ . '/../../../services/UserImpl.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

// session_start();

// アクセス制御: ログイン済みの場合、プロフィールページにリダイレクト
if (isset($_SESSION['user_id'])) {
    header("Location: localhost:3000/views/users/profile.php");
    exit;
}

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
            if (session_status() == PHP_SESSION_NONE) {
                // セッションがまだ開始されていない場合にのみセッションを開始
                session_start();
            }
            $_SESSION['toast_message'] = 'ログインしてください。';

            header("Location: http://localhost:3000/views/users/auth/login.php");
            exit;
        } else {
            echo 'ユーザー登録に失敗しました。';
        }
    }
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
            <input type="text" id="username" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8') : ''; ?>">
            <?php if (isset($errors['username'])) echo '<span class="error">' . $errors['username'] . '</span>'; ?><br>

            <label for="email">メールアドレス:</label>
            <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8') : ''; ?>">
            <?php if (isset($errors['email'])) echo '<span class="error">' . $errors['email'] . '</span>'; ?>

            <label for="password">パスワード:</label>
            <input type="password" id="password" name="password"><br>
            <?php if (isset($errors['password'])) echo '<span class="error">' . $errors['password'] . '</span>'; ?>

            <label for="password_confirm">パスワード確認</label>
            <input type="password" id="password_confirm" name="password_confirm">
            <?php if (isset($errors['password_confirm'])) echo '<span class="errors">' . $errors['password_confirm'] . '</span>'; ?>

            <button type="submit">登録</button>
        </form>
    </div>
    <p id="register-message"></p>
</body>

</html>
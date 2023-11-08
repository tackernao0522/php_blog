<?php
require_once(__DIR__ . '/../../../models/User.php');
require_once(__DIR__ . '/../../../services/UserManager.php');
require_once(__DIR__ . '/../../../services/UserManagerImpl.php');
require_once(__DIR__ . '/../../../services/PasswordHasher.php');
require_once(__DIR__ . '/../../../database/config.php');
require_once(__DIR__ . '/../../../services/UserImpl.php');
// require_once(__DIR__ . '/../../../views/component/header.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

// session_start();

// アクセス制御: ログイン済みの場合、プロフィールページにリダイレクト
if (isset($_SESSION['user_id'])) {
    header("Location: localhost:3000/views/users/profile.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ここに修正を適用
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_SPECIAL_CHARS);

    $passwordHasher = new PasswordHasher();
    $hashedPassword = $passwordHasher->hashPassword($password);

    // ここでUserProfileに関する情報を取得または設定する必要があります
    $userId = 1; // ユーザーIDを適切な方法で設定
    $fullName = 'ユーザーのフルネーム'; // ユーザーのフルネームを設定
    $bio = 'ユーザーのバイオ'; // ユーザーのバイオを設定

    $user = new UserImpl(0, $username, $email, $hashedPassword, $userId, $fullName, $bio);
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

// CSRFトークンの生成
$csrfToken = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrfToken;
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
            <input type="text" id="username" name="username" required><br>
            <label for="email">メールアドレス:</label>
            <input type="email" id="email" name="email" required><br>
            <label for="password">パスワード:</label>
            <input type="password" id="password" name="password" required><br>
            <button type="submit">登録</button>
        </form>
    </div>
    <p id="register-message"></p>
</body>

</html>
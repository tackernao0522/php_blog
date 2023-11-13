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
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ユーザーログイン</title>
    <link rel="stylesheet" type="text/css" href="../../../frontend/style.css">
</head>

<body>
    <?php require_once(__DIR__ . '/../../component/header.php'); ?>
    <h1>ユーザーログイン</h1>
    <div class="content">
        <form id="login-form" method="POST" action="login.php">
            <!-- CSRFトークンをフォーム内に追加 -->
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <label for="username">ユーザー名:</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($_SESSION['old']['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            <?php if (isset($errors['username'])) echo '<span class="error">' . htmlspecialchars($errors['username'], ENT_QUOTES, 'UTF-8') . '</span>';
            unset($_SESSION['errors']['username']); ?>
            <label for="password">パスワード:</label>
            <input type="password" id="password" name="password">
            <?php if (isset($errors['password'])) echo '<span class="error">' . htmlspecialchars($errors['password'], ENT_QUOTES, 'UTF-8') . '</span>';
            unset($_SESSION['errors']['password']); ?>
            <button type="submit">ログイン</button>
        </form>
    </div>
    <!-- Toastifyの読み込み -->
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script>
        <?php if (isset($_SESSION['toast_message'])) : ?>
            Toastify({
                text: "<?php echo $_SESSION['toast_message']; ?>",
                duration: 3000,
                close: true,
                gravity: "top",
                position: "right",
                style: {
                    background: "linear-gradient(to right, #00b09b, #96c93d)",
                }
            }).showToast();
        <?php endif; ?>
        <?php if (isset($_SESSION['logout_message'])) : ?>
            Toastify({
                text: "<?php echo $_SESSION['logout_message']; ?>",
                duration: 3000,
                close: true,
                gravity: "top",
                position: "right",
                style: {
                    background: "linear-gradient(to right, #00b09b, #96c93d)",
                }
            }).showToast();
            // session_destroy();
            <?php unset($_SESSION['logout_message']) ?>
            // unset($_SESSION['logout_message']);
        <?php endif; ?>
    </script>
    <?php
    // ページの最後で古い値をリセット
    $_SESSION['old'] = [];
    ?>
</body>

</html>
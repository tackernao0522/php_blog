<?php
// セッション開始
session_start();

// データベースとUserクラスを読み込む
require_once(__DIR__ . '/../../../database/db.php');
require_once(__DIR__ . '/../../../models/User.php');
require_once(__DIR__ . '/../../../controllers/UserController.php');
require_once(__DIR__ . '/../../../services/PasswordHasher.php');

// エラーレポーティングを設定
error_reporting(E_ALL);
ini_set('display_errors', 1);

// HTTPヘッダーのセキュリティ対策
header('X-Frame-Options: DENY');

// セッションIDの再生成
session_regenerate_id(true);

$userController = new UserController();

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRFトークンの検証
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("CSRF攻撃を検知");
    }

    // UserControllerのupdateLoginInfoメソッドを呼び出す。
    $userController->updateLoginInfo();
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
    <title>ログイン情報更新</title>
    <link rel="stylesheet" type="text/css" href="../../../frontend/style.css">
</head>

<body>
    <?php require_once(__DIR__ . '/../../component/header.php') ?>
    <h1>ログイン情報更新</h1>
    <div class="content">
        <form id="update-form" method="POST">
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

            <button type="submit">更新</button>
        </form>
    </div>
    <p id="register-message"></p>
</body>

</html>
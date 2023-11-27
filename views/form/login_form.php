<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ユーザーログイン</title>
    <link rel="stylesheet" type="text/css" href="../../../frontend/style.css">
</head>

<body>
    <?php require_once(__DIR__ . '/../component/header.php') ?>
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
            <a class="reset_request" href="../auth/reset_request.php">パスワードを忘れた場合</a>
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
            <?php unset($_SESSION['toast_message']) ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['login_update_message'])) : ?>
            Toastify({
                text: "<?php echo $_SESSION['login_update_message']; ?>",
                duration: 3000,
                close: true,
                gravity: "top",
                position: "right",
                style: {
                    background: "linear-gradient(to right, #00b09b, #96c93d)",
                }
            }).showToast();
            <?php unset($_SESSION['login_update_message']) ?>
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
        <?php endif; ?>
        <?php if (isset($_SESSION['reset_password_message'])) : ?>
            Toastify({
                text: "<?php echo $_SESSION['reset_password_message']; ?>",
                duration: 3000,
                close: true,
                gravity: "top",
                position: "right",
                style: {
                    background: "linear-gradient(to right, #00b09b, #96c93d)",
                }
            }).showToast();
            // session_destroy();
            <?php unset($_SESSION['reset_password_message']) ?>
        <?php endif; ?>
    </script>
    <?php
    // ページの最後で古い値をリセット
    $_SESSION['old'] = [];
    ?>
</body>

</html>
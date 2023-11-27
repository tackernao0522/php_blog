<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>パスワードリセット</title>
    <link rel="stylesheet" type="text/css" href="../../../frontend/style.css">
</head>

<body>
    <?php require_once(__DIR__ . '/../component/header.php') ?>
    <h1>パスワードリセット</h1>
    <div class="content">
        <form id="reset-password-form" method="POST" action="reset_password.php">
            <?php if (isset($_GET['token'])) : ?>
                <input type="hidden" name="token" value="<?php echo $_GET['token']; ?>">
            <?php endif; ?>
            <label for="password">新しいパスワード:</label>
            <input type="password" id="password" name="password">
            <?php if (isset($errors['password'])) echo '<span class="error">' . htmlspecialchars($errors['password'], ENT_QUOTES, 'UTF-8') . '</span>'; ?>
            <label for="password_confirm">パスワード確認:</label>
            <input type="password" id="password_confirm" name="password_confirm">
            <?php if (isset($errors['password_confirm'])) echo '<span class="error">' . htmlspecialchars($errors['password_confirm'], ENT_QUOTES, 'UTF-8') . '</span>'; ?>
            <?php if (isset($errors['token'])) echo '<span class="error">' . htmlspecialchars($errors['token'], ENT_QUOTES, 'UTF-8') . '</span>'; ?>
            <button type="submit">パスワードをリセット</button>
        </form>
    </div>
</body>

</html>

</html>
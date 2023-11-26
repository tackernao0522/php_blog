<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン情報更新</title>
    <link rel="stylesheet" type="text/css" href="../../../frontend/style.css">
</head>

<body>
    <?php require_once(__DIR__ . '/../component/header.php') ?>
    <h1>ログイン情報更新</h1>
    <div class="content">
        <form id="update-form" method="POST">
            <!-- CSRFトークンをフォーム内に追加 -->
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <label for="username">ユーザー名:</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($old['username'] ?? $user->getUsername(), ENT_QUOTES, 'UTF-8'); ?>">
            <?php if (isset($errors['username'])) echo '<span class="error">' . htmlspecialchars($errors['username'], ENT_QUOTES, 'UTF-8') . '</span>'; ?><br>

            <label for="email">メールアドレス:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($old['email'] ?? $user->getEmail(), ENT_QUOTES, 'UTF-8'); ?>">
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

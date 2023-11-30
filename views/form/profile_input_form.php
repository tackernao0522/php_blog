<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>プロフィール登録</title>
    <link rel="stylesheet" type="text/css" href="../../frontend/style.css">
</head>

<body>
    <?php require_once(__DIR__ . '/../../views/component/header.php') ?>
    <h1>プロフィール登録</h1>
    <div class="content">
        <form id="profile-form" method="POST">
            <!-- CSRFトークンをフォーム内に追加 -->
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <label for="full_name">フルネーム:</label>
            <input type="text" id="full_name" name="full_name" value="<?php echo isset($oldInput['full_name']) ? htmlspecialchars($oldInput['full_name'], ENT_QUOTES, 'UTF-8') : ''; ?>">
            <?php if (isset($_SESSION['validation_errors']['full_name'])) : ?>
                <p class="error"><?php echo htmlspecialchars($_SESSION['validation_errors']['full_name'], ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?><br>

            <label for="bio">自己紹介:</label>
            <textarea id="bio" name="bio" rows="4" cols="50"><?php echo isset($oldInput['bio']) ? htmlspecialchars($oldInput['bio'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
            <?php if (isset($_SESSION['validation_errors']['bio'])) : ?>
                <p class="error"><?php echo htmlspecialchars($_SESSION['validation_errors']['bio'], ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?><br>

            <button type="submit">保存</button>
        </form>
    </div>
</body>

</html>
<?php
// バリデーションエラーが表示された後にセッションからエラーメッセージを削除
if (isset($_SESSION['validation_errors'])) {
    unset($_SESSION['validation_errors']);
}
if (isset($_SESSION['old_input'])) {
    unset($_SESSION['old_input']);
}
?>
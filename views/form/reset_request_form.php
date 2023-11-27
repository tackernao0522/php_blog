<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>パスワードリセットリクエスト</title>
    <link rel="stylesheet" type="text/css" href="../../../frontend/style.css">
</head>

<body>
    <?php require_once(__DIR__ . '/../component/header.php') ?>
    <h1>パスワードリセットリクエスト</h1>
    <div class="content">
        <form id="reset-request-form" method="POST" action="reset_request.php">
            <label for="email">メールアドレス:</label>
            <input type="email" id="email" name="email">
            <?php if (isset($errors['email'])) echo '<span class="error">' . htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8') . '</span>'; ?>
            <?php if (isset($errors['user'])) echo '<span class="error">' . htmlspecialchars($errors['user'], ENT_QUOTES, 'UTF-8') . '</span>'; ?>
            <button type="submit">リセットリンクを送信</button>
        </form>
    </div>

    <!-- Toastifyの読み込み -->
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script>
        <?php if (isset($_SESSION['reset_request_message'])) : ?>
            Toastify({
                text: "<?php echo $_SESSION['reset_request_message']; ?>",
                duration: 3000,
                close: true,
                gravity: "top",
                position: "right",
                style: {
                    background: "linear-gradient(to right, #00b09b, #96c93d)",
                }
            }).showToast();
            <?php unset($_SESSION['reset_request_message']) ?>
        <?php endif; ?>
    </script>
</body>

</html>
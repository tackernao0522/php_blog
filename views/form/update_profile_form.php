<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once(__DIR__ . '/../../controllers/UserController.php');

$userController = new UserController();
$data = [];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $data = $userController->showUpdateProfileForm();
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>プロフィール更新</title>
    <link rel="stylesheet" type="text/css" href="../../frontend/style.css">
</head>

<body>
    <?php require_once(__DIR__ . '/../component/header.php'); ?>
    <h1>プロフィール更新</h1>
    <div class="content">
        <form id="profile-form" method="POST">
            <!-- CSRFトークンをフォーム内に追加 -->
            <input type="hidden" name="csrf_token" value="<?php echo $data['csrfToken']; ?>">
            <label for="full_name">フルネーム:</label>
            <input type="text" id="full_name" name="full_name" value="<?php echo isset($data['existingProfile']) ? htmlspecialchars($data['existingProfile']->getFullName(), ENT_QUOTES, 'UTF-8') : ''; ?>">
            <?php if (isset($data['errors']['full_name'])) : ?>
                <p class="error"><?php echo htmlspecialchars($data['errors']['full_name'], ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?><br>

            <label for="bio">自己紹介:</label>
            <textarea id="bio" name="bio" rows="4" cols="50"><?php echo isset($data['existingProfile']) ? htmlspecialchars($data['existingProfile']->getBio(), ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
            <?php if (isset($data['errors']['bio'])) : ?>
                <p class="error"><?php echo htmlspecialchars($data['errors']['bio'], ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?><br>

            <button type="submit">更新</button>
        </form>
    </div>

    <!-- Toastifyの読み込み -->
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

    <script>
        <?php if (isset($_SESSION['profile_update_message'])) : ?>
            Toastify({
                text: "<?php echo $_SESSION['profile_update_message']; ?>",
                duration: 3000,
                close: true,
                gravity: "top",
                position: "right",
                style: {
                    background: "linear-gradient(to right, #00b09b, #96c93d)",
                }
            }).showToast();
            <?php unset($_SESSION['profile_update_message']); ?>
        <?php endif; ?>
    </script>
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
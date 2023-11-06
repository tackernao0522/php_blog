<?php
// UserController.phpをインクルード
require_once 'controllers/UserController.php';
require_once 'UserProfile.php';

// ユーザーコントローラークラスのインスタンスを作成
$userController = new UserController();

// ログインしていない場合、ログインページにリダイレクト
if (!$userController->isUserLoggedIn()) {
    header("Location: login.php");
    exit;
}

// ユーザープロフィールが既に登録済みの場合、profile.phpにリダイレクト
$existingProfile = getUserProfile($_SESSION['user_id']);
if ($existingProfile) {
    header("Location: profile.php");
    exit;
}

// ユーザーがフォームを提出した場合
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRFトークンの検証
    if (isset($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        // editProfileメソッドを呼び出し
        $userController->storeProfile();
        // storeProfileメソッド内で新しいプロフィールが作成された場合、profile.phpにリダイレクト
        header("Location: profile.php");
        exit;
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
    <title>ユーザープロフィール編集</title>
    <link rel="stylesheet" type="text/css" href="frontend/style.css">
</head>

<body>
    <h1>プロフィール登録</h1>
    <form id="profile-form" method="POST">
        <!-- CSRFトークンをフォーム内に追加 -->
        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
        <label for="full_name">フルネーム:</label>
        <input type="text" id="full_name" name="full_name" required><br>
        <label for="bio">バイオ:</label>
        <input type="text" id="bio" name="bio"><br>
        <button type="submit">保存</button>
    </form>
</body>

<!-- Toastifyの読み込み -->
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

<script>
    <?php if (isset($_SESSION['toast_profile_input_message'])) : ?>
        Toastify({
            text: "<?php echo $_SESSION['toast_profile_input_message']; ?>",
            duration: 3000,
            close: true,
            gravity: "top",
            position: "right",
            style: {
                background: "linear-gradient(to right, #00b09b, #96c93d)",
            }
        }).showToast();
    <?php endif; ?>
</script>

</html>
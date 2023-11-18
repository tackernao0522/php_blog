<?php
// UserController.phpをインクルード
require_once(__DIR__ . '/../../controllers/UserController.php');
require_once(__DIR__ . '/../../models/UserProfile.php');
require_once(__DIR__ . '/../../validation/profile_validation.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

// HTTPヘッダーのセキュリティ対策
header('X-Frame-Options: DENY');

ob_start(); // 出力バッファリングを開始

// ユーザーコントローラークラスのインスタンスを作成
$userController = new UserController();

// ログインしていない場合、ログインページにリダイレクト
if (!$userController->isUserLoggedIn()) {
    header("Location: http://localhost:3000/views/users/auth/login.php");
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
        // バリデーションを行う
        $errors = validateProfileInput(
            htmlspecialchars($_POST['full_name'], ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($_POST['bio'], ENT_QUOTES, 'UTF-8')
        );

        if (empty($errors)) {
            // バリデーションエラーがない場合、プロフィールを保存
            // editProfileメソッドを呼び出し
            $userController->storeProfile();
            $_SESSION['profile_create_message'] = 'プロフィールが登録しました。';
            // storeProfileメソッド内で新しいプロフィールが作成された場合、profile.phpにリダイレクト
            // var_dump($_SESSION['profile_create_message']);
            header("Location: profile.php");
            ob_end_flush(); // 出力バッファリングを終了
            exit;
        } else {
            // バリデーションエラーがある場合、エラーメッセージをセッションに保存
            $_SESSION['validation_errors'] = $errors;
            $_SESSION['old_input'] = $_POST;

            // バリデーションエラーがある場合でもリダイレクト
            header("Location: http://localhost:3000/views/users/profile_input.php");
            ob_end_flush(); // 出力バッファリングを終了
            exit;
        }
    } else {
        die("不正なリクエストが検出されました。");
    }
}

$errors = isset($_SESSION['validation_errors']) ? $_SESSION['validation_errors'] : [];
$oldInput = isset($_SESSION['old_input']) ? $_SESSION['old_input'] : [];

// CSRFトークンの生成
$csrfToken = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrfToken;
?>

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
        <?php unset($_SESSION['toast_profile_input_message']) ?>
    <?php endif; ?>
</script>

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
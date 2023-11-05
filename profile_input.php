<?php
// UserController.phpをインクルード
require_once 'controllers/UserController.php';

// ユーザーコントローラークラスのインスタンスを作成
$userController = new UserController();

// ログインしていない場合、ログインページにリダイレクト
if (!$userController->isUserLoggedIn()) {
    header("Location: login.php");
    exit;
}

// ユーザーがフォームを提出した場合
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRFトークンの検証
    if (isset($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        // editProfileメソッドを呼び出し
        $userController->editProfile();
        
        // editProfileメソッド内で新しいプロフィールが作成された場合、profile.phpにリダイレクト
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
    <h1>ユーザープロフィール編集</h1>
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

</html>
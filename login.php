<?php
require_once 'User.php';
require_once 'db.php'; // データベース接続用ファイル

$loginMessage = ''; // ログインメッセージを初期化

// ユーザー名とパスワードがフォームから送信されたかどうかを確認
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // データベースからユーザー情報を取得するクエリを実行
    $stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userData) {
        // ユーザー情報が取得できた場合
        $user = new User($userData['id'], $userData['username'], $userData['email'], $userData['password']);

        $userProvidedPassword = $password;

        // パスワードの検証
        if ($user->verifyPassword($userProvidedPassword)) {
            $loginMessage = 'パスワードが一致しました。';
        } else {
            $loginMessage = 'パスワードが一致しません。';
        }
    } else {
        // ユーザー情報が見つからなかった場合
        $loginMessage = 'ユーザーが存在しません。';
    }
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ユーザーログイン</title>
    <link rel="stylesheet" type="text/css" href="frontend/style.css">
</head>

<body>
    <h1>ユーザーログイン</h1>
    <form id="login-form" method="POST">
        <label for="username">ユーザー名：</label>
        <input type="text" id="username" name="username" required><br>
        <label for="password">パスワード：</label>
        <input type="password" id="password" name="password" required><br>
        <button type="submit">ログイン</button>
    </form>
    <p id="login-message"><?php echo $loginMessage; ?></p>
</body>

</html>

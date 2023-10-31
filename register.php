<?php
require_once 'User.php';
require_once 'UserManager.php';
require_once 'UserManagerImpl.php';
require_once 'PasswordHasher.php';
require_once 'config.php';
require_once 'UserImpl.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $passwordHasher = new PasswordHasher();
    $hashedPassword = $passwordHasher->hashPassword($password);

    $user = new UserImpl(0, $username, $email, $hashedPassword);
    $userManager = new UserManagerImpl();

    if ($userManager->createUser($user)) {
        echo 'ユーザー登録が成功しました。';
    } else {
        echo 'ユーザー登録に失敗しました。';
    }
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ユーザー登録</title>
    <link rel="stylesheet" type="text/css" href="frontend/style.css">
</head>

<body>
    <h1>ユーザー登録</h1>
    <form id="register-form" method="POST">
        <label for="username">ユーザー名:</label>
        <input type="text" id="username" name="username" required><br>
        <label for="email">メールアドレス:</label>
        <input type="email" id="email" name="email" required><br>
        <label for="password">パスワード:</label>
        <input type="password" id="password" name="password" required><br>
        <button type="submit">登録</button>
    </form>
    <p id="register-message"></p>
</body>

</html>
<?php
// reset_password.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $password = $_POST['password'];
    // トークンがデータベースに存在し、有効であることを確認
    // 対応するユーザーのパスワードを新しいものに更新
    // ...
}
?>
<form method="POST" action="reset_password.php">
    <input type="hidden" name="token" value="<?php echo $_GET['token']; ?>">
    <label for="password">新しいパスワード:</label>
    <input type="password" id="password" name="password" required>
    <button type="submit">パスワードをリセット</button>
</form>

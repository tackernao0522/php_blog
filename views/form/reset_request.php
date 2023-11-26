<?php
require_once(__DIR__ . '/../../sendMail/ResetRequestHelper.php');
require_once(__DIR__ . '/../../database/db.php');
require_once(__DIR__ . '/../../services/UserManagerImpl.php');

use function sendMail\sendResetRequestEmail;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    // UserManagerImplのインスタンスを作成
    $userManager = new UserManagerImpl();

    // ユーザーが存在するか確認
    $user = $userManager->getUserByEmail($email);

    if ($user) {
        // ユーザーが存在する場合はトークンを生成してデータベースに保存
        $token = bin2hex(random_bytes(16)); // 32文字のランダムなトークンを生成
        $expires = time() + 600; // トークンの有効期限を現在時刻から10分後に設定

        $stmt = $db->prepare('INSERT INTO password_reset_requests(email, token, expires) VALUES (:email, :token, :expires)');

        $stmt->execute([
            ':email' => $email,
            ':token' => password_hash($token, PASSWORD_DEFAULT), // トークンをハッシュ化して保存
            ':expires' => date('Y-m-d H:i:s', $expires),
        ]);

        // その後、トークンを含むリセットリンクをメールで送信
        sendResetRequestEmail($email, $token);
    } else {
        // ユーザーが存在しない場合はエラーメッセージを表示
        echo '該当のメールアドレスを持つユーザーが存在しません。';
    }
}
?>

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
            <input type="email" id="email" name="email" required>
            <button type="submit">リセットリンクを送信</button>
        </form>
    </div>
</body>

</html>
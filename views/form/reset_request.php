<?php
session_start(); // セッションを開始
require_once(__DIR__ . '/../../sendMail/ResetRequestHelper.php');
require_once(__DIR__ . '/../../database/db.php');
require_once(__DIR__ . '/../../services/UserManagerImpl.php');
require_once(__DIR__ . '/../../controllers/UserController.php');

use function sendMail\sendResetRequestEmail;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];

    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    if ($email === false) {
        // メールアドレスが入力されていない、または無効な形式の場合はエラーメッセージを表示
        $errors['email'] = '有効なメールアドレスを入力して下さい。';
    } else {
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
            $errors['user'] = '該当のメールアドレスを持つユーザーが存在しません。';
        }
    }

    if (!empty($errors) || isset($_SESSION['reset_request_message'])) {
        // エラーがある場合は、エラーメッセージをセッションに保存してリダイレクト
        $_SESSION['errors'] = $errors;
        header("Location: reset_request.php");
        exit;
    }
} else {
    // GETリクエストの場合、セッションからエラーメッセージを取得して表示
    $errors = $_SESSION['errors'] ?? [];
    unset($_SESSION['errors']);  // エラーメッセージをセッションから削除
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
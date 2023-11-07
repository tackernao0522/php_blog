<?php
// 必要なファイルをインクルード
require_once 'User.php';
require_once 'UserManager.php';
require_once 'UserManagerImpl.php';
require_once 'PasswordHasher.php';
require_once 'config.php';
require_once 'UserImpl.php';
require_once 'controllers/UserController.php';
require_once 'header.php';

// エラーレポーティングを設定
error_reporting(E_ALL);
ini_set('display_errors', 1);

// セッション開始
// session_start();

// UserController クラスのインスタンスを作成
$userController = new UserController();

if ($userController->isUserLoggedIn()) {
    // ログイン済みの場合、プロフィールページにリダイレクト
    header("Location: profile.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRFトークンの検証
    if (isset($_SESSION['csrf_token']) && isset($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        // POSTデータからユーザー名とパスワードを取得
        $username = $_POST['username'];
        $password = $_POST['password'];

        // ユーザー認証
        $userManager = new UserManagerImpl();
        $user = $userManager->getUserByUsername($username);

        if ($user && $user->verifyPassword($password)) {
            // ログインが成功した場合、セッションにuser_idを設定
            $_SESSION['user_id'] = $user->getId();

            // プロフィールが登録されていない場合、プロフィール登録画面にリダイレクト
            $userProfile = getUserProfile($user->getId());
            if (!$userProfile) {
                $_SESSION['toast_profile_input_message'] = 'プロフィールを登録してください。';
                header("Location: profile_input.php");
                exit;
            }

            // プロフィール情報をセッションに保存
            $_SESSION['userProfile'] = $userProfile;

            header("Location: profile.php"); // プロフィールページにリダイレクト
            exit;
        } else {
            echo "認証エラー";
        }
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
    <title>ユーザーログイン</title>
    <link rel="stylesheet" type="text/css" href="frontend/style.css">
</head>

<body>
    <?php require_once 'header.php'; ?>
    <h1>ユーザーログイン</h1>
    <div class="content">
        <form id="login-form" method="POST" action="login.php">
            <!-- CSRFトークンをフォーム内に追加 -->
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <label for="username">ユーザー名:</label>
            <input type="text" id="username" name="username" required><br>
            <label for="password">パスワード:</label>
            <input type="password" id="password" name="password" required><br>
            <button type="submit">ログイン</button>
        </form>
    </div>
    <!-- Toastifyの読み込み -->
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script>
        <?php if (isset($_SESSION['toast_message'])) : ?>
            Toastify({
                text: "<?php echo $_SESSION['toast_message']; ?>",
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
</body>

</html>
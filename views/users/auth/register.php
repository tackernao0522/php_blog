<?php
require_once(__DIR__ . '/../../../models/User.php');
require_once(__DIR__ . '/../../../services/UserManager.php');
require_once(__DIR__ . '/../../../services/UserManagerImpl.php');
require_once(__DIR__ . '/../../../services/PasswordHasher.php');
require_once(__DIR__ . '/../../../database/config.php');
require_once(__DIR__ . '/../../../services/UserImpl.php');
// require_once(__DIR__ . '/../../../views/component/header.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

// session_start();

// アクセス制御: ログイン済みの場合、プロフィールページにリダイレクト
if (isset($_SESSION['user_id'])) {
    header("Location: localhost:3000/views/users/profile.php");
    exit;
}

// existingUsernames() 関数のスタブ
function existingUsernames($username)
{
    // この関数はユーザー名が既に存在するかどうかを確認するためのロジックを実装する必要があります。
    // 仮に存在しないとする場合は false を返します。
    return false;
}

// emailExists() 関数のスタブ
function emailExists($email)
{
    // この関数はメールアドレスが既に存在するかどうかを確認するためのロジックを実装する必要があります。
    // 仮に存在しないとする場合は false を返します。
    return false;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ここに修正を適用
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_SPECIAL_CHARS);
    $passwordConfirm = filter_input(INPUT_POST, 'password_confirm', FILTER_SANITIZE_SPECIAL_CHARS);

    // バリデーション
    $errors = [];

    // ユーザー名が空でないか確認
    if (empty($username)) {
        $errors['username'] = 'ユーザー名を入力してください。';
    } elseif (
        !(strlen($username) >= 3 && strlen($username) <= 20) ||
        !preg_match('/^[a-zA-Z0-9_-]+$/', $username) ||
        existingUsernames($username)
    ) {
        $errors['username'] = 'ユーザー名は3から20文字の英数字および一部の記号（_-）で構成され、既に使用されていない必要があります。';
    }

    // メールアドレスが空または無効な場合
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'メールアドレスは必須です。有効なメールアドレスを入力してください。';
    } elseif (emailExists($email)) {
        $errors['email'] = 'このメールアドレスは既に使用されています。';
    }

    // パスワードが空ではなく、一定の長さ以上であるか確認
    if (empty($password) || strlen($password) < 8) {
        $errors['password'] = 'パスワードは少なくとも6文字以上で入力してください。';
    }

    // パスワード確認が空でなく、パスワードと一致するか確認
    if (empty($passwordConfirm) || $password !== $passwordConfirm) {
        $errors['password_confirm'] = 'パスワードが一致しません。';
    }



    if (empty($errors)) {
        $passwordHasher = new PasswordHasher();
        $hashedPassword = $passwordHasher->hashPassword($password);

        // ここでUserProfileに関する情報を取得または設定する必要があります
        $userId = 1; // ユーザーIDを適切な方法で設定
        $fullName = 'ユーザーのフルネーム'; // ユーザーのフルネームを設定
        $bio = 'ユーザーのバイオ'; // ユーザーのバイオを設定

        $user = new UserImpl(0, $username, $email, $hashedPassword, $userId, $fullName, $bio);
        $userManager = new UserManagerImpl();

        if ($userManager->createUser($user)) {
            if (session_status() == PHP_SESSION_NONE) {
                // セッションがまだ開始されていない場合にのみセッションを開始
                session_start();
            }
            $_SESSION['toast_message'] = 'ログインしてください。';

            header("Location: http://localhost:3000/views/users/auth/login.php");
            exit;
        } else {
            echo 'ユーザー登録に失敗しました。';
        }
    }
}

// CSRFトークンの生成
$csrfToken = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrfToken;
// var_dump($errors);
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ユーザー登録</title>
    <link rel="stylesheet" type="text/css" href="../../../frontend/style.css">
</head>

<body>
    <?php require_once(__DIR__ . '/../../component/header.php') ?>
    <h1>ユーザー登録</h1>
    <div class="content">
        <form id="register-form" method="POST">
            <!-- CSRFトークンをフォーム内に追加 -->
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <label for="username">ユーザー名:</label>
            <input type="text" id="username" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8') : ''; ?>">
            <?php if (isset($errors['username'])) echo '<span class="error">' . $errors['username'] . '</span>'; ?><br>

            <label for="email">メールアドレス:</label>
            <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8') : ''; ?>">
            <?php if (isset($errors['email'])) echo '<span class="error">' . $errors['email'] . '</span>'; ?>

            <label for="password">パスワード:</label>
            <input type="password" id="password" name="password"><br>
            <?php if (isset($errors['password'])) echo '<span class="error">' . $errors['password'] . '</span>'; ?>

            <label for="password_confirm">パスワード確認</label>
            <input type="password" id="password_confirm" name="password_confirm">
            <?php if (isset($errors['password_confirm'])) echo '<span class="errors">' . $errors['password_confirm'] . '</span>'; ?>

            <button type="submit">登録</button>
        </form>
    </div>
    <p id="register-message"></p>
</body>

</html>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// セッション開始
session_start();

// ログインしていない場合、ログインページにリダイレクト
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// ユーザープロフィール情報を取得
$userProfile = null;

try {
    // データベースに接続
    require_once 'db.php';

    // ユーザープロフィール情報を取得
    $stmt = $db->prepare("SELECT * FROM user_profiles WHERE user_id = :user_id");
    $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();

    $userProfile = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userProfile) {
        // プロフィールが存在しない場合の処理
        // 例: 初めてプロフィールを設定する画面にリダイレクト
        header("Location: profile_input.php");
        exit;
    }
} catch (PDOException $e) {
    echo 'データベースエラー: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>プロフィール</title>
    <link rel="stylesheet" type="text/css" href="frontend/style.css">
</head>

<body>
    <?php require_once 'header.php'; ?>
    <h1>プロフィール</h1>
    <div id="profile-info">
        <?php if ($userProfile) : ?>
            <table>
                <tr>
                    <th>ユーザー ID:</th>
                    <td><?php echo htmlspecialchars($_SESSION['user_id'], ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
                <tr>
                    <th>フルネーム:</th>
                    <td><?php echo htmlspecialchars($userProfile['full_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
                <tr>
                    <th>自己紹介:</th>
                    <td><?php echo htmlspecialchars($userProfile['bio'], ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
            </table>
        <?php endif; ?>
    </div>
</body>

</html>
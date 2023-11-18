<?php
require_once(__DIR__ . '/../../controllers/UserController.php');
$userController = new UserController();
?>

<div class="header">
    <nav>
        <?php if ($userController->isUserLoggedIn()) : ?>
            <!-- ログイン済み -->
            <?php if (basename($_SERVER['PHP_SELF']) === 'profile.php') : ?>
                <!-- profile.php の画面にいる場合 -->
                <a href="/views/logout.php">ログアウト</a>
            <?php elseif (isset($_SESSION['userProfile'])) : ?>
                <!-- プロフィールが登録完了している場合 -->
                <a href="../../users/profile.php">プロフィール</a>
                <span>|</span>
                <a href="/views/logout.php">ログアウト</a>
            <?php else : ?>
                <!-- プロフィール未登録でログイン済み -->
                <!-- <a href="profile.php">プロフィール</a>
                    <span>|</span> -->
                <a href="/views/logout.php">ログアウト</a>
            <?php endif; ?>
        <?php else : ?>
            <!-- 未ログイン -->
            <a href="login.php">ログイン</a>
            <span>|</span>
            <a href="register.php">ユーザー登録</a>
        <?php endif; ?>
    </nav>
</div>

<?php
session_start();
session_destroy();
if (session_status() == PHP_SESSION_NONE) {
    // セッションがまだ開始されていない場合にのみセッションを開始
    session_start();
}
$_SESSION['logout_message'] = 'ログアウトしました。';
header("Location: http://localhost:3000/views/users/auth/login.php");
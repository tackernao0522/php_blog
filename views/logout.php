<?php
// セッション開始
session_start();

// トースターメッセージを設定

// ここでCSRFトークンを再生成
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// セッション破棄
session_destroy();

session_start();

$_SESSION['logout_message'] = 'ログアウトしました。';
// ログインページにリダイレクト
header("Location: http://localhost:3000/views/users/auth/login.php");
exit;

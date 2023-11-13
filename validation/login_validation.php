<?php

require_once(__DIR__ . '/../services/PasswordHasher.php');
require_once(__DIR__ . '/../models/User.php');
require_once(__DIR__ . '/../services/UserManager.php');
require_once(__DIR__ . '/../services/UserManagerImpl.php');
require_once(__DIR__ . '/../database/db.php');

// existingUsernames() 関数のスタブ
function existingUsernames($username)
{
    global $db;

    // ユーザー名が使用されているか確認するSQLを実行
    $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
    $stmt->bindValue(':username', $username);
    $stmt->execute();

    $count = $stmt->fetchColumn();

    // カウントが0より大きければtrueを返す
    return $count > 0;
}

function validateLogin($post)
{
    $errors = [];

    // ユーザー名が空でないか確認
    $username = $post['username'] ?? '';
    if (empty($username)) {
        $errors['username'] = 'ユーザー名を入力してください。';
    } elseif (!existingUsernames($username)) {
        $errors['username'] = 'このユーザー名は存在しません。';
    }

    // パスワードが空ではなく、一定の長さ以上であるか確認
    $password = $post['password'] ?? '';
    if (empty($password) || strlen($password) < 8) {
        $errors['password'] = 'パスワードは少なくとも8文字以上で入力してください。';
    }

    return $errors;
}

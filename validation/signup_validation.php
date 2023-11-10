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

// emailExists() 関数のスタブ
function emailExists($email)
{
    global $db;

    // メールアドレスが使用されているか確認するSQLを実行
    $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
    $stmt->bindValue(':email', $email);
    $stmt->execute();

    $count = $stmt->fetchColumn();

    // カウントが0より大きければtrue(存在する)を返す
    return $count > 0;
}

function validateRegistration($post)
{
    $errors = [];

    // ユーザー名が空でないか確認
    $username = $post['username'] ?? '';
    if (empty($username)) {
        $errors['username'] = 'ユーザー名を入力してください。';
    } elseif (!preg_match('/^[a-zA-Z0-9_-]+$/', $username)) {
        $errors['username'] = 'ユーザー名は3から20文字の英数字および一部の記号（_-）で構成されるようにしてください。';
    } elseif (strlen($username) < 3 || strlen($username) > 20) {
        $eerors['username'] = 'ユーザー名は3文字以上20文字以下にしてください。';
    } elseif (existingUsernames($username)) {
        $errors['username'] = 'このユーザー名は既に使用されています。';
    }

    // メールアドレスが空または無効な場合
    $email = $post['email'] ?? '';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'メールアドレスは必須です。有効なメールアドレスを入力してください。';
    } elseif (emailExists($email)) {
        $errors['email'] = 'このメールアドレスは既に使用されています。';
    }

    // パスワードが空ではなく、一定の長さ以上であるか確認
    $password = $post['password'] ?? '';
    if (empty($password) || strlen($password) < 8) {
        $errors['password'] = 'パスワードは少なくとも8文字以上で入力してください。';
    }

    // パスワード確認が空でなく、パスワードと一致するか確認
    $passwordConfirm = $post['password_confirm'] ?? '';
    if (empty($passwordConfirm) || $password !== $passwordConfirm) {
        $errors['password_confirm'] = 'パスワードが一致しません。';
    }

    return $errors;
}

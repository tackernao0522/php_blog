<?php

require_once(__DIR__ . '/../services/PasswordHasher.php');
require_once(__DIR__ . '/../models/User.php');
require_once(__DIR__ . '/../services/UserManager.php');
require_once(__DIR__ . '/../services/UserManagerImpl.php');
require_once(__DIR__ . '/../database/db.php');

function validateUpdateLoginInfo($post)
{
    $errors = [];

    // ユーザー名が空でないか確認
    $username = $post['username'] ?? '';
    if (empty($username)) {
        $errors['username'] = 'ユーザー名を入力してください。';
    } elseif (!preg_match('/^[a-zA-Z0-9_-]+$/', $username)) {
        $errors['username'] = 'ユーザー名は3から20文字の英数字および一部の記号（_-）で構成されるようにしてください。';
    } elseif (strlen($username) < 3 || strlen($username) > 20) {
        $errors['username'] = 'ユーザー名は3文字以上20文字以下にしてください。';
    }

    // メールアドレスが空または無効な場合
    $email = $post['email'] ?? '';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'メールアドレスは必須です。有効なメールアドレスを入力してください。';
    }

    // パスワードが空ではなく、一定の長さ以上であるか確認
    $password = $post['password'] ?? '';
    if (empty($password) || strlen($password) < 8) {
        $errors['password'] = 'パスワードは少なくとも8文字以上で入力してください。';
    }

    // パスワード確認がパスワードと一致しているか確認
    $password_confirm = $post['password_confirm'] ?? '';
    if ($password !== $password_confirm) {
        $errors['password_confirm'] = 'パスワード確認が一致しません。';
    }

    return $errors;
}

<?php

function validateUsername($username)
{
    // 3文字以上、20文字以下であること
    // アルファベット、数字、アンダースコア(_), ハイフン(-)のみを含むこと
    // 重複したユーザー名がないこと（仮にexistingUsernames()が存在すると仮定）
    $isValid = (strlen($username) >= 3 && strlen($username) <= 20) &&
        preg_match('/^[a-zA-Z0-9_-]+$/', $username) &&
        !existingUsernames($username);
    return $isValid;
}

function validateEmail($email)
{
    // 有効なメールアドレスの形式であること
    // 重複したメールアドレスがないこと（仮にexistingEmails()が存在すると仮定）
    $isValid = filter_var($email, FILTER_VALIDATE_EMAIL) && !existingEmails($email);
    return $isValid;
}

function validatePassword($password)
{
    // 8文字以上であること
    // 少なくとも1つの大文字のアルファベットを含むこと
    // 少なくとも1つの小文字のアルファベットを含むこと
    // 少なくとも1つの数字を含むこと
    // 特殊文字（例: !, @, #, $）を少なくとも1つ含むこと
    $isValid = strlen($password) >= 8 &&
        preg_match('/[A-Z]/', $password) &&
        preg_match('/[a-z]/', $password) &&
        preg_match('/[0-9]/', $password) &&
        preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password);
    return $isValid;
}

// 仮のデータベース関数（実際のデータベースと連携する必要があります）
function existingUsernames($username)
{
    $existingUsernames = ['existingUser1', 'existingUser2']; // 既存のユーザー名のリスト
    return in_array($username, $existingUsernames);
}

// 仮のデータベース関数（実際のデータベースと連携する必要があります）
function existingEmails($email)
{
    $existingEmails = ['existing@email.com', 'another@email.com']; // 既存のメールアドレスのリスト
    return in_array($email, $existingEmails);
}

// 使用例
$username = 'newUser123';
$email = 'newuser@example.com';
$password = 'Password123!';

if (validateUsername($username) && validateEmail($email) && validatePassword($password)) {
    echo '登録が成功しました！';
} else {
    echo '入力された情報が無効です。';
}

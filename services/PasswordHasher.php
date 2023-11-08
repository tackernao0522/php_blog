<?php

class PasswordHasher
{
    /**
     * パスワードをハッシュ化して返す
     * 
     * @param string $password ハッシュ化する平文パスワード
     * @return string ハッシュ化されたパスワード
     */
    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * パスワードハッシュが与えられたパスワードと一致するかを検証
     * 
     * @param string $password ユーザーが提供した平文パスワード
     * @param string $hashedPassword データベースに保存されたハッシュ化されたパスワード
     * @return bool パスワードが一致する場合は true、それ以外は false
     */
    public function verifyPassword(string $password, string $hashedPassword): bool
    {
        return password_verify($password, $hashedPassword);
    }
}

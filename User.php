<?php
require_once 'PasswordHasher.php';

class User
{
    private int $id;
    private string $username;
    private string $email;
    private string $password;
    private PasswordHasher $passwordHasher;

    public function __construct(int $id, string $username, string $email, string $password)
    {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->passwordHasher = new PasswordHasher(); // PasswordHasher インスタンスを生成
    }

    // ゲッターとセッターメソッド
    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * パスワードの検証
     * 
     * @param string $inputPassword ユーザーが入力したパスワード
     * @return bool パスワードが一致する場合は true、それ以外は false
     */
    public function verifyPassword(string $inputPassword): bool
    {
        return $this->passwordHasher->verifyPassword($inputPassword, $this->password);
    }
}

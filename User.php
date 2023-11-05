<?php
require_once 'PasswordHasher.php';
require_once 'UserProfile.php';

class User
{
    private int $id;
    private string $username;
    private string $email;
    private string $passwordHash;
    private ?PasswordHasher $passwordHasher; // PasswordHasher クラスのインスタンスを許容
    private ?UserProfile $userProfile; // UserProfile クラスのインスタンスを許容

    public function __construct(int $id, string $username, string $email, string $passwordHash, ?PasswordHasher $passwordHasher, ?UserProfile $userProfile)
    {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->passwordHasher = $passwordHasher;
        $this->userProfile = $userProfile;
    }

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
        return $this->passwordHash;
    }

    public function getUserProfile(): ?UserProfile
    {
        return $this->userProfile;
    }

    public function verifyPassword(string $inputPassword): bool
    {
        return $this->passwordHasher->verifyPassword($inputPassword, $this->passwordHash);
    }
}

<?php
require_once(__DIR__.'/../models/UserProfile.php');

class UserImpl extends User
{
    public function __construct(int $id, string $username, string $email, string $password, $userId, $fullName, $bio)
    {
        $passwordHasher = new PasswordHasher();
        $userProfile = new UserProfile($userId, $fullName, $bio);

        parent::__construct($id, $username, $email, $password, $passwordHasher, $userProfile);
    }
}

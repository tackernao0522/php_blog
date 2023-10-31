<?php

interface UserManager
{
    public function createUser(User $user): bool;
    public function getUserById(int $id): ?User;
    public function getUserByUsername(string $username): ?User;
    public function getUserByEmail(string $email): ?User;
    public function updateUser(User $user): bool;
    public function deleteUser(int $id): bool;
}

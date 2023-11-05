<?php

interface UserProfileManager
{
    public function createUserProfile(UserProfile $profile): bool;
    public function getUserProfile(int $userId): ?UserProfile;
    public function updateUserProfile(UserProfile $profile): bool;
}

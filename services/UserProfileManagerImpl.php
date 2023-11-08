<?php
require_once(__DIR__ . '/../models/UserProfile.php');
require_once 'UserProfileManager.php';
require_once(__DIR__ . '/../database/db.php'); // データベース接続用ファイル

class UserProfileManagerImpl implements UserProfileManager
{
    private $db;

    public function __construct()
    {
        global $db;
        $this->db = $db;
    }

    public function createUserProfile(UserProfile $userProfile): bool
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO user_profiles (user_id, full_name, bio) VALUES (:user_id, :full_name, :bio)");
            $stmt->bindParam(':user_id', $userProfile->getUserId(), PDO::PARAM_INT);
            $stmt->bindParam(':full_name', $userProfile->getFullName(), PDO::PARAM_STR);
            $stmt->bindParam(':bio', $userProfile->getBio(), PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

    public function getUserProfile(int $userId): ?UserProfile
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM user_profiles WHERE user_id = :user_id");
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $userProfileData = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($userProfileData) {
                return new UserProfile($userProfileData['user_id'], $userProfileData['full_name'], $userProfileData['bio']);
            }

            return null;
        } catch (PDOException $e) {
            echo 'Error: ' . $e->getMessage();
            return null;
        }
    }

    public function updateUserProfile(UserProfile $userProfile): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE user_profiles SET full_name = :full_name, bio = :bio WHERE user_id = :user_id");
            $stmt->bindParam(':user_id', $userProfile->getUserId(), PDO::PARAM_INT);
            $stmt->bindParam(':full_name', $userProfile->getFullName(), PDO::PARAM_STR);
            $stmt->bindParam(':bio', $userProfile->getBio(), PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }
}

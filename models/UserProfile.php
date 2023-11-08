<?php
// db接続のrequire
require_once(__DIR__.'../../database/db.php');

// セッション開始
// session_start();

// セッションからユーザーIDを取得
// $userId = $_SESSION['user_id'];

// ユーザープロフィールを取得
// $userProfile = getUserProfile($userId);

// ユーザープロフィール取得関数
function getUserProfile($userId)
{

    global $db;

    // クエリでユーザープロフィールを取得
    $stmt = $db->prepare("SELECT * FROM user_profiles WHERE user_id = :id");

    $stmt->bindValue(':id', $userId);
    $stmt->execute();

    $profile = $stmt->fetch();

    // 取得できなかった場合はnullを返す
    if (!$profile) {
        return null;
    }

    return new UserProfile(
        $profile['user_id'],
        $profile['full_name'],
        $profile['bio']
    );
}

// UserProfileクラス
class UserProfile
{

    private ?int $userId; // ユーザーIDの型に ?int を追加し、null を許容
    private string $fullName;
    private ?string $bio; // バイオの型に ?string を追加し、null を許容

    public function __construct(?int $userId, string $fullName, ?string $bio) // 引数の型を修正
    {
        if (is_null($userId)) {
            exit('ユーザーIDが不正です。');
        }

        $this->userId = $userId;
        $this->fullName = $fullName;
        $this->bio = $bio;
    }

    public function getUserId(): ?int // getUserId メソッドの返り値の型を修正
    {
        return $this->userId;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function getBio(): ?string // getBio メソッドの返り値の型を修正
    {
        return $this->bio;
    }
}
?>
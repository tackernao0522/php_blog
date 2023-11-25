<?php
require_once(__DIR__ . '/../models/User.php');
require_once 'UserManager.php';
require_once(__DIR__ . '/../database/config.php');

class UserManagerImpl implements UserManager
{
    private $conn;

    public function __construct()
    {
        $dsn = "pgsql:host=" . DB_HOST . ";dbname=" . DB_NAME;
        $username = DB_USER;
        $password = 'null'; // パスワードを直接指定
        try {
            $this->conn = new PDO($dsn, $username, $password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public function createUser(User $user): bool
    {
        try {
            $stmt = $this->conn->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
            $stmt->bindValue(':username', $user->getUsername(), PDO::PARAM_STR);
            $stmt->bindValue(':email', $user->getEmail(), PDO::PARAM_STR);
            $stmt->bindValue(':password', $user->getPassword(), PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    public function getUserById(int $id): ?User
    {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return null;
            }

            // パスワードハッシャーとユーザープロフィールを設定
            $passwordHasher = new PasswordHasher(); // パスワードハッシュの生成方法は適切に設定
            $userProfile = getUserProfile($user['id']); // プロフィール情報を取得

            return new User($user['id'], $user['username'], $user['email'], $user['password'], $passwordHasher, $userProfile);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }

    public function getUserByUsername(string $username): ?User
    {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = :username");
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return null;
            }

            // ユーザー情報を正しく取得したら、User クラスのコンストラクタで正しくインスタンス化
            $userProfile = null; // プロフィール情報はまだ取得できていないので null を設定
            $passwordHasher = new PasswordHasher(); // パスワードハッシュの生成方法は適切に設定
            return new User($user['id'], $user['username'], $user['email'], $user['password'], $passwordHasher, $userProfile);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }

    public function getUserByEmail(string $email): ?User
    {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return null;
            }

            return new User($user['id'], $user['username'], $user['email'], $user['password'], $user['passwordHasher'], $user['userProfile']);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }

    public function updateUser(User $user): bool
    {
        try {
            $stmt = $this->conn->prepare("UPDATE users SET username = :username, email = :email, password = :password WHERE id = :id");
            $stmt->bindParam(':username', $user->getUsername(), PDO::PARAM_STR);
            $stmt->bindParam(':email', $user->getEmail(), PDO::PARAM_STR);
            $stmt->bindParam(':password', $user->getPassword(), PDO::PARAM_STR);
            $stmt->bindParam(':id', $user->getId(), PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    public function deleteUser(int $id): bool
    {
        try {
            $stmt = $this->conn->prepare("DELETE FROM users WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
}

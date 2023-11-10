<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// require_once(__DIR__ . '/../models/UserProfile.php');
if (session_status() == PHP_SESSION_NONE) {
    // セッションがまだ開始されていない場合にのみセッションを開始
    session_start();
}


class UserController
{
    // プロフィール登録処理を処理するメソッドを追加
    public function storeProfile()
    {
        // ログインしていない場合、ログインページにリダイレクト
        if (!isset($_SESSION['user_id'])) {
            header("Location: login.php");
            exit;
        }

        // データベースに接続
        require_once(__DIR__ . '/../database/db.php');

        global $db;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRFトークンの検証
            if (isset($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
                // ユーザープロフィール情報を受け取り、データベースに新規登録する処理
                $fullName = filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $bio = filter_input(INPUT_POST, 'bio', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

                // データベース接続が成功したか確認
                if ($db) {
                    try {
                        // プリペアドステートメントの宣言
                        $stmt = $db->prepare("INSERT INTO user_profiles (user_id, full_name, bio) VALUES (:user_id, :full_name, :bio)");
                        $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
                        $stmt->bindValue(':full_name', $fullName, PDO::PARAM_STR);
                        $stmt->bindValue(':bio', $bio, PDO::PARAM_STR);

                        if ($stmt->execute()) {
                            $_SESSION['profile_create_message'] = 'プロフィールを登録しました。';
                            // var_dump($_SESSION['profile_create_message']);
                            // プロフィール情報の新規登録に成功した場合、プロフィール画面にリダイレクト
                            header("Location: profile.php");
                            exit;
                        } else {
                            // errorInfoの返り値をarrayのみにする 
                            /** @var array */
                            $errorInfo = $stmt->errorInfo();

                            die('失敗:' . implode(',', $errorInfo));
                        }
                    } catch (PDOException $e) {
                        echo 'データベースエラー: ' . $e->getMessage();
                    }
                } else {
                    die('データベースに接続できません。');
                }
            } else {
                die("CSRF攻撃を検知");
            }
        }
    }

    public function isUserLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }
}

<?php
session_start();
require_once 'UserProfile.php';

class UserController
{
    public function login()
    {
        // ログイン処理
        $this->render('login.php');
    }

    public function register()
    {
        // ユーザー処理
        $this->render('register.php');
    }

    public function profile()
    {
        // セッションからユーザーIDを取得
        if (isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];

            // ユーザープロフィールを取得
            // arrayのみを許容するように型定義を変更
            /** @var array */
            $userProfile = getUserProfile($userId);


            // nullチェック
            if (is_null($userProfile)) {
                exit('プロフィールがありません');
            }

            // クラス内でセッション変数を直接設定
            $_SESSION['userProfile'] = $userProfile;
        } else {
            // セッションにユーザーIDが設定されていない場合の処理
            exit('ログインしていません');
        }

        $this->render('profile.php');
    }

    // プロフィール登録処理を処理するメソッドを追加
    public function editProfile()
    {
        // ログインしていない場合、ログインページにリダイレクト
        if (!isset($_SESSION['user_id'])) {
            header("Location: login.php");
            exit;
        }

        // データベースに接続
        require_once 'db.php';

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

    public function render($view)
    {
        require __DIR__ . '/../' . $view;
    }
}
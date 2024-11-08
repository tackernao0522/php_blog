<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once(__DIR__ . '/../models/UserProfile.php');
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

    // プロフィール更新フォームを表示するメソッドを追加
    public function showUpdateProfileForm()
    {
        // CSRFトークンの作成
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;

        // バリデーションエラーと前回の入力値を取得
        $errors = isset($_SESSION['validation_errors']) ? $_SESSION['validation_errors'] : [];
        $oldInput = isset($_SESSION['old_input']) ? $_SESSION['old_input'] : [];

        // 既存のプロフィール情報を取得
        $existingProfile = getUserProfile($_SESSION['user_id']);

        // 必要な値を配列として返す
        return [
            'csrfToken' => $csrfToken,
            'errors' => $errors,
            'oldInput' => $oldInput,
            'existingProfile' => $existingProfile,
        ];
    }

    // プロフィール更新処理を処理するメソッドを追加
    public function updateProfile()
    {
        // ログインしていない場合、ログインページにリダイレクト
        if (!isset($_SESSION['user_id'])) {
            header("Location: http://localhost:3000/views/users/auth/login.php");
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
                        // プリぺアドステートメントの宣言
                        $stmt = $db->prepare("UPDATE user_profiles SET full_name = :full_name, bio = :bio, updated_at = NOW() WHERE user_id = :user_id");
                        $stmt->bindValue(':full_name', $fullName, PDO::PARAM_STR);
                        $stmt->bindValue(':bio', $bio, PDO::PARAM_STR);
                        $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);

                        // ステートメントの実行
                        if ($stmt->execute()) {
                            // 更新成功のメッセージをセッションに保存
                            $_SESSION['profile_update_message'] = 'プロフィールを更新しました。';
                            header("Location: http://localhost:3000/views/users/profile.php");
                            exit;
                        } else {
                            // errorInfoの返り値をarrayのみにする
                            /** @var array */
                            $errorInfo = $stmt->errorInfo();

                            die('更新失敗' . implode(',', $errorInfo));
                        }
                    } catch (PDOException $e) {
                        // エラーメッセージを表示
                        echo $e->getMessage();
                    }
                }
            }
        }
    }


    public function updateLoginInfo()
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: http://localhost:3000/views/users/auth/login.php");
            exit;
        }

        // プロフィールが登録していない場合、プロフィール登録画面にリダイレクト
        $existingProfile = getUserProfile($_SESSION['user_id']);
        if (!$existingProfile) {
            header("Location: http://localhost:3000/views/users/profile_input.php");
            exit;
        }

        // データベースに接続
        require_once(__DIR__ . '/../database/db.php');

        global $db;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF トークンの検証
            if (isset($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
                // ユーザー情報を受け取り、データベースを更新する処理
                $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_SPECIAL_CHARS);
                $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

                // パスワードをハッシュ化
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // データベース接続が成功したか確認
                if ($db) {
                    try {
                        // プリペアステートメントの宣言
                        $stmt = $db->prepare("UPDATE users SET username = :username, email = :email, password = :password, updated_at = NOW() WHERE id = :user_id");
                        $stmt->bindValue(':username', $username, PDO::PARAM_STR);
                        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
                        $stmt->bindValue(':password', $hashedPassword, PDO::PARAM_STR);
                        $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);

                        if ($stmt->execute()) {
                            // ログイン情報の更新に成功した場合、メッセージを一時的に保存
                            $login_update_message = 'ログイン情報を更新しました。再ログインして下さい。';

                            // 現在のセッションIDを保存
                            $sessionId = session_id();

                            // ログイン情報の更新に成功した場合、セッションを終了し、ログインページにリダイレクト
                            session_destroy();

                            // セッションを再開し、メッセージをセッションに設定
                            session_id($sessionId);
                            session_start();
                            $_SESSION['login_update_message'] = $login_update_message;

                            header("Location: http://localhost:3000/views/users/auth/login.php");
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

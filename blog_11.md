# PHP BLOG

```php:config.php
// config.php
<?php
define('DB_HOST', 'localhost'); // PostgreSQLホスト名
define('DB_NAME', 'blogdb'); // データベース名
define('DB_USER', 'postgres'); // データベースユーザー名
define('DB_PASSWORD', 'null'); // データベースパスワード
```

```php:db.php
// db.php
<?php
require_once 'config.php';

try {
    $db = new PDO('pgsql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    echo 'データベース接続エラー: ' . $e->getMessage();
    die();
}
```

```php:login.php
// login.php
<?php
// login.php

// 必要なファイルをインクルード
require_once 'User.php';
require_once 'UserManager.php';
require_once 'UserManagerImpl.php';
require_once 'PasswordHasher.php';
require_once 'config.php';
require_once 'UserImpl.php';

// エラーレポーティングを設定
error_reporting(E_ALL);
ini_set('display_errors', 1);

// セッション開始
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRFトークンの検証
    if (isset($_SESSION['csrf_token']) && isset($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        // POSTデータからユーザー名とパスワードを取得
        $username = $_POST['username'];
        $password = $_POST['password'];

        // ユーザー認証
        $userManager = new UserManagerImpl();
        $user = $userManager->getUserByUsername($username);

        if ($user && $user->verifyPassword($password)) {
            // ログインが成功した場合、セッションにuser_idを設定
            $_SESSION['user_id'] = $user->getId();

            // プロフィールが登録されていない場合、プロフィール登録画面にリダイレクト
            $userProfile = getUserProfile($user->getId());
            if (!$userProfile) {
                header("Location: profile_input.php");
                exit;
            }

            // プロフィール情報をセッションに保存
            $_SESSION['userProfile'] = $userProfile;

            header("Location: profile.php"); // プロフィールページにリダイレクト
            exit;
        } else {
            echo "認証エラー";
        }
    } else {
        die("CSRF攻撃を検知");
    }
}

// CSRFトークンの生成
$csrfToken = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrfToken;
?>

<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ユーザーログイン</title>
    <link rel="stylesheet" type="text/css" href="frontend/style.css">
</head>

<body>
    <h1>ユーザーログイン</h1>
    <form id="login-form" method="POST" action="login.php">
        <!-- CSRFトークンをフォーム内に追加 -->
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <label for="username">ユーザー名:</label>
        <input type="text" id="username" name="username" required><br>
        <label for="password">パスワード:</label>
        <input type="password" id="password" name="password" required><br>
        <button type="submit">ログイン</button>
    </form>
</body>

</html>
```

```php:PasswordHasher.php
// PasswordHasher.php
<?php

class PasswordHasher
{
    /**
     * パスワードをハッシュ化して返す
     * 
     * @param string $password ハッシュ化する平文パスワード
     * @return string ハッシュ化されたパスワード
     */
    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * パスワードハッシュが与えられたパスワードと一致するかを検証
     * 
     * @param string $password ユーザーが提供した平文パスワード
     * @param string $hashedPassword データベースに保存されたハッシュ化されたパスワード
     * @return bool パスワードが一致する場合は true、それ以外は false
     */
    public function verifyPassword(string $password, string $hashedPassword): bool
    {
        return password_verify($password, $hashedPassword);
    }
}
```

```php:register.php
// register.php
<?php
require_once 'User.php';
require_once 'UserManager.php';
require_once 'UserManagerImpl.php';
require_once 'PasswordHasher.php';
require_once 'config.php';
require_once 'UserImpl.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ここに修正を適用
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_SPECIAL_CHARS);

    $passwordHasher = new PasswordHasher();
    $hashedPassword = $passwordHasher->hashPassword($password);

    // ここでUserProfileに関する情報を取得または設定する必要があります
    $userId = 1; // ユーザーIDを適切な方法で設定
    $fullName = 'ユーザーのフルネーム'; // ユーザーのフルネームを設定
    $bio = 'ユーザーのバイオ'; // ユーザーのバイオを設定

    $user = new UserImpl(0, $username, $email, $hashedPassword, $userId, $fullName, $bio);
    $userManager = new UserManagerImpl();

    if ($userManager->createUser($user)) {
        echo 'ユーザー登録が成功しました。プロフィール登録してください。';
        // ユーザー登録が成功したらログインページにリダイレクト
        header("Location: login.php");
        exit;
    } else {
        echo 'ユーザー登録に失敗しました。';
    }
}

// CSRFトークンの生成
$csrfToken = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrfToken;
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ユーザー登録</title>
    <link rel="stylesheet" type="text/css" href="frontend/style.css">
</head>

<body>
    <h1>ユーザー登録</h1>
    <form id="register-form" method="POST">
        <!-- CSRFトークンをフォーム内に追加 -->
        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
        <label for="username">ユーザー名:</label>
        <input type="text" id="username" name="username" required><br>
        <label for="email">メールアドレス:</label>
        <input type="email" id="email" name="email" required><br>
        <label for="password">パスワード:</label>
        <input type="password" id="password" name="password" required><br>
        <button type="submit">登録</button>
    </form>
    <p id="register-message"></p>
</body>

</html>
```

```php:User.php
// User.php
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
```

```php:UserImpl.php
// UserImpl.php

```

```php:UserManager.php
// UserManager.php
<?php
require_once 'UserProfile.php';

class UserImpl extends User
{
    public function __construct(int $id, string $username, string $email, string $password, $userId, $fullName, $bio)
    {
        $passwordHasher = new PasswordHasher();
        $userProfile = new UserProfile($userId, $fullName, $bio);

        parent::__construct($id, $username, $email, $password, $passwordHasher, $userProfile);
    }
}
```

```php:UserManagerImpl.php
// UserManagerImpl.php
<?php
require_once 'User.php';
require_once 'UserManager.php';
require_once 'config.php';

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

            return new User($user['id'], $user['username'], $user['email'], $user['password'], $user['passwordHasher'], $user['userProfile']);
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
```

```php:UserProfile.php
// UserProfile.php
<?php

// db接続のrequire
require_once 'db.php';

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
```

```:.htaccess
// .htaccess
# .htaccess (プロジェクトルートディレクトリに置く)

# リライト設定
RewriteEngine On 

# index.phpをフロントコントローラとして設定
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [L]

# キャッシュ設定
<FilesMatch "\.(jpg|jpeg|png|gif|js|css|swf)$">
    Header set Cache-Control "max-age=604800, public"
</FilesMatch>

# PHPの設定
php_value max_execution_time 300
php_value memory_limit 256M
php_value post_max_size 100M
php_value upload_max_filesize 50M
```

```php:index.php
// index.php
<?php

// リクエストパラメータからコントローラとアクションを取得
$controller = $_GET['controller'] ?? 'home';
$action = $_GET['action'] ?? 'index';

// コントローラクラスを読み込む
switch ($controller) {

    case 'home':
        require 'controllers/HomeController.php';
        $controller = new HomeController();
        $controller->index();
        break;

    case 'user':
        require 'controllers/UserController.php';
        $controller = new UserController();
        $controller->login();
        break;

        // その他のコントローラ

    default:
        require 'controllers/ErrorController.php';
        $controller = new ErrorController();
        $action = '404';
}

// アクションメソッドを実行
$controller->{$action}();
```

```php:HomeController.php
// HomeController.php
<?php

class HomeController
{
    public function index()
    {
        // トップページの表示処理
        $this->render('index.php');
    }

    public function render($view)
    {
        require __DIR__ . '/../' . $view;
    }

    public function about()
    {
        // Aboutページの表示処理
    }
}
```

```php:UserController.php
// UserController.php
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
```

```php:profile.php
// profile.php
<?php
// profile.php

// セッション開始
session_start();

// ログインしていない場合、ログインページにリダイレクト
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// ユーザープロフィール情報を取得
$userProfile = null;

try {
    // データベースに接続
    require_once 'db.php';

    // ユーザープロフィール情報を取得
    $stmt = $db->prepare("SELECT * FROM user_profiles WHERE user_id = :user_id");
    $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();

    $userProfile = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userProfile) {
        // プロフィールが存在しない場合の処理
        // 例: 初めてプロフィールを設定する画面にリダイレクト
        header("Location: profile_input.php");
        exit;
    }
} catch (PDOException $e) {
    echo 'データベースエラー: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ユーザープロフィール</title>
    <link rel="stylesheet" type="text/css" href="frontend/style.css">
</head>

<body>
    <h1>ユーザープロフィール</h1>
    <div id="profile-info">
        <?php if ($userProfile) : ?>
            <p class="info-label">User ID: <span class="info-value"><?php echo htmlspecialchars($_SESSION['user_id'], ENT_QUOTES, 'UTF-8'); ?></span></p>
            <p class="info-label">Full Name: <span class="info-value"><?php echo htmlspecialchars($userProfile['full_name'], ENT_QUOTES, 'UTF-8'); ?></span></p>
            <p class="info-label">Bio: <span class="info-value"><?php echo htmlspecialchars($userProfile['bio'], ENT_QUOTES, 'UTF-8'); ?></span></p>
        <?php endif; ?>
    </div>
</body>

</html>
```

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

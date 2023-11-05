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

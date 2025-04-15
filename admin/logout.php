<?php
// セッション開始
session_start();

// セッション変数を全て解除
$_SESSION = array();

// セッションクッキーの削除
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// セッションの破棄
session_destroy();

// ログインページにリダイレクト
header('Location: index.php');
exit;
?> 
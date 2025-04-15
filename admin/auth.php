<?php
session_start();

// ログインしていない場合はログインページにリダイレクト
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// データベース接続
require_once '../includes/config.php';
?> 
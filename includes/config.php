<?php
/**
 * 野良ガチャVALORANT
 * データベース接続設定
 */

// データベース接続設定
define('DB_HOST', 'localhost'); // ホスト名
define('DB_NAME', 'noragacha_valorant');
define('DB_USER', 'noragacha_user');
define('DB_PASS', 'password123');
define('DB_PORT', '3306');

// PDOでの接続
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4;port=" . DB_PORT;
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // エラー処理
    die("データベース接続エラー: " . $e->getMessage());
}
?> 
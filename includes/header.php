<?php
// セッション開始
session_start();
// データベース接続
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- キャッシュ制御 -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title><?php echo isset($page_title) ? $page_title : '野良ガチャVALORANT | VALORANTの野良プレイヤーシミュレーター'; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700&display=swap" rel="stylesheet">
    
    <!-- カスタムCSS -->
    <link rel="stylesheet" href="css/style.css">
    
    <?php if (isset($ogp) && $ogp): ?>
    <!-- Twitter Card 設定 -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@VALORANT_JP">
    <meta name="twitter:creator" content="@VALORANT_JP">
    <meta name="twitter:title" content="<?php echo isset($ogp_title) ? $ogp_title : '野良ガチャVALORANT | VALORANTの野良プレイヤーシミュレーター'; ?>">
    <meta name="twitter:description" content="<?php echo isset($ogp_description) ? $ogp_description : 'VALORANTで野良と組んでみた結果... #野良ガチャVALORANT #VALORANT'; ?>">
    <meta name="twitter:image" content="<?php echo $ogp_image; ?>">
    
    <!-- OGP設定 -->
    <meta property="og:title" content="<?php echo isset($ogp_title) ? $ogp_title : '野良ガチャVALORANT | VALORANTの野良プレイヤーシミュレーター'; ?>">
    <meta property="og:description" content="<?php echo isset($ogp_description) ? $ogp_description : 'VALORANTで野良と組んでみた結果... #野良ガチャVALORANT #VALORANT'; ?>">
    <meta property="og:image" content="<?php echo $ogp_image; ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:url" content="<?php echo $current_url; ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="野良ガチャVALORANT">
    <?php endif; ?>
</head>
<body>
    <!-- ヘッダー -->
    <header class="site-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="site-title">野良ガチャVALORANT</h1>
                </div>
                <div class="col-md-6 text-end">
                    <?php if (isset($show_back_button) && $show_back_button): ?>
                    <a href="<?php echo $back_url; ?>" class="btn btn-secondary-valora">戻る</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>
    
    <!-- メインコンテンツ -->
    <div class="main-content">
        <div class="container"><?php 
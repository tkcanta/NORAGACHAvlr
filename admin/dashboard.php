<?php
// 認証チェック
require_once 'auth.php';
// データベース設定を明示的にインクルード
require_once '../includes/config.php';

$title = 'ダッシュボード - 野良ガチャVALORANT 管理画面';
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Noto Sans JP', sans-serif;
            background-color: #ECE8E1;
            min-height: 100vh;
        }
        .navbar-valora {
            background-color: #1F2326;
        }
        .navbar-brand {
            color: #FF4655;
            font-weight: bold;
        }
        .navbar-brand:hover {
            color: #FF4655;
        }
        .nav-link {
            color: white;
        }
        .nav-link:hover {
            color: #FF4655;
        }
        .nav-link.active {
            color: #FF4655;
            font-weight: bold;
        }
        .card {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card-header {
            background-color: #FF4655;
            color: white;
            font-weight: bold;
        }
        .btn-valora {
            background-color: #FF4655;
            border-color: #FF4655;
            color: white;
        }
        .btn-valora:hover {
            background-color: #E53945;
            border-color: #E53945;
            color: white;
        }
    </style>
</head>
<body>
    <!-- ナビゲーションバー -->
    <nav class="navbar navbar-expand-lg navbar-valora">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">野良ガチャVALORANT 管理画面</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">ダッシュボード</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="players.php">野良プレイヤー管理</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="phrases.php">害悪フレーズ管理</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="compositions.php">チーム構成管理</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> ログアウト</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- メインコンテンツ -->
    <div class="container py-5">
        <h2 class="mb-4">ダッシュボード</h2>
        
        <div class="row">
            <?php
            // 野良プレイヤー数を取得
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM random_players");
            $playerCount = $stmt->fetch()['count'];
            
            // 害悪フレーズ数を取得
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM toxic_phrases");
            $phraseCount = $stmt->fetch()['count'];
            
            // チーム構成数を取得
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM team_compositions");
            $teamCount = $stmt->fetch()['count'];
            ?>
            
            <!-- 野良プレイヤー統計 -->
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <i class="fas fa-users"></i> 野良プレイヤー
                    </div>
                    <div class="card-body">
                        <h1 class="display-4 text-center"><?php echo $playerCount; ?></h1>
                        <p class="text-center">登録済みプレイヤー</p>
                        <div class="d-grid">
                            <a href="players.php" class="btn btn-valora">管理する</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 害悪フレーズ統計 -->
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <i class="fas fa-comment-alt"></i> 害悪フレーズ
                    </div>
                    <div class="card-body">
                        <h1 class="display-4 text-center"><?php echo $phraseCount; ?></h1>
                        <p class="text-center">登録済みフレーズ</p>
                        <div class="d-grid">
                            <a href="phrases.php" class="btn btn-valora">管理する</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- チーム構成統計 -->
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <i class="fas fa-gamepad"></i> チーム構成
                    </div>
                    <div class="card-body">
                        <h1 class="display-4 text-center"><?php echo $teamCount; ?></h1>
                        <p class="text-center">生成済みチーム</p>
                        <div class="d-grid">
                            <a href="compositions.php" class="btn btn-valora">管理する</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-info-circle"></i> システム情報
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <th width="30%">PHPバージョン</th>
                                    <td><?php echo phpversion(); ?></td>
                                </tr>
                                <tr>
                                    <th>MySQLバージョン</th>
                                    <td><?php echo $pdo->getAttribute(PDO::ATTR_SERVER_VERSION); ?></td>
                                </tr>
                                <tr>
                                    <th>データベース名</th>
                                    <td><?php echo DB_NAME; ?></td>
                                </tr>
                                <tr>
                                    <th>現在時刻</th>
                                    <td><?php echo date('Y-m-d H:i:s'); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- フッター -->
    <footer class="bg-dark text-white text-center py-3 mt-5">
        <div class="container">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> 野良ガチャVALORANT 管理画面</p>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
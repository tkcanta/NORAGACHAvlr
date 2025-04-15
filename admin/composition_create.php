<?php
// 認証チェック
require_once 'auth.php';

// メッセージ初期化
$message = '';
$message_class = '';

// フォーム送信処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $user_name = isset($_POST['user_name']) ? trim($_POST['user_name']) : '';
    
    try {
        // トランザクション開始
        $pdo->beginTransaction();
        
        // チーム構成の作成
        $stmt = $pdo->prepare("
            INSERT INTO team_compositions (created_at) 
            VALUES (NOW())
        ");
        $stmt->execute([]);
        $composition_id = $pdo->lastInsertId();
        
        // コミット
        $pdo->commit();
        
        // 詳細ページにリダイレクト
        header('Location: composition_detail.php?id=' . $composition_id . '&message=created');
        exit;
    } catch (PDOException $e) {
        // ロールバック
        $pdo->rollBack();
        $message = 'エラー: ' . $e->getMessage();
        $message_class = 'danger';
    }
}

$title = '新規チーム構成作成 - 野良ガチャVALORANT 管理画面';
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
            margin-bottom: 20px;
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
                        <a class="nav-link" href="dashboard.php">ダッシュボード</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="players.php">野良プレイヤー管理</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="phrases.php">害悪フレーズ管理</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="compositions.php">チーム構成管理</a>
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>新規チーム構成作成</h2>
            <a href="compositions.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> 一覧に戻る
            </a>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $message_class; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <!-- 作成フォーム -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-plus-circle"></i> 新規チーム構成
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="user_name" class="form-label">ユーザー名</label>
                        <input type="text" class="form-control" id="user_name" name="user_name" required>
                        <div class="form-text">このチーム構成を作成したユーザーの名前を入力してください。</div>
                    </div>
                    
                    <div class="mt-4">
                        <input type="hidden" name="action" value="create">
                        <button type="submit" class="btn btn-valora">
                            <i class="fas fa-save"></i> 作成する
                        </button>
                        <a href="compositions.php" class="btn btn-secondary ms-2">キャンセル</a>
                    </div>
                </form>
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
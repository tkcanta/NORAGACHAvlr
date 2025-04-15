<?php
// 認証チェック
require_once 'auth.php';

// メッセージ初期化
$message = '';
$message_class = '';

// IDの取得とバリデーション
$composition_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($composition_id <= 0) {
    header('Location: compositions.php');
    exit;
}

// チーム構成の基本情報を取得
try {
    $stmt = $pdo->prepare("
        SELECT 
            tc.id, 
            tc.created_at, 
            '' as user_name,
            tc.ogp_image_path
        FROM 
            team_compositions tc
        WHERE 
            tc.id = ?
    ");
    $stmt->execute([$composition_id]);
    $composition = $stmt->fetch();
    
    if (!$composition) {
        header('Location: compositions.php');
        exit;
    }
    
    // チームメンバー情報を取得
    $stmt = $pdo->prepare("
        SELECT 
            ca.id,
            ca.player_name,
            ca.agent_name,
            ca.is_user,
            ca.player_id,
            ca.toxic_phrases
        FROM 
            composition_agents ca
        WHERE 
            ca.composition_id = ?
        ORDER BY 
            ca.is_user DESC, ca.id ASC
    ");
    $stmt->execute([$composition_id]);
    $agents = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $message = 'エラー: ' . $e->getMessage();
    $message_class = 'danger';
    header('Location: compositions.php');
    exit;
}

// フォーム送信処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $user_name = isset($_POST['user_name']) ? trim($_POST['user_name']) : '';
    
    try {
        // トランザクション開始
        $pdo->beginTransaction();
        
        // チーム構成の更新
        $stmt = $pdo->prepare("
            UPDATE team_compositions 
            SET id = id
            WHERE id = ?
        ");
        $stmt->execute([$composition_id]);
        
        // エージェント更新処理（存在する場合）
        if (isset($_POST['agent']) && is_array($_POST['agent'])) {
            foreach ($_POST['agent'] as $agent_id => $agent_data) {
                if (isset($agent_data['player_name']) && isset($agent_data['agent_name'])) {
                    $stmt = $pdo->prepare("
                        UPDATE composition_agents
                        SET player_name = ?, agent_name = ?
                        WHERE id = ? AND composition_id = ?
                    ");
                    $stmt->execute([
                        $agent_data['player_name'],
                        $agent_data['agent_name'],
                        $agent_id,
                        $composition_id
                    ]);
                }
            }
        }
        
        // コミット
        $pdo->commit();
        
        $message = 'チーム構成を更新しました。';
        $message_class = 'success';
        
        // 更新後のデータを再取得
        $composition['user_name'] = '';
        
        $stmt = $pdo->prepare("
            SELECT 
                ca.id,
                ca.player_name,
                ca.agent_name,
                ca.is_user,
                ca.player_id,
                ca.toxic_phrases
            FROM 
                composition_agents ca
            WHERE 
                ca.composition_id = ?
            ORDER BY 
                ca.is_user DESC, ca.id ASC
        ");
        $stmt->execute([$composition_id]);
        $agents = $stmt->fetchAll();
        
    } catch (PDOException $e) {
        // ロールバック
        $pdo->rollBack();
        $message = 'エラー: ' . $e->getMessage();
        $message_class = 'danger';
    }
}

$title = 'チーム構成編集 #' . $composition_id . ' - 野良ガチャVALORANT 管理画面';
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
        .user-player {
            background-color: #e8f4ff;
        }
        .agent-row {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
        }
        .agent-row.user-agent {
            background-color: #e8f4ff;
            border-color: #b8daff;
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
            <h2>チーム構成編集 #<?php echo $composition_id; ?></h2>
            <div>
                <a href="composition_detail.php?id=<?php echo $composition_id; ?>" class="btn btn-primary">
                    <i class="fas fa-eye"></i> 詳細に戻る
                </a>
                <a href="compositions.php" class="btn btn-secondary ms-2">
                    <i class="fas fa-arrow-left"></i> 一覧に戻る
                </a>
            </div>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $message_class; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <!-- 編集フォーム -->
        <form method="POST" action="">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-info-circle"></i> 基本情報
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="user_name" class="form-label">ユーザー名</label>
                                <input type="text" class="form-control" id="user_name" name="user_name" value="<?php echo htmlspecialchars($composition['user_name'] ?? ''); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">生成日時</label>
                                <input type="text" class="form-control" value="<?php echo date('Y年m月d日 H:i:s', strtotime($composition['created_at'])); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <?php if (!empty($composition['ogp_image_path'])): ?>
                                <div class="text-center">
                                    <h5 class="mb-3">OGP画像</h5>
                                    <img src="../<?php echo $composition['ogp_image_path']; ?>" class="img-fluid" style="max-height: 200px;" alt="OGP画像">
                                    <p class="text-muted mt-2">※OGP画像はシステムにより自動生成されるため、直接編集できません。</p>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning text-center">
                                    <i class="fas fa-image"></i> OGP画像がありません
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-users"></i> チームメンバー
                </div>
                <div class="card-body">
                    <?php if (count($agents) > 0): ?>
                        <?php foreach ($agents as $agent): ?>
                            <div class="agent-row <?php echo $agent['is_user'] ? 'user-agent' : ''; ?>">
                                <div class="row">
                                    <div class="col-md-5">
                                        <div class="mb-3">
                                            <label class="form-label">プレイヤー名</label>
                                            <input type="text" class="form-control" name="agent[<?php echo $agent['id']; ?>][player_name]" value="<?php echo htmlspecialchars($agent['player_name']); ?>">
                                            <?php if ($agent['is_user']): ?>
                                                <div class="form-text text-primary">あなた自身のプレイヤー名です</div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="mb-3">
                                            <label class="form-label">エージェント</label>
                                            <input type="text" class="form-control" name="agent[<?php echo $agent['id']; ?>][agent_name]" value="<?php echo htmlspecialchars($agent['agent_name']); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <?php if (!empty($agent['toxic_phrases'])): ?>
                                            <?php 
                                            $phrases = json_decode($agent['toxic_phrases'], true);
                                            if (is_array($phrases) && count($phrases) > 0):
                                            ?>
                                                <span class="badge bg-danger d-block mt-4">
                                                    害悪フレーズ: <?php echo count($phrases); ?>個
                                                </span>
                                                <div class="form-text">※害悪フレーズはシステムにより自動生成されます</div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-center my-5">このチーム構成にはメンバーがいません。</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="mt-4 d-flex justify-content-end">
                <input type="hidden" name="action" value="update">
                <a href="composition_detail.php?id=<?php echo $composition_id; ?>" class="btn btn-secondary me-2">キャンセル</a>
                <button type="submit" class="btn btn-valora">
                    <i class="fas fa-save"></i> 変更を保存
                </button>
            </div>
        </form>
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
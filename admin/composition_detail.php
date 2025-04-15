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

// チーム構成と所属メンバーの情報を取得
try {
    // チーム構成の基本情報取得
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
}

$title = 'チーム構成詳細 #' . $composition_id . ' - 野良ガチャVALORANT 管理画面';
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
        .table th {
            background-color: #f8f9fa;
        }
        .user-player {
            background-color: #e8f4ff;
        }
        .thumbnail {
            max-width: 100%;
            max-height: 200px;
            object-fit: contain;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .agent-name {
            font-weight: bold;
            color: #FF4655;
        }
        .toxicity-badge {
            background-color: #dc3545;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.8em;
            margin-left: 5px;
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
            <h2>チーム構成詳細 #<?php echo $composition_id; ?></h2>
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
        
        <!-- チーム構成の基本情報 -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-info-circle"></i> 基本情報
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th width="30%">ID</th>
                                <td><?php echo $composition['id']; ?></td>
                            </tr>
                            <tr>
                                <th>生成日時</th>
                                <td><?php echo date('Y年m月d日 H:i:s', strtotime($composition['created_at'])); ?></td>
                            </tr>
                            <tr>
                                <th>ユーザー名</th>
                                <td><?php echo htmlspecialchars($composition['user_name'] ?? '未設定'); ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <?php if (!empty($composition['ogp_image_path'])): ?>
                            <div class="text-center">
                                <h5 class="mb-3">OGP画像</h5>
                                <img src="../<?php echo $composition['ogp_image_path']; ?>" class="thumbnail" alt="OGP画像">
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
        
        <!-- チームメンバー情報 -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-users"></i> チームメンバー
            </div>
            <div class="card-body">
                <?php if (count($agents) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>プレイヤー</th>
                                    <th>エージェント</th>
                                    <th>害悪フレーズ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($agents as $agent): ?>
                                    <tr<?php echo $agent['is_user'] ? ' class="user-player"' : ''; ?>>
                                        <td>
                                            <?php echo htmlspecialchars($agent['player_name']); ?>
                                            <?php if ($agent['is_user']): ?>
                                                <span class="badge bg-primary">あなた</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="agent-name"><?php echo htmlspecialchars($agent['agent_name']); ?></td>
                                        <td>
                                            <?php if (!empty($agent['toxic_phrases'])): ?>
                                                <?php 
                                                $phrases = json_decode($agent['toxic_phrases'], true);
                                                if (is_array($phrases) && count($phrases) > 0):
                                                ?>
                                                    <span class="toxicity-badge">
                                                        <i class="fas fa-exclamation-triangle"></i> 害悪度: <?php echo count($phrases); ?>
                                                    </span>
                                                    <ul class="mt-2 mb-0">
                                                        <?php foreach ($phrases as $phrase): ?>
                                                            <li><?php echo htmlspecialchars($phrase); ?></li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php else: ?>
                                                    <span class="text-muted">なし</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">なし</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center my-5">このチーム構成にはメンバーがいません。</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- 操作ボタン -->
        <div class="mt-4 d-flex justify-content-between">
            <a href="compositions.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> 一覧に戻る
            </a>
            <div>
                <a href="composition_edit.php?id=<?php echo $composition_id; ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i> このチーム構成を編集
                </a>
                <button type="button" class="btn btn-danger ms-2" data-bs-toggle="modal" data-bs-target="#deleteModal">
                    <i class="fas fa-trash-alt"></i> このチーム構成を削除
                </button>
            </div>
        </div>
    </div>
    
    <!-- 削除確認モーダル -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">削除の確認</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>チーム構成 #<?php echo $composition_id; ?> を削除してもよろしいですか？</p>
                    <p class="text-danger"><strong>注意:</strong> この操作は元に戻せません。</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <form method="POST" action="compositions.php">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="composition_id" value="<?php echo $composition_id; ?>">
                        <button type="submit" class="btn btn-danger">削除する</button>
                    </form>
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
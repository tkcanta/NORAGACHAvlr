<?php
// 認証チェック
require_once 'auth.php';

// メッセージ初期化
$message = '';
$message_class = '';

// 削除処理
if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['composition_id'])) {
    $composition_id = (int)$_POST['composition_id'];
    
    try {
        // トランザクション開始
        $pdo->beginTransaction();
        
        // 関連するエージェントを削除
        $stmt = $pdo->prepare("DELETE FROM composition_agents WHERE composition_id = ?");
        $stmt->execute([$composition_id]);
        
        // チーム構成を削除
        $stmt = $pdo->prepare("DELETE FROM team_compositions WHERE id = ?");
        $stmt->execute([$composition_id]);
        
        // コミット
        $pdo->commit();
        
        $message = 'チーム構成 #' . $composition_id . ' を削除しました。';
        $message_class = 'success';
    } catch (PDOException $e) {
        // ロールバック
        $pdo->rollBack();
        $message = 'エラー: ' . $e->getMessage();
        $message_class = 'danger';
    }
}

// チーム構成一覧を取得
try {
    $stmt = $pdo->prepare("
        SELECT 
            tc.id, 
            tc.created_at, 
            '' as user_name,
            (SELECT COUNT(*) FROM composition_agents WHERE composition_id = tc.id) AS agent_count
        FROM 
            team_compositions tc
        ORDER BY 
            tc.created_at DESC
    ");
    $stmt->execute();
    $compositions = $stmt->fetchAll();
} catch (PDOException $e) {
    $message = 'エラー: ' . $e->getMessage();
    $message_class = 'danger';
    $compositions = [];
}

$title = 'チーム構成管理 - 野良ガチャVALORANT 管理画面';
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
            <h2>チーム構成管理</h2>
            <a href="composition_create.php" class="btn btn-valora">
                <i class="fas fa-plus"></i> 新規チーム構成
            </a>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $message_class; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <!-- チーム構成一覧 -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-list"></i> チーム構成一覧
            </div>
            <div class="card-body">
                <?php if (count($compositions) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>生成日時</th>
                                    <th>ユーザー名</th>
                                    <th>エージェント数</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($compositions as $comp): ?>
                                    <tr>
                                        <td><?php echo $comp['id']; ?></td>
                                        <td><?php echo date('Y年m月d日 H:i', strtotime($comp['created_at'])); ?></td>
                                        <td><?php echo htmlspecialchars($comp['user_name'] ?? '未設定'); ?></td>
                                        <td><?php echo $comp['agent_count']; ?></td>
                                        <td>
                                            <a href="composition_detail.php?id=<?php echo $comp['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i> 詳細
                                            </a>
                                            <a href="composition_edit.php?id=<?php echo $comp['id']; ?>" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i> 編集
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="<?php echo $comp['id']; ?>" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                                <i class="fas fa-trash-alt"></i> 削除
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center my-5">登録されているチーム構成はありません。</p>
                <?php endif; ?>
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
                    <p>チーム構成 #<span id="delete-id"></span> を削除してもよろしいですか？</p>
                    <p class="text-danger"><strong>注意:</strong> この操作は元に戻せません。</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <form method="POST">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="composition_id" id="delete-composition-id">
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
    <script>
        // 削除モーダル用のデータ設定
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                document.getElementById('delete-id').textContent = id;
                document.getElementById('delete-composition-id').value = id;
            });
        });
    </script>
</body>
</html> 
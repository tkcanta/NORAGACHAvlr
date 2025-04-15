<?php
// 認証チェック
require_once 'auth.php';

// メッセージ初期化
$message = '';
$message_class = '';

// プレイヤー追加処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $player_name = trim($_POST['player_name'] ?? '');
        $is_toxic = isset($_POST['is_toxic']) ? 1 : 0;
        
        if (!empty($player_name)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO random_players (name, is_toxic) VALUES (?, ?)");
                $stmt->execute([$player_name, $is_toxic]);
                $message = 'プレイヤーが正常に追加されました。';
                $message_class = 'success';
            } catch (PDOException $e) {
                $message = 'エラー: ' . $e->getMessage();
                $message_class = 'danger';
            }
        } else {
            $message = 'プレイヤー名を入力してください。';
            $message_class = 'warning';
        }
    } elseif ($_POST['action'] === 'edit') {
        $player_id = (int)$_POST['player_id'];
        $player_name = trim($_POST['player_name'] ?? '');
        $is_toxic = isset($_POST['is_toxic']) ? 1 : 0;
        
        if (!empty($player_name)) {
            try {
                $stmt = $pdo->prepare("UPDATE random_players SET name = ?, is_toxic = ? WHERE id = ?");
                $stmt->execute([$player_name, $is_toxic, $player_id]);
                $message = 'プレイヤーが正常に更新されました。';
                $message_class = 'success';
            } catch (PDOException $e) {
                $message = 'エラー: ' . $e->getMessage();
                $message_class = 'danger';
            }
        } else {
            $message = 'プレイヤー名を入力してください。';
            $message_class = 'warning';
        }
    } elseif ($_POST['action'] === 'delete') {
        $player_id = (int)$_POST['player_id'];
        
        try {
            $stmt = $pdo->prepare("DELETE FROM random_players WHERE id = ?");
            $stmt->execute([$player_id]);
            $message = 'プレイヤーが正常に削除されました。';
            $message_class = 'success';
        } catch (PDOException $e) {
            $message = 'エラー: ' . $e->getMessage();
            $message_class = 'danger';
        }
    }
}

// プレイヤー一覧を取得
$players = [];
try {
    $stmt = $pdo->query("SELECT * FROM random_players ORDER BY id DESC");
    $players = $stmt->fetchAll();
} catch (PDOException $e) {
    $message = 'プレイヤー一覧の取得に失敗しました: ' . $e->getMessage();
    $message_class = 'danger';
}

$title = '野良プレイヤー管理 - 野良ガチャVALORANT 管理画面';
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
        .badge-toxic {
            background-color: #FF4655;
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
                        <a class="nav-link active" href="players.php">野良プレイヤー管理</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="phrases.php">害悪フレーズ管理</a>
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
        <h2 class="mb-4">野良プレイヤー管理</h2>
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $message_class; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <!-- プレイヤー追加フォーム -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-plus"></i> 新規プレイヤー追加
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="add">
                            <div class="mb-3">
                                <label for="player_name" class="form-label">プレイヤー名</label>
                                <input type="text" class="form-control" id="player_name" name="player_name" required>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="is_toxic" name="is_toxic">
                                <label class="form-check-label" for="is_toxic">害悪プレイヤー</label>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-valora">追加する</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- プレイヤー一覧 -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-list"></i> プレイヤー一覧
                    </div>
                    <div class="card-body">
                        <?php if (count($players) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>プレイヤー名</th>
                                            <th>ステータス</th>
                                            <th>操作</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($players as $player): ?>
                                            <tr>
                                                <td><?php echo $player['id']; ?></td>
                                                <td><?php echo htmlspecialchars($player['name']); ?></td>
                                                <td>
                                                    <?php if ($player['is_toxic']): ?>
                                                        <span class="badge bg-danger">害悪</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success">通常</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-primary edit-player" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editPlayerModal"
                                                        data-id="<?php echo $player['id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($player['name']); ?>"
                                                        data-toxic="<?php echo $player['is_toxic']; ?>">
                                                        編集
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger delete-player"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#deletePlayerModal"
                                                        data-id="<?php echo $player['id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($player['name']); ?>">
                                                        削除
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-center">プレイヤーがまだ登録されていません。</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 編集モーダル -->
    <div class="modal fade" id="editPlayerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">プレイヤー編集</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="player_id" id="edit_player_id">
                        <div class="mb-3">
                            <label for="edit_player_name" class="form-label">プレイヤー名</label>
                            <input type="text" class="form-control" id="edit_player_name" name="player_name" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="edit_is_toxic" name="is_toxic">
                            <label class="form-check-label" for="edit_is_toxic">害悪プレイヤー</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                        <button type="submit" class="btn btn-primary">更新する</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- 削除確認モーダル -->
    <div class="modal fade" id="deletePlayerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">プレイヤー削除</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>プレイヤー「<span id="delete_player_name"></span>」を削除してもよろしいですか？</p>
                    <p class="text-danger">この操作は元に戻せません。</p>
                </div>
                <div class="modal-footer">
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="player_id" id="delete_player_id">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
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
    document.addEventListener('DOMContentLoaded', function() {
        // 編集モーダルのデータ設定
        var editPlayerBtns = document.querySelectorAll('.edit-player');
        editPlayerBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                var id = this.getAttribute('data-id');
                var name = this.getAttribute('data-name');
                var toxic = this.getAttribute('data-toxic') === '1';
                
                document.getElementById('edit_player_id').value = id;
                document.getElementById('edit_player_name').value = name;
                document.getElementById('edit_is_toxic').checked = toxic;
            });
        });
        
        // 削除モーダルのデータ設定
        var deletePlayerBtns = document.querySelectorAll('.delete-player');
        deletePlayerBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                var id = this.getAttribute('data-id');
                var name = this.getAttribute('data-name');
                
                document.getElementById('delete_player_id').value = id;
                document.getElementById('delete_player_name').textContent = name;
            });
        });
    });
    </script>
</body>
</html> 
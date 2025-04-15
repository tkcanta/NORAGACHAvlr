<?php
// 認証チェック
require_once 'auth.php';

// メッセージ初期化
$message = '';
$message_class = '';

// フレーズ追加処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $phrase = trim($_POST['phrase'] ?? '');
        $agent_id = !empty($_POST['agent_id']) ? (int)$_POST['agent_id'] : null;
        
        if (!empty($phrase)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO toxic_phrases (phrase, agent_id) VALUES (?, ?)");
                $stmt->execute([$phrase, $agent_id]);
                $message = 'フレーズが正常に追加されました。';
                $message_class = 'success';
            } catch (PDOException $e) {
                $message = 'エラー: ' . $e->getMessage();
                $message_class = 'danger';
            }
        } else {
            $message = 'フレーズを入力してください。';
            $message_class = 'warning';
        }
    } elseif ($_POST['action'] === 'edit') {
        $phrase_id = (int)$_POST['phrase_id'];
        $phrase = trim($_POST['phrase'] ?? '');
        $agent_id = !empty($_POST['agent_id']) ? (int)$_POST['agent_id'] : null;
        
        if (!empty($phrase)) {
            try {
                $stmt = $pdo->prepare("UPDATE toxic_phrases SET phrase = ?, agent_id = ? WHERE id = ?");
                $stmt->execute([$phrase, $agent_id, $phrase_id]);
                $message = 'フレーズが正常に更新されました。';
                $message_class = 'success';
            } catch (PDOException $e) {
                $message = 'エラー: ' . $e->getMessage();
                $message_class = 'danger';
            }
        } else {
            $message = 'フレーズを入力してください。';
            $message_class = 'warning';
        }
    } elseif ($_POST['action'] === 'delete') {
        $phrase_id = (int)$_POST['phrase_id'];
        
        try {
            $stmt = $pdo->prepare("DELETE FROM toxic_phrases WHERE id = ?");
            $stmt->execute([$phrase_id]);
            $message = 'フレーズが正常に削除されました。';
            $message_class = 'success';
        } catch (PDOException $e) {
            $message = 'エラー: ' . $e->getMessage();
            $message_class = 'danger';
        }
    }
}

// フレーズ一覧を取得
$phrases = [];
try {
    $stmt = $pdo->query("
        SELECT tp.*, a.display_name_ja, a.name
        FROM toxic_phrases tp
        LEFT JOIN agents a ON tp.agent_id = a.id
        ORDER BY tp.id DESC
    ");
    $phrases = $stmt->fetchAll();
} catch (PDOException $e) {
    $message = 'フレーズ一覧の取得に失敗しました: ' . $e->getMessage();
    $message_class = 'danger';
}

// エージェント一覧を取得
$agents = [];
try {
    $stmt = $pdo->query("SELECT id, name, display_name_ja FROM agents ORDER BY display_name_ja");
    $agents = $stmt->fetchAll();
} catch (PDOException $e) {
    $message = 'エージェント一覧の取得に失敗しました: ' . $e->getMessage();
    $message_class = 'danger';
}

$title = '害悪フレーズ管理 - 野良ガチャVALORANT 管理画面';
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
        .toxic-phrase {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
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
                        <a class="nav-link active" href="phrases.php">害悪フレーズ管理</a>
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
        <h2 class="mb-4">害悪フレーズ管理</h2>
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $message_class; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <!-- フレーズ追加フォーム -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-plus"></i> 新規フレーズ追加
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="add">
                            <div class="mb-3">
                                <label for="phrase" class="form-label">フレーズ</label>
                                <textarea class="form-control" id="phrase" name="phrase" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="agent_id" class="form-label">エージェント（オプション）</label>
                                <select class="form-select" id="agent_id" name="agent_id">
                                    <option value="">汎用フレーズ</option>
                                    <?php foreach ($agents as $agent): ?>
                                        <option value="<?php echo $agent['id']; ?>">
                                            <?php echo htmlspecialchars($agent['display_name_ja']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">特定のエージェント専用のフレーズにする場合は選択してください。</div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-valora">追加する</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- フレーズ一覧 -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-list"></i> フレーズ一覧
                    </div>
                    <div class="card-body">
                        <?php if (count($phrases) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>フレーズ</th>
                                            <th>エージェント</th>
                                            <th>操作</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($phrases as $phrase): ?>
                                            <tr>
                                                <td><?php echo $phrase['id']; ?></td>
                                                <td class="toxic-phrase" title="<?php echo htmlspecialchars($phrase['phrase']); ?>">
                                                    <?php echo htmlspecialchars($phrase['phrase']); ?>
                                                </td>
                                                <td>
                                                    <?php if ($phrase['agent_id']): ?>
                                                        <?php echo htmlspecialchars($phrase['display_name_ja']); ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">汎用</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-primary edit-phrase" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editPhraseModal"
                                                        data-id="<?php echo $phrase['id']; ?>"
                                                        data-phrase="<?php echo htmlspecialchars($phrase['phrase']); ?>"
                                                        data-agent="<?php echo $phrase['agent_id'] ?? ''; ?>">
                                                        編集
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger delete-phrase"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#deletePhraseModal"
                                                        data-id="<?php echo $phrase['id']; ?>"
                                                        data-phrase="<?php echo htmlspecialchars(mb_substr($phrase['phrase'], 0, 30)); ?>">
                                                        削除
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-center">フレーズがまだ登録されていません。</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 編集モーダル -->
    <div class="modal fade" id="editPhraseModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">フレーズ編集</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="phrase_id" id="edit_phrase_id">
                        <div class="mb-3">
                            <label for="edit_phrase" class="form-label">フレーズ</label>
                            <textarea class="form-control" id="edit_phrase" name="phrase" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="edit_agent_id" class="form-label">エージェント（オプション）</label>
                            <select class="form-select" id="edit_agent_id" name="agent_id">
                                <option value="">汎用フレーズ</option>
                                <?php foreach ($agents as $agent): ?>
                                    <option value="<?php echo $agent['id']; ?>">
                                        <?php echo htmlspecialchars($agent['display_name_ja']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
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
    <div class="modal fade" id="deletePhraseModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">フレーズ削除</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>フレーズ「<span id="delete_phrase_text"></span>...」を削除してもよろしいですか？</p>
                    <p class="text-danger">この操作は元に戻せません。</p>
                </div>
                <div class="modal-footer">
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="phrase_id" id="delete_phrase_id">
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
        var editPhraseBtns = document.querySelectorAll('.edit-phrase');
        editPhraseBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                var id = this.getAttribute('data-id');
                var phrase = this.getAttribute('data-phrase');
                var agent = this.getAttribute('data-agent');
                
                document.getElementById('edit_phrase_id').value = id;
                document.getElementById('edit_phrase').value = phrase;
                document.getElementById('edit_agent_id').value = agent;
            });
        });
        
        // 削除モーダルのデータ設定
        var deletePhraseBtns = document.querySelectorAll('.delete-phrase');
        deletePhraseBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                var id = this.getAttribute('data-id');
                var phrase = this.getAttribute('data-phrase');
                
                document.getElementById('delete_phrase_id').value = id;
                document.getElementById('delete_phrase_text').textContent = phrase;
            });
        });
    });
    </script>
</body>
</html> 
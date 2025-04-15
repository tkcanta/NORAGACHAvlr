<?php
// 戻るボタン設定
$show_back_button = true;
$back_url = 'index.php';

// ヘッダーを読み込み
require_once 'includes/header.php';
require_once 'includes/functions.php';

// エージェントを役割ごとに取得
$stmt = $pdo->prepare("SELECT * FROM agents ORDER BY role, display_name_ja");
$stmt->execute();
$agents = $stmt->fetchAll();

// 役割ごとに分類
$roleAgents = [
    'デュエリスト' => [],
    'センチネル' => [],
    'イニシエーター' => [],
    'コントローラー' => []
];

foreach ($agents as $agent) {
    $roleAgents[$agent['role']][] = $agent;
}
?>

<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card p-4 mb-4">
            <div class="card-body">
                <h2 class="mb-4 text-center">エージェントを選択</h2>
                
                <form id="agent-form" action="result.php" method="post">
                    <!-- ユーザー名入力 -->
                    <div class="mb-4">
                        <label for="username" class="form-label">あなたの名前 (2～12文字)</label>
                        <input type="text" class="form-control" id="username" name="username" required minlength="2" maxlength="12">
                    </div>
                    
                    <!-- エージェント選択 -->
                    <div class="agent-selection">
                        <label class="form-label">プレイするエージェントを選択</label>
                        <input type="hidden" id="selected_agent_id" name="selected_agent_id">
                        
                        <?php foreach ($roleAgents as $role => $roleAgentList): ?>
                        <h3 class="role-title"><?php echo $role; ?></h3>
                        <div class="agents-grid mb-4">
                            <?php foreach ($roleAgentList as $agent): ?>
                            <div class="agent-item" data-agent-id="<?php echo $agent['id']; ?>">
                                <img src="<?php echo $agent['image_path']; ?>" alt="<?php echo $agent['display_name_ja']; ?>">
                                <div class="agent-name"><?php echo $agent['display_name_ja']; ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- 送信ボタン -->
                    <div class="text-center mt-4">
                        <button type="submit" id="submit-button" class="btn btn-valora btn-lg" disabled>ガチャを回す</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// ページ読み込み時にJavaScriptが動作するため、以下のコードを追加
document.addEventListener('DOMContentLoaded', function() {
    // 初期状態ではボタンを無効化
    document.getElementById('submit-button').disabled = true;
});
</script>

<?php
// フッターを読み込み
require_once 'includes/footer.php';
?> 
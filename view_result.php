<?php
// 戻るボタン設定
$show_back_button = true;
$back_url = 'index.php';

// IDが指定されているか確認
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$composition_id = (int)$_GET['id'];

// OGP設定を先に初期化（エラー防止）
$ogp = true;
$page_title = '野良ガチャVALORANT - チーム構成結果 #' . $composition_id;
$ogp_title = '野良ガチャVALORANT - チーム構成 #' . $composition_id;
$ogp_description = 'VALORANTのチーム構成結果をご覧ください！ #VALORANT #野良ガチャ';
$current_url = '';
$ogp_image = '';

// データベース接続を含むヘッダーを読み込み
require_once 'includes/header.php';
require_once 'includes/functions.php';

// チーム構成と所属メンバーの情報を取得
try {
    // チーム構成の基本情報取得
    $stmt = $pdo->prepare("
        SELECT 
            tc.id, 
            tc.created_at, 
            tc.ogp_image_path
        FROM 
            team_compositions tc
        WHERE 
            tc.id = ?
    ");
    $stmt->execute([$composition_id]);
    $composition = $stmt->fetch();
    
    if (!$composition) {
        // 存在しない場合
        echo '<div class="container pt-5"><div class="alert alert-danger">指定されたチーム構成が見つかりません。</div></div>';
        require_once 'includes/footer.php';
        exit;
    }
    
    // チームメンバー情報を取得
    $stmt = $pdo->prepare("
        SELECT 
            ca.id,
            ca.player_name,
            ca.is_user,
            ca.is_toxic,
            ca.toxic_phrase,
            ca.agent_id,
            a.name as agent_name,
            a.display_name_ja,
            a.role,
            a.image_path
        FROM 
            composition_agents ca
        JOIN
            agents a ON ca.agent_id = a.id
        WHERE 
            ca.composition_id = ?
        ORDER BY 
            ca.is_user DESC, ca.id ASC
    ");
    $stmt->execute([$composition_id]);
    $agents = $stmt->fetchAll();
    
    // ユーザーとエージェント情報を抽出
    $userInfo = null;
    $randomPlayers = [];
    
    foreach ($agents as $agent) {
        if ($agent['is_user']) {
            $userInfo = [
                'name' => $agent['player_name'],
                'agent' => [
                    'id' => $agent['agent_id'],
                    'name' => $agent['agent_name'],
                    'display_name_ja' => $agent['display_name_ja'],
                    'role' => $agent['role'],
                    'image_path' => $agent['image_path']
                ]
            ];
        } else {
            $toxicPhrase = !empty($agent['toxic_phrase']) ? $agent['toxic_phrase'] : '';
            
            $randomPlayers[] = [
                'player' => [
                    'name' => $agent['player_name'],
                    'is_toxic' => $agent['is_toxic'] == 1
                ],
                'agent' => [
                    'id' => $agent['agent_id'],
                    'name' => $agent['agent_name'],
                    'display_name_ja' => $agent['display_name_ja'],
                    'role' => $agent['role'],
                    'image_path' => $agent['image_path']
                ],
                'toxic_phrase' => $toxicPhrase
            ];
        }
    }
    
    // ユーザー情報が取得できたらOGP情報を更新
    if ($userInfo) {
        $ogp_description = $userInfo['name'] . 'さんは' . $userInfo['agent']['display_name_ja'] . 'でプレイします！ #VALORANT #野良ガチャ';
    } else {
        $ogp_description = 'VALORANTのチーム構成結果をご覧ください！ #VALORANT #野良ガチャ';
    }
    
    // 現在のURL
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $script_name = $_SERVER['SCRIPT_NAME'];
    $directory = rtrim(dirname($script_name), '/');
    $current_url = $protocol . "://" . $host . $directory . "/view_result.php?id=" . $composition_id;
    
    // OGP画像URL処理
    $ogpImagePath = $composition['ogp_image_path'] ?? '';
    $ogpImageUrl = '';

    if (empty($ogpImagePath)) {
        $ogpImageUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/noragacha-vlr/images/ogp/default_ogp.png';
    } else if (strpos($ogpImagePath, 'http://') === 0 || strpos($ogpImagePath, 'https://') === 0) {
        // すでに絶対URLの場合はそのまま使用
        $ogpImageUrl = $ogpImagePath;
    } else {
        // 相対パスの場合は絶対URLに変換
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $ogpImageUrl = $protocol . $host . '/noragacha-vlr/' . ltrim($ogpImagePath, '/');
    }

    // X共有テキスト生成
    $twitter_text = '野良ガチャVALORANTでチーム構成を生成しました！';
    if ($userInfo) {
        $twitter_text .= ' 私は' . $userInfo['agent']['display_name_ja'] . 'でプレイします。';
    }
    $twitter_text .= ' #VALORANT #野良ガチャ';
    $twitter_url = 'https://twitter.com/intent/tweet?text=' . urlencode($twitter_text) . '&url=' . urlencode($current_url);
    
} catch (PDOException $e) {
    echo '<div class="container pt-5"><div class="alert alert-danger">エラーが発生しました: ' . $e->getMessage() . '</div></div>';
    require_once 'includes/footer.php';
    exit;
}

// 有害プレイヤーの数を計算
$toxicPlayers = array_filter($randomPlayers, function($player) {
    return $player['player']['is_toxic'];
});
$toxicPlayerCount = count($toxicPlayers);

// OGPタグ情報を更新
$page_title = '野良ガチャVALORANT - ' . $userInfo['name'] . 'のチーム評価結果';
$ogp_title = $page_title;
$ogp_description = $userInfo['name'] . 'さんのチーム構成評価 - 有害な言葉を言いそうなプレイヤー: ' . $toxicPlayerCount . '人';
$ogp_image = $ogpImageUrl;
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="team-composition mb-4">
                <h2 class="mb-4 text-center">チーム構成結果 #<?php echo $composition_id; ?></h2>
                <p class="text-center text-muted">生成日時: <?php echo date('Y年m月d日 H:i', strtotime($composition['created_at'])); ?></p>
                
                <div class="sequential-fade-in">
                    <!-- ユーザー -->
                    <div class="player-card user mb-4">
                        <div class="player-avatar">
                            <img src="<?php echo $userInfo['agent']['image_path']; ?>" alt="<?php echo $userInfo['agent']['display_name_ja']; ?>">
                        </div>
                        <div class="player-info">
                            <div class="player-name"><?php echo htmlspecialchars($userInfo['name']); ?> (プレイヤー)</div>
                            <div class="player-agent"><?php echo $userInfo['agent']['display_name_ja']; ?> (<?php echo $userInfo['agent']['role']; ?>)</div>
                        </div>
                    </div>
                    
                    <!-- ランダム生成されたプレイヤー -->
                    <?php foreach ($randomPlayers as $player): ?>
                    <div class="player-card <?php echo $player['player']['is_toxic'] ? 'toxic' : ''; ?> mb-4">
                        <div class="player-avatar">
                            <img src="<?php echo $player['agent']['image_path']; ?>" alt="<?php echo $player['agent']['display_name_ja']; ?>">
                        </div>
                        <div class="player-info">
                            <div class="player-name"><?php echo htmlspecialchars($player['player']['name']); ?></div>
                            <div class="player-agent"><?php echo $player['agent']['display_name_ja']; ?> (<?php echo $player['agent']['role']; ?>)</div>
                            
                            <?php if ($player['player']['is_toxic'] && isset($player['toxic_phrase'])): ?>
                            <div class="toxic-phrase">
                                <?php echo htmlspecialchars($player['toxic_phrase']); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- OGP画像表示 -->
                <?php if (!empty($composition['ogp_image_path'])): ?>
                <div class="text-center mt-4 mb-4">
                    <div class="card">
                        <div class="card-header">リザルト画像</div>
                        <div class="card-body">
                            <img src="<?php echo $ogpImageUrl; ?>" 
                                 class="img-fluid" 
                                 alt="リザルト画像"
                                 onerror="this.src='https://<?php echo $_SERVER['HTTP_HOST']; ?>/noragacha-vlr/images/ogp/default_ogp.png';this.classList.add('img-error');">
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- 共有ボタン -->
                <div class="text-center mt-4">
                    <a href="<?php echo $twitter_url; ?>" target="_blank" class="btn btn-valora">
                        <i class="fab fa-twitter"></i> Xで共有
                    </a>
                    <a href="index.php" class="btn btn-secondary-valora ms-2">トップに戻る</a>
                    <a href="select.php" class="btn btn-outline-valora ms-2">もう一度プレイ</a>
                </div>
                
                <!-- パーマリンク情報 -->
                <div class="mt-4 text-center">
                    <div class="card">
                        <div class="card-header">このリザルトへのリンク</div>
                        <div class="card-body">
                            <div class="input-group">
                                <input type="text" class="form-control" id="permalink" value="<?php echo $current_url; ?>" readonly>
                                <button class="btn btn-outline-secondary" type="button" id="copy-button" onclick="copyToClipboard()">コピー</button>
                            </div>
                            <small class="text-muted mt-2 d-block">このリンクを共有して、友達にあなたのチーム構成を見せよう！</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard() {
    var copyText = document.getElementById("permalink");
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(copyText.value);
    
    var button = document.getElementById("copy-button");
    button.innerHTML = "コピーしました！";
    button.classList.remove("btn-outline-secondary");
    button.classList.add("btn-success");
    
    setTimeout(function() {
        button.innerHTML = "コピー";
        button.classList.remove("btn-success");
        button.classList.add("btn-outline-secondary");
    }, 2000);
}
</script>

<?php
// フッターを読み込み
require_once 'includes/footer.php';
?>

<!-- OGPタグ設定 -->
<meta property="og:title" content="<?php echo htmlspecialchars($ogp_title); ?>">
<meta property="og:description" content="<?php echo htmlspecialchars($ogp_description); ?>">
<meta property="og:image" content="<?php echo htmlspecialchars($ogp_image); ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:type" content="website">
<meta property="og:url" content="<?php echo htmlspecialchars($current_url); ?>">

<!-- Twitterカード -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?php echo htmlspecialchars($ogp_title); ?>">
<meta name="twitter:description" content="<?php echo htmlspecialchars($ogp_description); ?>">
<meta name="twitter:image" content="<?php echo htmlspecialchars($ogp_image); ?>" onerror="this.onerror=null; this.src='https://<?php echo $_SERVER['HTTP_HOST']; ?>/noragacha-vlr/images/ogp/default_ogp.png';"> 
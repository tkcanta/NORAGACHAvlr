<?php
// 戻るボタン設定
$show_back_button = true;
$back_url = 'select.php';

// OGP設定を先に初期化（エラー防止）
$ogp = true;
$page_title = '野良ガチャVALORANT - チーム構成結果';
$ogp_title = '野良ガチャVALORANT - チーム構成結果';
$ogp_description = 'VALORANTのチーム構成結果をご覧ください！ #VALORANT #野良ガチャ';
$current_url = '';
$ogp_image = '';

// ヘッダーを読み込み
require_once 'includes/header.php';
require_once 'includes/functions.php';

// 既存の結果表示かチェック
if (isset($_GET['id'])) {
    // 既存の結果を取得
    $stmt = $pdo->prepare("
        SELECT tc.*, ca.*, a.name as agent_name, a.display_name_ja, a.role, a.image_path
        FROM team_compositions tc
        JOIN composition_agents ca ON tc.id = ca.composition_id
        JOIN agents a ON ca.agent_id = a.id
        WHERE tc.id = :id
    ");
    $stmt->bindValue(':id', $_GET['id'], PDO::PARAM_INT);
    $stmt->execute();
    $results = $stmt->fetchAll();
    
    if (empty($results)) {
        die('指定された結果が見つかりません。');
    }
    
    // ユーザーとエージェント情報を抽出
    $userInfo = null;
    $randomPlayers = [];
    $agents = [];
    
    foreach ($results as $result) {
        $agents[] = [
            'id' => $result['agent_id'],
            'name' => $result['agent_name'],
            'display_name_ja' => $result['display_name_ja'],
            'role' => $result['role'],
            'image_path' => $result['image_path']
        ];
        
        if ($result['is_user']) {
            $userInfo = [
                'name' => $result['player_name'],
                'agent' => [
                    'id' => $result['agent_id'],
                    'name' => $result['agent_name'],
                    'display_name_ja' => $result['display_name_ja'],
                    'role' => $result['role'],
                    'image_path' => $result['image_path']
                ]
            ];
        } else {
            $randomPlayers[] = [
                'player' => [
                    'name' => $result['player_name'],
                    'is_toxic' => $result['is_toxic']
                ],
                'agent' => [
                    'id' => $result['agent_id'],
                    'name' => $result['agent_name'],
                    'display_name_ja' => $result['display_name_ja'],
                    'role' => $result['role'],
                    'image_path' => $result['image_path']
                ],
                'toxic_phrase' => $result['toxic_phrase']
            ];
        }
    }
    
    $compositionId = $results[0]['composition_id'];
    $ogpImagePath = $results[0]['ogp_image_path'];
    
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // POSTデータからユーザー情報とエージェント選択を取得
    $username = htmlspecialchars($_POST['username']);
    $selectedAgentId = (int)$_POST['selected_agent_id'];
    
    // 入力検証
    if (empty($username) || empty($selectedAgentId)) {
        die('名前とエージェントを選択してください。');
    }
    
    if (strlen($username) < 2 || strlen($username) > 12) {
        die('名前は2～12文字で入力してください。');
    }
    
    // ユーザーセッションIDを生成（または既存のものを使用）
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['user_id'] = uniqid('user_', true);
    }
    $sessionId = $_SESSION['user_id'];
    
    // ユーザー情報を保存
    $stmt = $pdo->prepare("INSERT INTO users (username, selected_agent_id, session_id) VALUES (:username, :agent_id, :session_id)");
    $stmt->bindValue(':username', $username, PDO::PARAM_STR);
    $stmt->bindValue(':agent_id', $selectedAgentId, PDO::PARAM_INT);
    $stmt->bindValue(':session_id', $sessionId, PDO::PARAM_STR);
    $stmt->execute();
    $userId = $pdo->lastInsertId();
    
    // 選択したエージェント情報を取得
    $stmt = $pdo->prepare("SELECT * FROM agents WHERE id = :id");
    $stmt->bindValue(':id', $selectedAgentId, PDO::PARAM_INT);
    $stmt->execute();
    $userAgent = $stmt->fetch();
    
    if (!$userAgent) {
        die('選択したエージェントが見つかりません。');
    }
    
    // ランダムプレイヤーを生成（ユーザーが選んだエージェントを除く）
    $randomPlayers = getRandomPlayers($pdo, 4, $selectedAgentId);
    
    // 毒害度チェック（60%の確率で害悪プレイヤーが現れる）
    $toxicPlayerCount = 0;
    $toxicPhrases = [];
    
    foreach ($randomPlayers as $index => $player) {
        if ($player['player']['is_toxic']) {
            $toxicPlayerCount++;
            $toxicPhrases[$index] = getToxicPhrase($pdo, $player['agent']['id']);
        }
    }
    
    // チーム構成の評価
    $allAgents = array_merge([$userAgent], array_map(function($p) {
        return $p['agent'];
    }, $randomPlayers));
    
    $evaluation = evaluateTeamComposition($allAgents);
    
    // OGP画像を生成
    $ogpImagePath = generateOGPImage($userAgent, $username, $randomPlayers, $toxicPhrases);
    
    // 結果を保存
    $compositionId = saveTeamComposition($pdo, $userId, $selectedAgentId, $username, $randomPlayers, $ogpImagePath);
    
    $userInfo = [
        'name' => $username,
        'agent' => $userAgent
    ];
    
    // OGPタイトルと説明を更新
    $ogp_title = '野良ガチャVALORANT - ' . $username . 'の構成結果';
    $ogp_description = $username . 'さんは' . $userInfo['agent']['display_name_ja'] . 'でプレイします！ #VALORANT #野良ガチャ';
    
    // 永続的なリンクを生成
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $script_name = $_SERVER['SCRIPT_NAME'];
    $directory = rtrim(dirname($script_name), '/');
    $permanent_url = $protocol . "://" . $host . $directory . "/view_result.php?id=" . $compositionId;
    
    // X共有テキスト生成
    $twitter_text = '野良ガチャVALORANTでチーム構成を生成しました！';
    $twitter_text .= ' 私は' . $userAgent['display_name_ja'] . 'でプレイします。';
    $twitter_text .= ' #VALORANT #野良ガチャ';
    $twitter_url = 'https://twitter.com/intent/tweet?text=' . urlencode($twitter_text) . '&url=' . urlencode($permanent_url);
    
} else {
    // 無効なアクセス
    header('Location: index.php');
    exit;
}

// 現在のURLとOGP画像の絶対パス設定
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$script_name = $_SERVER['SCRIPT_NAME'];
$directory = rtrim(dirname($script_name), '/');
$current_url = $protocol . "://" . $host . $directory . "/result.php";

// OGP画像のURLを絶対パスに変換
$ogp_image_path = isset($ogpImagePath) ? $ogpImagePath : '';

// 既に絶対URLならそのまま、相対パスなら絶対URLに変換
if (!empty($ogp_image_path)) {
    if (strpos($ogp_image_path, 'http') !== 0) {
        // ドメインのルートから始まるパスに変換
        $ogp_image_path = ltrim($ogp_image_path, '/');
        $ogp_image = $protocol . "://" . $host . "/noragacha-vlr/" . $ogp_image_path;
    } else {
        $ogp_image = $ogp_image_path;
    }
} else {
    // デフォルトのOGP画像を設定（もしリザルト画像がない場合）
    $ogp_image = $protocol . "://" . $host . "/noragacha-vlr/images/ogp/default_ogp.png";
}
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="team-composition mb-4">
            <h2 class="mb-4 text-center">チーム構成結果</h2>
            
            <div class="sequential-fade-in">
                <!-- ユーザー -->
                <div class="player-card user mb-4">
                    <div class="player-avatar">
                        <img src="<?php echo $userInfo['agent']['image_path']; ?>" alt="<?php echo $userInfo['agent']['display_name_ja']; ?>">
                    </div>
                    <div class="player-info">
                        <div class="player-name"><?php echo htmlspecialchars($userInfo['name']); ?> (あなた)</div>
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
            
            <!-- チーム評価 -->
            <?php if (isset($evaluation)): ?>
            <div class="mt-4 mb-4">
                <h3>チーム評価</h3>
                <div class="alert alert-info">
                    <?php echo $evaluation['comment']; ?>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                デュエリスト
                                <span class="badge bg-primary rounded-pill"><?php echo $evaluation['roles']['デュエリスト']; ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                センチネル
                                <span class="badge bg-primary rounded-pill"><?php echo $evaluation['roles']['センチネル']; ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                イニシエーター
                                <span class="badge bg-primary rounded-pill"><?php echo $evaluation['roles']['イニシエーター']; ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                コントローラー
                                <span class="badge bg-primary rounded-pill"><?php echo $evaluation['roles']['コントローラー']; ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- 共有ボタン -->
            <div class="text-center mt-4">
                <a href="<?php echo $twitter_url; ?>" target="_blank" class="btn btn-valora">
                    <i class="fab fa-twitter"></i> Xで共有
                </a>
                <a href="select.php" id="retry-button" class="btn btn-secondary-valora ms-2">もう一度</a>
            </div>
            
            <!-- パーマリンク情報 -->
            <div class="mt-4 text-center">
                <div class="card">
                    <div class="card-header">このリザルトへの永続リンク</div>
                    <div class="card-body">
                        <div class="input-group">
                            <input type="text" class="form-control" id="permalink" value="<?php echo $permanent_url; ?>" readonly>
                            <button class="btn btn-outline-secondary" type="button" id="copy-button" onclick="copyToClipboard()">コピー</button>
                        </div>
                        <small class="text-muted mt-2 d-block">このリンクを保存・共有すると、あなたのチーム構成を誰でも見ることができます。</small>
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
<?php
// データベース設定を読み込み
require_once 'includes/config.php';

// テーブル作成クエリ
$queries = [
    // users テーブル
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) NOT NULL,
        selected_agent_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        session_id VARCHAR(255) NOT NULL
    )",

    // agents テーブル
    "CREATE TABLE IF NOT EXISTS agents (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL,
        display_name_ja VARCHAR(50) NOT NULL,
        role VARCHAR(50) NOT NULL,
        image_path VARCHAR(255) NOT NULL
    )",

    // random_players テーブル
    "CREATE TABLE IF NOT EXISTS random_players (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        is_toxic BOOLEAN DEFAULT FALSE
    )",

    // toxic_phrases テーブル
    "CREATE TABLE IF NOT EXISTS toxic_phrases (
        id INT AUTO_INCREMENT PRIMARY KEY,
        phrase TEXT NOT NULL,
        agent_id INT NULL
    )",

    // team_compositions テーブル
    "CREATE TABLE IF NOT EXISTS team_compositions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        composition_hash VARCHAR(255) NOT NULL,
        ogp_image_path VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",

    // composition_agents テーブル
    "CREATE TABLE IF NOT EXISTS composition_agents (
        id INT AUTO_INCREMENT PRIMARY KEY,
        composition_id INT NOT NULL,
        agent_id INT NOT NULL,
        player_name VARCHAR(255) NOT NULL,
        is_user BOOLEAN DEFAULT FALSE,
        is_toxic BOOLEAN DEFAULT FALSE,
        toxic_phrase TEXT NULL
    )"
];

// テーブルの作成
try {
    foreach ($queries as $query) {
        $pdo->exec($query);
    }
    echo "テーブルが正常に作成されました。<br>";
} catch (PDOException $e) {
    echo "テーブル作成エラー: " . $e->getMessage() . "<br>";
}

// エージェントデータの挿入
$agents = [
    // デュエリスト
    ['Jett', 'ジェット', 'デュエリスト', 'img/Jett.png'],
    ['Raze', 'レイズ', 'デュエリスト', 'img/Raze.png'],
    ['Phoenix', 'フェニックス', 'デュエリスト', 'img/Phoenix.png'],
    ['Reyna', 'レイナ', 'デュエリスト', 'img/Reyna.png'],
    ['Yoru', 'ヨル', 'デュエリスト', 'img/Yoru.png'],
    ['Neon', 'ネオン', 'デュエリスト', 'img/Neon.png'],
    ['Iso', 'アイソ', 'デュエリスト', 'img/Iso.png'],
    
    // センチネル
    ['Sage', 'セージ', 'センチネル', 'img/Sage.png'],
    ['Cypher', 'サイファー', 'センチネル', 'img/Cypher.png'],
    ['Killjoy', 'キルジョイ', 'センチネル', 'img/Killjoy.png'],
    ['Chamber', 'チェンバー', 'センチネル', 'img/Chamber.png'],
    ['Deadlock', 'デッドロック', 'センチネル', 'img/Deadlock.png'],
    
    // イニシエーター
    ['Breach', 'ブリーチ', 'イニシエーター', 'img/Breach.png'],
    ['Sova', 'ソーヴァ', 'イニシエーター', 'img/Sova.png'],
    ['Skye', 'スカイ', 'イニシエーター', 'img/Skye.png'],
    ['KAYO', 'ケイオー', 'イニシエーター', 'img/KAYO.png'],
    ['Fade', 'フェイド', 'イニシエーター', 'img/Fade.png'],
    ['Gekko', 'ゲッコー', 'イニシエーター', 'img/Gekko.png'],
    
    // コントローラー
    ['Omen', 'オーメン', 'コントローラー', 'img/Omen.png'],
    ['Brimstone', 'ブリムストーン', 'コントローラー', 'img/Brimstone.png'],
    ['Viper', 'ヴァイパー', 'コントローラー', 'img/Viper.png'],
    ['Astra', 'アストラ', 'コントローラー', 'img/Astra.png']
];

// エージェントデータの挿入
try {
    // 挿入前に既存データがあるか確認
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM agents");
    $row = $stmt->fetch();
    
    if ($row['count'] == 0) {
        $stmt = $pdo->prepare("INSERT INTO agents (name, display_name_ja, role, image_path) VALUES (?, ?, ?, ?)");
        
        foreach ($agents as $agent) {
            $stmt->execute($agent);
        }
        
        echo "エージェントデータが正常に挿入されました。<br>";
    } else {
        echo "エージェントデータは既に存在しています。<br>";
    }
} catch (PDOException $e) {
    echo "エージェントデータ挿入エラー: " . $e->getMessage() . "<br>";
}

// 野良プレイヤー名の挿入
$randomPlayers = [
    ['TenZ', false],
    ['Shroud', false],
    ['Sinatraa', false],
    ['ScreaM', false],
    ['VAC_BAN', true],
    ['Uninstall_NOW', true],
    ['Toxic_Player', true],
    ['ff15_go_next', true],
    ['Aim_Bot', false],
    ['Radianto_0', true],
    ['SharpShooter', false],
    ['WallHacker', true],
    ['ProGamer123', false],
    ['AFK_King', true],
    ['HeadHunter', false],
    ['Lurker', false],
    ['Flanker', false],
    ['BadAim', true],
    ['NoScope', false],
    ['Clutch_Master', false],
    ['1v5_God', false],
    ['Report_This_Guy', true],
    ['Smurf_Account', true],
    ['Boosted_Animal', true],
    ['OpFrag', false],
    ['VCT_Champion', false],
    ['Noob_Slayer', true],
    ['Vandal_Pro', false],
    ['Operator_Main', false],
    ['Phantom_Enjoyer', false],
    ['InstantLocker', true],
    ['DualistOrAFK', true],
    ['NeverSmokes', true],
    ['FlashTeammate', true],
    ['OdinSpammer', true],
    ['KnifeFighter', false],
    ['ClutchOrKick', true],
    ['BackseatGamer', true],
    ['BombHoarder', true],
    ['MicSpammer', true],
    ['Hardstuck_Iron', true],
    ['Radiant_Smurf', true],
    ['TeamMVP', false],
    ['NeverPeeks', true],
    ['AlwaysRotates', false],
    ['UtilityKing', false],
    ['SprayAndPray', true],
    ['CrouchWalker', false],
    ['RunAndGun', true],
    ['BigBrain', false]
];

// 野良プレイヤー名の挿入
try {
    // 挿入前に既存データがあるか確認
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM random_players");
    $row = $stmt->fetch();
    
    if ($row['count'] == 0) {
        $stmt = $pdo->prepare("INSERT INTO random_players (name, is_toxic) VALUES (?, ?)");
        
        foreach ($randomPlayers as $player) {
            $stmt->execute($player);
        }
        
        echo "野良プレイヤーデータが正常に挿入されました。<br>";
    } else {
        echo "野良プレイヤーデータは既に存在しています。<br>";
    }
} catch (PDOException $e) {
    echo "野良プレイヤーデータ挿入エラー: " . $e->getMessage() . "<br>";
}

// 害悪プレイヤーのセリフの挿入
$toxicPhrases = [
    ['gg ez', null],
    ['お前が原因で負けた', null],
    ['上手くなってからランク来い', null],
    ['お前がセージ選ぶな', 8], // Sage ID
    ['味方の足引っ張るなよ', null],
    ['キャリーできねえよお前', null],
    ['FF押せよ', null],
    ['uninstall please', null],
    ['お前のエイムどうなってんの？', null],
    ['レポートしといたから', null],
    ['学習して出直してこい', null],
    ['スパイク使えないの？', null],
    ['ガチャガチャうるせえぞ', null],
    ['黙れ雑魚', null],
    ['なんでその動きするの？', null],
    ['脳みそある？', null],
    ['バイアウトしろよ', null],
    ['オペレーター買えないの？', null],
    ['ミュートしたわ', null],
    ['マジでこのゲーム向いてないよ', null],
    ['デュエリストなのにキルとれないの？', null],
    ['敵の位置教えろよ', null],
    ['味方に当てんな', null],
    ['スキル使えないの？', null],
    ['なんでそこでULT？', null],
    ['逃げるなよ', null],
    ['なんでピークしたの？', null],
    ['味方の足を引っ張るな', null],
    ['そんなエージェント使うなよ', null],
    ['お前みたいなのがチームにいると負ける', null]
];

// 害悪プレイヤーのセリフの挿入
try {
    // 挿入前に既存データがあるか確認
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM toxic_phrases");
    $row = $stmt->fetch();
    
    if ($row['count'] == 0) {
        $stmt = $pdo->prepare("INSERT INTO toxic_phrases (phrase, agent_id) VALUES (?, ?)");
        
        foreach ($toxicPhrases as $phrase) {
            $stmt->execute($phrase);
        }
        
        echo "害悪プレイヤーのセリフデータが正常に挿入されました。<br>";
    } else {
        echo "害悪プレイヤーのセリフデータは既に存在しています。<br>";
    }
} catch (PDOException $e) {
    echo "害悪プレイヤーのセリフデータ挿入エラー: " . $e->getMessage() . "<br>";
}

echo "データベースのセットアップが完了しました。";
?> 
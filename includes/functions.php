<?php
/**
 * 野良ガチャVALORANT
 * 共通関数ファイル
 */

/**
 * ランダムなプレイヤーを取得する
 * @param PDO $pdo データベース接続
 * @param int $count 取得するプレイヤー数
 * @param int $excludeAgentId 除外するエージェントID
 * @return array ランダムプレイヤーの配列
 */
function getRandomPlayers($pdo, $count = 4, $excludeAgentId = null) {
    // ランダムなプレイヤー名を取得
    $stmt = $pdo->prepare("SELECT * FROM random_players ORDER BY RAND() LIMIT :count");
    $stmt->bindValue(':count', $count, PDO::PARAM_INT);
    $stmt->execute();
    $players = $stmt->fetchAll();
    
    // エージェントの除外条件を構築
    $excludeCondition = "";
    $params = [];
    
    if ($excludeAgentId !== null) {
        $excludeCondition = "WHERE id != :excludeId";
        $params[':excludeId'] = $excludeAgentId;
    }
    
    // ランダムなエージェントを取得
    $stmt = $pdo->prepare("SELECT * FROM agents $excludeCondition ORDER BY RAND()");
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $agents = $stmt->fetchAll();
    
    // プレイヤーごとにランダムなエージェントを割り当て、重複しないように
    $result = [];
    $usedAgentIds = [];
    
    if ($excludeAgentId !== null) {
        $usedAgentIds[] = $excludeAgentId;
    }
    
    foreach ($players as $index => $player) {
        // まだ使われていないエージェントを探す
        foreach ($agents as $agent) {
            if (!in_array($agent['id'], $usedAgentIds)) {
                $usedAgentIds[] = $agent['id'];
                
                // プレイヤーとエージェントを組み合わせる
                $result[$index] = [
                    'player' => $player,
                    'agent' => $agent
                ];
                
                break;
            }
        }
    }
    
    return $result;
}

/**
 * 害悪プレイヤー用のセリフを取得する
 * @param PDO $pdo データベース接続
 * @param int|null $agentId エージェントID（特定のエージェント用のセリフがある場合）
 * @return string セリフ
 */
function getToxicPhrase($pdo, $agentId = null) {
    // エージェント固有のセリフがあればそれを優先
    if ($agentId !== null) {
        $stmt = $pdo->prepare("SELECT phrase FROM toxic_phrases WHERE agent_id = :agent_id ORDER BY RAND() LIMIT 1");
        $stmt->bindValue(':agent_id', $agentId, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch();
        
        if ($result) {
            return $result['phrase'];
        }
    }
    
    // エージェント固有のセリフがなければ汎用セリフから取得
    $stmt = $pdo->prepare("SELECT phrase FROM toxic_phrases WHERE agent_id IS NULL ORDER BY RAND() LIMIT 1");
    $stmt->execute();
    
    $result = $stmt->fetch();
    
    return $result ? $result['phrase'] : 'gg ez';
}

/**
 * チーム構成を保存する
 * @param PDO $pdo データベース接続
 * @param int $userId ユーザーID
 * @param int $userAgentId ユーザーが選択したエージェントID
 * @param string $username ユーザー名
 * @param array $randomPlayers ランダムプレイヤーの配列
 * @param string $ogpImagePath OGP画像のパス
 * @return int 作成されたチーム構成のID
 */
function saveTeamComposition($pdo, $userId, $userAgentId, $username, $randomPlayers, $ogpImagePath) {
    // エージェントIDの配列を作成してハッシュ化
    $agentIds = [$userAgentId];
    foreach ($randomPlayers as $player) {
        $agentIds[] = $player['agent']['id'];
    }
    sort($agentIds);
    $compositionHash = md5(implode('-', $agentIds));
    
    try {
        $pdo->beginTransaction();
        
        // チーム構成を保存
        $stmt = $pdo->prepare("INSERT INTO team_compositions (user_id, composition_hash, ogp_image_path) VALUES (:user_id, :hash, :ogp_path)");
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':hash', $compositionHash, PDO::PARAM_STR);
        $stmt->bindValue(':ogp_path', $ogpImagePath, PDO::PARAM_STR);
        $stmt->execute();
        
        $compositionId = $pdo->lastInsertId();
        
        // ユーザーのエージェントを保存
        $stmt = $pdo->prepare("INSERT INTO composition_agents (composition_id, agent_id, player_name, is_user, is_toxic) VALUES (:comp_id, :agent_id, :name, 1, 0)");
        $stmt->bindValue(':comp_id', $compositionId, PDO::PARAM_INT);
        $stmt->bindValue(':agent_id', $userAgentId, PDO::PARAM_INT);
        $stmt->bindValue(':name', $username, PDO::PARAM_STR);
        $stmt->execute();
        
        // ランダムプレイヤーのエージェントを保存
        foreach ($randomPlayers as $player) {
            $isToxic = $player['player']['is_toxic'] ? 1 : 0;
            $toxicPhrase = null;
            
            if ($isToxic) {
                $toxicPhrase = getToxicPhrase($pdo, $player['agent']['id']);
            }
            
            $stmt = $pdo->prepare("INSERT INTO composition_agents (composition_id, agent_id, player_name, is_user, is_toxic, toxic_phrase) VALUES (:comp_id, :agent_id, :name, 0, :is_toxic, :phrase)");
            $stmt->bindValue(':comp_id', $compositionId, PDO::PARAM_INT);
            $stmt->bindValue(':agent_id', $player['agent']['id'], PDO::PARAM_INT);
            $stmt->bindValue(':name', $player['player']['name'], PDO::PARAM_STR);
            $stmt->bindValue(':is_toxic', $isToxic, PDO::PARAM_INT);
            $stmt->bindValue(':phrase', $toxicPhrase, PDO::PARAM_STR);
            $stmt->execute();
        }
        
        $pdo->commit();
        return $compositionId;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}

/**
 * チーム構成の評価を生成する
 * @param array $agents エージェントの配列
 * @return array 評価とコメント
 */
function evaluateTeamComposition($agents) {
    $roleCount = [
        'デュエリスト' => 0,
        'センチネル' => 0,
        'イニシエーター' => 0,
        'コントローラー' => 0
    ];
    
    // 役割ごとのカウント
    foreach ($agents as $agent) {
        $role = $agent['role'];
        if (isset($roleCount[$role])) {
            $roleCount[$role]++;
        }
    }
    
    // バランスの評価
    $hasController = $roleCount['コントローラー'] > 0;
    $hasSentinel = $roleCount['センチネル'] > 0;
    $hasInitiator = $roleCount['イニシエーター'] > 0;
    $duelistCount = $roleCount['デュエリスト'];
    
    $balance = 'balanced';
    $comment = 'バランスの取れたチーム構成です。';
    
    if ($duelistCount >= 3) {
        $balance = 'aggressive';
        $comment = '攻撃的すぎるチーム構成です。スモークがないと厳しいかも...';
    } elseif ($duelistCount == 0) {
        $balance = 'defensive';
        $comment = '守備的なチーム構成です。キルを取れるプレイヤーが必要かも...';
    } elseif (!$hasController) {
        $balance = 'no-smoke';
        $comment = 'スモークがいません！エントリーが難しいでしょう。';
    } elseif (!$hasSentinel && !$hasInitiator) {
        $balance = 'no-support';
        $comment = 'サポート系のエージェントがいません。情報収集が難しいかも...';
    } elseif ($roleCount['デュエリスト'] == 1 && $roleCount['コントローラー'] >= 1 && $roleCount['センチネル'] >= 1) {
        $balance = 'perfect';
        $comment = '理想的なチーム構成です！';
    }
    
    return [
        'balance' => $balance,
        'comment' => $comment,
        'roles' => $roleCount
    ];
}

/**
 * OGP画像を生成する
 * @param array $userAgent ユーザーのエージェント情報
 * @param string $username ユーザー名
 * @param array $randomPlayers ランダムプレイヤーの配列
 * @param array $toxicPlayers 害悪プレイヤーの配列
 * @return string 生成された画像のパス
 */
function generateOGPImage($userAgent, $username, $randomPlayers, $toxicPlayers = []) {
    // 画像サイズとパス
    $width = 1200;
    $height = 630;
    $outputPath = 'images/ogp/' . time() . '_' . md5($username . rand(1000, 9999)) . '.png';
    
    // 必要なディレクトリが存在することを確認
    if (!file_exists(dirname($outputPath))) {
        mkdir(dirname($outputPath), 0777, true);
    }
    
    // 画像の作成
    $image = imagecreatetruecolor($width, $height);
    
    // 色の定義（スクリーンショットに合わせた色）
    $bgColor = imagecolorallocate($image, 240, 240, 240); // 背景色（薄いグレー）
    $cardBgColor = imagecolorallocate($image, 255, 255, 255); // カード背景色（白）
    $headerBgColor = imagecolorallocate($image, 248, 249, 250); // ヘッダー背景
    $userCardBgColor = imagecolorallocate($image, 226, 242, 254); // ユーザーカード背景（水色）
    $toxicCardBgColor = imagecolorallocate($image, 255, 232, 232); // 有害プレイヤー背景（薄い赤）
    $toxicBubbleBgColor = imagecolorallocate($image, 255, 90, 95); // 有害プレイヤーの吹き出し背景（赤）
    $mainColor = imagecolorallocate($image, 0, 123, 255); // メインカラー（青）
    $textColor = imagecolorallocate($image, 33, 37, 41); // テキスト色（黒に近いグレー）
    $nameColor = imagecolorallocate($image, 73, 80, 87); // 名前テキスト色（濃いグレー）
    $white = imagecolorallocate($image, 255, 255, 255); // 白
    $lightBorderColor = imagecolorallocate($image, 222, 226, 230); // カードの境界線（薄いグレー）
    
    // 背景を塗りつぶす
    imagefill($image, 0, 0, $bgColor);
    
    // ヘッダー
    imagefilledrectangle($image, 0, 0, $width, 60, $headerBgColor);
    
    // フォントの設定
    $fontFound = false;
    
    // フォント検索パス
    $fontPaths = [
        '/home/cantacancan/tk-production.xyz/public_html/noragacha-vlr/fonts/NotoSansJP-Bold.ttf',
        '/home/cantacancan/tk-production.xyz/public_html/noragacha-vlr/fonts/NotoSansJP-Regular.ttf',
        '/usr/share/fonts/truetype/noto/NotoSansCJK-Regular.ttc',
        '/usr/share/fonts/opentype/noto/NotoSansCJK-Regular.ttc',
        '/usr/share/fonts/noto-cjk/NotoSansCJK-Regular.ttc',
        dirname(__FILE__) . '/../fonts/NotoSansJP-Bold.ttf',
        dirname(__FILE__) . '/../../fonts/NotoSansJP-Bold.ttf',
        'fonts/NotoSansJP-Bold.ttf',
    ];
    
    // 最初にフォントが存在するかチェック
    foreach ($fontPaths as $font) {
        if (file_exists($font)) {
            $fontPath = $font;
            $fontFound = true;
            break;
        }
    }
    
    // 他のLinuxサーバーでよく見られるフォント（日本語対応）も確認
    if (!$fontFound) {
        $additionalFonts = [
            '/usr/share/fonts/japanese/TrueType/ipag.ttf',
            '/usr/share/fonts/japanese/TrueType/ipagp.ttf',
            '/usr/share/fonts/japanese/gothic.ttf',
            '/usr/share/fonts/ipa-gothic/ipag.ttf',
            '/usr/share/fonts/vlgothic/VL-Gothic-Regular.ttf',
            '/usr/share/fonts/vlgothic/VL-PGothic-Regular.ttf',
            '/usr/share/fonts/google-noto/NotoSansCJK-Regular.ttc',
            '/usr/share/fonts/google-noto-cjk/NotoSansCJK-Regular.ttc',
        ];
        
        foreach ($additionalFonts as $font) {
            if (file_exists($font)) {
                $fontPath = $font;
                $fontFound = true;
                break;
            }
        }
    }
    
    // フォントが見つからない場合は組み込みフォントを使用する
    if (!$fontFound) {
        // フォールバックフォント指定（GDに組み込まれたもの）
        $fontPath = 1; // GDの組み込みフォント
    }
    
    // カード関連の値を定義
    $cardPadding = 30;
    $cardSpacing = 15;
    $cardWidth = $width - ($cardPadding * 2);
    $cardHeight = 85;
    $startY = 80;
    $avatarSize = 65;
    $cornerRadius = 8;
    
    // ヘッダータイトル
    if ($fontFound) {
        @imagettftext($image, 24, 0, 30, 40, $nameColor, $fontPath, '野良ガチャVALORANT - チーム評価結果');
    } else {
        imagestring($image, 5, 30, 20, 'VALORANT NORAGACHA - TEAM RESULT', $nameColor);
    }
    
    // ユーザーカード
    $userCardY = $startY;
    roundedRectangle($image, $cardPadding, $userCardY, $cardPadding + $cardWidth, $userCardY + $cardHeight, $cornerRadius, $userCardBgColor);
    
    // アバター画像を描画
    $userAvatarX = $cardPadding + 10;
    $userAvatarY = $userCardY + ($cardHeight - $avatarSize) / 2;
    
    // エージェントアバター画像読み込み
    if (file_exists($userAgent['image_path'])) {
        $avatarImage = @imagecreatefrompng($userAgent['image_path']);
        if ($avatarImage) {
            // アバター画像をリサイズしてカードに配置
            imagecopyresampled(
                $image, $avatarImage,
                $userAvatarX, $userAvatarY,
                0, 0, $avatarSize, $avatarSize,
                imagesx($avatarImage), imagesy($avatarImage)
            );
            imagedestroy($avatarImage);
        } else {
            // 代替表示
            roundedRectangle($image, $userAvatarX, $userAvatarY, $userAvatarX + $avatarSize, $userAvatarY + $avatarSize, 5, $mainColor);
        }
    } else {
        // 代替表示
        roundedRectangle($image, $userAvatarX, $userAvatarY, $userAvatarX + $avatarSize, $userAvatarY + $avatarSize, 5, $mainColor);
    }
    
    // ユーザー情報テキスト
    $userTextX = $userAvatarX + $avatarSize + 15;
    $userNameText = $username . " (プレイヤー)";
    $userAgentText = $userAgent['display_name_ja'] . " (" . $userAgent['role'] . ")";
    
    if ($fontFound) {
        @imagettftext($image, 20, 0, $userTextX, $userCardY + 35, $nameColor, $fontPath, $userNameText);
        @imagettftext($image, 16, 0, $userTextX, $userCardY + 65, $textColor, $fontPath, $userAgentText);
    } else {
        imagestring($image, 5, $userTextX, $userCardY + 20, $username, $nameColor);
        imagestring($image, 4, $userTextX, $userCardY + 50, $userAgent['display_name_ja'], $textColor);
    }
    
    // 有害プレイヤーカウンター
    $toxicCount = 0;
    
    // ランダムプレイヤーカード
    $currentY = $userCardY + $cardHeight + $cardSpacing;
    foreach ($randomPlayers as $index => $player) {
        $playerName = $player['player']['name'];
        $agentName = isset($player['agent']['display_name_ja']) ? $player['agent']['display_name_ja'] : "Agent";
        $agentRole = isset($player['agent']['role']) ? $player['agent']['role'] : "Role";
        $isToxic = $player['player']['is_toxic'];
        
        if ($isToxic) {
            $toxicCount++;
        }
        
        // カード背景（有害プレイヤーは特別なスタイル）
        $cardColor = $isToxic ? $toxicCardBgColor : $cardBgColor;
        roundedRectangle($image, $cardPadding, $currentY, $cardPadding + $cardWidth, $currentY + $cardHeight, $cornerRadius, $cardColor);
        
        // エージェントアバター
        $playerAvatarX = $cardPadding + 10;
        $playerAvatarY = $currentY + ($cardHeight - $avatarSize) / 2;
        
        if (file_exists($player['agent']['image_path'])) {
            $avatarImage = @imagecreatefrompng($player['agent']['image_path']);
            if ($avatarImage) {
                imagecopyresampled(
                    $image, $avatarImage,
                    $playerAvatarX, $playerAvatarY,
                    0, 0, $avatarSize, $avatarSize,
                    imagesx($avatarImage), imagesy($avatarImage)
                );
                imagedestroy($avatarImage);
            } else {
                // 代替表示
                roundedRectangle($image, $playerAvatarX, $playerAvatarY, $playerAvatarX + $avatarSize, $playerAvatarY + $avatarSize, 5, $mainColor);
            }
        } else {
            // 代替表示
            roundedRectangle($image, $playerAvatarX, $playerAvatarY, $playerAvatarX + $avatarSize, $playerAvatarY + $avatarSize, 5, $mainColor);
        }
        
        // プレイヤー情報テキスト
        $playerTextX = $playerAvatarX + $avatarSize + 15;
        $playerNameColor = $isToxic ? $nameColor : $nameColor;
        
        if ($fontFound) {
            @imagettftext($image, 20, 0, $playerTextX, $currentY + 35, $playerNameColor, $fontPath, $playerName);
            @imagettftext($image, 16, 0, $playerTextX, $currentY + 65, $textColor, $fontPath, $agentName . " (" . $agentRole . ")");
        } else {
            imagestring($image, 5, $playerTextX, $currentY + 20, $playerName, $playerNameColor);
            imagestring($image, 4, $playerTextX, $currentY + 50, $agentName, $textColor);
        }
        
        // 有害プレイヤーのセリフを表示
        if ($isToxic && isset($toxicPlayers[$index])) {
            $toxicPhrase = $toxicPlayers[$index];
            
            // セリフ表示用の吹き出し
            $bubbleX = $cardPadding + $cardWidth - 400;
            $bubbleY = $currentY + 20;
            $bubbleWidth = 380;
            $bubbleHeight = 45;
            
            roundedRectangle($image, $bubbleX, $bubbleY, $bubbleX + $bubbleWidth, $bubbleY + $bubbleHeight, $cornerRadius, $toxicBubbleBgColor);
            
            // セリフテキスト（短くする場合は省略）
            $shortenedPhrase = mb_strlen($toxicPhrase) > 30 ? mb_substr($toxicPhrase, 0, 30) . "..." : $toxicPhrase;
            
            if ($fontFound) {
                @imagettftext($image, 16, 0, $bubbleX + 15, $bubbleY + 30, $white, $fontPath, $shortenedPhrase);
            } else {
                imagestring($image, 4, $bubbleX + 15, $bubbleY + 15, $shortenedPhrase, $white);
            }
        }
        
        $currentY += $cardHeight + $cardSpacing;
    }
    
    // フッター情報
    $footerY = $height - 50;
    
    if ($fontFound) {
        @imagettftext($image, 18, 0, $cardPadding, $footerY, $nameColor, $fontPath, "有害な言葉を言いそうなプレイヤー: " . $toxicCount . "人");
        
        // サービス名
        $serviceText = "VALORANT 野良ガチャ | https://tk-production.xyz/noragacha-vlr/";
        $serviceBox = @imagettfbbox(14, 0, $fontPath, $serviceText);
        $serviceX = $serviceBox ? ($width - ($serviceBox[2] - $serviceBox[0])) / 2 : 300;
        @imagettftext($image, 14, 0, $serviceX, $height - 20, $nameColor, $fontPath, $serviceText);
    } else {
        imagestring($image, 4, $cardPadding, $footerY - 10, "Toxic Players: " . $toxicCount, $nameColor);
        imagestring($image, 3, 300, $height - 20, "VALORANT NORAGACHA | https://tk-production.xyz/noragacha-vlr/", $nameColor);
    }
    
    // 画像の保存
    imagepng($image, $outputPath);
    imagedestroy($image);
    
    return $outputPath;
}

/**
 * 角丸の四角形を描画する関数
 */
function roundedRectangle($image, $x1, $y1, $x2, $y2, $radius, $color) {
    // 四角形の各辺を描画
    imagefilledrectangle($image, $x1 + $radius, $y1, $x2 - $radius, $y1 + $radius, $color); // 上辺
    imagefilledrectangle($image, $x1, $y1 + $radius, $x2, $y2 - $radius, $color); // 中央部
    imagefilledrectangle($image, $x1 + $radius, $y2 - $radius, $x2 - $radius, $y2, $color); // 下辺
    
    // 四隅の円弧を描画
    imagefilledarc($image, $x1 + $radius, $y1 + $radius, $radius * 2, $radius * 2, 180, 270, $color, IMG_ARC_PIE);
    imagefilledarc($image, $x2 - $radius, $y1 + $radius, $radius * 2, $radius * 2, 270, 360, $color, IMG_ARC_PIE);
    imagefilledarc($image, $x1 + $radius, $y2 - $radius, $radius * 2, $radius * 2, 90, 180, $color, IMG_ARC_PIE);
    imagefilledarc($image, $x2 - $radius, $y2 - $radius, $radius * 2, $radius * 2, 0, 90, $color, IMG_ARC_PIE);
} 
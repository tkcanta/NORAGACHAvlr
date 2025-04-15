-- 野良ガチャVALORANT データベース構造
-- 作成日: 2024-05-24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+09:00";
SET NAMES utf8mb4;

-- --------------------------------------------------------

--
-- テーブル構造 `agents`
--

CREATE TABLE `agents` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `display_name_ja` varchar(50) NOT NULL,
  `role` varchar(30) NOT NULL,
  `image_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- テーブルのデータのダンプ `agents`
--

INSERT INTO `agents` (`id`, `name`, `display_name_ja`, `role`, `image_path`) VALUES
(1, 'Jett', 'ジェット', 'デュエリスト', 'images/agents/jett.jpg'),
(2, 'Phoenix', 'フェニックス', 'デュエリスト', 'images/agents/phoenix.jpg'),
(3, 'Raze', 'レイズ', 'デュエリスト', 'images/agents/raze.jpg'),
(4, 'Reyna', 'レイナ', 'デュエリスト', 'images/agents/reyna.jpg'),
(5, 'Neon', 'ネオン', 'デュエリスト', 'images/agents/neon.jpg'),
(6, 'Yoru', 'ヨル', 'デュエリスト', 'images/agents/yoru.jpg'),
(7, 'Sage', 'セージ', 'センチネル', 'images/agents/sage.jpg'),
(8, 'Cypher', 'サイファー', 'センチネル', 'images/agents/cypher.jpg'),
(9, 'Killjoy', 'キルジョイ', 'センチネル', 'images/agents/killjoy.jpg'),
(10, 'Chamber', 'チェンバー', 'センチネル', 'images/agents/chamber.jpg'),
(11, 'Deadlock', 'デッドロック', 'センチネル', 'images/agents/deadlock.jpg'),
(12, 'Sova', 'ソーヴァ', 'イニシエーター', 'images/agents/sova.jpg'),
(13, 'Breach', 'ブリーチ', 'イニシエーター', 'images/agents/breach.jpg'),
(14, 'Skye', 'スカイ', 'イニシエーター', 'images/agents/skye.jpg'),
(15, 'KAY/O', 'ケイオー', 'イニシエーター', 'images/agents/kayo.jpg'),
(16, 'Fade', 'フェイド', 'イニシエーター', 'images/agents/fade.jpg'),
(17, 'Gekko', 'ゲッコー', 'イニシエーター', 'images/agents/gekko.jpg'),
(18, 'Brimstone', 'ブリムストーン', 'コントローラー', 'images/agents/brimstone.jpg'),
(19, 'Viper', 'ヴァイパー', 'コントローラー', 'images/agents/viper.jpg'),
(20, 'Omen', 'オーメン', 'コントローラー', 'images/agents/omen.jpg'),
(21, 'Astra', 'アストラ', 'コントローラー', 'images/agents/astra.jpg'),
(22, 'Harbor', 'ハーバー', 'コントローラー', 'images/agents/harbor.jpg'),
(23, 'Clove', 'クローブ', 'コントローラー', 'images/agents/clove.jpg'),
(24, 'Iso', 'アイソ', 'デュエリスト', 'images/agents/iso.jpg');

-- --------------------------------------------------------

--
-- テーブル構造 `random_players`
--

CREATE TABLE `random_players` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `is_toxic` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- テーブルのデータのダンプ `random_players`
--

INSERT INTO `random_players` (`id`, `name`, `is_toxic`) VALUES
(1, 'aimGOD', 1),
(2, 'HeadHunter', 0),
(3, 'ShadowKiller', 0),
(4, 'TacMaster', 0),
(5, 'FlickMachine', 1),
(6, 'インスタロック勢', 1),
(7, 'ワンタップキング', 0),
(8, 'エイムbot疑惑', 1),
(9, '無言プレイヤー', 0),
(10, 'スモークマスター', 0),
(11, 'フラッシュキング', 0),
(12, 'スパイクプランター', 0),
(13, 'エコラウンドエース', 0),
(14, 'マイクなし', 1),
(15, 'ガングリッパー', 0),
(16, 'パワープレイヤー', 0),
(17, '初心者です', 0),
(18, 'キルレ気にしない', 0),
(19, 'クラッチマスター', 0),
(20, 'エイムがない男', 1),
(21, 'OP専門家', 0),
(22, '壁バン名人', 0),
(23, 'ゲームセンス', 0),
(24, 'ポジションキング', 0),
(25, 'APSハイ', 1),
(26, '2chプロ', 1),
(27, 'Vチューバー', 0),
(28, 'レディアント目指す', 0),
(29, 'スペック不足', 1),
(30, 'マウスカム勢', 0),
(31, 'センシ迷子', 0),
(32, '元CS民', 0),
(33, 'FPSkiller', 0),
(34, 'アイアン抜け出せない', 1),
(35, 'プレイスして！', 1),
(36, 'ラッシュ好き', 1),
(37, 'CQCマスター', 0),
(38, 'スモークダッシュ', 0),
(39, 'ヘッドショット率80%', 0),
(40, '芋プレイヤー', 1),
(41, 'ラダー専', 0),
(42, 'アンレ専', 0),
(43, '夜だけプレイ', 0),
(44, '朝型VALORANTer', 0),
(45, 'キャリーさせて', 1),
(46, 'ミッドオンリー', 1),
(47, '待ち伏せマスター', 0),
(48, '裏取り名人', 0),
(49, '強武器しか使えない', 1),
(50, 'アルティメット貯金', 0);

-- --------------------------------------------------------

--
-- テーブル構造 `toxic_phrases`
--

CREATE TABLE `toxic_phrases` (
  `id` int(11) NOT NULL,
  `phrase` text NOT NULL,
  `agent_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- テーブルのデータのダンプ `toxic_phrases`
--

INSERT INTO `toxic_phrases` (`id`, `phrase`, `agent_id`) VALUES
(1, 'gg ez', NULL),
(2, 'お前のエイム笑えるww', NULL),
(3, 'ブロンズ？シルバー？', NULL),
(4, 'キルとれないなら辞めれば？', NULL),
(5, 'なんでそんな立ち回りするの？バカなの？', NULL),
(6, 'AIMしっかりしろよ', NULL),
(7, 'センスないわ', NULL),
(8, 'チームガチャ負け', NULL),
(9, 'ジェット使えるならプレイしないで', 1),
(10, 'レイナ使ってこのキルレはヤバいでしょw', 4),
(11, 'セージ、ヒールしろよ！', 7),
(12, 'ウルト使えないならソーヴァ使うな', 12),
(13, 'スモークしっかり頼むよ', 18),
(14, '無言でプレイすんな', NULL),
(15, '射撃訓練から始めたら？', NULL),
(16, 'VALORANTやめたほうがいいよ', NULL),
(17, '敵と協力してるの？', NULL),
(18, 'フラッシュうまく使えよ', NULL),
(19, 'なんでそこでピークするの？', NULL),
(20, 'お前のせいで負ける', NULL),
(21, 'スキル使えないならアンレでやれよ', NULL),
(22, 'リコイルコントロールできないの？', NULL),
(23, 'そのエージェント使いこなせてないよね', NULL),
(24, 'エコラウンドの意味わかってる？', NULL),
(25, 'もっとコミュニケーション取れよ', NULL);

-- --------------------------------------------------------

--
-- テーブル構造 `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `selected_agent_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `session_id` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- テーブル構造 `team_compositions`
--

CREATE TABLE `team_compositions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `composition_hash` varchar(50) NOT NULL,
  `ogp_image_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- テーブル構造 `composition_agents`
--

CREATE TABLE `composition_agents` (
  `id` int(11) NOT NULL,
  `composition_id` int(11) NOT NULL,
  `agent_id` int(11) NOT NULL,
  `player_name` varchar(50) NOT NULL,
  `is_user` tinyint(1) NOT NULL DEFAULT 0,
  `is_toxic` tinyint(1) NOT NULL DEFAULT 0,
  `toxic_phrase` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- インデックスのダンプ
--

--
-- テーブルのインデックス `agents`
--
ALTER TABLE `agents`
  ADD PRIMARY KEY (`id`);

--
-- テーブルのインデックス `random_players`
--
ALTER TABLE `random_players`
  ADD PRIMARY KEY (`id`);

--
-- テーブルのインデックス `toxic_phrases`
--
ALTER TABLE `toxic_phrases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `agent_id` (`agent_id`);

--
-- テーブルのインデックス `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `selected_agent_id` (`selected_agent_id`);

--
-- テーブルのインデックス `team_compositions`
--
ALTER TABLE `team_compositions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- テーブルのインデックス `composition_agents`
--
ALTER TABLE `composition_agents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `composition_id` (`composition_id`),
  ADD KEY `agent_id` (`agent_id`);

--
-- AUTO_INCREMENT
--

--
-- テーブルの AUTO_INCREMENT `random_players`
--
ALTER TABLE `random_players`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- テーブルの AUTO_INCREMENT `toxic_phrases`
--
ALTER TABLE `toxic_phrases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- テーブルの AUTO_INCREMENT `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- テーブルの AUTO_INCREMENT `team_compositions`
--
ALTER TABLE `team_compositions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- テーブルの AUTO_INCREMENT `composition_agents`
--
ALTER TABLE `composition_agents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- ダンプしたテーブルの制約
--

--
-- テーブルの制約 `toxic_phrases`
--
ALTER TABLE `toxic_phrases`
  ADD CONSTRAINT `toxic_phrases_ibfk_1` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`) ON DELETE SET NULL;

--
-- テーブルの制約 `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`selected_agent_id`) REFERENCES `agents` (`id`);

--
-- テーブルの制約 `team_compositions`
--
ALTER TABLE `team_compositions`
  ADD CONSTRAINT `team_compositions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- テーブルの制約 `composition_agents`
--
ALTER TABLE `composition_agents`
  ADD CONSTRAINT `composition_agents_ibfk_1` FOREIGN KEY (`composition_id`) REFERENCES `team_compositions` (`id`),
  ADD CONSTRAINT `composition_agents_ibfk_2` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`); 
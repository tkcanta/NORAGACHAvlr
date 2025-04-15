<?php
// 戻るボタン設定
$show_back_button = false;

// ヘッダーを読み込み
require_once 'includes/header.php';
require_once 'includes/functions.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="card p-4 mb-4">
                <div class="card-body">
                    <h1 class="title mb-4">野良ガチャVALORANT</h1>
                    <p class="description mb-4">VALORANTでの野良チーム体験をシミュレートするサービスです。</p>
                    <p class="description mb-4">あなたのエージェントと名前を入力すると、一緒にプレイする野良の4人のエージェントと名前がランダムに決まります。</p>
                    <p class="description mb-4">60%の確率で害悪な野良プレイヤーが混じっています。</p>
                    
                    <div class="button-group">
                        <a href="select.php" class="btn btn-valora btn-lg">始める</a>
                    </div>
                </div>
            </div>
            
            <!-- リザルトIDで直接閲覧 -->
            <div class="card p-4 mt-4">
                <div class="card-body">
                    <h4 class="mb-3">既存のリザルトを見る</h4>
                    <form action="view_result.php" method="GET" class="d-flex">
                        <input type="number" name="id" class="form-control" placeholder="リザルトID" min="1" required>
                        <button type="submit" class="btn btn-secondary-valora ms-2">表示</button>
                    </form>
                    <small class="text-muted mt-2">お持ちのリザルトIDを入力して結果を表示できます</small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// フッターを読み込み
require_once 'includes/footer.php';
?> 
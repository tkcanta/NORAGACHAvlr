/**
 * 野良ガチャVALORANT
 * メインJavaScriptファイル
 */

document.addEventListener('DOMContentLoaded', function() {
    // エージェント選択の処理
    const agentItems = document.querySelectorAll('.agent-item');
    let selectedAgentId = null;
    
    agentItems.forEach(item => {
        item.addEventListener('click', function() {
            // 以前の選択を解除
            document.querySelectorAll('.agent-item.selected').forEach(selected => {
                selected.classList.remove('selected');
            });
            
            // 新しい選択を適用
            this.classList.add('selected');
            selectedAgentId = this.dataset.agentId;
            
            // 選択されたエージェントのIDを隠しフィールドに設定
            document.getElementById('selected_agent_id').value = selectedAgentId;
            
            // 送信ボタンを有効化
            const submitButton = document.getElementById('submit-button');
            if (submitButton && document.getElementById('username').value.trim() !== '') {
                submitButton.disabled = false;
            }
        });
    });
    
    // ユーザー名入力の検証
    const usernameInput = document.getElementById('username');
    if (usernameInput) {
        usernameInput.addEventListener('input', function() {
            const submitButton = document.getElementById('submit-button');
            if (submitButton) {
                // ユーザー名とエージェントが選択されている場合のみボタンを有効化
                submitButton.disabled = !(this.value.trim() !== '' && selectedAgentId !== null);
            }
        });
    }
    
    // フォーム送信前の検証
    const agentForm = document.getElementById('agent-form');
    if (agentForm) {
        agentForm.addEventListener('submit', function(e) {
            if (!selectedAgentId) {
                e.preventDefault();
                alert('エージェントを選択してください');
                return false;
            }
            
            const username = document.getElementById('username').value.trim();
            if (username === '') {
                e.preventDefault();
                alert('ユーザー名を入力してください');
                return false;
            }
            
            // 文字数制限（2～12文字）
            if (username.length < 2 || username.length > 12) {
                e.preventDefault();
                alert('ユーザー名は2～12文字で入力してください');
                return false;
            }
            
            return true;
        });
    }
    
    // SNS共有ボタンの処理
    const twitterShareButton = document.getElementById('twitter-share');
    if (twitterShareButton) {
        twitterShareButton.addEventListener('click', function(e) {
            e.preventDefault();
            
            const url = window.location.href;
            const text = 'VALORANTで野良と組んでみた結果... #野良ガチャVALORANT #VALORANT';
            
            window.open(
                `https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}&url=${encodeURIComponent(url)}`,
                'twitterwindow',
                'height=450, width=550, top=' + (window.innerHeight / 2 - 225) + ', left=' + (window.innerWidth / 2 - 275)
            );
        });
    }
    
    // 「もう一度」ボタンの処理
    const retryButton = document.getElementById('retry-button');
    if (retryButton) {
        retryButton.addEventListener('click', function() {
            window.location.href = 'select.php';
        });
    }
    
    // 結果ページでのアニメーション
    const playerCards = document.querySelectorAll('.player-card');
    if (playerCards.length > 0) {
        // 各プレイヤーカードを順番にフェードイン
        playerCards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.animation = `fadeIn 0.5s ease-in forwards ${index * 0.2}s`;
        });
    }
}); 
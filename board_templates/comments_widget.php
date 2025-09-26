<?php
// 게시글 상세 하단에 포함하는 댓글 위젯 - younglabor_posts 통합 호환
// younglabor_posts 호환성 레이어 로드
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database_helper.php';

// 테마 통합 시스템 로드
require_once __DIR__ . '/theme_integration.php';

if (session_status() === PHP_SESSION_NONE) { @session_start(); }
$csrf_token = $_SESSION['csrf_token'] ?? (function_exists('generateCSRFToken') ? generateCSRFToken() : ($_SESSION['csrf_token'] = bin2hex(random_bytes(32))));

// 데이터베이스 연결 확보
if (!isset($pdo)) {
    $pdo = getBoardDatabase();
}
if (!$pdo || !($pdo instanceof PDO)) {
    return;
}
// 액션 엔드포인트(오버라이드 가능)
// 기본값은 완전한 절대 URL(스킴+호스트+포트)로 지정하여 리버스 프록시/서브디렉토리 환경에서도 안정적으로 동작
$comment_action_url = $config['comment_action_url'] ?? '/board_templates/comment_handler.php';
if (preg_match('#^https?://#i', $comment_action_url)) {
    $comment_action_url_href = $comment_action_url;
} else {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? 'localhost');
    $comment_action_url_href = $scheme . '://' . $host . '/' . ltrim($comment_action_url, '/');
}

// post_id 결정: $post 배열 우선, 없으면 GET 파라미터 사용
$post_id = 0;
if (isset($post) && is_array($post) && isset($post['post_id'])) {
    $post_id = (int)$post['post_id'];
} elseif (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $post_id = (int)$_GET['id'];
}
if ($post_id <= 0) { return; }

// 댓글 목록 조회 (younglabor_posts 호환)
try {
    $comments = getBoardComments($post_id);
} catch (Throwable $e) {
    $comments = [];
}

// Note: Legacy GNU Board fallback removed

// 트리 구성
$byParent = [];
foreach ($comments as $c) {
    $pid = $c['parent_id'] ?? 0;
    if (!isset($byParent[$pid])) { $byParent[$pid] = []; }
    $byParent[$pid][] = $c;
}

function renderCommentsTree($parentId, $byParent, $current_user, $level = 0) {
    if (empty($byParent[$parentId])) return;
    echo '<ul class="space-y-3" style="margin-left:' . ($level*16) . 'px">';
    foreach ($byParent[$parentId] as $c) {
        $can_delete = false;
        if ($current_user) {
            if (($current_user['role'] ?? 'USER') === 'ADMIN') { $can_delete = true; }
            if ((int)($c['user_id'] ?? 0) === (int)$current_user['user_id']) { $can_delete = true; }
            if (($c['author_name'] ?? '') === ($current_user['username'] ?? '')) { $can_delete = true; }
        }
        // 깊이별 테두리 색상 적용
        $depthColors = ['#cbd5e1','#93c5fd','#86efac','#fca5a5','#fcd34d','#a5b4fc','#f9a8d4'];
        $color = $depthColors[$level % count($depthColors)];
        echo '<li class="p-3 bg-white rounded-md" style="border:1px solid ' . $color . '; border-left:4px solid ' . $color . ';">';
        echo    '<div class="text-sm text-slate-900 font-medium">' . htmlspecialchars($c['author_name']) . '</div>';
        echo    '<div class="text-sm text-slate-700 mt-1" style="white-space:pre-wrap; word-break:break-word;">' . nl2br(htmlspecialchars($c['content'])) . '</div>';
        echo    '<div class="text-xs text-slate-400 mt-1 flex items-center gap-2">' . htmlspecialchars($c['created_at']) . '';
        $authorAttr = htmlspecialchars($c['author_name'], ENT_QUOTES, 'UTF-8');
        echo        '<button type="button" class="ml-2 text-slate-500 hover:text-blue-600 underline" onclick="replyTo(' . (int)$c['comment_id'] . ', \'' . $authorAttr . '\')">답글</button>';
        if ($can_delete) {
            echo    '<button type="button" class="text-red-600 hover:opacity-80 underline" onclick="deleteComment(' . (int)$c['comment_id'] . ')">삭제</button>';
        }
        echo    '</div>';
        echo '</li>';
        renderCommentsTree((int)$c['comment_id'], $byParent, $current_user, $level+1);
    }
    echo '</ul>';
}

// 현재 사용자 정보 (두 가지 세션 구조 지원)
$current_user = null;
if (isset($_SESSION['user_id'])) {
    $current_user = [
        'user_id' => (int)$_SESSION['user_id'],
        'username' => (string)($_SESSION['username'] ?? ''),
        'role' => (string)($_SESSION['role'] ?? 'USER')
    ];
} elseif (isset($_SESSION['id'])) {
    $current_user = [
        'user_id' => (int)$_SESSION['id'],
        'username' => (string)($_SESSION['username'] ?? ''),
        'role' => (string)($_SESSION['role'] ?? 'USER')
    ];
}
?>

<?php
// Admin 테마 통합 렌더링 (comments_widget에서는 CSS만 추가, 중복 로드 방지)
if (!defined('BOARD_THEME_LOADED')) {
    if (function_exists('renderBoardTheme')) {
        renderBoardTheme();
    } else {
        // 폴백: 기본 board-theme.css 로드
        echo '<link rel="stylesheet" href="/younglabor/board_templates/assets/board-theme-enhanced.css?v=' . time() . '" />' . "\n";
    }
    define('BOARD_THEME_LOADED', true);
}
?>

<div class="board-surface mt-6">
  <h3 class="text-base font-semibold text-slate-900 mb-3" id="commentsHeading">댓글</h3>

  <!-- 댓글 작성 폼 -->
  <form id="commentForm" class="mb-4" aria-labelledby="commentsHeading" aria-describedby="commentHelp">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
    <input type="hidden" name="post_id" value="<?= (int)$post_id ?>">
    <input type="hidden" name="parent_id" id="parent_id" value="">
    <?php if (!$current_user): ?>
      <input type="text" name="author_name" class="w-full border border-slate-300 rounded-md px-3 py-2 mb-2" placeholder="작성자명" required>
    <?php endif; ?>
    <textarea name="content" class="w-full border border-slate-300 rounded-md px-3 py-2" rows="3" placeholder="댓글을 입력하세요" required></textarea>
    <div class="mt-2 flex items-center gap-2">
      <button type="submit" class="btn-primary" aria-label="댓글 등록">등록</button>
      <button type="button" class="btn-secondary" id="cancelReply" style="display:none">답글 취소</button>
    </div>
  </form>

  <!-- 댓글 목록 -->
  <div id="commentsList" class="space-y-3">
    <?php renderCommentsTree(0, $byParent, $current_user, 0); ?>
  </div>
</div>

<p id="commentHelp" class="sr-only">댓글 내용을 입력 후 등록 버튼을 누르세요.</p>

<style>
/* 테마 분리선(구분선) 컬러를 전역 테마 변수로 동기화 */
.board-surface .border-slate-200 { border-color: var(--theme-divider-color, #e2e8f0) !important; }
.board-surface .border-slate-300 { border-color: var(--theme-divider-strong, #cbd5e1) !important; }
.board-surface .divide-slate-200 > :not([hidden]) ~ :not([hidden]) { border-color: var(--theme-divider-color, #e2e8f0) !important; }
.board-surface .border-t { border-top-color: var(--theme-divider-color, #e2e8f0) !important; }
.board-surface .border-b { border-bottom-color: var(--theme-divider-color, #e2e8f0) !important; }
</style>

<script>
(function(){
  const form = document.getElementById('commentForm');
  const parentInput = document.getElementById('parent_id');
  const cancelReplyBtn = document.getElementById('cancelReply');

  window.replyTo = function(commentId, author){
    parentInput.value = String(commentId);
    cancelReplyBtn.style.display = 'inline-flex';
    try {
      const textarea = form.querySelector('textarea[name="content"]');
      if (author && textarea && textarea.value.trim() === '') {
        textarea.value = '@' + author + ' ';
        textarea.focus();
        // caret to end
        textarea.selectionStart = textarea.selectionEnd = textarea.value.length;
      }
    } catch(_) {}
  };
  cancelReplyBtn.addEventListener('click', function(){
    parentInput.value = '';
    cancelReplyBtn.style.display = 'none';
  });

  form.addEventListener('submit', function(e){
    e.preventDefault();
    const data = new FormData(form);
    data.append('action', 'create');
    fetch('<?= htmlspecialchars($comment_action_url_href) ?>', { method:'POST', credentials:'same-origin', headers:{'Accept':'application/json'}, body:data })
      .then(async r => {
        const text = await r.text();
        try { return JSON.parse(text); } catch(_) {
          const snippet = (text || '').replace(/<[^>]+>/g,' ').replace(/\s+/g,' ').trim().slice(0,200);
          throw new Error(snippet || '서버 응답 형식 오류');
        }
      })
      .then(j => {
        if (!j.success) throw new Error(j.error || '댓글 등록 실패');
        try { form.reset(); parentInput.value=''; cancelReplyBtn.style.display='none'; } catch(_) {}
        window.location.reload();
      })
      .catch(err => alert(err.message));
  });

  window.deleteComment = function(commentId){
    if (!confirm('이 댓글을 삭제하시겠습니까?')) return;
    const data = new FormData();
    data.append('action','delete');
    data.append('comment_id', String(commentId));
    data.append('csrf_token','<?= htmlspecialchars($csrf_token) ?>');
    fetch('<?= htmlspecialchars($comment_action_url_href) ?>', { method:'POST', credentials:'same-origin', headers:{'Accept':'application/json'}, body:data })
      .then(async r => {
        const text = await r.text();
        try { return JSON.parse(text); } catch(_) {
          const snippet = (text || '').replace(/<[^>]+>/g,' ').replace(/\s+/g,' ').trim().slice(0,200);
          throw new Error(snippet || '서버 응답 형식 오류');
        }
      })
      .then(j => {
        if (!j.success) throw new Error(j.error || '삭제 실패');
        window.location.reload();
      })
      .catch(err => alert(err.message));
  }
})();
</script>


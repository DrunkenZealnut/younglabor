<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin 테마 통합 시스템 데모 - 게시판</title>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <?php
    // 테마 통합 시스템 로드 및 렌더링
    require_once __DIR__ . '/theme_integration.php';
    
    // 테마 통합 렌더링
    if (function_exists('renderBoardTheme')) {
        renderBoardTheme();
    } else {
        echo '<link rel="stylesheet" href="/hopec/board_templates/assets/board-theme-enhanced.css?v=' . time() . '" />' . "\n";
    }
    
    // 현재 테마 변수들 가져오기 (디버깅용)
    if (function_exists('getBoardThemeVariables')) {
        $themeVars = getBoardThemeVariables();
    } else {
        $themeVars = [];
    }
    ?>
    
    <style>
    body {
        background-color: #f8fafc;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        padding: 2rem 1rem;
    }
    .demo-container {
        max-width: 1200px;
        margin: 0 auto;
    }
    .demo-section {
        margin-bottom: 3rem;
    }
    .demo-title {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
        color: var(--theme-text-primary, #1f3b2d);
    }
    .demo-description {
        color: var(--theme-text-secondary, #4b5563);
        margin-bottom: 2rem;
        line-height: 1.6;
    }
    .color-preview {
        width: 4rem;
        height: 4rem;
        border-radius: 0.5rem;
        border: 2px solid var(--theme-border-light);
        display: inline-block;
        margin-right: 1rem;
        margin-bottom: 0.5rem;
    }
    .variable-display {
        font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
        background-color: var(--theme-bg-secondary);
        padding: 0.5rem 1rem;
        border-radius: 0.375rem;
        border: 1px solid var(--theme-border-light);
        margin: 0.5rem 0;
        font-size: 0.875rem;
    }
    </style>
</head>
<body>
    <div class="demo-container">
        <header class="demo-section">
            <h1 class="demo-title" style="font-size: 2rem;">🎨 Admin 테마 통합 시스템 데모</h1>
            <p class="demo-description">
                관리자 설정에서 변경한 테마 색상이 게시판에 실시간으로 적용되는 것을 확인할 수 있습니다.
                <br>Admin → 사이트 설정 → 테마 관리에서 색상을 변경하면 이 페이지의 모든 요소가 자동으로 업데이트됩니다.
            </p>
        </header>

        <!-- 현재 적용된 테마 색상 미리보기 -->
        <section class="demo-section">
            <h2 class="demo-title">현재 적용된 테마 색상</h2>
            <div class="board-surface">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <?php
                    $colorVars = [
                        'Primary' => '--theme-primary',
                        'Secondary' => '--theme-secondary', 
                        'Success' => '--theme-success',
                        'Warning' => '--theme-warning',
                        'Error' => '--theme-error',
                        'Info' => '--theme-info'
                    ];
                    
                    foreach ($colorVars as $label => $varName): ?>
                    <div style="text-align: center;">
                        <div class="color-preview" style="background-color: var(<?= $varName ?>); margin: 0 auto;"></div>
                        <div style="font-weight: 600; margin-bottom: 0.25rem;"><?= $label ?></div>
                        <div class="variable-display"><?= $varName ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- 게시판 목록 데모 -->
        <section class="demo-section">
            <h2 class="demo-title">게시판 목록 스타일 데모</h2>
            <div class="board-surface bg-white rounded-lg border border-slate-200 shadow-sm max-w-4xl mx-auto">
                <!-- 게시판 헤더 -->
                <div class="px-6 py-4 border-b border-slate-200 board-header">
                    <h3 class="board-title">공지사항</h3>
                    <p class="board-description">중요한 소식과 안내사항을 확인하세요</p>
                </div>
                
                <!-- 게시판 컨트롤 -->
                <div class="px-6 py-4 bg-gray-50 border-b border-slate-200">
                    <div style="display: flex; justify-content: between; align-items: center; gap: 1rem; flex-wrap: wrap;">
                        <div style="display: flex; gap: 0.5rem;">
                            <button class="btn btn-primary">글쓰기</button>
                            <button class="btn btn-secondary">새로고침</button>
                            <button class="btn btn-outline">설정</button>
                        </div>
                        <div class="search-container" style="display: flex; gap: 0.5rem;">
                            <select style="padding: 0.5rem; border-radius: 0.375rem;">
                                <option>제목+내용</option>
                                <option>제목</option>
                                <option>작성자</option>
                            </select>
                            <input type="text" placeholder="검색어 입력" style="padding: 0.5rem; border-radius: 0.375rem; min-width: 200px;">
                            <button class="btn btn-primary">검색</button>
                        </div>
                    </div>
                </div>
                
                <!-- 게시글 목록 -->
                <div class="divide-slate-200 divide-y">
                    <div style="display: flex; align-items: center; padding: 1rem 1.5rem; background-color: var(--theme-bg-secondary);">
                        <div style="flex: 1; font-weight: 600;">제목</div>
                        <div style="width: 6rem; text-align: center;">작성자</div>
                        <div style="width: 6rem; text-align: center;">작성일</div>
                        <div style="width: 4rem; text-align: center;">조회</div>
                    </div>
                    
                    <div style="display: flex; align-items: center; padding: 1rem 1.5rem; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='var(--theme-bg-hover)'" onmouseout="this.style.backgroundColor='transparent'">
                        <div style="flex: 1;">
                            <span class="notice-badge">공지</span>
                            <a href="#" style="margin-left: 0.5rem; color: var(--theme-primary); text-decoration: none;">2024년 하반기 교육 프로그램 안내</a>
                        </div>
                        <div style="width: 6rem; text-align: center; color: var(--theme-text-secondary);">관리자</div>
                        <div style="width: 6rem; text-align: center; color: var(--theme-text-secondary);">2024-01-15</div>
                        <div style="width: 4rem; text-align: center; color: var(--theme-text-muted);">1,234</div>
                    </div>
                    
                    <div style="display: flex; align-items: center; padding: 1rem 1.5rem; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='var(--theme-bg-hover)'" onmouseout="this.style.backgroundColor='transparent'">
                        <div style="flex: 1;">
                            <span class="featured-badge">추천</span>
                            <a href="#" style="margin-left: 0.5rem; color: var(--theme-primary); text-decoration: none;">희망씨와 함께하는 든든한 일터 만들기</a>
                        </div>
                        <div style="width: 6rem; text-align: center; color: var(--theme-text-secondary);">희망씨</div>
                        <div style="width: 6rem; text-align: center; color: var(--theme-text-secondary);">2024-01-14</div>
                        <div style="width: 4rem; text-align: center; color: var(--theme-text-muted);">856</div>
                    </div>
                    
                    <div style="display: flex; align-items: center; padding: 1rem 1.5rem; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='var(--theme-bg-hover)'" onmouseout="this.style.backgroundColor='transparent'">
                        <div style="flex: 1;">
                            <a href="#" style="color: var(--theme-text-primary); text-decoration: none;">근로자 권익 보호 상담 서비스 이용 안내</a>
                        </div>
                        <div style="width: 6rem; text-align: center; color: var(--theme-text-secondary);">상담팀</div>
                        <div style="width: 6rem; text-align: center; color: var(--theme-text-secondary);">2024-01-13</div>
                        <div style="width: 4rem; text-align: center; color: var(--theme-text-muted);">542</div>
                    </div>
                </div>
                
                <!-- 페이지네이션 -->
                <div class="px-6 py-4 border-t border-slate-200">
                    <div class="pagination">
                        <a href="#" class="page-link">‹ 이전</a>
                        <a href="#" class="page-link">1</a>
                        <a href="#" class="page-link page-item active">2</a>
                        <a href="#" class="page-link">3</a>
                        <a href="#" class="page-link">4</a>
                        <a href="#" class="page-link">5</a>
                        <a href="#" class="page-link">다음 ›</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- 댓글 시스템 데모 -->
        <section class="demo-section">
            <h2 class="demo-title">댓글 시스템 데모</h2>
            <div class="board-surface max-w-4xl mx-auto">
                <h3 class="text-base font-semibold text-slate-900 mb-3">댓글 (3)</h3>
                
                <!-- 댓글 작성 폼 -->
                <form class="mb-4" style="background-color: var(--theme-bg-secondary); padding: 1rem; border-radius: var(--theme-radius-sm); border: 1px solid var(--theme-border-light);">
                    <textarea placeholder="댓글을 입력하세요..." style="width: 100%; padding: 0.75rem; border-radius: var(--theme-radius-sm); border: 1px solid var(--theme-border-medium); resize: vertical; min-height: 4rem;"></textarea>
                    <div style="display: flex; justify-content: flex-end; margin-top: 0.5rem;">
                        <button type="submit" class="btn btn-primary">댓글 등록</button>
                    </div>
                </form>
                
                <!-- 댓글 목록 -->
                <div style="space-y: 1rem;">
                    <div class="comment-item">
                        <div class="comment-author">김희망</div>
                        <div class="comment-date" style="margin-top: 0.25rem;">2024-01-15 14:30</div>
                        <div class="comment-content">정말 유익한 정보네요! 많은 분들이 도움받을 수 있을 것 같습니다. 감사합니다.</div>
                    </div>
                    
                    <div class="comment-item">
                        <div class="comment-author">이상현</div>
                        <div class="comment-date" style="margin-top: 0.25rem;">2024-01-15 15:45</div>
                        <div class="comment-content">문의사항이 있는데 어디로 연락드리면 될까요?</div>
                    </div>
                    
                    <div class="comment-item">
                        <div class="comment-author">관리자</div>
                        <div class="comment-date" style="margin-top: 0.25rem;">2024-01-15 16:20</div>
                        <div class="comment-content">@이상현 님, 대표번호 02-2236-1105로 연락주시거나 홈페이지 상담 게시판을 이용해 주세요.</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 폼 요소 데모 -->
        <section class="demo-section">
            <h2 class="demo-title">폼 요소 데모</h2>
            <div class="board-surface max-w-4xl mx-auto">
                <form style="display: grid; gap: 1.5rem;">
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--theme-text-primary);">제목 *</label>
                        <input type="text" placeholder="제목을 입력하세요" style="width: 100%;">
                    </div>
                    
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--theme-text-primary);">카테고리</label>
                        <select style="width: 100%;">
                            <option>공지사항</option>
                            <option>일반 게시글</option>
                            <option>질문/답변</option>
                        </select>
                    </div>
                    
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--theme-text-primary);">내용</label>
                        <textarea placeholder="내용을 입력하세요" style="width: 100%; min-height: 8rem;"></textarea>
                    </div>
                    
                    <div style="display: flex; justify-content: flex-end; gap: 0.5rem; padding-top: 1rem; border-top: 1px solid var(--theme-border-light);">
                        <button type="button" class="btn btn-outline">취소</button>
                        <button type="submit" class="btn btn-primary">등록</button>
                    </div>
                </form>
            </div>
        </section>

        <!-- 현재 테마 변수 정보 (개발자용) -->
        <?php if (!empty($themeVars) && isset($_GET['debug'])): ?>
        <section class="demo-section">
            <h2 class="demo-title">현재 테마 변수 (개발자 정보)</h2>
            <div class="board-surface max-w-4xl mx-auto">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem;">
                    <?php foreach ($themeVars as $variable => $value): ?>
                    <div class="variable-display">
                        <strong><?= htmlspecialchars($variable) ?>:</strong><br>
                        <span style="color: var(--theme-text-secondary);"><?= htmlspecialchars($value) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- 사용 방법 안내 -->
        <section class="demo-section">
            <h2 class="demo-title">🔧 사용 방법</h2>
            <div class="board-surface max-w-4xl mx-auto">
                <ol style="list-style: decimal; padding-left: 2rem; line-height: 1.8; color: var(--theme-text-primary);">
                    <li><strong>Admin 접속:</strong> /hopec/admin/settings/site_settings.php 페이지로 이동</li>
                    <li><strong>테마 관리:</strong> "테마 색상 설정" 섹션에서 8가지 색상 변경</li>
                    <li><strong>실시간 적용:</strong> 색상 변경 후 이 데모 페이지를 새로고침하여 변경사항 확인</li>
                    <li><strong>게시판 적용:</strong> 모든 board_templates/*.php 파일에서 자동으로 테마 적용</li>
                </ol>
                
                <div style="margin-top: 2rem; padding: 1rem; background-color: var(--theme-bg-secondary); border-radius: var(--theme-radius-sm); border-left: 4px solid var(--theme-primary);">
                    <strong style="color: var(--theme-primary);">💡 팁:</strong> 이 페이지에서 <code>?debug=1</code> 파라미터를 추가하면 현재 적용된 모든 테마 변수를 확인할 수 있습니다.
                </div>
            </div>
        </section>
    </div>
</body>
</html>
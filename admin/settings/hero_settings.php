<?php 
session_start();
require_once '../auth.php';
require_once '../bootstrap.php';

// 히어로 섹션 데이터 조회
$pdo = new PDO('mysql:host=localhost;dbname=hopec;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// 모든 히어로 섹션 조회
$stmt = $pdo->query("SELECT * FROM hopec_hero_sections ORDER BY priority ASC, id DESC");
$heroSections = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 활성 히어로 섹션 찾기
$activeHeroId = null;
foreach ($heroSections as $section) {
    if ($section['is_active']) {
        $activeHeroId = $section['id'];
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>히어로 섹션 관리 - 관리자</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/monokai.min.css">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            font-family: 'Segoe UI', sans-serif;
        }
        
        .sidebar {
            width: 220px;
            min-width: 220px;
            max-width: 220px;
            flex-shrink: 0;
            background-color: #343a40;
            color: white;
            min-height: 100vh;
            overflow-x: hidden;
        }
        
        .sidebar a {
            color: white;
            padding: 12px 16px;
            display: block;
            text-decoration: none;
            transition: background-color 0.2s;
            white-space: nowrap;
            text-overflow: ellipsis;
            overflow: hidden;
        }
        
        .sidebar a:hover {
            background-color: #495057;
        }
        
        .sidebar a.active {
            background-color: #0d6efd;
        }
        
        .main-content {
            flex-grow: 1;
            flex-basis: 0;
            padding: 30px;
            background-color: #f8f9fa;
            min-width: 0;
        }
        
        .sidebar .logo {
            font-weight: bold;
            font-size: 1.3rem;
            padding: 16px;
            border-bottom: 1px solid #495057;
            white-space: nowrap;
            text-overflow: ellipsis;
            overflow: hidden;
        }
        
        .hero-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }
        
        .hero-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .hero-card.active {
            border: 2px solid #28a745;
            background: #f0fff4;
        }
        
        .editor-container {
            display: flex;
            gap: 20px;
            height: 500px;
        }
        
        .code-editor {
            flex: 1;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .preview-container {
            flex: 1;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: white;
            position: relative;
        }
        
        .preview-iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        
        .device-selector {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 10;
        }
        
        .template-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        
        .template-card {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .template-card:hover {
            border-color: #007bff;
            transform: scale(1.05);
        }
        
        .template-card.selected {
            border-color: #28a745;
            background: #f0fff4;
        }
        
        .badge-active {
            background: #28a745;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 0.8em;
        }
        
        .CodeMirror {
            height: 100%;
        }
        
        .btn-group-action {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .status-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 8px;
        }
        
        .status-indicator.active {
            background-color: #28a745;
            animation: pulse 2s infinite;
        }
        
        .status-indicator.inactive {
            background-color: #6c757d;
        }
        
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.4);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(40, 167, 69, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
            }
        }
        
        .modal-lg {
            max-width: 90%;
        }
        
        .config-editor {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-top: 15px;
        }
        
        .config-group {
            margin-bottom: 15px;
        }
        
        .config-group label {
            font-weight: 600;
            margin-bottom: 5px;
            display: block;
        }
        
        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                min-width: 100%;
                max-width: 100%;
                min-height: auto;
                position: relative;
            }
            
            .main-content {
                flex-grow: 1;
                padding: 15px;
            }
            
            .editor-container {
                flex-direction: column;
                height: auto;
            }
            
            .code-editor,
            .preview-container {
                height: 400px;
            }
        }
    </style>
</head>
<body>
    <!-- 사이드바 -->
    <?php 
    $current_menu = 'hero';
    include '../includes/sidebar.php'; 
    ?>
    
    <!-- 메인 컨텐츠 -->
    <div class="main-content">
        <div class="container-fluid">
            <!-- 헤더 -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>히어로 섹션 관리</h1>
                <button class="btn btn-primary" onclick="openNewHeroModal()">
                    <i class="bi bi-plus-circle"></i> 새 히어로 섹션 추가
                </button>
            </div>
            
            <!-- 히어로 섹션 목록 -->
            <div class="row mb-4">
                <div class="col-12">
                    <h4>현재 히어로 섹션</h4>
                    <?php if (empty($heroSections)): ?>
                        <div class="alert alert-info">
                            등록된 히어로 섹션이 없습니다. 새 히어로 섹션을 추가해주세요.
                        </div>
                    <?php else: ?>
                        <?php foreach ($heroSections as $section): ?>
                            <div class="hero-card <?= $section['is_active'] ? 'active' : '' ?>" data-id="<?= $section['id'] ?>">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5>
                                            <span class="status-indicator <?= $section['is_active'] ? 'active' : 'inactive' ?>"></span>
                                            <?= htmlspecialchars($section['name']) ?>
                                            <?php if ($section['is_active']): ?>
                                                <span class="badge-active">활성</span>
                                            <?php endif; ?>
                                        </h5>
                                        <p class="text-muted mb-2">
                                            타입: <?= $section['type'] ?> | 
                                            생성일: <?= date('Y-m-d', strtotime($section['created_at'])) ?>
                                        </p>
                                        <?php if ($section['type'] == 'default'): ?>
                                            <p class="small">갤러리 이미지를 자동으로 슬라이드로 표시합니다.</p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="btn-group-action">
                                        <?php if (!$section['is_active']): ?>
                                            <button class="btn btn-sm btn-success" onclick="activateHero(<?= $section['id'] ?>)">
                                                활성화
                                            </button>
                                        <?php endif; ?>
                                        <button class="btn btn-sm btn-info" onclick="editHero(<?= $section['id'] ?>)">
                                            편집
                                        </button>
                                        <button class="btn btn-sm btn-warning" onclick="previewHero(<?= $section['id'] ?>)">
                                            미리보기
                                        </button>
                                        <?php if ($section['type'] != 'default'): ?>
                                            <button class="btn btn-sm btn-danger" onclick="deleteHero(<?= $section['id'] ?>)">
                                                삭제
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- 템플릿 섹션 -->
            <div class="row">
                <div class="col-12">
                    <h4>템플릿 갤러리</h4>
                    <div class="template-grid">
                        <div class="template-card" onclick="useTemplate('gradient')">
                            <i class="bi bi-palette" style="font-size: 2rem;"></i>
                            <h6>그라디언트</h6>
                            <p class="small">색상 그라디언트 배경</p>
                        </div>
                        <div class="template-card" onclick="useTemplate('video')">
                            <i class="bi bi-play-circle" style="font-size: 2rem;"></i>
                            <h6>비디오 배경</h6>
                            <p class="small">동영상 배경 히어로</p>
                        </div>
                        <div class="template-card" onclick="useTemplate('carousel')">
                            <i class="bi bi-images" style="font-size: 2rem;"></i>
                            <h6>캐러셀</h6>
                            <p class="small">이미지 캐러셀</p>
                        </div>
                        <div class="template-card" onclick="useTemplate('parallax')">
                            <i class="bi bi-layers" style="font-size: 2rem;"></i>
                            <h6>패럴랙스</h6>
                            <p class="small">패럴랙스 스크롤 효과</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 새 히어로 섹션 모달 -->
    <div class="modal fade" id="newHeroModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">새 히어로 섹션 추가</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="newHeroForm">
                        <div class="mb-3">
                            <label for="heroName" class="form-label">이름</label>
                            <input type="text" class="form-control" id="heroName" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="heroType" class="form-label">타입</label>
                            <select class="form-control" id="heroType">
                                <option value="custom">커스텀 코드</option>
                                <option value="template">템플릿</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">코드 편집기</label>
                            <div class="editor-container">
                                <div class="code-editor">
                                    <textarea id="codeEditor"></textarea>
                                </div>
                                <div class="preview-container">
                                    <div class="device-selector">
                                        <select class="form-select form-select-sm" onchange="changePreviewDevice(this.value)">
                                            <option value="desktop">데스크톱</option>
                                            <option value="tablet">태블릿</option>
                                            <option value="mobile">모바일</option>
                                        </select>
                                    </div>
                                    <iframe class="preview-iframe" id="previewFrame"></iframe>
                                </div>
                            </div>
                        </div>
                        
                        <div class="config-editor">
                            <h6>설정</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="config-group">
                                        <label>높이</label>
                                        <input type="text" class="form-control" id="configHeight" value="500px">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="config-group">
                                        <label>우선순위</label>
                                        <input type="number" class="form-control" id="configPriority" value="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                    <button type="button" class="btn btn-primary" onclick="saveNewHero()">저장</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 편집 모달 -->
    <div class="modal fade" id="editHeroModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">히어로 섹션 편집</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editHeroForm">
                        <input type="hidden" id="editHeroId">
                        <div class="mb-3">
                            <label for="editHeroName" class="form-label">이름</label>
                            <input type="text" class="form-control" id="editHeroName" required>
                        </div>
                        
                        <div class="mb-3" id="editCodeSection">
                            <label class="form-label">코드 편집기</label>
                            <div class="editor-container">
                                <div class="code-editor">
                                    <textarea id="editCodeEditor"></textarea>
                                </div>
                                <div class="preview-container">
                                    <iframe class="preview-iframe" id="editPreviewFrame"></iframe>
                                </div>
                            </div>
                        </div>
                        
                        <div class="config-editor">
                            <h6>설정</h6>
                            <div id="editConfigContainer"></div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                    <button type="button" class="btn btn-primary" onclick="updateHero()">저장</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- CodeMirror JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/xml/xml.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/javascript/javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/css/css.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/htmlmixed/htmlmixed.min.js"></script>
    
    <script>
    let codeEditor;
    let editCodeEditor;
    
    // CodeMirror 초기화
    document.addEventListener('DOMContentLoaded', function() {
        codeEditor = CodeMirror.fromTextArea(document.getElementById('codeEditor'), {
            lineNumbers: true,
            mode: 'htmlmixed',
            theme: 'monokai',
            lineWrapping: true
        });
        
        editCodeEditor = CodeMirror.fromTextArea(document.getElementById('editCodeEditor'), {
            lineNumbers: true,
            mode: 'htmlmixed',
            theme: 'monokai',
            lineWrapping: true
        });
        
        // 실시간 미리보기
        codeEditor.on('change', debounce(updatePreview, 500));
        editCodeEditor.on('change', debounce(updateEditPreview, 500));
    });
    
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    function updatePreview() {
        const code = codeEditor.getValue();
        const previewFrame = document.getElementById('previewFrame');
        const doc = previewFrame.contentDocument || previewFrame.contentWindow.document;
        doc.open();
        doc.write(code);
        doc.close();
    }
    
    function updateEditPreview() {
        const code = editCodeEditor.getValue();
        const previewFrame = document.getElementById('editPreviewFrame');
        const doc = previewFrame.contentDocument || previewFrame.contentWindow.document;
        doc.open();
        doc.write(code);
        doc.close();
    }
    
    function openNewHeroModal() {
        const modal = new bootstrap.Modal(document.getElementById('newHeroModal'));
        modal.show();
    }
    
    function changePreviewDevice(device) {
        const previewContainer = document.querySelector('.preview-container');
        switch(device) {
            case 'tablet':
                previewContainer.style.width = '768px';
                break;
            case 'mobile':
                previewContainer.style.width = '375px';
                break;
            default:
                previewContainer.style.width = '100%';
        }
    }
    
    function useTemplate(templateType) {
        fetch(`hero/templates/${templateType}.html`)
            .then(response => response.text())
            .then(code => {
                codeEditor.setValue(code);
                openNewHeroModal();
            })
            .catch(() => {
                // 템플릿이 없으면 기본 템플릿 사용
                const defaultTemplate = getDefaultTemplate(templateType);
                codeEditor.setValue(defaultTemplate);
                openNewHeroModal();
            });
    }
    
    function getDefaultTemplate(type) {
        const templates = {
            gradient: `<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 500px; display: flex; align-items: center; justify-content: center;">
    <div style="text-align: center; color: white;">
        <h1 style="font-size: 3rem; margin-bottom: 1rem;">환영합니다</h1>
        <p style="font-size: 1.5rem;">멋진 그라디언트 히어로 섹션</p>
    </div>
</div>`,
            video: `<div style="position: relative; height: 500px; overflow: hidden;">
    <video autoplay muted loop style="position: absolute; width: 100%; height: 100%; object-fit: cover;">
        <source src="/path/to/video.mp4" type="video/mp4">
    </video>
    <div style="position: relative; z-index: 1; height: 100%; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.4);">
        <div style="text-align: center; color: white;">
            <h1 style="font-size: 3rem;">비디오 배경</h1>
            <p style="font-size: 1.5rem;">동적인 비디오 히어로</p>
        </div>
    </div>
</div>`,
            carousel: `<div style="height: 500px; position: relative;">
    <!-- 간단한 캐러셀 예제 -->
    <div style="height: 100%; background: url('/path/to/image1.jpg') center/cover; display: flex; align-items: center; justify-content: center;">
        <div style="text-align: center; color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.7);">
            <h1 style="font-size: 3rem;">이미지 캐러셀</h1>
            <p style="font-size: 1.5rem;">여러 이미지를 순환합니다</p>
        </div>
    </div>
</div>`,
            parallax: `<div style="height: 500px; background: url('/path/to/image.jpg') center/cover fixed; display: flex; align-items: center; justify-content: center;">
    <div style="text-align: center; color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.7);">
        <h1 style="font-size: 3rem;">패럴랙스 효과</h1>
        <p style="font-size: 1.5rem;">스크롤시 배경이 천천히 움직입니다</p>
    </div>
</div>`
        };
        return templates[type] || templates.gradient;
    }
    
    function saveNewHero() {
        const name = document.getElementById('heroName').value;
        const type = document.getElementById('heroType').value;
        const code = codeEditor.getValue();
        const config = {
            height: document.getElementById('configHeight').value,
            priority: document.getElementById('configPriority').value
        };
        
        if (!name) {
            alert('이름을 입력해주세요.');
            return;
        }
        
        fetch('hero/api/save.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                name,
                type,
                code,
                config
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('히어로 섹션이 저장되었습니다.');
                location.reload();
            } else {
                alert('저장 중 오류가 발생했습니다: ' + data.message);
            }
        });
    }
    
    function editHero(id) {
        fetch(`hero/api/get.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('editHeroId').value = data.id;
                document.getElementById('editHeroName').value = data.name;
                
                if (data.type === 'default') {
                    document.getElementById('editCodeSection').style.display = 'none';
                    // 기본 타입은 설정만 편집
                    displayDefaultConfig(data.config);
                } else {
                    document.getElementById('editCodeSection').style.display = 'block';
                    editCodeEditor.setValue(data.code || '');
                    displayCustomConfig(data.config);
                }
                
                const modal = new bootstrap.Modal(document.getElementById('editHeroModal'));
                modal.show();
            });
    }
    
    function displayDefaultConfig(config) {
        const container = document.getElementById('editConfigContainer');
        const configObj = typeof config === 'string' ? JSON.parse(config) : config;
        
        container.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <div class="config-group">
                        <label>슬라이드 개수</label>
                        <input type="number" class="form-control" id="editSlideCount" value="${configObj.slide_count || 5}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="config-group">
                        <label>자동 재생</label>
                        <select class="form-control" id="editAutoPlay">
                            <option value="true" ${configObj.auto_play ? 'selected' : ''}>활성화</option>
                            <option value="false" ${!configObj.auto_play ? 'selected' : ''}>비활성화</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="config-group">
                        <label>자동 재생 간격 (ms)</label>
                        <input type="number" class="form-control" id="editAutoPlayInterval" value="${configObj.auto_play_interval || 6000}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="config-group">
                        <label>높이</label>
                        <input type="text" class="form-control" id="editHeight" value="${configObj.height || '500px'}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="config-group">
                        <label>인디케이터 표시</label>
                        <select class="form-control" id="editShowIndicators">
                            <option value="true" ${configObj.show_indicators ? 'selected' : ''}>표시</option>
                            <option value="false" ${!configObj.show_indicators ? 'selected' : ''}>숨김</option>
                        </select>
                    </div>
                </div>
            </div>
        `;
    }
    
    function displayCustomConfig(config) {
        const container = document.getElementById('editConfigContainer');
        const configObj = typeof config === 'string' ? JSON.parse(config) : config;
        
        container.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <div class="config-group">
                        <label>높이</label>
                        <input type="text" class="form-control" id="editHeight" value="${configObj.height || '500px'}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="config-group">
                        <label>우선순위</label>
                        <input type="number" class="form-control" id="editPriority" value="${configObj.priority || 0}">
                    </div>
                </div>
            </div>
        `;
    }
    
    function updateHero() {
        const id = document.getElementById('editHeroId').value;
        const name = document.getElementById('editHeroName').value;
        
        // 설정 수집
        let config = {};
        if (document.getElementById('editSlideCount')) {
            // 기본 타입 설정
            config = {
                slide_count: parseInt(document.getElementById('editSlideCount').value),
                auto_play: document.getElementById('editAutoPlay').value === 'true',
                auto_play_interval: parseInt(document.getElementById('editAutoPlayInterval').value),
                height: document.getElementById('editHeight').value,
                show_indicators: document.getElementById('editShowIndicators').value === 'true'
            };
        } else {
            // 커스텀 타입 설정
            config = {
                height: document.getElementById('editHeight').value,
                priority: parseInt(document.getElementById('editPriority').value)
            };
        }
        
        const code = document.getElementById('editCodeSection').style.display !== 'none' 
            ? editCodeEditor.getValue() 
            : null;
        
        fetch('hero/api/update.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id,
                name,
                code,
                config
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('히어로 섹션이 업데이트되었습니다.');
                location.reload();
            } else {
                alert('업데이트 중 오류가 발생했습니다: ' + data.message);
            }
        });
    }
    
    function activateHero(id) {
        if (confirm('이 히어로 섹션을 활성화하시겠습니까?')) {
            fetch('hero/api/activate.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('히어로 섹션이 활성화되었습니다.');
                    location.reload();
                } else {
                    alert('활성화 중 오류가 발생했습니다: ' + data.message);
                }
            });
        }
    }
    
    function deleteHero(id) {
        if (confirm('이 히어로 섹션을 삭제하시겠습니까?')) {
            fetch('hero/api/delete.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('히어로 섹션이 삭제되었습니다.');
                    location.reload();
                } else {
                    alert('삭제 중 오류가 발생했습니다: ' + data.message);
                }
            });
        }
    }
    
    function previewHero(id) {
        window.open(`hero/api/preview.php?id=${id}`, '_blank');
    }
    </script>
</body>
</html>
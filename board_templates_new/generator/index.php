<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Board Templates 자동 생성기</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4rem 0;
        }
        .feature-card {
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }
        .feature-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }
        .upload-zone {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 3rem 2rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .upload-zone:hover {
            border-color: #667eea;
            background-color: #f8f9fa;
        }
        .upload-zone.dragover {
            border-color: #667eea;
            background-color: #e3f2fd;
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-3">
                        <i class="bi bi-magic"></i>
                        Board Templates 자동 생성기
                    </h1>
                    <p class="lead mb-4">
                        SQL 스키마 파일을 업로드하면 완전히 동작하는 게시판 시스템을 자동으로 생성해드립니다.
                    </p>
                    <div class="d-flex gap-2">
                        <span class="badge bg-light text-dark px-3 py-2">
                            <i class="bi bi-check-circle"></i> 자동 테이블 분석
                        </span>
                        <span class="badge bg-light text-dark px-3 py-2">
                            <i class="bi bi-check-circle"></i> 페이지 자동 생성
                        </span>
                        <span class="badge bg-light text-dark px-3 py-2">
                            <i class="bi bi-check-circle"></i> 즉시 사용 가능
                        </span>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="text-center">
                        <i class="bi bi-database-gear" style="font-size: 8rem; opacity: 0.8;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container my-5">
        <?php
        // URL 파라미터 확인하여 테스트 모드 표시
        $debug = isset($_GET['debug']) && $_GET['debug'] === '1';
        $simple = isset($_GET['simple']) && $_GET['simple'] === '1';
        
        if ($debug || $simple):
        ?>
        <div class="alert alert-info">
            <h5><i class="bi bi-info-circle"></i> 테스트 모드</h5>
            <?php if ($debug): ?>
                <p><strong>디버그 모드</strong>: 파일 업로드만 테스트하고 실제 페이지는 생성하지 않습니다.</p>
            <?php elseif ($simple): ?>
                <p><strong>간단 모드</strong>: 기본 파일만 생성하여 빠르게 테스트할 수 있습니다.</p>
            <?php endif; ?>
            <div class="mt-2">
                <a href="?" class="btn btn-sm btn-outline-secondary">일반 모드로 전환</a>
                <a href="?debug=1" class="btn btn-sm btn-outline-info">디버그 모드</a>
                <a href="?simple=1" class="btn btn-sm btn-outline-success">간단 모드</a>
            </div>
        </div>
        <?php endif; ?>

        <!-- 업로드 섹션 -->
        <div class="row justify-content-center mb-5">
            <div class="col-lg-8">
                <div class="card feature-card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="bi bi-cloud-upload"></i>
                            1단계: SQL 스키마 파일 업로드
                        </h4>
                    </div>
                    <div class="card-body">
                        <form id="uploadForm" enctype="multipart/form-data">
                            <div class="upload-zone" id="uploadZone">
                                <i class="bi bi-file-earmark-code display-1 text-muted mb-3"></i>
                                <h5>SQL 파일을 여기에 드래그하거나 클릭하여 선택</h5>
                                <p class="text-muted mb-3">
                                    지원 형식: .sql, .txt<br>
                                    최대 크기: 10MB
                                </p>
                                <input type="file" id="sqlFile" name="sqlFile" accept=".sql,.txt" class="d-none" required>
                                <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('sqlFile').click()">
                                    <i class="bi bi-folder2-open"></i> 파일 선택
                                </button>
                            </div>
                            
                            <div id="fileInfo" class="mt-3 d-none">
                                <div class="alert alert-info">
                                    <i class="bi bi-file-earmark-check"></i>
                                    <strong>선택된 파일:</strong> <span id="fileName"></span>
                                    <span class="badge bg-secondary ms-2" id="fileSize"></span>
                                </div>
                            </div>

                            <!-- 프로젝트 설정 -->
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <label for="projectName" class="form-label">프로젝트 이름</label>
                                    <input type="text" class="form-control" id="projectName" name="projectName" 
                                           placeholder="예: 우리 회사 게시판" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="tablePrefix" class="form-label">테이블 접두사 (선택)</label>
                                    <input type="text" class="form-control" id="tablePrefix" name="tablePrefix" 
                                           placeholder="예: my_board_">
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label for="theme" class="form-label">테마 선택</label>
                                    <select class="form-select" id="theme" name="theme">
                                        <option value="bootstrap">Bootstrap 5 (기본)</option>
                                        <option value="material">Material Design</option>
                                        <option value="minimal">미니멀</option>
                                        <option value="admin">관리자용</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="language" class="form-label">언어</label>
                                    <select class="form-select" id="language" name="language">
                                        <option value="ko">한국어</option>
                                        <option value="en">English</option>
                                        <option value="ja">日本語</option>
                                    </select>
                                </div>
                            </div>

                            <hr>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-magic"></i>
                                    게시판 자동 생성하기
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- 특징 소개 -->
        <div class="row mb-5">
            <div class="col-md-4">
                <div class="card feature-card h-100">
                    <div class="card-body text-center p-4">
                        <i class="bi bi-lightning-charge display-4 text-primary mb-3"></i>
                        <h5>빠른 생성</h5>
                        <p class="text-muted">SQL 파일 업로드 후 30초 내에 완전한 게시판 시스템이 생성됩니다.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card feature-card h-100">
                    <div class="card-body text-center p-4">
                        <i class="bi bi-cpu display-4 text-success mb-3"></i>
                        <h5>스마트 분석</h5>
                        <p class="text-muted">AI 기반 스키마 분석으로 테이블 구조를 자동으로 파악하고 최적화합니다.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card feature-card h-100">
                    <div class="card-body text-center p-4">
                        <i class="bi bi-puzzle display-4 text-warning mb-3"></i>
                        <h5>완전 호환</h5>
                        <p class="text-muted">생성된 페이지는 어떤 PHP 환경에서도 바로 사용할 수 있습니다.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 샘플 다운로드 -->
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-download"></i>
                            샘플 파일
                        </h5>
                    </div>
                    <div class="card-body">
                        <p>SQL 스키마 파일이 없으신가요? 샘플 파일로 먼저 테스트해보세요.</p>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="samples/basic_board.sql" class="btn btn-outline-primary btn-sm" download>
                                <i class="bi bi-download"></i> 기본 게시판
                            </a>
                            <a href="samples/community_board.sql" class="btn btn-outline-primary btn-sm" download>
                                <i class="bi bi-download"></i> 커뮤니티 게시판
                            </a>
                            <a href="samples/notice_board.sql" class="btn btn-outline-primary btn-sm" download>
                                <i class="bi bi-download"></i> 공지사항 게시판
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 로딩 모달 -->
    <div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-5">
                    <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"></div>
                    <h5>게시판 생성 중...</h5>
                    <p class="text-muted mb-0">SQL 스키마를 분석하고 페이지를 생성하고 있습니다.</p>
                    <div class="progress mt-3">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             style="width: 0%" id="progressBar"></div>
                    </div>
                    <small class="text-muted" id="progressText">스키마 분석 중...</small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 파일 드래그 앤 드롭 핸들링
        const uploadZone = document.getElementById('uploadZone');
        const sqlFile = document.getElementById('sqlFile');
        const fileInfo = document.getElementById('fileInfo');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');

        uploadZone.addEventListener('click', () => sqlFile.click());

        uploadZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadZone.classList.add('dragover');
        });

        uploadZone.addEventListener('dragleave', () => {
            uploadZone.classList.remove('dragover');
        });

        uploadZone.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadZone.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                sqlFile.files = files;
                showFileInfo(files[0]);
            }
        });

        sqlFile.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                showFileInfo(e.target.files[0]);
            }
        });

        function showFileInfo(file) {
            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);
            fileInfo.classList.remove('d-none');
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // 폼 제출 핸들링
        document.getElementById('uploadForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
            const progressBar = document.getElementById('progressBar');
            const progressText = document.getElementById('progressText');
            
            loadingModal.show();
            
            // 진행률 시뮬레이션
            let progress = 0;
            const progressSteps = [
                { progress: 20, text: 'SQL 스키마 분석 중...' },
                { progress: 40, text: '테이블 구조 파악 중...' },
                { progress: 60, text: '페이지 템플릿 생성 중...' },
                { progress: 80, text: '코드 생성 중...' },
                { progress: 100, text: '패키지 준비 중...' }
            ];
            
            for (const step of progressSteps) {
                await new Promise(resolve => setTimeout(resolve, 800));
                progressBar.style.width = step.progress + '%';
                progressText.textContent = step.text;
            }
            
            try {
                // 모드 확인 (URL 파라미터)
                const urlParams = new URLSearchParams(window.location.search);
                let processUrl = 'process.php';
                
                if (urlParams.get('debug') === '1') {
                    processUrl = 'debug_process.php';
                } else if (urlParams.get('simple') === '1') {
                    processUrl = 'simple_process.php';
                }
                
                const response = await fetch(processUrl, {
                    method: 'POST',
                    body: formData
                });
                
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                const responseText = await response.text();
                console.log('Raw response:', responseText);
                
                let result;
                try {
                    result = JSON.parse(responseText);
                    console.log('Parsed result:', result);
                } catch (parseError) {
                    console.error('JSON parsing error:', parseError);
                    console.error('Response text was:', responseText);
                    throw new Error('서버 응답을 파싱할 수 없습니다: ' + parseError.message);
                }
                
                if (result.success) {
                    console.log('Success! Redirecting to:', 'result.php?token=' + result.token);
                    
                    // 간단 모드면 간단한 결과 페이지로, 아니면 일반 결과 페이지로
                    const resultUrl = urlParams.get('simple') === '1' 
                        ? 'simple_result.php?token=' + result.token
                        : 'result.php?token=' + result.token;
                    
                    console.log('Redirecting to:', resultUrl);
                    window.location.href = resultUrl;
                } else {
                    console.error('Server error:', result);
                    alert('오류: ' + result.message + '\n\n디버그 정보: ' + JSON.stringify(result.debug_info || {}));
                    loadingModal.hide();
                }
            } catch (error) {
                alert('처리 중 오류가 발생했습니다: ' + error.message);
                loadingModal.hide();
            }
        });
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>템플릿 설정 마법사</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #84cc16;
            --secondary-color: #16a34a;
            --success-color: #65a30d;
            --forest-700: #1f3b2d;
            --natural-50: #fafffe;
            --natural-100: #f4f8f3;
        }
        
        body {
            background: linear-gradient(135deg, var(--natural-50) 0%, var(--natural-100) 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .setup-container {
            max-width: 900px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .setup-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .setup-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .setup-body {
            padding: 2rem;
        }
        
        .step {
            display: none;
        }
        
        .step.active {
            display: block;
        }
        
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }
        
        .step-item {
            display: flex;
            align-items: center;
            margin: 0 10px;
        }
        
        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #6c757d;
            margin-right: 10px;
        }
        
        .step-number.active {
            background: var(--primary-color);
            color: white;
        }
        
        .step-number.completed {
            background: var(--success-color);
            color: white;
        }
        
        .step-line {
            width: 50px;
            height: 2px;
            background: #e9ecef;
        }
        
        .step-line.completed {
            background: var(--success-color);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--forest-700);
            margin-bottom: 0.5rem;
        }
        
        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 16px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(132, 204, 22, 0.25);
        }
        
        .btn-primary {
            background: var(--primary-color);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #6c757d;
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
        }
        
        .color-picker-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .color-preset {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .color-preset:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .color-preset.selected {
            border-color: var(--primary-color);
            background: rgba(132, 204, 22, 0.1);
        }
        
        .color-preview {
            height: 40px;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .progress {
            height: 8px;
            border-radius: 10px;
            background: #e9ecef;
            margin-bottom: 2rem;
        }
        
        .progress-bar {
            background: var(--primary-color);
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <div class="setup-card">
            <div class="setup-header">
                <h1><i class="bi bi-magic"></i> 템플릿 설정 마법사</h1>
                <p class="mb-0">새로운 프로젝트를 위한 admin과 theme 템플릿을 설정합니다</p>
            </div>
            
            <div class="setup-body">
                <!-- Progress Bar -->
                <div class="progress">
                    <div class="progress-bar" role="progressbar" style="width: 20%" id="progressBar"></div>
                </div>
                
                <!-- Step Indicator -->
                <div class="step-indicator">
                    <div class="step-item">
                        <div class="step-number active" id="step1">1</div>
                        <span>프로젝트</span>
                    </div>
                    <div class="step-line"></div>
                    <div class="step-item">
                        <div class="step-number" id="step2">2</div>
                        <span>데이터베이스</span>
                    </div>
                    <div class="step-line"></div>
                    <div class="step-item">
                        <div class="step-number" id="step3">3</div>
                        <span>관리자</span>
                    </div>
                    <div class="step-line"></div>
                    <div class="step-item">
                        <div class="step-number" id="step4">4</div>
                        <span>테마</span>
                    </div>
                    <div class="step-line"></div>
                    <div class="step-item">
                        <div class="step-number" id="step5">5</div>
                        <span>완료</span>
                    </div>
                </div>
                
                <form id="setupForm" method="POST" action="template-setup-process.php">
                    <!-- Step 1: 프로젝트 정보 -->
                    <div class="step active" id="stepContent1">
                        <h3><i class="bi bi-folder"></i> 프로젝트 정보</h3>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="app_name" class="form-label">프로젝트명 *</label>
                                    <input type="text" class="form-control" id="app_name" name="app_name" required 
                                           placeholder="My Awesome Project">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="app_url" class="form-label">프로젝트 URL *</label>
                                    <input type="url" class="form-control" id="app_url" name="app_url" required 
                                           placeholder="https://example.com">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="site_name" class="form-label">사이트명 *</label>
                            <input type="text" class="form-control" id="site_name" name="site_name" required 
                                   placeholder="내 웹사이트">
                        </div>
                        <div class="form-group">
                            <label for="site_description" class="form-label">사이트 설명</label>
                            <textarea class="form-control" id="site_description" name="site_description" rows="3"
                                      placeholder="웹사이트에 대한 간단한 설명을 입력하세요"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="app_env" class="form-label">환경 설정</label>
                            <select class="form-select" id="app_env" name="app_env">
                                <option value="local">로컬 개발</option>
                                <option value="development">개발 서버</option>
                                <option value="production">운영 서버</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Step 2: 데이터베이스 설정 -->
                    <div class="step" id="stepContent2">
                        <h3><i class="bi bi-database"></i> 데이터베이스 설정</h3>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="db_host" class="form-label">호스트 *</label>
                                    <input type="text" class="form-control" id="db_host" name="db_host" value="localhost" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="db_port" class="form-label">포트</label>
                                    <input type="number" class="form-control" id="db_port" name="db_port" value="3306">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="db_database" class="form-label">데이터베이스명 *</label>
                            <input type="text" class="form-control" id="db_database" name="db_database" required 
                                   placeholder="my_database">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="db_username" class="form-label">사용자명 *</label>
                                    <input type="text" class="form-control" id="db_username" name="db_username" value="root" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="db_password" class="form-label">비밀번호</label>
                                    <input type="password" class="form-control" id="db_password" name="db_password">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="db_prefix" class="form-label">테이블 프리픽스</label>
                            <input type="text" class="form-control" id="db_prefix" name="db_prefix" 
                                   placeholder="예: myapp_">
                            <small class="form-text text-muted">다른 시스템과의 충돌을 방지하기 위한 테이블 프리픽스입니다.</small>
                        </div>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>주의:</strong> 데이터베이스는 미리 생성되어 있어야 합니다. 설정 중에 자동으로 테이블들이 생성됩니다.
                        </div>
                    </div>
                    
                    <!-- Step 3: 관리자 계정 -->
                    <div class="step" id="stepContent3">
                        <h3><i class="bi bi-person-gear"></i> 관리자 계정 설정</h3>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="admin_username" class="form-label">관리자 아이디 *</label>
                                    <input type="text" class="form-control" id="admin_username" name="admin_username" required 
                                           placeholder="admin">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="admin_email" class="form-label">관리자 이메일 *</label>
                                    <input type="email" class="form-control" id="admin_email" name="admin_email" required 
                                           placeholder="admin@example.com">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="admin_password" class="form-label">관리자 비밀번호 *</label>
                            <input type="password" class="form-control" id="admin_password" name="admin_password" required 
                                   minlength="8" placeholder="최소 8자 이상">
                        </div>
                        <div class="form-group">
                            <label for="admin_password_confirm" class="form-label">비밀번호 확인 *</label>
                            <input type="password" class="form-control" id="admin_password_confirm" name="admin_password_confirm" required 
                                   minlength="8" placeholder="비밀번호를 다시 입력하세요">
                        </div>
                        <div class="form-group">
                            <label for="admin_name" class="form-label">관리자 이름</label>
                            <input type="text" class="form-control" id="admin_name" name="admin_name" 
                                   placeholder="홍길동">
                        </div>
                    </div>
                    
                    <!-- Step 4: 테마 설정 -->
                    <div class="step" id="stepContent4">
                        <h3><i class="bi bi-palette"></i> 테마 설정</h3>
                        <div class="form-group">
                            <label class="form-label">색상 테마 선택</label>
                            <div class="color-picker-container">
                                <div class="color-preset selected" data-theme="natural-green">
                                    <div class="color-preview" style="background: linear-gradient(135deg, #84cc16 0%, #16a34a 100%);"></div>
                                    <h6>Natural Green</h6>
                                    <small>자연친화적인 녹색 테마</small>
                                </div>
                                <div class="color-preset" data-theme="ocean-blue">
                                    <div class="color-preview" style="background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);"></div>
                                    <h6>Ocean Blue</h6>
                                    <small>시원한 바다색 테마</small>
                                </div>
                                <div class="color-preset" data-theme="sunset-orange">
                                    <div class="color-preview" style="background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);"></div>
                                    <h6>Sunset Orange</h6>
                                    <small>따뜻한 노을색 테마</small>
                                </div>
                                <div class="color-preset" data-theme="royal-purple">
                                    <div class="color-preview" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);"></div>
                                    <h6>Royal Purple</h6>
                                    <small>고급스러운 보라색 테마</small>
                                </div>
                            </div>
                            <input type="hidden" id="selected_theme" name="selected_theme" value="natural-green">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="theme_site_title" class="form-label">사이트 제목</label>
                                    <input type="text" class="form-control" id="theme_site_title" name="theme_site_title" 
                                           placeholder="우리 회사">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="theme_site_content" class="form-label">사이트 부제목</label>
                                    <input type="text" class="form-control" id="theme_site_content" name="theme_site_content" 
                                           placeholder="함께하는 미래">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Step 5: 완료 -->
                    <div class="step" id="stepContent5">
                        <div class="text-center">
                            <h3><i class="bi bi-check-circle text-success"></i> 설정 완료!</h3>
                            <p class="text-muted">모든 설정이 완료되었습니다. 아래 버튼을 클릭하여 템플릿을 설치하세요.</p>
                            
                            <div class="alert alert-info text-start">
                                <h6><i class="bi bi-info-circle"></i> 설치 내용:</h6>
                                <ul class="mb-0">
                                    <li>.env 파일 생성 및 환경 변수 설정</li>
                                    <li>admin과 theme 폴더의 설정 파일 업데이트</li>
                                    <li>데이터베이스 테이블 생성</li>
                                    <li>관리자 계정 생성</li>
                                    <li>선택한 테마 적용</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Navigation Buttons -->
                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-secondary" id="prevBtn" onclick="changeStep(-1)" style="display: none;">
                            <i class="bi bi-arrow-left"></i> 이전
                        </button>
                        <button type="button" class="btn btn-primary" id="nextBtn" onclick="changeStep(1)">
                            다음 <i class="bi bi-arrow-right"></i>
                        </button>
                        <button type="submit" class="btn btn-success" id="submitBtn" style="display: none;">
                            <i class="bi bi-download"></i> 템플릿 설치
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentStep = 1;
        const totalSteps = 5;
        
        function updateProgress() {
            const progress = (currentStep / totalSteps) * 100;
            document.getElementById('progressBar').style.width = progress + '%';
        }
        
        function updateStepIndicator() {
            for (let i = 1; i <= totalSteps; i++) {
                const stepElement = document.getElementById('step' + i);
                const stepContent = document.getElementById('stepContent' + i);
                
                stepElement.classList.remove('active', 'completed');
                stepContent.classList.remove('active');
                
                if (i < currentStep) {
                    stepElement.classList.add('completed');
                } else if (i === currentStep) {
                    stepElement.classList.add('active');
                    stepContent.classList.add('active');
                }
            }
            
            // Update step lines
            const lines = document.querySelectorAll('.step-line');
            lines.forEach((line, index) => {
                if (index < currentStep - 1) {
                    line.classList.add('completed');
                } else {
                    line.classList.remove('completed');
                }
            });
        }
        
        function updateButtons() {
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            const submitBtn = document.getElementById('submitBtn');
            
            prevBtn.style.display = currentStep > 1 ? 'block' : 'none';
            
            if (currentStep === totalSteps) {
                nextBtn.style.display = 'none';
                submitBtn.style.display = 'block';
            } else {
                nextBtn.style.display = 'block';
                submitBtn.style.display = 'none';
            }
        }
        
        function validateStep(step) {
            let isValid = true;
            const stepContent = document.getElementById('stepContent' + step);
            const requiredFields = stepContent.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                field.classList.remove('is-invalid');
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                }
            });
            
            // 비밀번호 확인 검증
            if (step === 3) {
                const password = document.getElementById('admin_password').value;
                const confirm = document.getElementById('admin_password_confirm').value;
                
                if (password !== confirm) {
                    document.getElementById('admin_password_confirm').classList.add('is-invalid');
                    isValid = false;
                }
            }
            
            return isValid;
        }
        
        function changeStep(direction) {
            if (direction === 1 && !validateStep(currentStep)) {
                return;
            }
            
            currentStep += direction;
            
            if (currentStep < 1) currentStep = 1;
            if (currentStep > totalSteps) currentStep = totalSteps;
            
            updateProgress();
            updateStepIndicator();
            updateButtons();
        }
        
        // Color theme selection
        document.querySelectorAll('.color-preset').forEach(preset => {
            preset.addEventListener('click', function() {
                document.querySelectorAll('.color-preset').forEach(p => p.classList.remove('selected'));
                this.classList.add('selected');
                document.getElementById('selected_theme').value = this.dataset.theme;
            });
        });
        
        // Auto-fill site name from project name
        document.getElementById('app_name').addEventListener('input', function() {
            const siteName = document.getElementById('site_name');
            if (!siteName.value) {
                siteName.value = this.value;
            }
        });
        
        // Auto-fill theme titles from site name
        document.getElementById('site_name').addEventListener('input', function() {
            const themeTitle = document.getElementById('theme_site_title');
            if (!themeTitle.value) {
                themeTitle.value = this.value;
            }
        });
        
        // Form submission handling
        document.getElementById('setupForm').addEventListener('submit', function(e) {
            // Show loading state
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> 설치 중...';
            submitBtn.disabled = true;
        });
        
        // Initialize
        updateProgress();
        updateStepIndicator();
        updateButtons();
    </script>
</body>
</html>
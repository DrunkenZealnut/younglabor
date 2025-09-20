<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>설정 완료</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #84cc16;
            --secondary-color: #16a34a;
            --natural-50: #fafffe;
            --natural-100: #f4f8f3;
        }
        
        body {
            background: linear-gradient(135deg, var(--natural-50) 0%, var(--natural-100) 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .success-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .success-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .success-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 3rem 2rem;
            text-align: center;
        }
        
        .success-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }
        
        .success-body {
            padding: 2rem;
        }
        
        .next-steps {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 1.5rem;
            margin: 1.5rem 0;
        }
        
        .step-item {
            display: flex;
            align-items: start;
            margin-bottom: 1rem;
            padding: 1rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .step-number {
            background: var(--primary-color);
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 1rem;
            flex-shrink: 0;
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
        
        .btn-outline-primary {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-outline-primary:hover {
            background: var(--primary-color);
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-card">
            <div class="success-header">
                <div class="success-icon">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <h1>설정이 완료되었습니다!</h1>
                <p class="mb-0">admin과 theme 템플릿이 성공적으로 설치되었습니다.</p>
            </div>
            
            <div class="success-body">
                <div class="alert alert-success">
                    <h5><i class="bi bi-check-circle"></i> 완료된 작업</h5>
                    <ul class="mb-0">
                        <li>✅ 환경 설정 파일 (.env) 생성</li>
                        <li>✅ 데이터베이스 테이블 생성</li>
                        <li>✅ 관리자 계정 생성</li>
                        <li>✅ 선택한 테마 적용</li>
                        <li>✅ 초기 사이트 설정 완료</li>
                    </ul>
                </div>
                
                <div class="next-steps">
                    <h5><i class="bi bi-list-check"></i> 다음 단계</h5>
                    
                    <div class="step-item">
                        <div class="step-number">1</div>
                        <div>
                            <h6>관리자 페이지 접속</h6>
                            <p class="mb-2">아래 버튼을 클릭하여 관리자 페이지에 로그인하세요.</p>
                            <a href="admin/login.php" class="btn btn-primary btn-sm">
                                <i class="bi bi-box-arrow-in-right"></i> 관리자 로그인
                            </a>
                        </div>
                    </div>
                    
                    <div class="step-item">
                        <div class="step-number">2</div>
                        <div>
                            <h6>사이트 설정 확인</h6>
                            <p class="mb-2">관리자 페이지에서 사이트 설정을 확인하고 필요한 부분을 수정하세요.</p>
                            <small class="text-muted">설정 → 사이트 설정 메뉴를 이용하세요.</small>
                        </div>
                    </div>
                    
                    <div class="step-item">
                        <div class="step-number">3</div>
                        <div>
                            <h6>컨텐츠 추가</h6>
                            <p class="mb-2">게시판, 메뉴, 페이지 등의 컨텐츠를 추가하여 사이트를 구성하세요.</p>
                            <small class="text-muted">게시판 관리, 메뉴 관리 메뉴를 이용하세요.</small>
                        </div>
                    </div>
                    
                    <div class="step-item">
                        <div class="step-number">4</div>
                        <div>
                            <h6>테마 커스터마이징</h6>
                            <p class="mb-2">선택한 테마의 색상과 레이아웃을 원하는 대로 조정하세요.</p>
                            <small class="text-muted">설정 → 테마 설정 메뉴를 이용하세요.</small>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <h6><i class="bi bi-info-circle"></i> 중요한 정보</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>관리자 정보:</strong><br>
                            <small class="text-muted">설정 시 입력한 계정 정보로 로그인하세요.</small>
                        </div>
                        <div class="col-md-6">
                            <strong>보안:</strong><br>
                            <small class="text-muted">이 설정 파일들을 삭제하거나 접근을 제한하세요.</small>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <a href="admin/login.php" class="btn btn-primary me-3">
                        <i class="bi bi-box-arrow-in-right"></i> 관리자 페이지로 이동
                    </a>
                    <a href="index.php" class="btn btn-outline-primary">
                        <i class="bi bi-house"></i> 메인 사이트 보기
                    </a>
                </div>
                
                <hr class="my-4">
                
                <div class="row text-center">
                    <div class="col-md-4">
                        <h6><i class="bi bi-folder"></i> 프로젝트 구조</h6>
                        <small class="text-muted">
                            admin/ - 관리자 시스템<br>
                            theme/ - 테마 파일<br>
                            .env - 환경 설정
                        </small>
                    </div>
                    <div class="col-md-4">
                        <h6><i class="bi bi-book"></i> 문서</h6>
                        <small class="text-muted">
                            <a href="admin/README.md">Admin 가이드</a><br>
                            <a href="theme/natural-green/README.md">테마 가이드</a>
                        </small>
                    </div>
                    <div class="col-md-4">
                        <h6><i class="bi bi-shield-check"></i> 보안</h6>
                        <small class="text-muted">
                            template-setup* 파일들을<br>
                            삭제하거나 접근 제한 설정
                        </small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <small class="text-muted">
                <i class="bi bi-heart-fill text-danger"></i> 
                Template Setup Wizard by HopeC Team
            </small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 페이지 로드 시 애니메이션 효과
        document.addEventListener('DOMContentLoaded', function() {
            const stepItems = document.querySelectorAll('.step-item');
            stepItems.forEach((item, index) => {
                setTimeout(() => {
                    item.style.opacity = '0';
                    item.style.transform = 'translateY(20px)';
                    item.style.transition = 'all 0.5s ease';
                    
                    setTimeout(() => {
                        item.style.opacity = '1';
                        item.style.transform = 'translateY(0)';
                    }, 100);
                }, index * 200);
            });
        });
    </script>
</body>
</html>
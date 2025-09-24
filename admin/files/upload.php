<?php
// /admin/files/upload.php - 파일 업로드 페이지
require_once '../bootstrap.php';

// 템플릿 변수 설정
$title = '파일 업로드';
$breadcrumb = [
    ['title' => '관리자', 'url' => t_url('index.php')],
    ['title' => '자료실 관리', 'url' => t_url('files/list.php')],
    ['title' => '파일 업로드', 'url' => '']
];

// 컨텐츠 시작
ob_start();
?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">파일 업로드</h5>
            </div>
            <div class="card-body">
                <form action="upload_process.php" method="POST" enctype="multipart/form-data" id="uploadForm">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label for="files" class="form-label">파일 선택</label>
                        <input type="file" class="form-control" id="files" name="files[]" multiple required>
                        <div class="form-text">
                            여러 파일을 동시에 선택할 수 있습니다. 최대 파일 크기: 5MB
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="category" class="form-label">카테고리</label>
                        <select name="category_id" id="category" class="form-select">
                            <option value="">카테고리 없음</option>
                            <!-- 카테고리는 동적으로 로드될 수 있음 -->
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">설명 (선택사항)</label>
                        <textarea name="description" id="description" class="form-control" rows="3" 
                                  placeholder="파일에 대한 간단한 설명을 입력하세요"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="is_public" name="is_public" value="1">
                            <label class="form-check-label" for="is_public">
                                공개 파일 (체크하지 않으면 관리자만 접근 가능)
                            </label>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">업로드</button>
                        <a href="list.php" class="btn btn-secondary">목록으로</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">업로드 가이드</h5>
            </div>
            <div class="card-body">
                <h6>허용되는 파일 형식:</h6>
                <ul class="list-unstyled">
                    <li><i class="bi bi-file-earmark-text"></i> 문서: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX</li>
                    <li><i class="bi bi-file-earmark-image"></i> 이미지: JPG, JPEG, PNG, GIF</li>
                    <li><i class="bi bi-file-earmark-zip"></i> 압축: ZIP, RAR</li>
                </ul>
                
                <hr>
                
                <h6>주의사항:</h6>
                <ul class="small">
                    <li>최대 파일 크기: 5MB</li>
                    <li>동시에 최대 10개 파일 업로드 가능</li>
                    <li>업로드된 파일은 자동으로 검사됩니다</li>
                    <li>문제가 있는 파일은 자동으로 거부됩니다</li>
                </ul>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">진행 상황</h5>
            </div>
            <div class="card-body">
                <div id="uploadProgress" style="display: none;">
                    <div class="progress mb-2">
                        <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                    </div>
                    <div id="uploadStatus">준비 중...</div>
                </div>
                <div id="uploadResults"></div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const progressDiv = document.getElementById('uploadProgress');
    const progressBar = progressDiv.querySelector('.progress-bar');
    const statusDiv = document.getElementById('uploadStatus');
    const resultsDiv = document.getElementById('uploadResults');
    
    progressDiv.style.display = 'block';
    statusDiv.textContent = '업로드 중...';
    
    fetch('upload_process.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        progressBar.style.width = '100%';
        statusDiv.textContent = '완료!';
        
        if (data.success) {
            resultsDiv.innerHTML = `
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i> ${data.message}
                    <br><small>업로드된 파일: ${data.uploaded_count}개</small>
                </div>
            `;
            
            // 3초 후 목록으로 이동
            setTimeout(() => {
                window.location.href = 'list.php';
            }, 3000);
        } else {
            resultsDiv.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> ${data.message}
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        statusDiv.textContent = '오류 발생';
        resultsDiv.innerHTML = `
            <div class="alert alert-danger">
                업로드 중 오류가 발생했습니다.
            </div>
        `;
    });
});

// 파일 선택 시 미리보기
document.getElementById('files').addEventListener('change', function(e) {
    const files = Array.from(e.target.files);
    const resultsDiv = document.getElementById('uploadResults');
    
    if (files.length > 0) {
        let preview = '<h6>선택된 파일:</h6><ul class="list-group list-group-flush">';
        files.forEach(file => {
            const sizeText = (file.size / 1024 / 1024).toFixed(2) + ' MB';
            preview += `<li class="list-group-item d-flex justify-content-between align-items-center">
                <span>${file.name}</span>
                <span class="badge bg-secondary">${sizeText}</span>
            </li>`;
        });
        preview += '</ul>';
        resultsDiv.innerHTML = preview;
    }
});
</script>

<?php
$content = ob_get_clean();

// 템플릿 렌더링
t_render_layout('sidebar', compact('title', 'content', 'breadcrumb'));
?>
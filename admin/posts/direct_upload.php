<?php
include '../auth.php'; // 관리자 인증 확인
require_once '../db.php'; // DB 연결
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>직접 이미지 업로드</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <style>
    #upload-area {
      border: 2px dashed #ccc;
      padding: 20px;
      text-align: center;
      margin-bottom: 20px;
      border-radius: 5px;
      background-color: #f8f9fa;
      cursor: pointer;
    }
    
    #upload-area:hover {
      border-color: #0d6efd;
      background-color: #f1f8ff;
    }
    
    #uploaded-images {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-top: 20px;
    }
    
    .image-item {
      border: 1px solid #ddd;
      padding: 5px;
      border-radius: 4px;
      position: relative;
      width: 150px;
    }
    
    .image-item img {
      width: 100%;
      height: 120px;
      object-fit: cover;
      margin-bottom: 5px;
    }
    
    .image-item .btn-group {
      display: flex;
      justify-content: space-between;
    }
    
    #debug-info {
      font-family: monospace;
      white-space: pre-wrap;
      background-color: #f5f5f5;
      padding: 10px;
      border-radius: 4px;
      font-size: 12px;
      margin-top: 10px;
      max-height: 200px;
      overflow-y: auto;
    }
  </style>
</head>
<body>
  <div class="container mt-4">
    <h3>이미지 직접 업로드</h3>
    <p class="text-muted">이미지를 업로드하고 에디터에 삽입하세요.</p>
    
    <!-- 업로드 영역 -->
    <div id="upload-area">
      <div class="mb-2">
        <i class="bi bi-cloud-arrow-up fs-1"></i>
      </div>
      <p>이미지를 끌어다 놓거나 클릭하여 선택하세요</p>
      <input type="file" id="file-input" accept="image/*" multiple style="display: none;">
      <button class="btn btn-primary btn-sm" id="select-button">파일 선택</button>
    </div>
    
    <!-- 업로드된 이미지 목록 -->
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span>업로드된 이미지</span>
        <button id="insert-all-btn" class="btn btn-success btn-sm" style="display: none;">모두 에디터에 삽입</button>
      </div>
      <div class="card-body">
        <div id="uploaded-images"></div>
        <p id="no-images-msg" class="text-center text-muted mt-3">업로드된 이미지가 없습니다.</p>
      </div>
    </div>
    
    <!-- 디버그 정보 -->
    <div class="collapse show" id="debugCollapse">
      <div class="card mt-3">
        <div class="card-header">디버그 정보</div>
        <div class="card-body">
          <div id="debug-info"></div>
        </div>
      </div>
    </div>
    
    <!-- 작업 버튼 -->
    <div class="mt-3 d-flex justify-content-between">
      <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="collapse" data-bs-target="#debugCollapse">디버그 정보 보기</button>
      <button class="btn btn-primary" id="close-btn">완료</button>
    </div>
  </div>
  
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    $(document).ready(function() {
      const uploadedImages = [];
      
      // 이미지 로드 오류 처리기
      $(document).on('error', 'img', function(e) {
        const $img = $(this);
        const src = $img.attr('src');
        $img.after(`<div class="text-danger small mt-1"><i class="bi bi-exclamation-triangle"></i> 이미지 로드 실패</div>`);
        $img.css('border', '1px solid red');
        addDebugInfo('이미지 로드 실패: ' + src);
      });
      
      // 파일 입력 필드와 업로드 영역 연결
      $('#select-button, #upload-area').on('click', function(e) {
        if (e.target !== this) return;
        $('#file-input').click();
      });
      
      // 파일 선택 이벤트
      $('#file-input').on('change', function() {
        const files = this.files;
        if (files && files.length > 0) {
          uploadFiles(files);
        }
      });
      
      // 드래그 앤 드롭 이벤트
      const uploadArea = document.getElementById('upload-area');
      
      uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.style.borderColor = '#0d6efd';
        this.style.backgroundColor = '#f1f8ff';
      });
      
      uploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.style.borderColor = '#ccc';
        this.style.backgroundColor = '#f8f9fa';
      });
      
      uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.style.borderColor = '#ccc';
        this.style.backgroundColor = '#f8f9fa';
        
        const files = e.dataTransfer.files;
        if (files && files.length > 0) {
          uploadFiles(files);
        }
      });
      
      // 파일 업로드 함수
      function uploadFiles(files) {
        for (let i = 0; i < files.length; i++) {
          const file = files[i];
          
          // 이미지 파일 확인
          if (!file.type.match('image.*')) {
            addDebugInfo('건너뜀: ' + file.name + ' (이미지 파일이 아님)');
            continue;
          }
          
          uploadSingleFile(file);
        }
      }
      
      // 단일 파일 업로드
      function uploadSingleFile(file) {
        const formData = new FormData();
        formData.append('image', file);
        
        addDebugInfo('업로드 시작: ' + file.name);
        
        $.ajax({
          url: 'upload_image.php',
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          cache: false,
          success: function(response) {
            try {
              if (typeof response === 'string') {
                response = JSON.parse(response);
              }
              
              if (response.success) {
                addDebugInfo('업로드 성공: ' + file.name);
                
                // URL 정보 표시
                addDebugInfo('기본 URL: ' + response.url);
                
                // 다양한 URL 형식 지원 (새 API)
                if (response.urls) {
                  addDebugInfo('사용할 URL: ' + response.urls.admin_relative);
                  addImageToList(response.url, file.name, response.urls.admin_relative);
                } else {
                  // 이전 버전 호환성 유지
                  let displayUrl = '../../' + response.url;
                  addDebugInfo('표시 URL(이전 방식): ' + displayUrl);
                  addImageToList(response.url, file.name, displayUrl);
                }
              } else {
                addDebugInfo('업로드 실패: ' + response.message);
                alert('이미지 업로드 실패: ' + response.message);
              }
            } catch (e) {
              addDebugInfo('응답 처리 오류: ' + e.message);
              console.error('응답 처리 중 오류:', e);
            }
          },
          error: function(xhr, status, error) {
            addDebugInfo('AJAX 오류: ' + error);
            alert('업로드 중 오류가 발생했습니다: ' + error);
          }
        });
      }
      
      // 이미지 목록에 추가
      function addImageToList(url, filename, displayUrl) {
        uploadedImages.push(url);
        
        // 이미지 미리보기 URL 처리
        // 이미지를 화면에 표시하기 위한 URL 수정
        if (!displayUrl) {
          displayUrl = url;
          if (url.startsWith('uploads/')) {
            displayUrl = '../../' + url;
          }
        }
        
        // 이미지 URL에서 파일 확장자 추출
        const fileExt = displayUrl.split('.').pop().toLowerCase();
        
        // 올바른 MIME 타입으로 표시될 수 있도록 src 형식 변경
        // 로컬 파일이 아닌 전체 URL 형식으로 처리
        let imgSrc = displayUrl;
        
        // URL이 상대 경로로 시작하는 경우, 현재 페이지 기준 절대 경로로 변환
        if (imgSrc.startsWith('../../')) {
            const baseUrl = window.location.origin + 
                (window.location.hostname === 'localhost' ? '' : '');
            imgSrc = baseUrl + '/' + imgSrc.substring(6); // '../../' 제거
        }
        
        addDebugInfo('이미지 표시 URL: ' + imgSrc);
        
        const imageItem = $('<div class="image-item"></div>');
        imageItem.html(`
          <img src="${imgSrc}" alt="${filename}" onerror="this.onerror=null; this.src=''; this.alt='이미지 로드 실패'; this.style.border='1px solid red';">
          <div class="btn-group">
            <button class="btn btn-sm btn-primary insert-btn">삽입</button>
            <button class="btn btn-sm btn-danger delete-btn">삭제</button>
          </div>
        `);
        
        $('#uploaded-images').append(imageItem);
        $('#no-images-msg').hide();
        $('#insert-all-btn').show();
        
        // 삽입 버튼 이벤트
        imageItem.find('.insert-btn').on('click', function() {
          insertImageToEditor(url);
        });
        
        // 삭제 버튼 이벤트
        imageItem.find('.delete-btn').on('click', function() {
          const index = uploadedImages.indexOf(url);
          if (index > -1) {
            uploadedImages.splice(index, 1);
          }
          imageItem.remove();
          
          if (uploadedImages.length === 0) {
            $('#no-images-msg').show();
            $('#insert-all-btn').hide();
          }
        });
      }
      
      // 모든 이미지 삽입 버튼
      $('#insert-all-btn').on('click', function() {
        uploadedImages.forEach(url => {
          insertImageToEditor(url);
        });
        window.parent.closeDirectUpload();
      });
      
      // 에디터에 이미지 삽입
      function insertImageToEditor(url) {
        // URL 경로 수정 - 상대 경로를 admin 폴더 기준 상대 경로로 수정
        // '/admin/posts/'에서 실행 중일 때 상대 경로 보정
        // 경로가 'uploads/' 형태로 시작한다면 '../../' 접두사 추가
        if (url.startsWith('uploads/')) {
          url = '../../' + url;
        }
        
        // 이미지 URL에서 중복 경로 제거
        url = url.replace('/admin/posts/', '/');
        
        // 디버깅 로그
        addDebugInfo('최종 이미지 URL: ' + url);
        
        if (window.parent && window.parent.insertDirectUploadImage) {
          window.parent.insertDirectUploadImage(url);
          addDebugInfo('에디터에 이미지 삽입: ' + url);
        } else {
          addDebugInfo('에디터 연결 실패');
        }
      }
      
      // 디버그 정보 추가
      function addDebugInfo(message) {
        const time = new Date().toLocaleTimeString();
        $('#debug-info').append(`[${time}] ${message}\n`);
        // 스크롤 맨 아래로
        const debugInfo = document.getElementById('debug-info');
        debugInfo.scrollTop = debugInfo.scrollHeight;
      }
      
      // 완료 버튼
      $('#close-btn').on('click', function() {
        if (window.parent && window.parent.closeDirectUpload) {
          window.parent.closeDirectUpload();
        } else {
          window.close();
        }
      });
    });
  </script>
</body>
</html> 
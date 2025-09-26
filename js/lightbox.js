/**
 * younglabor 통합 라이트박스 시스템
 * gallery_view.php, nepal_view.php, newsletter_view.php 공용 모듈
 * 
 * @version 1.0.0
 * @author younglabor Development Team
 */

class younglaborLightbox {
  constructor(images, options = {}) {
    this.images = images || [];
    this.currentIndex = 0;
    this.options = {
      enableKeyboard: true,
      enableNavigation: true,
      showCounter: true,
      ...options
    };
    
    this.lightbox = null;
    this.lightboxImage = null;
    this.currentImageSpan = null;
    this.totalImagesSpan = null;
    this.prevBtn = null;
    this.nextBtn = null;
    
    this.init();
  }

  init() {
    // DOM이 로드된 후 초기화
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () => this.setupLightbox());
    } else {
      this.setupLightbox();
    }
  }

  setupLightbox() {
    // DOM 요소들 찾기
    this.lightbox = document.getElementById('lightbox');
    this.lightboxImage = document.getElementById('lightbox-image');
    this.currentImageSpan = document.getElementById('current-image');
    this.totalImagesSpan = document.getElementById('total-images');
    this.prevBtn = document.getElementById('prev-btn');
    this.nextBtn = document.getElementById('next-btn');

    // 이벤트 리스너 설정
    this.setupEventListeners();
    
    // 초기 상태 설정
    if (this.totalImagesSpan) {
      this.totalImagesSpan.textContent = this.images.length;
    }
  }

  setupEventListeners() {
    // 키보드 이벤트
    if (this.options.enableKeyboard) {
      document.addEventListener('keydown', (e) => this.handleKeydown(e));
    }

    // 라이트박스 배경 클릭으로 닫기
    if (this.lightbox) {
      this.lightbox.addEventListener('click', (e) => {
        if (e.target === this.lightbox) {
          this.close();
        }
      });
    }
  }

  handleKeydown(e) {
    if (!this.isOpen()) return;
    
    switch(e.key) {
      case 'Escape':
        this.close();
        break;
      case 'ArrowLeft':
        e.preventDefault();
        this.prev();
        break;
      case 'ArrowRight':
        e.preventDefault();
        this.next();
        break;
    }
  }

  open(index = 0) {
    if (!this.images.length || !this.lightbox) return;
    
    this.currentIndex = Math.max(0, Math.min(index, this.images.length - 1));
    this.updateLightbox();
    
    // 라이트박스 표시
    this.lightbox.classList.remove('hidden');
    this.lightbox.classList.add('flex');
    
    // 스크롤 방지
    document.body.style.overflow = 'hidden';
  }

  close() {
    if (!this.lightbox) return;
    
    this.lightbox.classList.add('hidden');
    this.lightbox.classList.remove('flex');
    
    // 스크롤 복원
    document.body.style.overflow = '';
  }

  prev() {
    if (this.currentIndex > 0) {
      this.currentIndex--;
      this.updateLightbox();
    }
  }

  next() {
    if (this.currentIndex < this.images.length - 1) {
      this.currentIndex++;
      this.updateLightbox();
    }
  }

  updateLightbox() {
    if (!this.lightboxImage || !this.images.length) return;

    // 이미지 업데이트
    this.lightboxImage.src = this.images[this.currentIndex];
    this.lightboxImage.alt = `갤러리 이미지 ${this.currentIndex + 1}`;
    
    // 카운터 업데이트
    if (this.currentImageSpan) {
      this.currentImageSpan.textContent = this.currentIndex + 1;
    }
    
    // 네비게이션 버튼 상태 업데이트
    this.updateNavigationButtons();
  }

  updateNavigationButtons() {
    if (!this.options.enableNavigation || this.images.length <= 1) return;

    // 이전 버튼 상태
    if (this.prevBtn) {
      if (this.currentIndex === 0) {
        this.prevBtn.classList.add('opacity-30', 'cursor-not-allowed');
        this.prevBtn.classList.remove('hover:bg-forest-700', 'hover:scale-110');
        this.prevBtn.style.pointerEvents = 'none';
      } else {
        this.prevBtn.classList.remove('opacity-30', 'cursor-not-allowed');
        this.prevBtn.classList.add('hover:bg-forest-700', 'hover:scale-110');
        this.prevBtn.style.pointerEvents = 'auto';
      }
    }

    // 다음 버튼 상태
    if (this.nextBtn) {
      if (this.currentIndex === this.images.length - 1) {
        this.nextBtn.classList.add('opacity-30', 'cursor-not-allowed');
        this.nextBtn.classList.remove('hover:bg-forest-700', 'hover:scale-110');
        this.nextBtn.style.pointerEvents = 'none';
      } else {
        this.nextBtn.classList.remove('opacity-30', 'cursor-not-allowed');
        this.nextBtn.classList.add('hover:bg-forest-700', 'hover:scale-110');  
        this.nextBtn.style.pointerEvents = 'auto';
      }
    }
  }

  isOpen() {
    return this.lightbox && this.lightbox.classList.contains('flex');
  }

  // 이미지 배열 업데이트 (동적 갤러리용)
  updateImages(newImages) {
    this.images = newImages || [];
    if (this.totalImagesSpan) {
      this.totalImagesSpan.textContent = this.images.length;
    }
    
    // 현재 인덱스가 범위를 벗어나면 조정
    if (this.currentIndex >= this.images.length) {
      this.currentIndex = Math.max(0, this.images.length - 1);
    }
  }

  // 파괴자 (메모리 정리용)
  destroy() {
    if (this.options.enableKeyboard) {
      document.removeEventListener('keydown', this.handleKeydown);
    }
    document.body.style.overflow = '';
  }
}

// 전역 함수들 (기존 코드 호환성을 위해)
let younglaborLightbox = null;

function openLightbox(index) {
  if (younglaborLightbox) {
    younglaborLightbox.open(index);
  }
}

function closeLightbox() {
  if (younglaborLightbox) {
    younglaborLightbox.close();
  }
}

function prevImage() {
  if (younglaborLightbox) {
    younglaborLightbox.prev();
  }
}

function nextImage() {
  if (younglaborLightbox) {
    younglaborLightbox.next();
  }
}

// 전역 인스턴스 생성 함수
function inityounglaborLightbox(images, options = {}) {
  younglaborLightbox = new younglaborLightbox(images, options);
  return younglaborLightbox;
}

// AMD/CommonJS/Global 지원
if (typeof define === 'function' && define.amd) {
  define(function() { return younglaborLightbox; });
} else if (typeof module !== 'undefined' && module.exports) {
  module.exports = younglaborLightbox;
} else {
  window.younglaborLightbox = younglaborLightbox;
  window.inityounglaborLightbox = inityounglaborLightbox;
}
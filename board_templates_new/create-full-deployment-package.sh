#!/bin/bash

# =============================================================================
# Board Templates 완전 배포 패키지 생성 스크립트
# =============================================================================
# 설명: board_templates와 모든 필수 의존성 파일들을 포함한 배포 패키지 생성
# 작성자: Claude Code
# 생성일: $(date +"%Y-%m-%d")
# =============================================================================

set -e  # 에러 발생시 스크립트 중단

# 색상 정의
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 로깅 함수
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# 스크립트 시작
log_info "Board Templates 완전 배포 패키지 생성을 시작합니다..."

# 현재 디렉토리와 상위 디렉토리 확인
CURRENT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PARENT_DIR="$(dirname "$CURRENT_DIR")"
PACKAGE_NAME="board_templates_complete_$(date +%Y%m%d_%H%M%S)"
TEMP_DIR="/tmp/$PACKAGE_NAME"
FINAL_ARCHIVE="${PARENT_DIR}/${PACKAGE_NAME}.tar.gz"

log_info "현재 디렉토리: $CURRENT_DIR"
log_info "상위 디렉토리: $PARENT_DIR"
log_info "패키지명: $PACKAGE_NAME"

# 임시 디렉토리 생성 및 정리
log_info "임시 작업 디렉토리를 생성합니다..."
rm -rf "$TEMP_DIR"
mkdir -p "$TEMP_DIR"

# =============================================================================
# 1. board_templates 전체 복사 (create_board_tables.sql 제외)
# =============================================================================
log_info "board_templates 파일들을 복사합니다..."
mkdir -p "$TEMP_DIR/board_templates"

# board_templates의 모든 파일과 디렉토리 복사 (create_board_tables.sql 제외)
cd "$CURRENT_DIR"
find . -type f -name "*" ! -name "create_board_tables.sql" ! -name "create-full-deployment-package.sh" | \
while IFS= read -r file; do
    # 상대 경로에서 ./ 제거
    rel_path="${file#./}"
    target_dir="$TEMP_DIR/board_templates/$(dirname "$rel_path")"
    
    # 디렉토리 생성
    mkdir -p "$target_dir"
    
    # 파일 복사
    cp "$file" "$target_dir/"
    log_info "복사됨: $rel_path"
done

# =============================================================================
# 2. 필수 의존성 파일들 복사
# =============================================================================

# config 디렉토리 복사
log_info "config 디렉토리를 복사합니다..."
if [ -d "$PARENT_DIR/config" ]; then
    cp -r "$PARENT_DIR/config" "$TEMP_DIR/"
    log_success "config 디렉토리 복사 완료"
else
    log_warning "config 디렉토리가 존재하지 않습니다: $PARENT_DIR/config"
fi

# includes 디렉토리에서 필수 파일들 복사
log_info "includes 디렉토리에서 필수 파일들을 복사합니다..."
mkdir -p "$TEMP_DIR/includes"

REQUIRED_INCLUDES=(
    "board_module.php"
    "board_loader.php" 
    "board_init.php"
    "config.php"
    "db_connect.php"
    "db.php"
    "functions.php"
    "comment_functions.php"
)

for file in "${REQUIRED_INCLUDES[@]}"; do
    if [ -f "$PARENT_DIR/includes/$file" ]; then
        cp "$PARENT_DIR/includes/$file" "$TEMP_DIR/includes/"
        log_success "복사됨: includes/$file"
    else
        log_warning "파일이 존재하지 않습니다: includes/$file"
    fi
done

# 선택적 파일들 (존재하면 복사)
OPTIONAL_INCLUDES=(
    "visitor_logger.php"
    "header.php"
    "footer.php"
    "image_mapping.php"
)

log_info "선택적 includes 파일들을 확인합니다..."
for file in "${OPTIONAL_INCLUDES[@]}"; do
    if [ -f "$PARENT_DIR/includes/$file" ]; then
        cp "$PARENT_DIR/includes/$file" "$TEMP_DIR/includes/"
        log_info "추가됨: includes/$file"
    fi
done

# =============================================================================
# 3. 데이터베이스 스키마 파일 복사 (원본은 제외했으므로)
# =============================================================================
log_info "표준 배포 설정 파일을 복사합니다..."
if [ -f "$CURRENT_DIR/standard_deployment_setup.sql" ]; then
    cp "$CURRENT_DIR/standard_deployment_setup.sql" "$TEMP_DIR/"
    log_success "standard_deployment_setup.sql 복사 완료"
fi

# =============================================================================
# 4. 설치 스크립트 생성
# =============================================================================
log_info "자동 설치 스크립트를 생성합니다..."

cat > "$TEMP_DIR/install.sh" << 'EOF'
#!/bin/bash

# =============================================================================
# Board Templates 자동 설치 스크립트
# =============================================================================

set -e

GREEN='\033[0;32m'
BLUE='\033[0;34m' 
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

log_info() { echo -e "${BLUE}[INFO]${NC} $1"; }
log_success() { echo -e "${GREEN}[SUCCESS]${NC} $1"; }
log_warning() { echo -e "${YELLOW}[WARNING]${NC} $1"; }
log_error() { echo -e "${RED}[ERROR]${NC} $1"; }

echo "========================================"
echo "Board Templates 자동 설치를 시작합니다"
echo "========================================"

# 설치 위치 확인
INSTALL_DIR="${1:-$(pwd)/udong_board_system}"
log_info "설치 위치: $INSTALL_DIR"

# 설치 디렉토리 생성
mkdir -p "$INSTALL_DIR"
cd "$INSTALL_DIR"

# 현재 스크립트 위치
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# 디렉토리 구조 생성
log_info "디렉토리 구조를 생성합니다..."
mkdir -p {config,includes,board_templates,uploads/editor_images,uploads/board_documents}

# 파일 복사
log_info "파일들을 복사합니다..."

# config 복사
if [ -d "$SCRIPT_DIR/config" ]; then
    cp -r "$SCRIPT_DIR/config/"* config/
    log_success "config 파일들이 복사되었습니다"
fi

# includes 복사  
if [ -d "$SCRIPT_DIR/includes" ]; then
    cp -r "$SCRIPT_DIR/includes/"* includes/
    log_success "includes 파일들이 복사되었습니다"
fi

# board_templates 복사
if [ -d "$SCRIPT_DIR/board_templates" ]; then
    cp -r "$SCRIPT_DIR/board_templates/"* board_templates/
    log_success "board_templates 파일들이 복사되었습니다"
fi

# SQL 스키마 복사
if [ -f "$SCRIPT_DIR/standard_deployment_setup.sql" ]; then
    cp "$SCRIPT_DIR/standard_deployment_setup.sql" ./
    log_success "데이터베이스 스키마 파일이 복사되었습니다"
fi

# 권한 설정
log_info "파일 권한을 설정합니다..."
find . -type f -name "*.php" -exec chmod 644 {} \;
find . -type f -name "*.sh" -exec chmod 755 {} \;
chmod -R 755 uploads/

log_success "설치가 완료되었습니다!"
echo ""
echo "다음 단계:"
echo "1. 데이터베이스에서 standard_deployment_setup.sql을 실행하세요"
echo "2. config/database.php에서 데이터베이스 연결 설정을 확인하세요"  
echo "3. config/BoardConfig.php에서 기본 설정을 확인하세요"
echo "4. 웹서버에서 접근 가능한 위치에 파일들을 배치하세요"
echo ""
log_info "설치 위치: $INSTALL_DIR"
EOF

chmod +x "$TEMP_DIR/install.sh"
log_success "install.sh 스크립트가 생성되었습니다"

# =============================================================================
# 5. README 파일 생성
# =============================================================================
log_info "README 파일을 생성합니다..."

cat > "$TEMP_DIR/README.md" << EOF
# Board Templates 완전 배포 패키지

이 패키지는 우리동네노동권찾기 프로젝트의 게시판 템플릿 시스템을 완전히 배포할 수 있도록 구성되었습니다.

## 패키지 구성

\`\`\`
📦 $PACKAGE_NAME/
├── 📁 board_templates/          # 게시판 템플릿 파일들
│   ├── 📄 *.php                # PHP 템플릿 파일들
│   ├── 📁 assets/              # CSS, 이미지 등
│   ├── 📁 comments_drivers/    # 댓글 드라이버
│   └── 📄 *.md                 # 문서 파일들
├── 📁 config/                  # 설정 파일들
│   ├── 📄 BoardConfig.php      # 게시판 설정
│   ├── 📄 database.php         # DB 연결 설정
│   └── 📄 ...                  # 기타 설정들
├── 📁 includes/                # 핵심 PHP 파일들
│   ├── 📄 board_module.php     # 게시판 모듈
│   ├── 📄 functions.php        # 유틸리티 함수들
│   └── 📄 ...                  # 기타 필수 파일들
├── 📄 standard_deployment_setup.sql  # DB 스키마
├── 📄 install.sh               # 자동 설치 스크립트
└── 📄 README.md               # 이 파일
\`\`\`

## 빠른 설치

\`\`\`bash
# 1. 패키지 압축 해제
tar -xzf ${PACKAGE_NAME}.tar.gz
cd $PACKAGE_NAME

# 2. 자동 설치 실행
./install.sh [설치경로]

# 3. 데이터베이스 설정
mysql -u [사용자명] -p [데이터베이스명] < standard_deployment_setup.sql
\`\`\`

## 수동 설치

1. **파일 배치**
   - 웹 서버 문서 루트에 파일들을 복사
   - 적절한 디렉토리 구조 유지

2. **데이터베이스 설정**
   - \`standard_deployment_setup.sql\`을 데이터베이스에서 실행
   - \`config/database.php\`에서 DB 연결 정보 설정

3. **권한 설정**
   - PHP 파일: 644 권한
   - uploads 디렉토리: 755 권한 (쓰기 가능)

## 설정 파일 수정

### config/database.php
\`\`\`php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');  
define('DB_NAME', 'your_database');
\`\`\`

### config/BoardConfig.php
게시판 기본 설정을 필요에 맞게 수정하세요.

## 주요 기능

- ✅ 자유게시판 시스템
- ✅ 자료실 시스템  
- ✅ 파일 업로드 (이미지/문서)
- ✅ CAPTCHA 시스템
- ✅ 댓글 시스템
- ✅ 반응형 디자인
- ✅ 테마 시스템

## 지원

- 설치 관련 문제: INSTALLATION_CHECKLIST.md 참조
- 테마 커스터마이징: THEME_CONFIGURATION.md 참조  
- CAPTCHA 설정: CAPTCHA_README.md 참조

---
생성일: $(date)
패키지 버전: 1.0
EOF

# =============================================================================
# 6. 압축 파일 생성
# =============================================================================
log_info "배포 패키지를 압축합니다..."
cd "/tmp"
tar -czf "$FINAL_ARCHIVE" "$PACKAGE_NAME"

# 임시 디렉토리 정리
rm -rf "$TEMP_DIR"

# =============================================================================
# 결과 출력
# =============================================================================
log_success "배포 패키지가 성공적으로 생성되었습니다!"
echo ""
echo "========================================"
echo "📦 패키지 정보"
echo "========================================"
echo "📄 파일명: ${PACKAGE_NAME}.tar.gz"
echo "📍 위치: $FINAL_ARCHIVE"
echo "📊 크기: $(du -h "$FINAL_ARCHIVE" | cut -f1)"
echo ""
echo "========================================"
echo "🚀 사용 방법"
echo "========================================"
echo "1. 압축 해제:"
echo "   tar -xzf ${PACKAGE_NAME}.tar.gz"
echo ""
echo "2. 설치 실행:"
echo "   cd $PACKAGE_NAME"
echo "   ./install.sh [설치경로]"
echo ""
echo "3. 데이터베이스 설정:"
echo "   mysql -u [사용자] -p [DB명] < standard_deployment_setup.sql"
echo ""
log_success "배포 패키지 생성이 완료되었습니다! 🎉"
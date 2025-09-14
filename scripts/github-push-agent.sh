#!/bin/bash

# =============================================================================
# GitHub Push Agent
# 전체 프로젝트를 GitHub에 안전하게 push하는 자동화 스크립트
# =============================================================================

set -e  # 에러 발생시 스크립트 종료

# 색상 정의
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 로그 함수들
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

# 도움말 함수
show_help() {
    echo "GitHub Push Agent - 전체 코드를 안전하게 GitHub에 push"
    echo ""
    echo "사용법:"
    echo "  $0 [옵션]"
    echo ""
    echo "옵션:"
    echo "  -h, --help           이 도움말 표시"
    echo "  -b, --branch BRANCH  push할 브랜치 지정 (기본값: fresh-start)"
    echo "  -m, --message MSG    커밋 메시지 지정 (대화형 입력하지 않음)"
    echo "  -f, --force          강제 push (주의해서 사용)"
    echo "  -d, --dry-run        실제 push 없이 시뮬레이션만 실행"
    echo ""
    echo "예시:"
    echo "  $0                                    # 대화형 모드로 실행"
    echo "  $0 -b main -m \"프로젝트 업데이트\"        # 지정된 브랜치와 메시지로 실행"
    echo "  $0 --dry-run                          # 시뮬레이션 모드"
    echo ""
}

# 기본 설정
BRANCH="fresh-start"
COMMIT_MESSAGE=""
FORCE_PUSH=false
DRY_RUN=false
INTERACTIVE=true

# 명령행 인자 처리
while [[ $# -gt 0 ]]; do
    case $1 in
        -h|--help)
            show_help
            exit 0
            ;;
        -b|--branch)
            BRANCH="$2"
            shift 2
            ;;
        -m|--message)
            COMMIT_MESSAGE="$2"
            INTERACTIVE=false
            shift 2
            ;;
        -f|--force)
            FORCE_PUSH=true
            shift
            ;;
        -d|--dry-run)
            DRY_RUN=true
            shift
            ;;
        *)
            log_error "알 수 없는 옵션: $1"
            show_help
            exit 1
            ;;
    esac
done

# 배너 출력
echo "======================================================================="
echo "🚀 GitHub Push Agent v1.0"
echo "======================================================================="
echo ""

# Git 저장소 확인
if [ ! -d ".git" ]; then
    log_error "Git 저장소가 아닙니다. git init을 먼저 실행하세요."
    exit 1
fi

# 원격 저장소 확인
REMOTE_URL=$(git config --get remote.origin.url 2>/dev/null || echo "")
if [ -z "$REMOTE_URL" ]; then
    log_error "원격 저장소가 설정되지 않았습니다."
    echo "다음 명령으로 원격 저장소를 추가하세요:"
    echo "git remote add origin https://github.com/사용자명/저장소명.git"
    exit 1
fi

log_info "원격 저장소: $REMOTE_URL"
log_info "대상 브랜치: $BRANCH"

# 현재 상태 확인
log_info "현재 Git 상태를 확인합니다..."
git status --porcelain > /tmp/git_status.tmp

UNTRACKED_COUNT=$(grep -c "^??" /tmp/git_status.tmp 2>/dev/null || echo "0")
MODIFIED_COUNT=$(grep -c "^ M" /tmp/git_status.tmp 2>/dev/null || echo "0")
STAGED_COUNT=$(grep -c "^[MADRC]" /tmp/git_status.tmp 2>/dev/null || echo "0")

echo ""
log_info "📊 현재 상태:"
echo "  - 추적되지 않은 파일: $UNTRACKED_COUNT개"
echo "  - 수정된 파일: $MODIFIED_COUNT개" 
echo "  - 스테이징된 파일: $STAGED_COUNT개"
echo ""

# .gitignore 확인
if [ ! -f ".gitignore" ]; then
    log_warning ".gitignore 파일이 없습니다. 민감한 파일이 포함될 수 있습니다."
    if [ "$INTERACTIVE" = true ]; then
        read -p "계속하시겠습니까? (y/N): " confirm
        if [[ ! $confirm =~ ^[Yy]$ ]]; then
            log_info "작업을 취소했습니다."
            exit 0
        fi
    fi
fi

# 민감한 파일 확인
SENSITIVE_FILES=(
    ".env"
    ".env.local" 
    ".env.production"
    "config/database.php"
    "*.log"
    "backup.sql"
)

log_info "민감한 파일을 확인합니다..."
FOUND_SENSITIVE=false
for pattern in "${SENSITIVE_FILES[@]}"; do
    if ls $pattern 1> /dev/null 2>&1; then
        log_warning "민감한 파일 발견: $pattern"
        FOUND_SENSITIVE=true
    fi
done

if [ "$FOUND_SENSITIVE" = true ] && [ "$INTERACTIVE" = true ]; then
    echo ""
    log_warning "민감한 파일이 발견되었습니다. .gitignore에 추가되었는지 확인하세요."
    read -p "계속하시겠습니까? (y/N): " confirm
    if [[ ! $confirm =~ ^[Yy]$ ]]; then
        log_info "작업을 취소했습니다."
        exit 0
    fi
fi

# 대화형 커밋 메시지 입력
if [ "$INTERACTIVE" = true ] && [ -z "$COMMIT_MESSAGE" ]; then
    echo ""
    echo "📝 커밋 메시지를 입력하세요:"
    echo "   (예: 새로운 기능 추가, 버그 수정, 문서 업데이트 등)"
    echo ""
    read -p "커밋 메시지: " COMMIT_MESSAGE
    
    if [ -z "$COMMIT_MESSAGE" ]; then
        log_error "커밋 메시지가 필요합니다."
        exit 1
    fi
fi

# 커밋 메시지에 자동 서명 추가
FULL_COMMIT_MESSAGE="$COMMIT_MESSAGE

🤖 Generated with [Claude Code](https://claude.ai/code)

Co-Authored-By: Claude <noreply@anthropic.com>"

# 드라이런 모드
if [ "$DRY_RUN" = true ]; then
    log_info "🔍 드라이런 모드: 다음 작업이 실행될 예정입니다:"
    echo ""
    echo "1. git add ."
    echo "2. git commit -m \"$COMMIT_MESSAGE\""
    echo "3. git push origin $BRANCH"
    echo ""
    log_info "실제 작업을 수행하려면 --dry-run 옵션을 제거하세요."
    exit 0
fi

# 최종 확인
if [ "$INTERACTIVE" = true ]; then
    echo ""
    log_info "📋 실행할 작업 요약:"
    echo "  - 브랜치: $BRANCH"
    echo "  - 커밋 메시지: $COMMIT_MESSAGE"
    echo "  - 원격 저장소: $REMOTE_URL"
    if [ "$FORCE_PUSH" = true ]; then
        echo "  - 강제 push: 예 (⚠️  위험)"
    fi
    echo ""
    read -p "계속하시겠습니까? (y/N): " final_confirm
    if [[ ! $final_confirm =~ ^[Yy]$ ]]; then
        log_info "작업을 취소했습니다."
        exit 0
    fi
fi

echo ""
log_info "🚀 GitHub Push를 시작합니다..."
echo ""

# Step 1: 모든 파일 추가
log_info "1️⃣ 파일을 스테이징합니다..."
if git add .; then
    log_success "파일 스테이징 완료"
else
    log_error "파일 스테이징 실패"
    exit 1
fi

# Step 2: 변경사항이 있는지 확인
if git diff --staged --quiet; then
    log_warning "커밋할 변경사항이 없습니다."
    log_info "최신 상태를 원격 저장소와 동기화합니다..."
    
    # 강제 푸시가 아닌 경우에만 확인
    if [ "$FORCE_PUSH" = false ]; then
        if git push origin $BRANCH; then
            log_success "원격 저장소와 동기화 완료"
        else
            log_error "동기화 실패"
            exit 1
        fi
    fi
    exit 0
fi

# Step 3: 커밋 생성
log_info "2️⃣ 커밋을 생성합니다..."
if git commit -m "$FULL_COMMIT_MESSAGE"; then
    COMMIT_HASH=$(git rev-parse --short HEAD)
    log_success "커밋 생성 완료 (${COMMIT_HASH})"
else
    log_error "커밋 생성 실패"
    exit 1
fi

# Step 4: Push 실행
log_info "3️⃣ GitHub에 push합니다..."

PUSH_CMD="git push origin $BRANCH"
if [ "$FORCE_PUSH" = true ]; then
    PUSH_CMD="$PUSH_CMD --force"
    log_warning "강제 push를 사용합니다. 주의하세요!"
fi

if eval $PUSH_CMD; then
    log_success "Push 완료!"
    echo ""
    echo "🎉 성공적으로 GitHub에 업로드되었습니다!"
    echo ""
    echo "📍 저장소 정보:"
    echo "  - URL: $REMOTE_URL"
    echo "  - 브랜치: $BRANCH"
    echo "  - 커밋: $COMMIT_HASH"
    echo ""
    
    # GitHub URL 생성 (https://github.com/user/repo.git 형태인 경우)
    if [[ $REMOTE_URL == *"github.com"* ]]; then
        GITHUB_URL=$(echo $REMOTE_URL | sed 's/\.git$//' | sed 's/git@github\.com:/https:\/\/github\.com\//')
        echo "🔗 GitHub에서 확인: $GITHUB_URL/tree/$BRANCH"
    fi
    
else
    log_error "Push 실패"
    echo ""
    echo "🛠️  문제 해결 방법:"
    echo "1. 네트워크 연결을 확인하세요"
    echo "2. GitHub 인증 정보를 확인하세요"
    echo "3. 원격 브랜치와 충돌이 있는지 확인하세요"
    echo "4. 필요시 --force 옵션을 사용하세요 (주의!)"
    exit 1
fi

# 정리
rm -f /tmp/git_status.tmp

echo ""
log_success "GitHub Push Agent 실행 완료! ✨"
echo "======================================================================="
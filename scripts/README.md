# GitHub Push Agent 사용 가이드

전체 프로젝트를 GitHub에 안전하게 push하는 자동화 스크립트입니다.

## 🚀 기능

- **안전한 push**: 민감한 파일 자동 감지 및 경고
- **대화형 모드**: 사용자 친화적인 인터페이스
- **배치 모드**: 스크립트 자동화 지원
- **드라이런**: 실제 실행 전 시뮬레이션
- **상세한 로깅**: 각 단계별 진행 상황 표시

## 📖 사용법

### 기본 사용법 (대화형 모드)
```bash
./scripts/github-push-agent.sh
```

### 고급 사용법
```bash
# 특정 브랜치에 push
./scripts/github-push-agent.sh -b main

# 커밋 메시지 지정 (자동 모드)
./scripts/github-push-agent.sh -m "새로운 기능 추가"

# 강제 push (주의!)
./scripts/github-push-agent.sh -f

# 시뮬레이션 모드
./scripts/github-push-agent.sh --dry-run

# 모든 옵션 조합
./scripts/github-push-agent.sh -b main -m "프로젝트 완료" --dry-run
```

## 🔧 옵션

| 옵션 | 설명 | 예시 |
|------|------|------|
| `-h, --help` | 도움말 표시 | `./github-push-agent.sh -h` |
| `-b, --branch` | 대상 브랜치 지정 | `-b main` |
| `-m, --message` | 커밋 메시지 지정 | `-m "업데이트"` |
| `-f, --force` | 강제 push | `-f` |
| `-d, --dry-run` | 시뮬레이션 모드 | `--dry-run` |

## ⚡ 빠른 시작

1. **스크립트 실행 권한 확인**
   ```bash
   chmod +x scripts/github-push-agent.sh
   ```

2. **대화형 모드로 실행**
   ```bash
   ./scripts/github-push-agent.sh
   ```

3. **안내에 따라 진행**
   - 현재 상태 확인
   - 민감한 파일 점검
   - 커밋 메시지 입력
   - 최종 확인

## 🛡️ 안전 기능

### 자동 감지되는 민감한 파일
- `.env` (환경 변수)
- `.env.local`, `.env.production`
- `config/database.php` (데이터베이스 설정)
- `*.log` (로그 파일)
- `backup.sql` (백업 파일)

### 안전 점검
- Git 저장소 확인
- 원격 저장소 설정 확인
- .gitignore 존재 확인
- 민감한 파일 검사
- 사용자 최종 승인

## 📝 출력 예시

```
=======================================================================
🚀 GitHub Push Agent v1.0
=======================================================================

[INFO] 원격 저장소: https://github.com/username/younglabor.git
[INFO] 대상 브랜치: fresh-start

[INFO] 현재 Git 상태를 확인합니다...
[INFO] 📊 현재 상태:
  - 추적되지 않은 파일: 0개
  - 수정된 파일: 3개
  - 스테이징된 파일: 0개

📝 커밋 메시지를 입력하세요:
커밋 메시지: 새로운 기능 추가

[INFO] 🚀 GitHub Push를 시작합니다...

[INFO] 1️⃣ 파일을 스테이징합니다...
[SUCCESS] 파일 스테이징 완료

[INFO] 2️⃣ 커밋을 생성합니다...
[SUCCESS] 커밋 생성 완료 (a1b2c3d)

[INFO] 3️⃣ GitHub에 push합니다...
[SUCCESS] Push 완료!

🎉 성공적으로 GitHub에 업로드되었습니다!

📍 저장소 정보:
  - URL: https://github.com/username/younglabor.git
  - 브랜치: fresh-start
  - 커밋: a1b2c3d

🔗 GitHub에서 확인: https://github.com/username/younglabor/tree/fresh-start
```

## 🔄 CI/CD 통합

GitHub Actions나 다른 CI/CD 시스템에서 사용할 때:

```bash
# 자동 모드로 실행
./scripts/github-push-agent.sh -m "Automated deployment" -b main
```

## ❗ 주의사항

- `--force` 옵션은 데이터 손실 위험이 있으니 신중히 사용
- 민감한 파일은 반드시 `.gitignore`에 추가
- 대용량 파일은 Git LFS 사용 권장
- 정기적으로 백업 수행 권장

## 🐛 문제 해결

### 일반적인 오류

1. **"Git 저장소가 아닙니다"**
   ```bash
   git init
   ```

2. **"원격 저장소가 설정되지 않았습니다"**
   ```bash
   git remote add origin https://github.com/사용자명/저장소명.git
   ```

3. **Push 권한 오류**
   - GitHub 인증 정보 확인
   - Personal Access Token 설정 확인

4. **브랜치 충돌**
   ```bash
   git pull origin 브랜치명
   # 충돌 해결 후 다시 실행
   ```

## 📞 지원

문제 발생 시:
1. `--dry-run` 옵션으로 시뮬레이션 실행
2. `-h` 옵션으로 도움말 확인
3. Git 상태 점검: `git status`
4. 로그 확인 및 오류 메시지 분석
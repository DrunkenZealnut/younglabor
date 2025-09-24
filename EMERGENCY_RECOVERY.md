# 긴급 복구 시스템

## 즉시 원상복구 방법

### 방법 1: 긴급 파일 생성 (가장 빠름)
```bash
touch /Applications/XAMPP/xamppfiles/htdocs/hopec/includes/EMERGENCY_FALLBACK.txt
```

이 파일이 존재하면 무조건 기존 CDN 방식으로 복구됩니다.

### 방법 2: Git 브랜치 복구
```bash
cd /Applications/XAMPP/xamppfiles/htdocs/hopec
git checkout fresh-start
```

### 방법 3: URL 파라미터로 테스트
- 기존 방식: `http://localhost/hopec/` (기본값)
- 최적화 방식: `http://localhost/hopec/?optimized=1`
- 디버그 모드: `http://localhost/hopec/?debug=1`

## 현재 상태 확인
- 기존 CDN 모드: 우상단에 빨간색 "CDN Mode Active" 표시
- 최적화 모드: 우상단에 초록색 "Optimized CSS Active" 표시

## 문제 발생 시 체크리스트
1. [ ] EMERGENCY_FALLBACK.txt 파일 생성으로 즉시 복구
2. [ ] Git 브랜치를 fresh-start로 변경
3. [ ] 웹서버 재시작 (필요시)
4. [ ] 브라우저 캐시 클리어

## 연락처
문제 발생 시 개발팀에 즉시 연락
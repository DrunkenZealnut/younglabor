# 배포/운영 Runbook

본 문서는 hopec 사이트의 스테이징/운영 배포 절차와 모니터링, 롤백 계획을 요약합니다.

## 1) 사전 점검 (필수)
- [ ] 최근 백업 확인: `data/backup/` 최신 파일 존재
- [ ] 헬스체크 동작: `/health.php` 200 OK, DB ok:true
- [ ] 주요 페이지 상태: 홈/커뮤니티/기부/프로그램 200 OK (간이 curl 스크립트로 검증)

## 2) 스테이징 반영
- [ ] 스테이징 서버에 전체 동기화(rsync/FTP 등 조직 표준 도구 사용)
- [ ] `.htaccess` 포함 여부 확인(화이트리스트/캐시/보안헤더)
- [ ] 환경 차이 점검(php.ini, mod_rewrite 활성, DocumentRoot)

## 3) 기능 검증 (스테이징)
- [ ] 커뮤니티 목록/상세/댓글: 글 열람, 본문 이미지 레이아웃 이탈 없음
- [ ] 기부 폼: 허니팟·시간트랩·원타임 토큰·레이트리밋 정상, 유효성 메시지 한글 출력
- [ ] 접근성: 키보드 네비게이션(모바일 메뉴 포커스 트랩/ESC 닫힘) 확인
- [ ] SEO: 페이지 타이틀/디스크립션/OG/캐노니컬 확인

## 4) 모니터링 (운영 반영 후 1일)
- [ ] `/health.php` 1분 주기 핑(서버 측 크론 또는 외부 모니터링)
- [ ] 에러 로그: Apache/PHP error_log, `data/log/`(onetime) 확인
- [ ] 레이트리밋 차단 이벤트(가드/댓글) 건수 모니터링

## 5) 롤백 계획
- [ ] 문제 발생 시 즉시 직전 백업으로 복구(`data/backup/`)
- [ ] 문제 원인 파악 시 재배포 전 재현/수정 후 재검증

## 6) 유의사항
- 운영 환경에서는 `display_errors=Off` 유지(로컬/`?debug=1` 만 표시)
- 민감정보가 포함될 수 있는 로그는 마스킹(이름/전화/계좌 등)

## 부록: 간이 상태 점검 명령
```bash
# 헬스체크
curl -s http://<HOST>/health.php | jq .

# 주요 페이지 상태 (예: 로컬)
for u in \
  /hopec/ \
  /hopec/community/notices.php \
  /hopec/community/press.php \
  /hopec/community/newsletter.php \
  /hopec/community/gallery.php \
  /hopec/donate/monthly.php \
  /hopec/donate/one-time.php; do \
  curl -s -o /dev/null -w "%{http_code} %\{url_effective}\n" "http://localhost:8012$u"; done
```

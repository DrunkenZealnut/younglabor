# TODOS

## Deferred Work

### news.php: DB 기반 CMS 확장
- **What:** news.php를 하드코딩 PHP 배열에서 DB 기반 콘텐츠 관리로 전환
- **Why:** 3년 운영 중 캠페인 후기, 배경 설명, 공지사항이 쌓이면 PHP 파일 직접 편집으로는 한계
- **Pros:** 관리자 패널에서 글 작성/수정/삭제 가능. 비개발자도 콘텐츠 관리 가능
- **Cons:** DB 스키마 추가, admin 패널 확장 개발 필요
- **Context:** 디자인 문서 Approach C에서 보류. 캠페인(3월 시작) 이후 콘텐츠 양을 보고 필요성 판단. admin 패널 프레임워크가 이미 있으므로 확장은 비교적 간단
- **Depends on:** 사이트 재편 (Phase 1-5) 완료 후. 실제 콘텐츠 운영 경험 후 판단

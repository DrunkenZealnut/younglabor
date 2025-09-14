# Board Templates 설치 체크리스트

## ✅ 사전 준비

- [ ] PHP 7.4+ 설치 확인
- [ ] MySQL/MariaDB 설치 확인
- [ ] 웹서버 (Apache/Nginx) 구성
- [ ] GD Library 설치 확인
- [ ] mbstring extension 설치 확인

## ✅ 파일 설치

- [ ] `board_templates/` 폴더를 프로젝트에 복사
- [ ] `uploads/` 디렉토리 생성 및 권한 설정 (755)
- [ ] `uploads/editor_images/` 디렉토리 생성
- [ ] `uploads/board_documents/` 디렉토리 생성

## ✅ 데이터베이스 설치

- [ ] MySQL/phpMyAdmin 접속
- [ ] `standard_deployment_setup.sql` 실행
- [ ] 설치 완료 메시지 확인
- [ ] 생성된 테이블 확인 (12개 테이블)

## ✅ 설정 파일

- [ ] `includes/db_connect.php` 데이터베이스 연결 정보 설정
- [ ] `includes/config.php` 기본 설정 확인
- [ ] 베이스 URL 설정 확인

## ✅ 테스트

### 기본 기능 테스트
- [ ] 게시판 목록 표시 확인
- [ ] 글쓰기 기능 테스트
- [ ] 파일 업로드 테스트
- [ ] CAPTCHA 표시 확인 (공개 게시판)
- [ ] 댓글 작성 테스트

### 관리자 기능 테스트  
- [ ] 관리자 로그인 (admin/admin123)
- [ ] 관리자 CAPTCHA 면제 확인
- [ ] 게시글 관리 기능 확인

### 권한별 테스트
- [ ] 공개 게시판 (`write_level=0`): CAPTCHA 표시
- [ ] 회원 게시판 (`write_level=1`): CAPTCHA 숨김  
- [ ] 관리자 페이지: CAPTCHA 면제

## ✅ 보안 설정

- [ ] **관리자 비밀번호 변경** (⚠️ 중요!)
- [ ] 데이터베이스 비밀번호 강화
- [ ] 업로드 디렉토리 보안 설정
- [ ] .htaccess 보안 설정 (선택사항)

## ✅ 성능 최적화

- [ ] 이미지 최적화 설정
- [ ] 캐싱 설정 (선택사항)
- [ ] 데이터베이스 인덱스 확인
- [ ] 로그 파일 관리 설정

## ✅ 최종 확인

- [ ] 모든 게시판 유형 테스트 완료
- [ ] 파일 업로드/다운로드 정상 동작
- [ ] 모바일 반응형 확인
- [ ] 에러 로그 확인
- [ ] 백업 계획 수립

---

## 🚨 필수 체크 항목

아래 항목들은 **반드시** 완료해야 합니다:

1. **관리자 비밀번호 변경**: 기본 비밀번호(`admin123`)는 보안상 매우 위험합니다.
2. **업로드 디렉토리 권한**: 파일 업로드가 작동하지 않는 주된 원인입니다.
3. **데이터베이스 인코딩**: UTF-8 설정이 없으면 한글이 깨집니다.
4. **CAPTCHA 테스트**: 권한별 CAPTCHA 표시가 올바르게 작동하는지 확인하세요.

## 📞 문제 발생 시

각 항목을 순서대로 확인하고, 문제가 발생하면 `DEPLOYMENT_README.md`의 문제 해결 섹션을 참조하세요.

**설치 완료 확인 쿼리**:
```sql
SELECT 
    'Board Templates 설치 상태' as status,
    (SELECT COUNT(*) FROM labor_rights_boards) as boards,
    (SELECT COUNT(*) FROM board_categories) as categories,
    (SELECT COUNT(*) FROM labor_rights_admin_user) as admins;
```

결과가 `boards: 4, categories: 4, admins: 1`이면 정상 설치된 것입니다.
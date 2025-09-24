# wr_is_notice 마이그레이션 가이드

## 개요
hopec_posts 테이블의 wr_1 컬럼을 wr_is_notice로 변경하여 공지사항 기능을 전용 컬럼으로 구현하는 마이그레이션입니다.

## 변경 사항 요약

### 1. 데이터베이스 구조 변경
- **변경 전**: wr_option SET 필드의 'notice' 값으로 공지사항 구분
- **변경 후**: wr_is_notice TINYINT(1) 전용 컬럼으로 공지사항 구분 (0: 일반글, 1: 공지사항)

### 2. 코드 변경 사항

#### admin/posts/list.php
```sql
-- 변경 전
CASE WHEN FIND_IN_SET('notice', wr_option) > 0 THEN 1 ELSE 0 END as is_notice
ORDER BY CASE WHEN FIND_IN_SET('notice', wr_option) > 0 THEN 0 ELSE 1 END, wr_datetime DESC, wr_id DESC

-- 변경 후  
wr_is_notice as is_notice
ORDER BY wr_is_notice DESC, wr_datetime DESC, wr_id DESC
```

#### admin/posts/write.php
```php
// 변경 전
$valid_options = ['html1', 'html2', 'secret', 'mail', 'notice'];

// 변경 후
$is_notice = in_array('notice', $options) ? 1 : 0;
$valid_options = ['html1', 'html2', 'secret', 'mail']; // notice 제외

// INSERT 문에 wr_is_notice 필드 추가
```

## 마이그레이션 실행 단계

### 1단계: 데이터베이스 백업
```bash
mysqldump -u root hopec > hopec_backup_before_notice_migration.sql
```

### 2단계: 마이그레이션 실행
```bash
mysql -u root hopec < /Applications/XAMPP/xamppfiles/htdocs/hopec/admin/migrate_wr_1_to_wr_is_notice.sql
```

### 3단계: 마이그레이션 검증

#### phpMyAdmin 또는 MySQL 콘솔에서 확인:
```sql
-- 테이블 구조 확인
DESCRIBE hopec_posts;

-- 공지사항 데이터 확인
SELECT wr_id, wr_subject, wr_is_notice, wr_option 
FROM hopec_posts 
WHERE wr_is_notice = 1 
ORDER BY wr_datetime DESC;

-- 마이그레이션 정확성 검증
SELECT 
    COUNT(*) as total_posts,
    COUNT(CASE WHEN wr_is_notice = 1 THEN 1 END) as notice_posts,
    COUNT(CASE WHEN wr_is_notice = 0 THEN 1 END) as normal_posts
FROM hopec_posts;
```

### 4단계: 웹 인터페이스 테스트

#### 게시글 목록 확인
1. `http://localhost/hopec/admin/posts/list.php` 접속
2. 공지사항이 상단에 고정 표시되는지 확인
3. "공지" 배지가 정상 표시되는지 확인

#### 게시글 작성 테스트
1. `http://localhost/hopec/admin/posts/write.php` 접속
2. 공지사항 체크박스 선택하여 게시글 작성
3. 작성된 게시글이 목록 상단에 표시되는지 확인

## 성능 개선 사항

### 이전 방식의 문제점
- `FIND_IN_SET('notice', wr_option)` 함수 사용으로 인한 성능 저하
- SET 타입 필드의 복잡성
- 인덱스 활용 불가

### 개선된 방식의 장점
- 단순한 TINYINT 비교로 성능 향상
- `wr_is_notice` 컬럼 인덱스로 빠른 조회
- 명확한 데이터 구조

## 롤백 절차

문제 발생 시 아래 스크립트로 롤백 가능:

```sql
-- 백업에서 복원
DROP TABLE hopec_posts;
CREATE TABLE hopec_posts AS SELECT * FROM hopec_posts_backup_before_notice_migration;

-- 백업 테이블 정리
DROP TABLE hopec_posts_backup_before_notice_migration;
```

## 추가 최적화 권장사항

### 1. wr_option에서 notice 완전 제거 (선택사항)
마이그레이션이 완전히 안정화된 후 실행:
```sql
UPDATE hopec_posts 
SET wr_option = TRIM(BOTH ',' FROM REPLACE(CONCAT(',', wr_option, ','), ',notice,', ','))
WHERE FIND_IN_SET('notice', wr_option) > 0;
```

### 2. 인덱스 최적화 확인
```sql
-- 추가된 인덱스 확인
SHOW INDEX FROM hopec_posts WHERE Column_name IN ('wr_is_notice');

-- 쿼리 실행 계획 확인
EXPLAIN SELECT * FROM hopec_posts WHERE wr_is_notice = 1 ORDER BY wr_datetime DESC;
```

## 예상 효과

### 성능 개선
- 공지사항 조회 성능 **약 3-5배 향상**
- 게시글 목록 정렬 성능 개선
- 메모리 사용량 감소

### 코드 가독성
- 복잡한 `FIND_IN_SET()` 함수 제거
- 직관적인 boolean 필드 사용
- 유지보수성 향상

### 확장성
- 향후 공지사항 관련 기능 추가 용이
- 다른 필드와의 조합 쿼리 최적화
- 데이터베이스 정규화 개선

---

**마이그레이션 완료 후 이 파일은 보관용으로 유지하시기 바랍니다.**
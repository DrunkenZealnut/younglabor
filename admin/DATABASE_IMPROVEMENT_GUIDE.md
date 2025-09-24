# 📊 hopec_posts 테이블 구조 개선 가이드

관리자 게시글 작성 시스템 개선을 위한 데이터베이스 테이블 구조 개선 가이드입니다.

## 🎯 개선 목표

참조 텍스트의 게시글 작성 폼 양식을 기반으로 `hopec_posts` 테이블에 누락된 필드들을 추가하여 완전한 게시글 관리 시스템을 구현합니다.

## 📋 추가될 주요 필드들

### 👤 작성자 정보 필드
- `wr_email` - 이메일 주소 (선택사항)
- `wr_homepage` - 홈페이지 URL (선택사항)  
- `wr_password` - 게시글 비밀번호 (비회원 작성시)
- `mb_id` - 회원 ID (로그인 시 자동 설정)

### 🔗 링크 필드
- `wr_link1` - 링크 1 (1000자 제한)
- `wr_link2` - 링크 2 (1000자 제한)
- `wr_link1_hit`, `wr_link2_hit` - 링크 클릭 수

### ⚙️ 옵션 필드
- `wr_option` - 게시글 옵션 (HTML사용, 비밀글, 메일수신, 공지사항)
- `ca_name` - 카테고리/분류

### 🔧 확장 필드
- `wr_1` ~ `wr_10` - 게시판별 커스텀 필드

## 🚀 실행 단계

### 1단계: 사전 준비

#### 1.1 데이터베이스 백업
```bash
# MySQL 덤프를 통한 백업
mysqldump -u root -p hopec > hopec_backup_$(date +%Y%m%d_%H%M%S).sql

# 또는 phpMyAdmin에서 내보내기 기능 사용
```

#### 1.2 현재 테이블 구조 확인
웹 브라우저에서 다음 URL 접속:
```
http://localhost/hopec/admin/database_structure_check.php
```

### 2단계: 테이블 구조 개선

#### 2.1 MySQL 명령어로 실행
```bash
mysql -u root -p hopec < admin/improve_hopec_posts_table.sql
```

#### 2.2 phpMyAdmin에서 실행
1. phpMyAdmin 접속
2. `hopec` 데이터베이스 선택
3. `SQL` 탭 클릭
4. `improve_hopec_posts_table.sql` 파일 내용 복사하여 실행

#### 2.3 단계별 실행 (권장)
안전을 위해 다음 섹션별로 나누어 실행:

1. **컬럼 추가** (작성자 정보 필드들)
```sql
-- 작성자 정보 컬럼들
ALTER TABLE hopec_posts ADD COLUMN IF NOT EXISTS wr_email VARCHAR(255) DEFAULT NULL COMMENT '이메일';
ALTER TABLE hopec_posts ADD COLUMN IF NOT EXISTS wr_homepage VARCHAR(255) DEFAULT NULL COMMENT '홈페이지 URL';
-- ... (나머지 컬럼들)
```

2. **인덱스 추가** (성능 최적화)
```sql
CREATE INDEX IF NOT EXISTS idx_board_type ON hopec_posts (board_type);
CREATE INDEX IF NOT EXISTS idx_wr_datetime ON hopec_posts (wr_datetime);
-- ... (나머지 인덱스들)
```

3. **기본값 업데이트** (기존 데이터 정리)
```sql
UPDATE hopec_posts SET wr_num = COALESCE(wr_num, 0) WHERE wr_id IS NOT NULL;
-- ... (나머지 업데이트들)
```

### 3단계: 검증

#### 3.1 테이블 구조 확인
```sql
DESCRIBE hopec_posts;
SHOW CREATE TABLE hopec_posts;
```

#### 3.2 데이터 무결성 확인
```sql
-- 전체 레코드 수 확인
SELECT COUNT(*) as total_posts FROM hopec_posts;

-- 게시판별 게시글 수 확인
SELECT board_type, COUNT(*) as post_count 
FROM hopec_posts 
WHERE wr_is_comment = 0 
GROUP BY board_type;
```

#### 3.3 웹에서 재확인
개선 후 다시 구조 확인 페이지 접속:
```
http://localhost/hopec/admin/database_structure_check.php
```

## ⚠️ 주의사항

### 실행 전 체크리스트
- [ ] 데이터베이스 백업 완료
- [ ] 운영 중인 사이트의 경우 점검 시간 확보
- [ ] MySQL/MariaDB 버전 호환성 확인
- [ ] 충분한 디스크 공간 확보

### 문제 발생 시 대응
1. **스크립트 실행 중 오류 발생**
   - 즉시 실행 중단
   - 백업 파일로 복원
   - 오류 메시지 확인 후 문제 해결

2. **기존 데이터 손실 우려**
   - 백업 파일 확인
   - 테스트 환경에서 먼저 실행
   - 단계별 검증 후 진행

## 📈 성능 최적화

### 추가된 인덱스들
- `idx_board_type` - 게시판별 조회 최적화
- `idx_wr_datetime` - 날짜별 정렬 최적화  
- `idx_wr_is_comment` - 게시글/댓글 구분 최적화
- `idx_ca_name` - 카테고리별 조회 최적화
- `idx_mb_id` - 회원별 게시글 조회 최적화

### 복합 인덱스들
- `idx_board_type_datetime` - 게시판별 날짜순 조회
- `idx_board_type_is_comment` - 게시판별 게시글/댓글 구분
- `idx_parent_is_comment` - 부모글-댓글 관계 조회

## 🔄 다음 단계

데이터베이스 구조 개선 완료 후:

1. **관리자 게시글 작성 폼 업데이트** (`admin/posts/write.php`)
2. **백엔드 처리 로직 개선** (새 필드들 처리)
3. **게시글 목록/상세 페이지 업데이트**
4. **테스트 및 검증**

## 📞 지원

문제 발생 시:
1. 백업 파일로 즉시 복원
2. 오류 로그 확인
3. 개발팀 문의

---

> ⚡ **성능 팁**: 대용량 데이터가 있는 경우 인덱스 생성에 시간이 소요될 수 있습니다. 점검 시간을 충분히 확보하세요.
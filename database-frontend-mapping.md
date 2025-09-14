# 희망씨 웹사이트 - 데이터베이스 테이블과 프론트엔드 제목 매핑

## 개요
이 문서는 희망씨 웹사이트에서 실제로 데이터베이스에서 데이터를 불러와 프론트엔드에 표시하는 테이블들과 해당 제목을 정리한 것입니다.

## 게시판 테이블 (Board Tables)

### 커뮤니티 섹션
| 기존 테이블명 | 새 테이블명 (hopec_) | 화면 제목 | 설명 | 사용 페이지 |
|-------------|---------------------|----------|------|------------|
| `g5_write_B31` | `hopec_notices` | **공지사항** | 희망씨 공지 및 소식을 안내 | `/community/notices.php` |
| `g5_write_B32` | `hopec_press` | **언론보도** | 언론보도 자료 | `/community/press.php` |
| `g5_write_B33` | `hopec_newsletter` | **소식지** | 정기 소식지 게시 | `/community/newsletter.php` |
| `g5_write_B34` | `hopec_gallery` | **갤러리** | 사진 갤러리 | `/community/gallery.php` |
| `g5_write_B35` | `hopec_resources` | **자료실** | 각종 자료 및 문서 | `/community/resources.php` |
| `g5_write_B36` | `hopec_nepal_travel` | **네팔나눔연대여행** | 네팔 여행 관련 게시판 | `/community/nepal.php` |

### 단체소개 섹션
| 기존 테이블명 | 새 테이블명 (hopec_) | 화면 제목 | 설명 | 사용 페이지 |
|-------------|---------------------|----------|------|------------|
| `g5_write_B03_3` | `hopec_history` | **연혁** | 단체 연혁 정보 | `/about/history.php` |
| `g5_write_B04` | `hopec_location` | **찾아오시는 길** | 오시는 길 및 위치 정보 | `/about/location.php` |
| `g5_write_B05` | `hopec_finance_reports` | **재정보고** | 재정 현황 및 보고서 | `/about/finance.php` |

### 메인 홈페이지
| 기존 테이블명 | 새 테이블명 (hopec_) | 용도 | 설명 | 사용 위치 |
|-------------|---------------------|------|------|----------|
| `g5_write_B34` | `hopec_gallery` | **갤러리 이미지** | 메인 페이지 히어로 이미지 슬라이드용 | `/theme/natural-green/pages/home.php` |

## 시스템 테이블 (System Tables)

### 핵심 관리 테이블
| 기존 테이블명 | 새 테이블명 (hopec_) | 화면 제목/용도 | 설명 |
|-------------|---------------------|---------------|------|
| `g5_board_file` | `hopec_board_files` | **첨부파일** | 게시글 첨부파일 관리 |
| `g5_member` | `hopec_members` | **회원정보** | 사용자 계정 관리 |
| `g5_config` | `hopec_config` | **사이트 설정** | 전역 설정값 |
| `g5_menu` | `hopec_menu` | **메뉴 관리** | 사이트 네비게이션 |
| `g5_content` | `hopec_content` | **컨텐츠 관리** | 정적 페이지 콘텐츠 |

### 기타 지원 테이블
| 기존 테이블명 | 새 테이블명 (hopec_) | 용도 | 설명 |
|-------------|---------------------|------|------|
| `g5_banner` | `hopec_banners` | **배너 관리** | 사이트 배너 이미지 |
| `g5_visit` | `hopec_visit_stats` | **방문자 통계** | 접속자 로그 |
| `g5_faq` | `hopec_faq` | **자주하는 질문** | FAQ 관리 |
| `g5_poll` | `hopec_polls` | **투표** | 온라인 설문조사 |

## 데이터 표시 패턴

### 게시판 목록 표시 필드
모든 게시판은 공통적으로 다음 정보를 표시합니다:
- **제목** (`wr_subject`)
- **작성자** (`wr_name`)
- **작성일** (`wr_datetime`)
- **조회수** (`wr_hit`)
- **첨부파일 개수** (g5_board_file 테이블에서 조회)

### 템플릿 구조
- 모든 게시판은 `/board_templates/board_list.php` 템플릿을 공통 사용
- 각 페이지에서 `$config` 배열로 제목과 설정을 전달
- PDO를 사용한 안전한 데이터베이스 접근

## 주요 특징

1. **테이블 명명 규칙**: `g5_write_B[숫자]` 형태로 게시판별 고유 번호 부여
2. **통합 템플릿**: 모든 게시판이 동일한 템플릿을 사용하여 일관성 유지
3. **보안**: PDO prepared statements 사용으로 SQL 인젝션 방지
4. **반응형 디자인**: Tailwind CSS를 활용한 모바일 친화적 UI

## 테이블별 세부 정보

### 커뮤니티 게시판 상세

#### g5_write_B31 (공지사항)
- **파일 위치**: `/community/notices.php`, `/community/notice_view.php`
- **페이지당 게시글**: 10개
- **특징**: 검색 기능 지원, 글쓰기 버튼 비활성화

#### g5_write_B32 (언론보도)
- **파일 위치**: `/community/press.php`, `/community/press_view.php`
- **페이지당 게시글**: 10개
- **특징**: 언론 보도 자료 전용

#### g5_write_B33 (소식지)
- **파일 위치**: `/community/newsletter.php`, `/community/newsletter_view.php`
- **페이지당 게시글**: 12개
- **특징**: 정기 소식지 게시용

#### g5_write_B34 (갤러리)
- **파일 위치**: `/community/gallery.php`, `/community/gallery_view.php`
- **페이지당 게시글**: 12개
- **특징**: 이미지 중심, 메인 페이지 히어로 이미지 소스

#### g5_write_B35 (자료실)
- **파일 위치**: `/community/resources.php`, `/community/resources_view.php`
- **페이지당 게시글**: 10개
- **특징**: 파일 첨부 중심

#### g5_write_B36 (네팔나눔연대여행)
- **파일 위치**: `/community/nepal.php`, `/community/nepal_view.php`
- **페이지당 게시글**: 12개
- **특징**: 특별 프로그램 전용 게시판

### 단체소개 게시판 상세

#### g5_write_B03_3 (연혁)
- **파일 위치**: `/about/history.php`
- **특징**: 시간순 연혁 정보

#### g5_write_B04 (찾아오시는 길)
- **파일 위치**: `/about/location.php`
- **특징**: 위치 및 교통 정보

#### g5_write_B05 (재정보고)
- **파일 위치**: `/about/finance.php`, `/about/finance_view.php`
- **특징**: 투명한 재정 공개

## 개발 참고사항

### 데이터베이스 연결
모든 페이지에서 PDO를 사용하여 안전한 데이터베이스 연결:

```php
$pdo = new PDO(
    'mysql:host=' . G5_MYSQL_HOST . ';dbname=' . G5_MYSQL_DB . ';charset=utf8mb4',
    G5_MYSQL_USER,
    G5_MYSQL_PASSWORD,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
);
```

### 공통 쿼리 패턴
```php
// 총 게시글 수 조회
$total_sql = 'SELECT COUNT(*) FROM ' . $g5['write_prefix'] . 'B31 WHERE wr_is_comment = 0';

// 목록 데이터 조회
$list_sql = 'SELECT wr_id, wr_subject, wr_name, wr_datetime, wr_hit 
             FROM ' . $g5['write_prefix'] . 'B31 
             WHERE wr_is_comment = 0 
             ORDER BY wr_id DESC 
             LIMIT :limit OFFSET :offset';
```

### 템플릿 설정 예시
```php
$config = [
    'board_title' => '공지사항',
    'board_description' => '희망씨 공지 및 소식을 안내합니다.',
    'show_write_button' => false,
    'enable_search' => true,
    'detail_url' => G5_URL . '/community/notice_view.php',
    'list_url' => G5_URL . '/community/notices.php',
];
```

---

**문서 작성일**: 2025년 1월
**프로젝트**: 희망씨 웹사이트 (그누보드5 기반)
**데이터베이스**: MySQL, 기존 접두사 `g5_` → 새 접두사 `hopec_`
**스키마 파일**: `hopec_schema.sql` (hopec_ prefix 적용된 새로운 테이블 구조)
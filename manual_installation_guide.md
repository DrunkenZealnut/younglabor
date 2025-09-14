# 테마 프리셋 기능 수동 설치 가이드

## 📋 설치 단계

### 1️⃣ 데이터베이스 설정

#### 방법 1: phpMyAdmin 사용
1. phpMyAdmin에 접속
2. `hopec` 데이터베이스 선택
3. "SQL" 탭 클릭
4. `manual_theme_presets_setup.sql` 파일의 내용을 복사하여 붙여넣기
5. "실행" 버튼 클릭

#### 방법 2: MySQL 클라이언트 사용
```bash
mysql -u username -p hopec < manual_theme_presets_setup.sql
```

### 2️⃣ 파일 확인
다음 파일들이 올바른 위치에 있는지 확인:

```
/admin/mvc/services/ThemeService.php (업데이트됨)
/admin/api/theme_presets.php (새 파일)
/admin/theme_settings_enhanced.php (업데이트됨)
/admin/test_theme_presets.php (테스트용)
```

### 3️⃣ 권한 확인
- `uploads/theme_cache/` 폴더에 쓰기 권한이 있는지 확인
- `css/theme/` 폴더에 쓰기 권한이 있는지 확인

### 4️⃣ 기능 테스트

1. **관리자 로그인**
   - admin 패널에 로그인

2. **테마 설정 페이지 접속**
   - `admin/theme_settings_enhanced.php` 페이지 방문

3. **테마 관리 섹션 확인**
   - 페이지 하단에 "테마 관리" 섹션이 표시되는지 확인
   - 드롭다운에 4개의 기본 테마가 로드되는지 확인

4. **기능 테스트**
   - 색상 변경 후 "현재 테마 저장" 버튼 테스트
   - 저장된 테마 "불러오기" 버튼 테스트
   - "테마 관리" → 삭제 기능 테스트

## 🔧 문제 해결

### DB 연결 오류가 계속 발생하는 경우:

#### 1. `admin/db.php` 파일 확인
```php
// 29번째 줄 근처에서 배열 접근 오류가 발생하는 경우
// 다음과 같이 수정:

// 기존 코드 (문제가 있는 경우):
$host = $config['database']['host'];

// 수정된 코드:
$host = $config['database']['host'] ?? 'localhost';
$dbname = $config['database']['name'] ?? 'hopec';  
$username = $config['database']['username'] ?? 'root';
$password = $config['database']['password'] ?? '';
$charset = $config['database']['charset'] ?? 'utf8mb4';
```

#### 2. 직접 연결 테스트
테스트용 간단한 연결 파일 생성:

```php
<?php
// test_connection.php
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=hopec;charset=utf8mb4",
        "your_username", 
        "your_password",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    echo "✅ 데이터베이스 연결 성공!";
} catch (PDOException $e) {
    echo "❌ 연결 실패: " . $e->getMessage();
}
?>
```

### JavaScript 오류가 발생하는 경우:
1. 브라우저 개발자 도구 (F12) → Console 탭에서 오류 확인
2. Bootstrap 5와 jQuery가 올바르게 로드되었는지 확인
3. CSRF 토큰이 올바르게 설정되었는지 확인

## ✅ 설치 완료 확인

설치가 성공적으로 완료되면:

1. **테마 설정 페이지에서:**
   - "테마 관리" 섹션이 표시됨
   - 4개의 기본 테마가 드롭다운에 로드됨
   - 색상 변경 후 저장/불러오기 기능이 작동함

2. **데이터베이스에서:**
   ```sql
   SELECT * FROM hopec_theme_presets;
   ```
   - 4개의 기본 테마 레코드가 존재

3. **파일 시스템에서:**
   - `uploads/theme_cache/` 폴더에 CSS 파일들이 생성됨

## 🎯 사용법

### 새 테마 저장하기:
1. 색상 편집기로 원하는 색상 조정
2. "현재 테마 저장" 버튼 클릭
3. 테마 이름과 설명 입력
4. "저장" 버튼 클릭

### 저장된 테마 불러오기:
1. 드롭다운에서 원하는 테마 선택
2. "불러오기" 버튼 클릭
3. 실시간으로 색상이 적용됨
4. "테마 적용" 버튼으로 최종 저장

### 테마 삭제하기:
1. "테마 관리" 버튼 클릭
2. 삭제할 테마의 휴지통 아이콘 클릭
3. 확인 대화상자에서 "삭제" 클릭

---

문제가 지속되면 구체적인 오류 메시지와 함께 알려주세요!
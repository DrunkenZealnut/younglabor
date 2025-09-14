<?php
/**
 * Board Templates Generator - Processing Backend
 * 
 * SQL 스키마 파일을 처리하여 게시판 페이지를 자동 생성합니다.
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// 에러 출력 활성화 (디버그용)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// 필요한 클래스들 로드 - 절대 경로로 수정
$baseDir = dirname(__DIR__);
require_once $baseDir . '/src/Config/BoardTableConfig.php';
require_once $baseDir . '/src/Config/SchemaParser.php';

// 디버그 정보 추가
error_log("Base directory: " . $baseDir);
error_log("BoardTableConfig exists: " . (class_exists('BoardTemplates\\Config\\BoardTableConfig') ? 'yes' : 'no'));
error_log("SchemaParser exists: " . (class_exists('BoardTemplates\\Config\\SchemaParser') ? 'yes' : 'no'));

use BoardTemplates\Config\SchemaParser;
use BoardTemplates\Config\BoardTableConfig;

try {
    // POST 요청만 허용
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('POST 요청만 허용됩니다.');
    }
    
    // 파일 업로드 확인
    if (!isset($_FILES['sqlFile']) || $_FILES['sqlFile']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('SQL 파일이 업로드되지 않았습니다.');
    }
    
    $uploadedFile = $_FILES['sqlFile'];
    $projectName = $_POST['projectName'] ?? '새 프로젝트';
    $tablePrefix = $_POST['tablePrefix'] ?? '';
    $theme = $_POST['theme'] ?? 'bootstrap';
    $language = $_POST['language'] ?? 'ko';
    
    // 파일 검증
    $allowedExtensions = ['sql', 'txt'];
    $maxFileSize = 10 * 1024 * 1024; // 10MB
    
    $fileExtension = strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExtension, $allowedExtensions)) {
        throw new Exception('SQL 또는 TXT 파일만 업로드할 수 있습니다.');
    }
    
    if ($uploadedFile['size'] > $maxFileSize) {
        throw new Exception('파일 크기는 10MB를 초과할 수 없습니다.');
    }
    
    // 임시 디렉토리 생성
    $token = uniqid('board_gen_', true);
    $tempDir = __DIR__ . '/temp/' . $token;
    if (!is_dir($tempDir)) {
        mkdir($tempDir, 0755, true);
    }
    
    // 업로드된 파일 읽기
    $sqlContent = file_get_contents($uploadedFile['tmp_name']);
    if ($sqlContent === false) {
        throw new Exception('SQL 파일을 읽을 수 없습니다.');
    }
    
    // SQL 스키마 파싱
    if (!class_exists('BoardTemplates\\Config\\SchemaParser')) {
        throw new Exception('SchemaParser 클래스를 로드할 수 없습니다.');
    }
    
    $parser = new SchemaParser();
    $tableConfig = $parser->parseFromSqlString($sqlContent);
    
    if (!$tableConfig || !($tableConfig instanceof BoardTableConfig)) {
        throw new Exception('SQL 스키마 파싱에 실패했습니다.');
    }
    
    // 테이블 접두사 설정 (사용자 지정이 있으면 우선)
    if (!empty($tablePrefix)) {
        $customConfig = new BoardTableConfig($tablePrefix);
        // 기존 파싱 결과를 사용자 지정 접두사로 재생성
        $tableConfig = $parser->parseFromSqlString($sqlContent);
    }
    
    // 생성된 설정 저장
    $configPath = $tempDir . '/table_config.php';
    $parser->exportToConfigFile($tableConfig, $configPath);
    
    // 페이지 생성
    $pageGenerator = new PageGenerator($tableConfig, $theme, $language);
    $generatedFiles = $pageGenerator->generatePages($tempDir, $projectName);
    
    // 메타데이터 저장
    $metadata = [
        'token' => $token,
        'project_name' => $projectName,
        'table_prefix' => $tableConfig->getTablePrefix(),
        'theme' => $theme,
        'language' => $language,
        'generated_at' => date('Y-m-d H:i:s'),
        'original_filename' => $uploadedFile['name'],
        'tables' => $tableConfig->getAllTableNames(),
        'generated_files' => $generatedFiles,
        'download_ready' => true
    ];
    
    file_put_contents($tempDir . '/metadata.json', json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    // 생성된 파일 확인
    $actualFiles = [];
    foreach ($generatedFiles as $file) {
        $filePath = $tempDir . '/' . $file;
        if (file_exists($filePath)) {
            $actualFiles[] = $file;
        }
    }
    
    if (empty($actualFiles)) {
        throw new Exception('생성된 파일이 없습니다. 템플릿 생성 과정에서 오류가 발생했습니다.');
    }
    
    // ZIP 파일 생성
    $zipPath = $tempDir . '/board_templates.zip';
    createZipArchive($tempDir, $zipPath, $actualFiles);
    
    if (!file_exists($zipPath)) {
        throw new Exception('ZIP 파일 생성에 실패했습니다.');
    }
    
    // 성공 응답
    echo json_encode([
        'success' => true,
        'token' => $token,
        'message' => '게시판이 성공적으로 생성되었습니다.',
        'tables_found' => count($tableConfig->getAllTableNames()),
        'files_generated' => count($generatedFiles),
        'download_url' => 'download.php?token=' . $token
    ]);
    
} catch (Exception $e) {
    // 오류 로깅
    error_log("Board Generator Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // 더 자세한 디버그 정보
    $debug_info = [
        'file' => basename($e->getFile()),
        'line' => $e->getLine(),
        'php_version' => phpversion(),
        'temp_dir' => isset($tempDir) ? $tempDir : 'not_created',
        'base_dir' => $baseDir,
        'class_exists_btc' => class_exists('BoardTemplates\\Config\\BoardTableConfig'),
        'class_exists_sp' => class_exists('BoardTemplates\\Config\\SchemaParser'),
        'full_message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'post_data' => $_POST ?? 'none',
        'files_data' => $_FILES ?? 'none'
    ];
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_code' => $e->getCode(),
        'debug_info' => $debug_info
    ], JSON_UNESCAPED_UNICODE);
}

/**
 * 페이지 생성기 클래스
 */
class PageGenerator
{
    private BoardTableConfig $tableConfig;
    private string $theme;
    private string $language;
    
    public function __construct(BoardTableConfig $tableConfig, string $theme, string $language)
    {
        $this->tableConfig = $tableConfig;
        $this->theme = $theme;
        $this->language = $language;
    }
    
    public function generatePages(string $outputDir, string $projectName): array
    {
        $generatedFiles = [];
        $tables = $this->tableConfig->getAllTableNames();
        
        // 메인 인덱스 파일 생성
        $generatedFiles[] = $this->generateIndexPage($outputDir, $projectName, $tables);
        
        // 각 테이블별 페이지 생성
        foreach ($tables as $tableKey => $tableName) {
            $generatedFiles[] = $this->generateBoardListPage($outputDir, $tableKey, $tableName);
            $generatedFiles[] = $this->generateWriteFormPage($outputDir, $tableKey, $tableName);
            $generatedFiles[] = $this->generateDetailPage($outputDir, $tableKey, $tableName);
        }
        
        // 공통 파일들 생성
        $generatedFiles[] = $this->generateConfigFile($outputDir);
        $generatedFiles[] = $this->generateStyleFile($outputDir);
        $generatedFiles[] = $this->generateScriptFile($outputDir);
        
        return $generatedFiles;
    }
    
    private function generateIndexPage(string $outputDir, string $projectName, array $tables): string
    {
        $tableList = '';
        foreach ($tables as $tableKey => $tableName) {
            $displayName = $this->getDisplayName($tableKey);
            $tableList .= "                        <li class=\"list-group-item d-flex justify-content-between align-items-center\">\n";
            $tableList .= "                            <div>\n";
            $tableList .= "                                <h6 class=\"mb-1\">{$displayName}</h6>\n";
            $tableList .= "                                <small class=\"text-muted\">테이블: {$tableName}</small>\n";
            $tableList .= "                            </div>\n";
            $tableList .= "                            <div>\n";
            $tableList .= "                                <a href=\"{$tableKey}_list.php\" class=\"btn btn-primary btn-sm\">목록</a>\n";
            $tableList .= "                                <a href=\"{$tableKey}_write.php\" class=\"btn btn-success btn-sm\">작성</a>\n";
            $tableList .= "                            </div>\n";
            $tableList .= "                        </li>\n";
        }
        
        $content = $this->getTemplate('index', [
            'project_name' => $projectName,
            'table_list' => $tableList,
            'generation_date' => date('Y-m-d H:i:s'),
            'table_count' => count($tables)
        ]);
        
        $filePath = $outputDir . '/index.php';
        file_put_contents($filePath, $content);
        
        return 'index.php';
    }
    
    private function generateBoardListPage(string $outputDir, string $tableKey, string $tableName): string
    {
        $displayName = $this->getDisplayName($tableKey);
        
        // 테이블별 컬럼 매핑 (존재하지 않는 컬럼은 대체)
        $idColumn = $this->getColumnSafe($tableKey, 'id', 'id');
        $titleColumn = $this->getColumnSafe($tableKey, 'title', 'name'); // title이 없으면 name 사용
        $authorColumn = $this->getColumnSafe($tableKey, 'author', 'author');
        $createdAtColumn = $this->getColumnSafe($tableKey, 'created_at', 'created_at');
        
        $content = $this->getTemplate('board_list', [
            'table_key' => $tableKey,
            'table_name' => $tableName,
            'display_name' => $displayName,
            'id_column' => $idColumn,
            'title_column' => $titleColumn,
            'author_column' => $authorColumn,
            'created_at_column' => $createdAtColumn
        ]);
        
        $filePath = $outputDir . "/{$tableKey}_list.php";
        file_put_contents($filePath, $content);
        
        return "{$tableKey}_list.php";
    }
    
    private function generateWriteFormPage(string $outputDir, string $tableKey, string $tableName): string
    {
        $displayName = $this->getDisplayName($tableKey);
        
        $content = $this->getTemplate('write_form', [
            'table_key' => $tableKey,
            'table_name' => $tableName,
            'display_name' => $displayName
        ]);
        
        $filePath = $outputDir . "/{$tableKey}_write.php";
        file_put_contents($filePath, $content);
        
        return "{$tableKey}_write.php";
    }
    
    private function generateDetailPage(string $outputDir, string $tableKey, string $tableName): string
    {
        $displayName = $this->getDisplayName($tableKey);
        
        // 테이블별 컬럼 매핑 (존재하지 않는 컬럼은 대체)
        $idColumn = $this->getColumnSafe($tableKey, 'id', 'id');
        $titleColumn = $this->getColumnSafe($tableKey, 'title', 'name');
        $contentColumn = $this->getColumnSafe($tableKey, 'content', 'description');
        $authorColumn = $this->getColumnSafe($tableKey, 'author', 'author');
        $createdAtColumn = $this->getColumnSafe($tableKey, 'created_at', 'created_at');
        
        $content = $this->getTemplate('detail', [
            'table_key' => $tableKey,
            'table_name' => $tableName,
            'display_name' => $displayName,
            'id_column' => $idColumn,
            'title_column' => $titleColumn,
            'content_column' => $contentColumn,
            'author_column' => $authorColumn,
            'created_at_column' => $createdAtColumn
        ]);
        
        $filePath = $outputDir . "/{$tableKey}_detail.php";
        file_put_contents($filePath, $content);
        
        return "{$tableKey}_detail.php";
    }
    
    private function generateConfigFile(string $outputDir): string
    {
        $content = $this->getTemplate('config', [
            'table_prefix' => $this->tableConfig->getTablePrefix(),
            'generation_date' => date('Y-m-d H:i:s')
        ]);
        
        $filePath = $outputDir . '/config.php';
        file_put_contents($filePath, $content);
        
        return 'config.php';
    }
    
    private function generateStyleFile(string $outputDir): string
    {
        $content = $this->getTemplate('style', [
            'theme' => $this->theme
        ]);
        
        $filePath = $outputDir . '/style.css';
        file_put_contents($filePath, $content);
        
        return 'style.css';
    }
    
    private function generateScriptFile(string $outputDir): string
    {
        $content = $this->getTemplate('script', []);
        
        $filePath = $outputDir . '/script.js';
        file_put_contents($filePath, $content);
        
        return 'script.js';
    }
    
    private function getDisplayName(string $tableKey): string
    {
        $names = [
            'posts' => '게시글',
            'categories' => '카테고리',
            'comments' => '댓글',
            'attachments' => '첨부파일',
            'users' => '사용자',
            'boards' => '게시판'
        ];
        
        return $names[$tableKey] ?? ucfirst($tableKey);
    }
    
    /**
     * 안전한 컬럼명 가져오기 (존재하지 않는 컬럼은 대체값 사용)
     */
    private function getColumnSafe(string $tableKey, string $columnKey, string $fallback): string
    {
        try {
            return $this->tableConfig->getColumnName($tableKey, $columnKey);
        } catch (Exception $e) {
            // 컬럼이 존재하지 않으면 대체값 사용
            return $fallback;
        }
    }
    
    private function getTemplate(string $templateName, array $variables = []): string
    {
        $templateDir = __DIR__ . '/templates';
        $templateFile = $templateDir . '/' . $templateName . '.php.template';
        
        if (!file_exists($templateFile)) {
            // 템플릿 디렉토리 생성
            if (!is_dir($templateDir)) {
                mkdir($templateDir, 0755, true);
            }
            throw new Exception("템플릿 파일을 찾을 수 없습니다: {$templateFile}");
        }
        
        $content = file_get_contents($templateFile);
        
        if ($content === false) {
            throw new Exception("템플릿 파일을 읽을 수 없습니다: {$templateName}");
        }
        
        // 변수 치환
        foreach ($variables as $key => $value) {
            $content = str_replace("{{$key}}", $value, $content);
        }
        
        return $content;
    }
}

/**
 * ZIP 아카이브 생성
 */
function createZipArchive(string $sourceDir, string $zipPath, array $files): bool
{
    if (!class_exists('ZipArchive')) {
        throw new Exception('ZipArchive 클래스가 설치되어 있지 않습니다. PHP ZIP 확장이 필요합니다.');
    }
    
    $zip = new ZipArchive();
    $result = $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    
    if ($result !== TRUE) {
        $errorMessages = [
            ZipArchive::ER_OK => 'No error',
            ZipArchive::ER_MULTIDISK => 'Multi-disk zip archives not supported',
            ZipArchive::ER_RENAME => 'Renaming temporary file failed',
            ZipArchive::ER_CLOSE => 'Closing zip archive failed',
            ZipArchive::ER_SEEK => 'Seek error',
            ZipArchive::ER_READ => 'Read error',
            ZipArchive::ER_WRITE => 'Write error',
            ZipArchive::ER_CRC => 'CRC error',
            ZipArchive::ER_ZIPCLOSED => 'Containing zip archive was closed',
            ZipArchive::ER_NOENT => 'No such file',
            ZipArchive::ER_EXISTS => 'File already exists',
            ZipArchive::ER_OPEN => 'Can\'t open file',
            ZipArchive::ER_TMPOPEN => 'Failure to create temporary file',
            ZipArchive::ER_ZLIB => 'Zlib error',
            ZipArchive::ER_MEMORY => 'Memory allocation failure',
            ZipArchive::ER_CHANGED => 'Entry has been changed',
            ZipArchive::ER_COMPNOTSUPP => 'Compression method not supported',
            ZipArchive::ER_EOF => 'Premature EOF',
            ZipArchive::ER_INVAL => 'Invalid argument',
            ZipArchive::ER_NOZIP => 'Not a zip archive',
            ZipArchive::ER_INTERNAL => 'Internal error',
            ZipArchive::ER_INCONS => 'Zip archive inconsistent',
            ZipArchive::ER_REMOVE => 'Can\'t remove file',
            ZipArchive::ER_DELETED => 'Entry has been deleted'
        ];
        
        $errorMessage = $errorMessages[$result] ?? "알 수 없는 오류 (코드: {$result})";
        throw new Exception("ZIP 파일을 생성할 수 없습니다: {$errorMessage}");
    }
    
    foreach ($files as $file) {
        $filePath = $sourceDir . '/' . $file;
        if (file_exists($filePath)) {
            $zip->addFile($filePath, $file);
        }
    }
    
    // 메타데이터 파일도 포함
    $metadataPath = $sourceDir . '/metadata.json';
    if (file_exists($metadataPath)) {
        $zip->addFile($metadataPath, 'metadata.json');
    }
    
    // 설정 파일도 포함
    $configPath = $sourceDir . '/table_config.php';
    if (file_exists($configPath)) {
        $zip->addFile($configPath, 'table_config.php');
    }
    
    return $zip->close();
}
?>
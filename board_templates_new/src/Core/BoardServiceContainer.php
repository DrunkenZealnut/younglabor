<?php

namespace BoardTemplates\Core;

// 필요한 클래스 파일들을 직접 로드
require_once dirname(__DIR__) . '/Interfaces/BoardConfigProviderInterface.php';
require_once dirname(__DIR__) . '/Interfaces/BoardRepositoryInterface.php';
require_once dirname(__DIR__) . '/Config/BoardTableConfig.php';
require_once dirname(__DIR__) . '/Config/SchemaParser.php';
require_once dirname(__DIR__) . '/Config/UdongConfigProvider.php';
require_once dirname(__DIR__) . '/Config/DefaultConfigProvider.php';
require_once dirname(__DIR__) . '/Config/EnvironmentConfigProvider.php';
require_once dirname(__DIR__) . '/Repository/MySQLBoardRepository.php';
require_once dirname(__DIR__) . '/Repository/PDOBoardRepository.php';

use BoardTemplates\Interfaces\BoardConfigProviderInterface;
use BoardTemplates\Interfaces\BoardRepositoryInterface;
use BoardTemplates\Config\UdongConfigProvider;
use BoardTemplates\Config\DefaultConfigProvider;
use BoardTemplates\Config\EnvironmentConfigProvider;
use BoardTemplates\Repository\MySQLBoardRepository;
use BoardTemplates\Repository\PDOBoardRepository;
use Exception;

/**
 * Board Service Container
 * 
 * 간단한 의존성 주입 컨테이너로 보드 템플릿 시스템의
 * 모든 서비스와 의존성을 관리합니다.
 */
class BoardServiceContainer
{
    private array $services = [];
    private array $singletons = [];
    private array $configurations = [];

    /**
     * 생성자
     * 
     * @param array $config 초기 설정
     */
    public function __construct(array $config = [])
    {
        $this->configurations = $config;
        $this->registerDefaultServices();
    }

    /**
     * 기본 서비스들을 등록합니다
     */
    private function registerDefaultServices(): void
    {
        // Configuration Provider 등록
        $this->bind(BoardConfigProviderInterface::class, function() {
            return $this->createConfigProvider();
        }, true);

        // Repository 등록
        $this->bind(BoardRepositoryInterface::class, function() {
            $config = $this->get(BoardConfigProviderInterface::class);
            return $this->createRepository($config);
        }, true);

        // 편의 별칭들 등록
        $this->alias('config', BoardConfigProviderInterface::class);
        $this->alias('repository', BoardRepositoryInterface::class);
        $this->alias('repo', BoardRepositoryInterface::class);
    }

    /**
     * 설정 제공자를 생성합니다
     */
    private function createConfigProvider(): BoardConfigProviderInterface
    {
        $providerType = $this->configurations['config_provider'] ?? 'auto';
        
        switch ($providerType) {
            case 'udong':
                return new UdongConfigProvider();
                
            case 'environment':
                $envPrefix = $this->configurations['env_prefix'] ?? 'BT_';
                $envFile = $this->configurations['env_file'] ?? null;
                return new EnvironmentConfigProvider($envPrefix, $envFile);
                
            case 'default':
                $customConfig = $this->configurations['custom_config'] ?? [];
                return new DefaultConfigProvider($customConfig);
                
            case 'auto':
            default:
                return $this->autoDetectConfigProvider();
        }
    }

    /**
     * 환경을 감지하여 적절한 설정 제공자를 선택합니다
     */
    private function autoDetectConfigProvider(): BoardConfigProviderInterface
    {
        // .env 파일이 있으면 EnvironmentConfigProvider 사용
        $possibleEnvFiles = [
            dirname(__DIR__, 2) . '/.env',
            dirname(__DIR__, 3) . '/.env',
            dirname(__DIR__, 4) . '/.env'
        ];

        foreach ($possibleEnvFiles as $envFile) {
            if (file_exists($envFile)) {
                return new EnvironmentConfigProvider('BT_', $envFile);
            }
        }

        // UDONG 프로젝트 구조인지 감지
        $udongIndicators = [
            dirname(__DIR__, 3) . '/includes/config.php',
            dirname(__DIR__, 3) . '/config/database.php',
            defined('DB_HOST') && defined('DB_NAME')
        ];

        $udongScore = 0;
        foreach ($udongIndicators as $indicator) {
            if (is_string($indicator) ? file_exists($indicator) : $indicator) {
                $udongScore++;
            }
        }

        if ($udongScore >= 2) {
            return new UdongConfigProvider();
        }

        // 기본 설정 제공자 사용
        return new DefaultConfigProvider($this->configurations['custom_config'] ?? []);
    }

    /**
     * 리포지토리를 생성합니다
     */
    private function createRepository(BoardConfigProviderInterface $config): BoardRepositoryInterface
    {
        $repositoryType = $this->configurations['repository_type'] ?? 'auto';
        
        switch ($repositoryType) {
            case 'mysql':
            case 'mysqli':
                return new MySQLBoardRepository($config);
                
            case 'pdo':
                return new PDOBoardRepository($config);
                
            case 'auto':
            default:
                return $this->autoDetectRepository($config);
        }
    }

    /**
     * 환경을 감지하여 적절한 리포지토리를 선택합니다
     */
    private function autoDetectRepository(BoardConfigProviderInterface $config): BoardRepositoryInterface
    {
        $dbConfig = $config->getDatabaseConfig();
        $driver = $dbConfig['driver'] ?? 'mysql';

        // PDO 우선 사용 (더 현대적이고 안전함)
        if (extension_loaded('pdo')) {
            $pdoDrivers = \PDO::getAvailableDrivers();
            if (in_array('mysql', $pdoDrivers) || in_array('pgsql', $pdoDrivers)) {
                return new PDOBoardRepository($config);
            }
        }

        // PDO가 없으면 MySQLi 사용
        if (extension_loaded('mysqli')) {
            return new MySQLBoardRepository($config);
        }

        throw new Exception('No suitable database driver available. Please install PDO or MySQLi extension.');
    }

    /**
     * 서비스를 바인드합니다
     * 
     * @param string $abstract 추상화 이름
     * @param callable|object|string $concrete 구현체
     * @param bool $singleton 싱글톤 여부
     */
    public function bind(string $abstract, $concrete, bool $singleton = false): void
    {
        $this->services[$abstract] = [
            'concrete' => $concrete,
            'singleton' => $singleton
        ];

        // 이미 생성된 싱글톤 인스턴스가 있다면 제거
        if (isset($this->singletons[$abstract])) {
            unset($this->singletons[$abstract]);
        }
    }

    /**
     * 싱글톤 서비스를 바인드합니다
     */
    public function singleton(string $abstract, $concrete): void
    {
        $this->bind($abstract, $concrete, true);
    }

    /**
     * 서비스에 별칭을 지정합니다
     */
    public function alias(string $alias, string $abstract): void
    {
        $this->services[$alias] = ['alias' => $abstract];
    }

    /**
     * 서비스를 해결하여 반환합니다
     * 
     * @param string $abstract
     * @return mixed
     */
    public function get(string $abstract)
    {
        return $this->resolve($abstract);
    }

    /**
     * 서비스 존재 여부를 확인합니다
     */
    public function has(string $abstract): bool
    {
        return isset($this->services[$abstract]);
    }

    /**
     * 서비스를 해결합니다
     */
    private function resolve(string $abstract)
    {
        // 별칭 처리
        if (isset($this->services[$abstract]['alias'])) {
            return $this->resolve($this->services[$abstract]['alias']);
        }

        // 싱글톤 캐시 확인
        if (isset($this->singletons[$abstract])) {
            return $this->singletons[$abstract];
        }

        // 서비스 정의가 없으면 예외 발생
        if (!isset($this->services[$abstract])) {
            throw new Exception("Service [{$abstract}] not found in container.");
        }

        $service = $this->services[$abstract];
        $concrete = $service['concrete'];

        // 인스턴스 생성
        if (is_callable($concrete)) {
            $instance = $concrete($this);
        } elseif (is_string($concrete)) {
            $instance = new $concrete();
        } else {
            $instance = $concrete;
        }

        // 싱글톤이면 캐시에 저장
        if ($service['singleton']) {
            $this->singletons[$abstract] = $instance;
        }

        return $instance;
    }

    /**
     * 인스턴스를 직접 설정합니다
     */
    public function instance(string $abstract, $instance): void
    {
        $this->singletons[$abstract] = $instance;
    }

    /**
     * 모든 서비스를 초기화합니다
     */
    public function flush(): void
    {
        $this->services = [];
        $this->singletons = [];
    }

    /**
     * 컨테이너 설정을 업데이트합니다
     */
    public function updateConfiguration(array $config): void
    {
        $this->configurations = array_merge($this->configurations, $config);
        
        // 기존 인스턴스들을 제거하여 새 설정 적용
        $this->singletons = [];
    }

    /**
     * 현재 설정을 반환합니다
     */
    public function getConfiguration(): array
    {
        return $this->configurations;
    }

    /**
     * 팩토리 메서드: UDONG 프로젝트용 컨테이너
     */
    public static function createForUdong(): self
    {
        return new self([
            'config_provider' => 'udong',
            'repository_type' => 'mysql'
        ]);
    }

    /**
     * 팩토리 메서드: 새 프로젝트용 컨테이너
     */
    public static function createForNewProject(array $customConfig = []): self
    {
        return new self([
            'config_provider' => 'default',
            'repository_type' => 'pdo',
            'custom_config' => $customConfig
        ]);
    }

    /**
     * 팩토리 메서드: 환경변수 기반 컨테이너
     */
    public static function createForEnvironment(string $envPrefix = 'BT_', ?string $envFile = null): self
    {
        return new self([
            'config_provider' => 'environment',
            'repository_type' => 'pdo',
            'env_prefix' => $envPrefix,
            'env_file' => $envFile
        ]);
    }

    /**
     * 팩토리 메서드: 자동 감지 컨테이너
     */
    public static function createAuto(array $config = []): self
    {
        return new self(array_merge([
            'config_provider' => 'auto',
            'repository_type' => 'auto'
        ], $config));
    }

    /**
     * 서비스 상태를 검증합니다
     */
    public function validate(): array
    {
        $errors = [];
        $warnings = [];

        try {
            // 설정 제공자 검증
            $config = $this->get(BoardConfigProviderInterface::class);
            $configValidation = $config->validateConfig();
            
            if (!$configValidation['valid']) {
                $errors = array_merge($errors, $configValidation['errors']);
            }

            // 리포지토리 연결 검증
            $repository = $this->get(BoardRepositoryInterface::class);
            if (!$repository->isConnected()) {
                $errors[] = 'Repository connection failed';
            }

        } catch (Exception $e) {
            $errors[] = 'Container validation failed: ' . $e->getMessage();
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings
        ];
    }

    /**
     * 디버그 정보를 반환합니다
     */
    public function getDebugInfo(): array
    {
        return [
            'registered_services' => array_keys($this->services),
            'singleton_instances' => array_keys($this->singletons),
            'configuration' => $this->configurations,
            'validation' => $this->validate()
        ];
    }

    /**
     * 매직 메서드: 동적 프로퍼티 접근
     */
    public function __get(string $name)
    {
        return $this->get($name);
    }

    /**
     * 매직 메서드: isset 지원
     */
    public function __isset(string $name): bool
    {
        return $this->has($name);
    }
}
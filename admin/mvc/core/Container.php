<?php
/**
 * 의존성 주입 컨테이너
 * 서비스 및 컴포넌트의 의존성 관리
 */

class Container 
{
    private static $instance = null;
    private $services = [];
    private $singletons = [];
    private $bindings = [];
    
    /**
     * 싱글톤 인스턴스 반환
     */
    public static function getInstance() 
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {}
    private function __clone() {}
    
    /**
     * 서비스 바인딩
     */
    public function bind($abstract, $concrete = null, $shared = false) 
    {
        $this->bindings[$abstract] = [
            'concrete' => $concrete ?? $abstract,
            'shared' => $shared
        ];
        
        return $this;
    }
    
    /**
     * 싱글톤 서비스 바인딩
     */
    public function singleton($abstract, $concrete = null) 
    {
        return $this->bind($abstract, $concrete, true);
    }
    
    /**
     * 서비스 인스턴스 직접 등록
     */
    public function instance($abstract, $instance) 
    {
        $this->singletons[$abstract] = $instance;
        return $this;
    }
    
    /**
     * 서비스 해결 (Resolve)
     */
    public function make($abstract, $parameters = []) 
    {
        // 이미 생성된 싱글톤 인스턴스가 있는 경우
        if (isset($this->singletons[$abstract])) {
            return $this->singletons[$abstract];
        }
        
        // 바인딩 정보 가져오기
        $binding = $this->bindings[$abstract] ?? ['concrete' => $abstract, 'shared' => false];
        $concrete = $binding['concrete'];
        
        // 인스턴스 생성
        if (is_callable($concrete)) {
            $instance = $concrete($this, $parameters);
        } elseif (is_string($concrete) && class_exists($concrete)) {
            $instance = $this->build($concrete, $parameters);
        } else {
            $instance = $concrete;
        }
        
        // 공유 인스턴스인 경우 싱글톤으로 저장
        if ($binding['shared']) {
            $this->singletons[$abstract] = $instance;
        }
        
        return $instance;
    }
    
    /**
     * 클래스 인스턴스 빌드
     */
    protected function build($class, $parameters = []) 
    {
        try {
            $reflector = new ReflectionClass($class);
            
            // 인스턴스화 불가능한 클래스 체크
            if (!$reflector->isInstantiable()) {
                throw new Exception("클래스 {$class}는 인스턴스화할 수 없습니다.");
            }
            
            $constructor = $reflector->getConstructor();
            
            // 생성자가 없는 경우
            if ($constructor === null) {
                return new $class();
            }
            
            // 생성자 의존성 해결
            $dependencies = $this->resolveDependencies($constructor->getParameters(), $parameters);
            
            return $reflector->newInstanceArgs($dependencies);
            
        } catch (ReflectionException $e) {
            throw new Exception("클래스 {$class}를 해결할 수 없습니다: " . $e->getMessage());
        }
    }
    
    /**
     * 의존성 해결
     */
    protected function resolveDependencies($parameters, $primitives = []) 
    {
        $dependencies = [];
        
        foreach ($parameters as $parameter) {
            $name = $parameter->getName();
            
            // 원시 타입 파라미터 처리
            if (array_key_exists($name, $primitives)) {
                $dependencies[] = $primitives[$name];
                continue;
            }
            
            // 타입 힌트가 있는 경우
            $type = $parameter->getType();
            if ($type && !$type->isBuiltin()) {
                $className = $type->getName();
                $dependencies[] = $this->make($className);
                continue;
            }
            
            // 기본값이 있는 경우
            if ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
                continue;
            }
            
            // 해결할 수 없는 의존성
            throw new Exception("의존성을 해결할 수 없습니다: {$name}");
        }
        
        return $dependencies;
    }
    
    /**
     * 서비스 바인딩 확인
     */
    public function bound($abstract) 
    {
        return isset($this->bindings[$abstract]) || isset($this->singletons[$abstract]);
    }
    
    /**
     * 모든 바인딩 반환
     */
    public function getBindings() 
    {
        return $this->bindings;
    }
    
    /**
     * 모든 싱글톤 인스턴스 반환
     */
    public function getSingletons() 
    {
        return $this->singletons;
    }
    
    /**
     * 컨테이너 초기화
     */
    public function flush() 
    {
        $this->services = [];
        $this->singletons = [];
        $this->bindings = [];
    }
}

/**
 * 전역 컨테이너 헬퍼 함수들
 */

/**
 * 컨테이너 인스턴스 반환
 */
function app($abstract = null, $parameters = []) 
{
    $container = Container::getInstance();
    
    if ($abstract === null) {
        return $container;
    }
    
    return $container->make($abstract, $parameters);
}

/**
 * 서비스 바인딩
 */
function bind($abstract, $concrete = null, $shared = false) 
{
    return app()->bind($abstract, $concrete, $shared);
}

/**
 * 싱글톤 바인딩
 */
function singleton($abstract, $concrete = null) 
{
    return app()->singleton($abstract, $concrete);
}

/**
 * 인스턴스 등록
 */
function instance($abstract, $instance) 
{
    return app()->instance($abstract, $instance);
}

/**
 * 서비스 해결
 */
function resolve($abstract, $parameters = []) 
{
    return app()->make($abstract, $parameters);
}
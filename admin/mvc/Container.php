<?php

/**
 * Container Class - 의존성 주입 컨테이너
 * 서비스 및 의존성을 관리하는 간단한 IoC 컨테이너
 */
class Container
{
    private $bindings = [];
    private $instances = [];
    
    /**
     * 서비스 바인딩 등록
     * 
     * @param string $name 서비스 이름
     * @param mixed $resolver 생성자 함수 또는 클래스명
     * @param bool $singleton 싱글톤으로 관리할지 여부
     */
    public function bind($name, $resolver, $singleton = false)
    {
        $this->bindings[$name] = [
            'resolver' => $resolver,
            'singleton' => $singleton
        ];
    }
    
    /**
     * 싱글톤으로 서비스 바인딩
     * 
     * @param string $name 서비스 이름  
     * @param mixed $resolver 생성자 함수 또는 클래스명
     */
    public function singleton($name, $resolver)
    {
        $this->bind($name, $resolver, true);
    }
    
    /**
     * 서비스 인스턴스 가져오기
     * 
     * @param string $name 서비스 이름
     * @return mixed 서비스 인스턴스
     */
    public function get($name)
    {
        // 이미 생성된 싱글톤 인스턴스가 있으면 반환
        if (isset($this->instances[$name])) {
            return $this->instances[$name];
        }
        
        // 바인딩이 없으면 예외 발생
        if (!isset($this->bindings[$name])) {
            throw new Exception("Service '{$name}' not found in container");
        }
        
        $binding = $this->bindings[$name];
        $instance = $this->resolve($binding['resolver']);
        
        // 싱글톤이면 인스턴스 저장
        if ($binding['singleton']) {
            $this->instances[$name] = $instance;
        }
        
        return $instance;
    }
    
    /**
     * 서비스 리졸버 실행
     * 
     * @param mixed $resolver 생성자 함수 또는 클래스명
     * @return mixed 생성된 인스턴스
     */
    private function resolve($resolver)
    {
        // 클로저 함수인 경우
        if ($resolver instanceof Closure) {
            return call_user_func($resolver);
        }
        
        // 클래스명인 경우
        if (is_string($resolver) && class_exists($resolver)) {
            return $this->makeInstance($resolver);
        }
        
        // 이미 생성된 인스턴스인 경우
        return $resolver;
    }
    
    /**
     * 클래스 인스턴스 생성 (의존성 주입)
     * 
     * @param string $className 클래스명
     * @return object 생성된 인스턴스
     */
    private function makeInstance($className)
    {
        $reflector = new ReflectionClass($className);
        
        // 생성자가 없으면 단순 인스턴스 생성
        if (!$reflector->isInstantiable() || !$reflector->getConstructor()) {
            return new $className();
        }
        
        $constructor = $reflector->getConstructor();
        $parameters = $constructor->getParameters();
        $dependencies = [];
        
        // 생성자 파라미터 의존성 해결
        foreach ($parameters as $parameter) {
            $type = $parameter->getType();
            
            if ($type && !$type->isBuiltin()) {
                // 클래스 타입인 경우 재귀적으로 해결
                $dependencies[] = $this->makeInstance($type->getName());
            } else {
                // 기본값이 있으면 사용, 없으면 null
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else {
                    $dependencies[] = null;
                }
            }
        }
        
        return $reflector->newInstanceArgs($dependencies);
    }
    
    /**
     * 서비스가 바인딩되어 있는지 확인
     * 
     * @param string $name 서비스 이름
     * @return bool 바인딩 여부
     */
    public function has($name)
    {
        return isset($this->bindings[$name]);
    }
    
    /**
     * 모든 바인딩 정보 반환 (디버깅용)
     * 
     * @return array 바인딩 정보
     */
    public function getBindings()
    {
        return $this->bindings;
    }
    
    /**
     * 특정 서비스의 바인딩 해제
     * 
     * @param string $name 서비스 이름
     */
    public function unbind($name)
    {
        unset($this->bindings[$name]);
        unset($this->instances[$name]);
    }
    
    /**
     * 모든 바인딩 및 인스턴스 클리어
     */
    public function clear()
    {
        $this->bindings = [];
        $this->instances = [];
    }
    
    /**
     * 매직 메서드 - 서비스에 직접 접근
     * 
     * @param string $name 서비스 이름
     * @return mixed 서비스 인스턴스
     */
    public function __get($name)
    {
        return $this->get($name);
    }
    
    /**
     * 매직 메서드 - 서비스 존재 여부 확인
     * 
     * @param string $name 서비스 이름
     * @return bool 존재 여부
     */
    public function __isset($name)
    {
        return $this->has($name);
    }
}
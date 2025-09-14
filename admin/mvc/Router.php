<?php

/**
 * Router Class - MVC 라우팅 시스템
 * URL을 컨트롤러와 메서드에 매핑하는 역할
 */
class Router
{
    private $routes = [];
    private $container;
    
    public function __construct($container)
    {
        $this->container = $container;
    }
    
    /**
     * GET 라우트 등록
     */
    public function get($path, $callback)
    {
        $this->routes['GET'][$path] = $callback;
    }
    
    /**
     * POST 라우트 등록
     */
    public function post($path, $callback)
    {
        $this->routes['POST'][$path] = $callback;
    }
    
    /**
     * PUT 라우트 등록 (PATCH와 동일하게 처리)
     */
    public function put($path, $callback)
    {
        $this->routes['PUT'][$path] = $callback;
        $this->routes['PATCH'][$path] = $callback;
    }
    
    /**
     * DELETE 라우트 등록
     */
    public function delete($path, $callback)
    {
        $this->routes['DELETE'][$path] = $callback;
    }
    
    /**
     * 현재 요청을 라우팅
     */
    public function dispatch()
    {
        $method = $this->getRequestMethod();
        $path = $this->getRequestPath();
        
        // 라우트 매칭
        $callback = $this->matchRoute($method, $path);
        
        if ($callback) {
            // 콜백이 문자열이면 컨트롤러@메서드 형식으로 파싱
            if (is_string($callback)) {
                return $this->executeControllerMethod($callback, $path);
            }
            
            // 클로저 콜백 실행
            return call_user_func($callback);
        }
        
        // 404 처리
        return $this->handle404();
    }
    
    /**
     * 요청 메서드 가져오기 (_method 필드 지원)
     */
    private function getRequestMethod()
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        // HTML 폼에서 PUT, DELETE 등을 시뮬레이션
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }
        
        return $method;
    }
    
    /**
     * 요청 경로 가져오기
     */
    private function getRequestPath()
    {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($requestUri, PHP_URL_PATH);
        
        // /admin/mvc/ 부분 제거 (admin MVC 전용)
        $basePath = '/admin/mvc';
        if (strpos($path, $basePath) === 0) {
            $path = substr($path, strlen($basePath));
        }
        
        return $path ?: '/';
    }
    
    /**
     * 라우트 매칭
     */
    private function matchRoute($method, $path)
    {
        if (!isset($this->routes[$method])) {
            return null;
        }
        
        foreach ($this->routes[$method] as $route => $callback) {
            $params = [];
            if ($this->isMatch($route, $path, $params)) {
                // 파라미터를 $_GET에 추가
                $_GET = array_merge($_GET, $params);
                return $callback;
            }
        }
        
        return null;
    }
    
    /**
     * 라우트 패턴과 경로 매칭
     */
    private function isMatch($route, $path, &$params)
    {
        // 정확한 매치
        if ($route === $path) {
            return true;
        }
        
        // 패러미터가 있는 라우트 ({id} 등)
        $routePattern = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '([^/]+)', $route);
        $routePattern = str_replace('/', '\/', $routePattern);
        $routePattern = '/^' . $routePattern . '$/';
        
        if (preg_match($routePattern, $path, $matches)) {
            // 파라미터 이름 추출
            preg_match_all('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', $route, $paramNames);
            
            // 파라미터 값 매핑
            for ($i = 1; $i < count($matches); $i++) {
                if (isset($paramNames[1][$i - 1])) {
                    $params[$paramNames[1][$i - 1]] = $matches[$i];
                }
            }
            
            return true;
        }
        
        return false;
    }
    
    /**
     * 컨트롤러 메서드 실행
     */
    private function executeControllerMethod($callback, $path)
    {
        if (strpos($callback, '@') === false) {
            throw new InvalidArgumentException("Invalid callback format. Use 'Controller@method'");
        }
        
        [$controllerName, $method] = explode('@', $callback, 2);
        
        // 컨트롤러 클래스 로드
        $controllerClass = $controllerName;
        if (!class_exists($controllerClass)) {
            $controllerFile = __DIR__ . '/controllers/' . $controllerClass . '.php';
            if (!file_exists($controllerFile)) {
                throw new Exception("Controller file not found: " . $controllerFile);
            }
            require_once $controllerFile;
        }
        
        // 컨트롤러 인스턴스 생성
        $controller = new $controllerClass($this->container);
        
        if (!method_exists($controller, $method)) {
            throw new Exception("Method {$method} not found in controller {$controllerClass}");
        }
        
        // URL 파라미터 추출 (REST API 스타일)
        $pathSegments = array_filter(explode('/', trim($path, '/')));
        $params = [];
        
        // 라우트에서 추출된 파라미터와 경로 파라미터 병합
        if (!empty($_GET)) {
            foreach ($_GET as $key => $value) {
                if (!in_array($key, ['page', 'search', 'sort', 'order'])) { // 쿼리 파라미터 제외
                    $params[] = $value;
                }
            }
        }
        
        // 메서드 실행
        return call_user_func_array([$controller, $method], $params);
    }
    
    /**
     * 404 에러 처리
     */
    private function handle404()
    {
        http_response_code(404);
        
        // 404 페이지 표시
        $title = '페이지를 찾을 수 없습니다';
        $message = '요청하신 페이지를 찾을 수 없습니다.';
        
        include __DIR__ . '/views/errors/404.php';
    }
    
    /**
     * 라우트 그룹 등록 (prefix, middleware 등)
     */
    public function group($attributes, $callback)
    {
        $prefix = $attributes['prefix'] ?? '';
        $middleware = $attributes['middleware'] ?? [];
        
        // 임시로 그룹 설정 저장
        $this->groupPrefix = $prefix;
        $this->groupMiddleware = $middleware;
        
        // 콜백 실행
        call_user_func($callback, $this);
        
        // 그룹 설정 초기화
        $this->groupPrefix = '';
        $this->groupMiddleware = [];
    }
    
    /**
     * 리소스 라우트 등록 (RESTful)
     */
    public function resource($name, $controller)
    {
        $this->get("/{$name}", "{$controller}@index");           // GET /items
        $this->get("/{$name}/create", "{$controller}@create");   // GET /items/create
        $this->post("/{$name}", "{$controller}@store");          // POST /items
        $this->get("/{$name}/{id}", "{$controller}@show");       // GET /items/1
        $this->get("/{$name}/{id}/edit", "{$controller}@edit");  // GET /items/1/edit
        $this->put("/{$name}/{id}", "{$controller}@update");     // PUT /items/1
        $this->delete("/{$name}/{id}", "{$controller}@destroy"); // DELETE /items/1
    }
    
    /**
     * URL 생성 헬퍼
     */
    public function url($path = '', $params = [])
    {
        $basePath = '/admin/mvc';
        $url = $basePath . '/' . ltrim($path, '/');
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        return $url;
    }
    
    /**
     * 리다이렉트 헬퍼
     */
    public function redirect($path = '', $params = [])
    {
        $url = $this->url($path, $params);
        header('Location: ' . $url);
        exit;
    }
    
    /**
     * 등록된 모든 라우트 정보 반환 (디버깅용)
     */
    public function getRoutes()
    {
        return $this->routes;
    }
    
    /**
     * 라우트 캐시 (성능 최적화용)
     */
    public function cacheRoutes($cacheFile)
    {
        $cacheData = [
            'routes' => $this->routes,
            'timestamp' => time()
        ];
        
        file_put_contents($cacheFile, '<?php return ' . var_export($cacheData, true) . ';');
    }
    
    /**
     * 캐시된 라우트 로드
     */
    public function loadCachedRoutes($cacheFile)
    {
        if (file_exists($cacheFile)) {
            $cacheData = include $cacheFile;
            $this->routes = $cacheData['routes'] ?? [];
            return true;
        }
        
        return false;
    }
}
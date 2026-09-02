<?php

/**
 * Tiny router — no external dependency.
 * Routes are registered as: $router->get('/posts', $handler);
 * Supports {param} placeholders, e.g. '/posts/{id}'.
 * All routes are automatically mounted under the API prefix (see router.php).
 */
class Router
{
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    public function patch(string $path, callable $handler): void
    {
        $this->add('PATCH', $path, $handler);
    }

    public function delete(string $path, callable $handler): void
    {
        $this->add('DELETE', $path, $handler);
    }

    private function add(string $method, string $path, callable $handler): void
    {
        $path = '/' . trim($path, '/');
        $pattern = preg_replace('#\{[a-zA-Z_]+\}#', '([^/]+)', $path);
        $paramNames = [];
        preg_match_all('#\{([a-zA-Z_]+)\}#', $path, $matches);
        $paramNames = $matches[1] ?? [];

        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'pattern' => '#^' . $pattern . '$#',
            'params' => $paramNames,
            'handler' => $handler,
        ];
    }

    public function dispatch(Request $request): void
    {
        $pathMatchedButMethodWrong = false;

        foreach ($this->routes as $route) {
            if (!preg_match($route['pattern'], $request->path, $matches)) {
                continue;
            }

            if ($route['method'] !== $request->method) {
                $pathMatchedButMethodWrong = true;
                continue;
            }

            array_shift($matches);
            $args = array_combine($route['params'], $matches);

            call_user_func($route['handler'], $request, $args);
            return;
        }

        if ($pathMatchedButMethodWrong) {
            Response::methodNotAllowed();
            return;
        }

        Response::notFound('Route not found.');
    }
}

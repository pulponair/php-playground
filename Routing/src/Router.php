<?php declare(strict_types=1);

namespace Pulponair\PhpPlayground\Routing;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Zend\HttpHandlerRunner\Emitter\EmitterInterface;

class Router
{
    public const NOT_FOUND = 0;
    public const FOUND = 1;
    public const METHOD_NOT_ALLOWED = 2;

    private const HTTP_METHODS = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD'];

    /**
     * @var ResponseFactoryInterface
     */
    protected $responseFactory;

    /**
     * @var StreamFactoryInterface
     */
    protected $streamFactory;

    /**
     * @var EmitterInterface
     */
    protected $emitter;

    /**
     * @var array
     */
    protected $routes = [];

    /**
     * Router constructor.
     *
     * @param ResponseFactoryInterface $responseFactory
     * @param StreamFactoryInterface $streamFactory
     * @param EmitterInterface $emitter
     */
    public function __construct(ResponseFactoryInterface $responseFactory,
                                StreamFactoryInterface $streamFactory,
                                EmitterInterface $emitter)
    {
        $this->responseFactory = $responseFactory;
        $this->streamFactory = $streamFactory;
        $this->emitter = $emitter;
    }

    /**
     * Checks if given http method is valid
     *
     * @param string $method
     * @return bool
     */
    protected function isValidHttpMethod(string $method): bool
    {
        return in_array($method, self::HTTP_METHODS);
    }


    /**
     * Add a route
     *
     * @param string $path
     * @param string $method
     * @param string|callable $handler
     */
    public function map(string $method, string $path, $handler): void
    {
        $method = strtoupper($method);
        $path = trim($path);

        if (!$this->isValidHttpMethod($method)) {
            throw new \InvalidArgumentException('Method "' . $method .'" is not valid');
        }

        if ($path === '') {
            throw new \InvalidArgumentException('Path is empty string');
        }

        $this->routes[$method][$path] = $handler;
    }

    /**
     * Add a GET route
     *
     * @param string $path
     * @param string|callable $handler
     */
    public function get(string $path, $handler): void
    {
        $this->map('GET', $path, $handler);
    }

    /**
     * Add a POST route
     *
     * @param string $path
     * @param string|callable $handler
     */
    public function post(string $path, $handler): void
    {
        $this->map('POST', $path, $handler);
    }

    /**
     * Add a PUT route
     *
     * @param string $path
     * @param string|callable $handler
     */
    public function put(string $path, $handler): void
    {
        $this->map('PUT', $path, $handler);
    }

    /**
     * Add a PATCH route
     *
     * @param string $path
     * @param string|callable $handler
     */
    public function patch(string $path, $handler): void
    {
        $this->map('PATCH', $path, $handler);
    }

    /**
     * Add a DELETE route
     *
     * @param string $path
     * @param string|callable $handler
     */
    public function delete(string $path, $handler): void
    {
        $this->map('DELETE', $path, $handler);
    }

    /**
     * Add a HEAD route
     *
     * @param string $path
     * @param string|callable $handler
     */
    public function head(string $path, $handler): void
    {
        $this->map('HEAD', $path, $handler);
    }


    /**
     * Resolve callable
     *
     * @param string|callable $handler
     * @return callable
     */
    protected function resolveCallable($handler): callable
    {
        if (is_array($handler) && is_string($handler[0]) && is_string($handler[1])) {
            $callback = [new $handler[0], $handler[1]];
        } else {
            $callback = $handler;
        }

        return $callback;
    }

    /**
     * Resolve callback and arguments
     *
     * @param RequestInterface $request
     * @return array
     */
    protected function resolveRoute(RequestInterface $request): array
    {
        if (!isset($this->routes[$request->getMethod()])) {
            return [self::METHOD_NOT_ALLOWED];
        }

        $routesByMethod = $this->routes[$request->getMethod()];

        $routeFound = false;
        foreach ($routesByMethod as $path => $handler) {
            if ($routeFound = (bool)preg_match('/^' . str_replace('/', '\/', $path) . '$/',
                $request->getUri()->getPath(), $matches)) {
                break;
            }
        }

        if ($routeFound === true) {
            $arguments = array_slice($matches, 1);
            $result = [self::FOUND, $handler, $arguments];
        } else {
            $result = [self::NOT_FOUND];
        }

        return $result;
    }

    /**
     * Dispatch
     *
     * @param RequestInterface $request
     * @throws \Exception
     */
    public function dispatch(RequestInterface $request): void
    {
        if (empty($this->routes)) {
            throw new \Exception('No Routes defined');
        }

        $routeInfo = $this->resolveRoute($request);
        switch ($routeInfo[0]) {
            case self::METHOD_NOT_ALLOWED:
                throw new \Exception('Method not allowed');
            case self::NOT_FOUND:
                throw new \Exception('Not found');
        }

        list(, $handler, $arguments) = $routeInfo;

        $callback = $this->resolveCallable($handler);
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('Could not resolve a callable for this route');
        }

        $response = $this->responseFactory->createResponse();

        $arguments[] = &$request;
        $arguments[] = &$response;

        $response = $response->withBody(
            $this->streamFactory->createStream(call_user_func_array($callback, $arguments))
        );

        $this->emitter->emit($response);
    }
}

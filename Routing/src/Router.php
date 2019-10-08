<?php declare(strict_types=1);

namespace Pulponair\PhpPlayground\Routing;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Zend\HttpHandlerRunner\Emitter\EmitterInterface;

class Router
{
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
     * @var RequestInterface
     */
    protected $request;

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
     * Add a route
     *
     * @param string $path
     * @param string $method
     * @param string|callable $handler
     */
    public function map(string $method, string $path, $handler): void
    {
        $this->routes[$method][$path] = $handler;
    }

    /**
     * Add a GET route
     *
     * @param string $path
     * @param string|callable $handler
     */
    public function get(string $path, $handler): void {
        $this->map('GET', $path, $handler);
    }

    /**
     * Add a POST route
     *
     * @param string $path
     * @param string|callable $handler
     */
    public function post(string $path, $handler): void {
        $this->map('POST', $path, $handler);
    }

    /**
     * Add a PUT route
     *
     * @param string $path
     * @param string|callable $handler
     */
    public function put(string $path, $handler): void {
        $this->map('PUT', $path, $handler);
    }

    /**
     * Add a PATCH route
     *
     * @param string $path
     * @param string|callable $handler
     */
    public function patch(string $path, $handler): void {
        $this->map('PATCH', $path, $handler);
    }

    /**
     * Add a DELETE route
     *
     * @param string $path
     * @param string|callable $handler
     */
    public function delete(string $path, $handler): void {
        $this->map('DELETE', $path, $handler);
    }

    /**
     * Add a HEAD route
     *
     * @param string $path
     * @param string|callable $handler
     */
    public function head(string $path, $handler): void {
        $this->map('HEAD', $path, $handler);
    }


    /**
     * Resolve callable
     *
     * @param string|callable $handler
     * @return callable
     * @throws \InvalidArgumentException
     */
    protected function resolveCallable($handler): callable {
        if (is_array($handler) && is_string($handler[0]) && is_string($handler[1])) {
            $callback = [new $handler[0], $handler[1]];
        } else {
            $callback = $handler;
        }

        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('Could not resolve a callable for this route');
        }

        return $callback;
    }

    /**
     * Resolve callback and arguments
     *
     * @param string|callable $handler
     * @param array $arguments
     * @return bool
     */
    protected function resolveHandlerAndArguments(&$handler, array &$arguments): bool
    {
        if (!isset($this->routes[$this->request->getMethod()])) {
            return false;
        }

        $bingo = false;
        $paths = array_keys($this->routes[$this->request->getMethod()]);

        foreach ($paths as $path) {
            if ($bingo = (bool)preg_match('/^' . str_replace('/', '\/', $path) . '$/',
                $this->request->getUri()->getPath(), $matches)) {
                break;
            }
        }

        if ($bingo === true) {
            $handler  = $this->routes[$this->request->getMethod()][$path];

            array_shift($matches);
            $arguments = $matches;
        }

        return $bingo;
    }

    /**
     * Run
     *
     * @param RequestInterface $request
     * @throws \Exception
     */
    public function dispatch(RequestInterface $request): void
    {
        if (empty($this->routes)) {
            throw new \Exception('No Routes defined');
        }

        $this->request = $request;
        $arguments = [];

        if (false === $this->resolveHandlerAndArguments($handler, $arguments)) {
            throw new \Exception('Route not defined for "' . $request->getUri()->getPath() . '"');
        }

        $callback = $this->resolveCallable($handler);

        $response = $this->responseFactory->createResponse();

        $arguments[] = &$request;
        $arguments[] = &$response;

        $response = $response->withBody(
            $this->streamFactory->createStream(call_user_func_array($callback, $arguments))
        );

        $this->emitter->emit($response);
    }
}

<?php declare(strict_types=1);

namespace Pulponair\PhpPlayground\Routing;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Zend\HttpHandlerRunner\Emitter\EmitterInterface;

class Router
{
    public const VARIABLE_REGEX = "\s*([a-zA-Z_][a-zA-Z0-9_-]*)\s*(?::\s*([^{}]*(?:\{(?-1)\}[^{}]*)*))?";

    /**
     * @var StreamFactoryInterface
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

    public function __construct(ResponseFactoryInterface $responseFactory,
                                StreamFactoryInterface $streamFactory,
                                EmitterInterface $emitter)
    {
        $this->responseFactory = $streamFactory;
        $this->streamFactory = $streamFactory;
        $this->emitter = $emitter;
    }

    /**
     * Add a route
     *
     * @param string $path
     * @param string $method
     * @param callable $callback
     */
    public function addRoute(string $method, string $path, callable $callback): void
    {
        $this->routes[$method][$path] = $callback;
    }

    /**
     * @return bool
     */
    protected function getCallback()
    {
        return isset($this->routes[$this->request->getMethod()][$this->request->getUri()->getPath()]) ?
            $this->routes[$this->request->getMethod()][$this->request->getUri()->getPath()] :
            false;
    }

    /**
     * Run
     *
     * @param RequestInterface $request
     * @throws \Exception
     */
    public function run(RequestInterface $request): void
    {
        $this->request = $request;
        if (false === $callback = $this->getCallback()) {
            throw new \Exception('Route not defined for "' . $request->getUri()->getPath() . '"');
        }

        $response = $this->responseFactory->createResponse(200)->withBody(
            $this->streamFactory->createStream(call_user_func($callback))
        );

        $this->emitter->emit($response);
    }
}

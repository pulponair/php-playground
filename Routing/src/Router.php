<?php declare(strict_types=1);

namespace Pulponair\PhpPlayground\Routing;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Zend\HttpHandlerRunner\Emitter\EmitterInterface;

class Router
{
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
     * Get callback and arguments
     *
     * @param callable $callback
     * @param array $arguments
     * @return bool
     */
    protected function getCallbackAndArguments(callable &$callback, array &$arguments): bool
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
            $callback = $this->routes[$this->request->getMethod()][$path];
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
    public function run(RequestInterface $request): void
    {

        if (empty($this->routes)) {
            throw new \Exception('No Routes defined');
        }

        $this->request = $request;
        $arguments = [];
        $callback = function () {
        };

        if (false === $this->getCallbackAndArguments($callback, $arguments)) {
            throw new \Exception('Route not defined for "' . $request->getUri()->getPath() . '"');
        }
        $response = $this->responseFactory->createResponse();

        $arguments[] = &$request;
        $arguments[] = &$response;
        $body = $this->streamFactory->createStream(call_user_func_array($callback, $arguments));
        $response = $response->withBody(
            $body
        );

        $this->emitter->emit($response);
    }
}

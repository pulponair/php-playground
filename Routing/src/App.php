<?php declare(strict_types=1);

namespace Pulponair\PhpPlayground\Routing;

use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\RequestInterface;
use Slim\Http\Factory\DecoratedResponseFactory;

class App
{
    /**
     * @var array
     */
    protected $routes = [];

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * Add a route
     *
     * @param string $path
     * @param string $method
     * @param callable $callback
     */
    public function addRoute(string $path, string $method, callable $callback): void
    {
        $this->routes[$path][$method] = $callback;
    }

    /**
     * @return bool
     */
    protected function getCallback()
    {
        return isset($this->routes[$this->request->getUri()->getPath()][$this->request->getMethod()]) ?
            $this->routes[$this->request->getUri()->getPath()][$this->request->getMethod()] :
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


        //@todo move to contructor or function arguments
        $psr17Factory = new Psr17Factory();
        $decoratedResponseFactory = new DecoratedResponseFactory($psr17Factory, $psr17Factory);

        $response = $decoratedResponseFactory->createResponse(200)->withBody($psr17Factory->createStream(
            call_user_func($callback)
        ));

        (new \Zend\HttpHandlerRunner\Emitter\SapiEmitter())->emit($response);
    }
}

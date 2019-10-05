<?php declare(strict_types=1);

namespace Pulponair\PhpPlayground\Routing;

use Psr\Http\Message\RequestInterface;

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
    public function run(RequestInterface $request)
    {
        $this->request = $request;
        if (false === $callback = $this->getCallback()) {
            throw new \Exception('Route not defined for "' . $request->getUri()->getPath() . '"');
        }

        $psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();
        $responseBody = $psr17Factory->createStream(call_user_func($callback));
        $response = $psr17Factory->createResponse(200)->withBody($responseBody);

        (new \Zend\HttpHandlerRunner\Emitter\SapiEmitter())->emit($response);
    }
}

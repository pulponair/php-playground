<?php declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';


class TestController {
    public function indexAction() {
        return 'indexAction';
    }

    public static function staticindexAction() {
        return 'staticIndexAction';
    }
}

final class Bootstrap
{

    static public function start()
    {
        $psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();

        $serverRequest = (new \Nyholm\Psr7Server\ServerRequestCreator(
            $psr17Factory, // ServerRequestFactory
            $psr17Factory, // UriFactory
            $psr17Factory, // UploadedFileFactoryls -l
            $psr17Factory  // StreamFactory
        ))->fromGlobals();

        $router = new \Pulponair\PhpPlayground\Routing\Router($psr17Factory, $psr17Factory,
            new Zend\HttpHandlerRunner\Emitter\SapiEmitter());

        $router->get('/', function ($request, &$response) {
            $response = $response->withStatus(201, 'All goood');
            return '<pre>' . var_export(func_get_args(), true);
        });

        $router->get('/(\d+)', function ($a) {
            return '<pre>' . var_export(func_get_args(), true);
        });

        $router->get('/(\d+)/(\d+)', function ($a, $b) {
            return '<pre>' . var_export(func_get_args(), true);
        });

        $router->get('/test', ['TestController', 'indexAction']);
        $router->get('/test2', 'TestController::staticIndexAction');

        $router->dispatch($serverRequest);
    }
}

Bootstrap::start();

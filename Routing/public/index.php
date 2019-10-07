<?php declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';


final class Bootstrap
{

    static public function start()
    {
        $psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();

        $serverRequest = (new \Nyholm\Psr7Server\ServerRequestCreator(
            $psr17Factory, // ServerRequestFactory
            $psr17Factory, // UriFactory
            $psr17Factory, // UploadedFileFactory
            $psr17Factory  // StreamFactory
        ))->fromGlobals();

        $router = new \Pulponair\PhpPlayground\Routing\Router($psr17Factory, $psr17Factory,
            new Zend\HttpHandlerRunner\Emitter\SapiEmitter());

        $router->addRoute('GET', '/', function () {
            return 'hello world';
        });

        $router->run($serverRequest);
    }
}

;


Bootstrap::start();

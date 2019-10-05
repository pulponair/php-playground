<?php declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();

$creator = new \Nyholm\Psr7Server\ServerRequestCreator(
    $psr17Factory, // ServerRequestFactory
    $psr17Factory, // UriFactory
    $psr17Factory, // UploadedFileFactory
    $psr17Factory  // StreamFactory
);

$serverRequest = $creator->fromGlobals();

$app = new \Pulponair\PhpPlayground\Routing\App();
$app->addRoute('/', 'GET', function() {
    return 'hello world';
});

$app->run($serverRequest);

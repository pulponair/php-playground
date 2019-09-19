<?php declare(strict_types=1);

use Pulponair\PhpPlayground\AOP\ApplicationAspectKernel;
use Pulponair\PhpPlayground\AOP\Person;

require __DIR__ . '/../vendor/autoload.php';

// Initialize an application aspect container
$applicationAspectKernel = ApplicationAspectKernel::getInstance();
$applicationAspectKernel->init([
    'debug'        => true, // use 'false' for production mode
    'appDir'       => __DIR__ . '/../', // Application root directory
    'cacheDir'     => __DIR__ . '/../var/cache/aop', // Cache directory
    // Include paths restricts the directories where aspects should be applied, or empty for all source files
    'includePaths' => [
        __DIR__ . '/../src/'
    ]
]);

$person = new Person();
$person->setGender(Person::GENDER_MALE)
    ->setFirstName('Nikolas')
    ->setFirstName('Hagelstein');


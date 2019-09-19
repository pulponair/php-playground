<?php declare(strict_types=1);
Namespace Pulponair\PhpPlayground\AOP;
Use Pulponair\PhpPlayground\AOP\Aspect;
use Go\Core\AspectKernel;
use Go\Core\AspectContainer;use Pulponair\PhpPlayground\AOP\Aspect\MonitorAspect;

class ApplicationAspectKernel extends AspectKernel
{
    protected function configureAop(AspectContainer $container)
    {
        $container->registerAspect(new Aspect\MonitorAspect());
        $container->registerAspect(new Aspect\FluentInterfaceAspect());
    }
}
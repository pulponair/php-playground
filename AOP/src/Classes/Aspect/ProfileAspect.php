<?php declare(strict_types=1);

Namespace Pulponair\PhpPlayground\AOP\Aspect;

use Go\Aop\Aspect;
use Go\Aop\Intercept\FieldAccess;
use Go\Aop\Intercept\MethodInvocation;
use Go\Lang\Annotation\After;
use Go\Lang\Annotation\Before;
use Go\Lang\Annotation\Around;
use Go\Lang\Annotation\Pointcut;

/**
 * Monitor aspect
 */
class ProfileAspect implements Aspect
{

    /**
     * Fluent interface advice
     *
     * @Around("execution(public Pulponair\PhpPlayground\AOP\*->*(*))")
     *
     * @param MethodInvocation $invocation
     * @return mixed|null|object
     */
    protected function aroundMethodExecution(MethodInvocation $invocation)
    {
        $start = microtime(true);
        $result = $invocation->proceed();
        echo 'Calling: ',
        $invocation,
        ' with arguments: ',
        json_encode($invocation->getArguments()),
        ' took: ',
        (microtime(true) - $start) ,
        ' s',
        "\n";
        return $result;
    }
}
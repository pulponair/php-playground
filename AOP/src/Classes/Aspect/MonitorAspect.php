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
class MonitorAspect implements Aspect
{

    /**
     * Method that will be called before real method
     *
     * @param MethodInvocation $invocation Invocation
     * @Before ("execution (public Pulponair\PhpPlayground\AOP\*->*(*))")
     */
    public function beforeMethodExecution(MethodInvocation $invocation)
    {
        echo 'Calling Before Interceptor for: ',
        $invocation,
        ' with arguments: ',
        json_encode($invocation->getArguments()),
        "\n";
    }
}
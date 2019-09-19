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
class FluentInterfaceAspect implements Aspect
{

    /**
     * Fluent interface advice
     *
     * @Around("within(Pulponair\PhpPlayground\AOP\Aspect\FluentInterface+) && execution(public **->set*(*))")
     *
     * @param MethodInvocation $invocation
     * @return mixed|null|object
     */
    protected function aroundMethodExecution(MethodInvocation $invocation)
    {
        $result = $invocation->proceed();
        return $result!==null ? $result : $invocation->getThis();
    }
}
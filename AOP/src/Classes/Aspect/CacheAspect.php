<?php declare(strict_types=1);

Namespace Pulponair\PhpPlayground\AOP\Aspect;

use Go\Aop\Aspect;
use Go\Aop\Intercept\MethodInvocation;
use Go\Lang\Annotation\Around;


/**
 * Monitor aspect
 */
class CacheAspect implements Aspect {

    protected $cache = [];

    /**
     * @Around("@execution(Pulponair\PhpPlayground\AOP\Annotation\Cacheable)")
     */
    public function aroundCacheable(MethodInvocation $invocation)
    {
        $obj   = $invocation->getThis();
        $class = is_object($obj) ? get_class($obj) : $obj;
        $key   = $class . ':' .
            $invocation->getMethod()->name. ':' .
            serialize($invocation->getArguments());

        $result = @$this->cache[$key];
        if ($result === null) {
            $result = $invocation->proceed();
            $this->cache[$key] = $result;
        }

        return $result;
    }
}
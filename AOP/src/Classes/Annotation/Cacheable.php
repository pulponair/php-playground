<?php
declare(strict_types = 1);

namespace Pulponair\PhpPlayground\AOP\Annotation;

use Doctrine\Common\Annotations\Annotation;
/**
 * @Annotation
 * @Target("METHOD")
 */
class Cacheable extends Annotation
{

}
<?php declare(strict_types=1);

Namespace Pulponair\PhpPlayground\AOP;



use Pulponair\PhpPlayground\AOP\Annotation\Cacheable;

class Math
{
    /**
     * @Cacheable()
     * @param $x
     * @return float|int
     */
    public static function factorial($x) {
        if($x <= 1){
            return 1;
        }
        else{
            return $x * self::factorial($x - 1);
        }
    }

    /**
     * @Cacheable()
     * @param float $x
     * @param int $iterations
     * @return float
     */
    public static function sin(float $x, int $iterations = 200): float {
        $x = deg2rad($x);
        $sine = 0;
        for ($i = 0; $i <= $iterations; $i++) {
            $sign = pow(-1, $i);
            $j = 2* $i + 1;
            $sine = $sine + (pow($x, $j) / self::factorial($j)) * $sign;
        }
        return $sine;
    }
}
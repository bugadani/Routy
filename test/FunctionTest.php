<?php

namespace Routy\Test;

class FunctionTest extends \PHPUnit_Framework_TestCase
{
    public function testCallUserFuncNamed()
    {
        $func = function ($a, $b = 'b', $c = 'd') {
            $this->assertEquals('a', $a);
            $this->assertEquals('b', $b);
            $this->assertEquals('c', $c);
        };

        \Routy\call_user_func_named($func, ['c' => 'c', 'a' => 'a']);
    }
}
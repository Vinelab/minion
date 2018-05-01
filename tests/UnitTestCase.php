<?php

/**
 * @author Abed Halawi <abed.halawi@vinelab.com>
 */
class UnitTestCase extends PHPUnit\Framework\TestCase
{
    public function test_running()
    {
        $this->asserttrue(true);
    }

    protected function unProtectMethod($name, $class)
    {
        $class = new \ReflectionClass(get_class($class));
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }
}

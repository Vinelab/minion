<?php

class UnitTestCase extends PHPUnit_Framework_TestCase {

    public function test_running()
    {
    }

    protected function unProtectMethod($name, $class)
    {
        $class = new \ReflectionClass(get_class($class));
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }
}

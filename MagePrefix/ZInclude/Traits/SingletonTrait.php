<?php

namespace MagePrefix\ZInclude\Traits;

trait SingletonTrait
{
    protected static $instances = [];
    static public function getSingleton() : self
    {
        $class = static::class;
        if (!isset(self::$instances[$class])){
            self::$instances[$class] = new $class();
        }
        return self::$instances[$class];
    }
}

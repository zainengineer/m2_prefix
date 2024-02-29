<?php

namespace MagePrefix\ZInclude\OtherSnippets;

use MagePrefix\ZInclude\Traits\SingletonTrait;

Class ZReflection
{
    use SingletonTrait;
    public  function getPrivateValue($object,$property)
    {
        $myClassReflection = new \ReflectionClass(get_class($object));
        $secret = $myClassReflection->getProperty($property);
        $secret->setAccessible(true);
        return $secret->getValue($object);
    }
    public  function hasProperty($object,$property) : bool
    {
        $myClassReflection = new \ReflectionClass(get_class($object));
        return $myClassReflection->hasProperty($property);
    }
    public  function recursiveData($object)
    {
        $return = $object;
        $data = null;
        if (is_object($return)) {
            if (self::hasProperty($object, '_data')){
                $data = self::getPrivateValue($object, '_data');
            }
        }
        elseif(is_array($object)){
            $data = $object;
        }
        if ($data
            && is_array($data)) {
            $return = [];
            foreach ($data as  $key=>$value) {
                $transformedValue = $value;
                if (is_object($value) || is_array($value)){
                    $transformedValue = self::recursiveData($value);
                }
                $return[$key] = $transformedValue;
            }
        }
        return $return;
    }
}

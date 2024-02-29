<?php

namespace MagePrefix\ZInclude;

class BuildDependencies
{
    protected static $dependenciesAttempted = [];
    protected static $dependenciesBuilt = [];

    public static function buildClass(string $className): object
    {
        if (isset(self::$dependenciesBuilt[$className])) {
            //TODO: allow things other than singleton
            return self::$dependenciesBuilt[$className];
        }
        if (isset(self::$dependenciesAttempted[$className])) {
            throw new \Exception("cyclic dependencies for $className");
        }
        self::$dependenciesAttempted[$className] = true;
        self::$dependenciesBuilt[$className] = self::buildClassCore($className);
        return self::$dependenciesBuilt[$className];
    }

    protected static function buildClassCore(string $className)
    {
        $reflector = new \ReflectionClass($className);
        if (!$reflector->isInstantiable()) {
            throw new \Exception("$className is not instantiable.");
        }
        $constructor = $reflector->getConstructor();
        if ($constructor === null) {
            return new $className;
        }
        $dependencies = [];
        foreach ($constructor->getParameters() as $parameter) {
            $dependencies[] = self::getDependencyFromParameter($parameter, $className);
        }
        return $reflector->newInstanceArgs($dependencies);

    }

    protected static function getDependencyFromParameter(
        \ReflectionParameter $parameter,
        string               $className,
    )
    {
        if (!$parameter->getType() instanceof \ReflectionNamedType || $parameter->getType()->isBuiltin()) {
            // Resolve a non-class hinted primitive dependency.
            if ($parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            } else if ($parameter->isVariadic()) {
                return [];
            } else {
                throw new \Exception("Unexpected parameter type in  {$parameter->getType()} in $className");
            }
        }

        $parameterTypeName = $parameter->getType()->getName();

        try {
            return self::buildClass($parameterTypeName);
        } catch (\Exception $e) {
            if ($parameter->isOptional()) {
                return $parameter->getDefaultValue();
            }
            throw $e;
        }
    }

}


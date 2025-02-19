<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\Di\Aop;

use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Definition\PropertyHandlerManager;
use Hyperf\Di\ReflectionManager;

trait PropertyHandlerTrait
{
    protected function __handlePropertyHandler(string $className)
    {
        $propertyHandlers = PropertyHandlerManager::all();
        if (! $propertyHandlers) {
            return;
        }
        $reflectionClass = ReflectionManager::reflectClass($className);
        $properties = ReflectionManager::reflectPropertyNames($className);

        // Inject the properties of parent class
        $parentReflectionClass = $reflectionClass;
        while ($parentReflectionClass = $parentReflectionClass->getParentClass()) {
            $parentClassProperties = ReflectionManager::reflectPropertyNames($parentReflectionClass->getName());
            $this->__handle($className, $parentReflectionClass->getName(), $propertyHandlers, $parentClassProperties);
            $properties = array_diff($properties, $parentClassProperties);
        }

        // Inject the properties of traits
        $traitNames = $reflectionClass->getTraitNames();
        if (is_array($traitNames)) {
            foreach ($traitNames ?? [] as $traitName) {
                $traitProperties = ReflectionManager::reflectPropertyNames($traitName);
                $this->__handle($className, $traitName, $propertyHandlers, $traitProperties);
                $properties = array_diff($properties, $traitProperties);
            }
        }
        // Inject the properties of current class
        $this->__handle($className, $className, $propertyHandlers, $properties);
    }

    protected function __handle(string $currentClassName, string $targetClassName, array $propertyHandlers, array $properties)
    {
        foreach ($properties as $propertyName) {
            $propertyMetadata = AnnotationCollector::getClassPropertyAnnotation($targetClassName, $propertyName);
            if (! $propertyMetadata) {
                continue;
            }
            foreach ($propertyMetadata as $annotationName => $annotation) {
                if (isset($propertyHandlers[$annotationName])) {
                    $callbacks = $propertyHandlers[$annotationName];
                    foreach ($callbacks as $callback) {
                        call($callback, [$this, $currentClassName, $targetClassName, $propertyName, $annotation]);
                    }
                }
            }
        }
    }
}

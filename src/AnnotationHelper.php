<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Hopeter1018\DoctrineExtension;

use ReflectionClass;
use ReflectionMethod;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\IndexedReader;

/**
 * Description of AnnotationHelper
 *
 * @version $id$
 * @author peter.ho
 */
final class AnnotationHelper
{

    /**
     * Get the annotation reader.
     * 
     * @todo cache
     * @return IndexedReader
     */
    private static function getReader()
    {
        return new IndexedReader(new AnnotationReader());
//        return new CachedReader(
//            new IndexedReader(new AnnotationReader()),
//            Cache::instance(__CLASS__),
//            false
//        );
    }

    /**
     * Get array of annotations
     * 
     * @param string $className
     * @return \stdClass[]|array
     */
    public static function byClass($className)
    {
        return static::getReader()->getClassAnnotations(new ReflectionClass($className));
    }

    /**
     * Get array of annotations
     * 
     * @param string $className
     * @return \stdClass[]|array
     */
    public static function byMethod($className, $methodName)
    {
        return static::getReader()->getMethodAnnotations(new ReflectionMethod($className, $methodName));
    }

    /**
     * Return instance of the first annotation class, which extends from the super class
     * 
     * @param string $className
     * @param string $superAnnotationClassName
     * @return \stdClass|none
     */
    public static function classAnnoExtends($className, $superAnnotationClassName)
    {
        return static::annoExtends(static::byClass($className), $superAnnotationClassName);
    }

    /**
     * Return instance of the first annotation class, which extends from the super class
     * 
     * @param string $className
     * @param string $method
     * @param string $superAnnotationClassName
     * @return \stdClass|none
     */
    public static function methodAnnoExtends($className, $method, $superAnnotationClassName)
    {
        return static::annoExtends(static::byMethod($className, $method), $superAnnotationClassName);
    }

    /**
     * Return instance of the first annotation class, which extends from the super class
     * 
     * @param \stdClass[]|array $classAnnots
     * @param string $superAnnotationClassName
     * @return \stdClass|none
     */
    public static function annoExtends($classAnnots, $superAnnotationClassName)
    {
        $found = null;
        foreach ($classAnnots as $anno)
        {
            if ($anno instanceof $superAnnotationClassName) {
                $found = $anno;
                break;
            }
        }
        return $found;
    }
}

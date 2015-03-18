<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Hopeter1018\DoctrineExtension;

/**
 * Magic Entity Alias at runtime<br />
 * So user can use Entity::alias() to get field-name-hints from netbean.
 *
 * @author peter.ho
 */
class Alias
{

    /** @var string */
    private $alias = '';

    /** @var string */
    private $entityName = '';

    /**
     * index for suffix to those non-declared alias
     * @var int
     */
    private static $aliasIndex = 0;

    public function __construct($alias = null, $entityName)
    {
        $this->alias = ($alias === null) ? 'alias_' . ( ++static::$aliasIndex) : $alias;
        $this->entityName = $entityName;
    }

    /**
     * Check against the fields, if a field is not in *.* format, prefix with alias
     * 
     * @param string[] $fields
     * @param string $alias
     * @return string[]
     */
    private static function prefixFieldsInSelect($fields, $alias)
    {
        array_walk($fields, function (&$val) use ($alias) {
            if (!strstr($val, '.')) {
                $val = $alias . '.' . $val;
            }
        });
        return $fields;
    }

    /**
     * String build from variant number of parameter.
     * 
     * @return type
     */
    public function select()
    {
        $result = null;
        if (func_num_args() === 0) {
            $result = $this->alias;
        } else {
            $fields = func_get_args();
            $alias = $this->getAlias();
            static::prefixFieldsInSelect($fields, $alias);
            $result = implode(',', $fields);
        }
        return $result;
    }

    /**
     * Magic method to serve the prefix of generated netbean-hint:
     * w => {alias}.{fieldName} implode(' ', $arguments)
     * as => {alias}.{fieldName} AS $arguments[0]
     * 
     * @param type $name
     * @param type $arguments
     * @return string
     */
    public function __call($name, $arguments)
    {
        if (substr($name, 0, 1) === 'w') {
            $result = $this->alias . '.' . lcfirst(substr($name, 1)) . ' ' . implode('', $arguments);
        } elseif (substr($name, 0, 2) === 'as') {
            $result = $this->alias . '.' . lcfirst(substr($name, 2)) . ' AS ' . $arguments[0];
        } else {
            throw new \Exception('Alias fatal error');
        }
        return $result;
    }

    /**
     * Magic property to serve the prefix of generated netbean-hint:
     * f => {alias}.{fieldName}
     * _ => {fieldName}
     * 
     * @param type $name
     * @param type $arguments
     * @return string
     */
    public function __get($name)
    {
        if (substr($name, 0, 1) === 'f') {
            $result = $this->alias . '.' . lcfirst(substr($name, 1));
        } elseif (substr($name, 0, 1) === '_') {
            $result = substr($name, 1);
        } else {
            throw new \Exception('Alias fatal error');
        }
        return $result;
    }

    /**
     * Magic method to return $this->alias
     * @return string
     */
    public function __toString()
    {
        return $this->alias;
    }

    /**
     * Return $this->alias
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Return $this->entityName;
     * @return string
     */
    public function getEntityName()
    {
        return $this->entityName;
    }


}

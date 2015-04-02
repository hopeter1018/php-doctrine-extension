<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Hopeter1018\DoctrineExtension;

use Hopeter1018\DoctrineExtension\Exceptions\ParameterTypeException;

/**
 * Description of DqlHelper
 *
 * @version $id$
 * @author peter.ho
 */
final class DqlHelper
{

    const APP_MYSQL_DATE = 'Y-m-d';
    const APP_MYSQL_DATE_TIME = 'Y-m-d h:i:s';

    /**
     * Return a new instance of query-builder from the static stored entityManager
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected static function dql()
    {
        return Connection::em()->createQueryBuilder();
    }

    /**
     * Return a new instance of expression from the static stored entityManager
     * @return \Doctrine\ORM\Query\Expr
     */
    public static function expr()
    {
        return static::dql()->expr();
    }

    /**
     * Return Query if passed QueryBuilder
     * @param \Doctrine\ORM\QueryBuilder|\Doctrine\ORM\Query $dql
     */
    private static function getQuery($dql)
    {
        if ($dql instanceof \Doctrine\ORM\QueryBuilder) {
            $dql = $dql->getQuery();
        } elseif (! $dql instanceof \Doctrine\ORM\Query) {
            throw new ParameterTypeException();
        }
        return $dql;
    }

    /**
     * Return a parameter-binded SQL from dql using string replace.<br />
     * * bind type may not correct.
     * 
     * @param \Doctrine\ORM\QueryBuilder|\Doctrine\ORM\Query $srcDql
     * @return string
     */
    public static function debug($srcDql)
    {
        $dql = static::getQuery($srcDql);

        $params = $dql->getParameters();
        $sql = $dql->getSQL();
        $dqlStr = $dql->getDQL();

        $orderParam = array();
        foreach ($params as $param) {
            /* @var $param \Doctrine\ORM\Query\Parameter */
            $orderParam[ strpos($dqlStr, ":{$param->getName()}") ] = $param;
        }
        ksort($orderParam);
        foreach ($orderParam as $param) {
            /* @var $param \Doctrine\ORM\Query\Parameter */
            $value = $param->getValue();
            switch ($param->getType()) {
                case 'date':
                    /* @var $value \DateTime */
                    $value = Connection::conn()->quote($value->format(static::APP_MYSQL_DATE));
                    break;
                case 'datetime':
                    /* @var $value \DateTime */
                    $value = Connection::conn()->quote($value->format(static::APP_MYSQL_DATE_TIME));
                    break;
                case 'integer':
                case 'double':
                    break;
                default:
                    $value = Connection::conn()->quote($value);
                    break;
            }

            $pos = strpos($sql,'?');
            if ($pos !== false) {
                $sql = substr_replace($sql,$value,$pos,strlen('?'));
            }
        }
        return $sql;
    }

    /**
     * 
     * @param \Doctrine\ORM\QueryBuilder|\Doctrine\ORM\Query $srcDql
     */
    public static function serialize($srcDql)
    {
        $dql = static::getQuery($srcDql);
        return serialize(array (
            'DQLParts' => $dql->getDQLParts(),
            'Parameters' => $dql->getParameters(),
        ));
    }

    /**
     * 
     * @param string $serializedDql
     */
    public static function deserialize($serializedDql)
    {
        $parts = unserialize($serializedDql);

        $newDql = static::dql();
        $newDql->setParameters($parts['Parameters']);
        foreach ($parts['DQLParts'] as $dqlPartName => $dqlPart) {
            if (is_array($dqlPart) and is_array(reset($dqlPart))) {
                $joinParts = array ();
                foreach ($dqlPart as $key => $partString) {
                    foreach ($partString as $index => $part) {
                        $joinParts[$key][$index] = $part;
                        $newDql->add($dqlPartName, array ($index => $part), true);
                    }
                }
            } elseif (is_array($dqlPart)) {
                foreach ($dqlPart as $key => $partString) {
                    $newDql->add($dqlPartName, $partString, true);
                }
            } else {
                $newDql->add($dqlPartName, $dqlPart);
            }
        }
        return $newDql;
    }

    public static function getBindingParameter($sql)
    {
        $matches = array();
        preg_match_all("|:([a-z0-9_])+|i", $sql, $matches);
        return array_values($matches[0]);
    }
}

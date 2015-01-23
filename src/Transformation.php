<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Hopeter1018\DoctrineExtension;

use Hopeter1018\DoctrineExtension\Exceptions\ParameterTypeException;

/**
 * Transformation function pack
 *
 * @version $id$
 * @author peter.ho
 */
final class Transformation
{

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

}

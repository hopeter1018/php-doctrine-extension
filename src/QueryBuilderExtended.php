<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Hopeter1018\DoctrineExtension;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

/**
 * Description of QueryBuilderExtended
 *
 * @version $id$
 * @author peter.ho
 */
class QueryBuilderExtended extends QueryBuilder
{

    protected static $whereParamIndex = 0;

    public function __construct(EntityManager $em)
    {
        parent::__construct($em);
    }

// <editor-fold defaultstate="__collapsed" desc="Alias Join Helper">

    /**
     * 
     * @param \Hopeter1018\DoctrineExtension\Alias $alias
     * @param string $condition
     * @param string $indexBy
     * @return static
     */
    public function aliasJoin(Alias $alias, $condition = null, $indexBy = null)
    {
        return $this->join($alias->getEntityName(), $alias->getAlias(), 'WITH', $condition, $indexBy);
    }

    /**
     * 
     * @param \Hopeter1018\DoctrineExtension\Alias $alias
     * @param string $condition
     * @param string $indexBy
     * @return static
     */
    public function aliasLeftJoin(Alias $alias, $condition = null, $indexBy = null)
    {
        return $this->leftJoin($alias->getEntityName(), $alias->getAlias(), 'WITH', $condition, $indexBy);
    }

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Query Helper">

    /**
     * 
     * @return int|string
     */
    public function getQuerySingleScalar()
    {
        return $this->getQuery()->getSingleScalarResult();
    }

    /**
     * 
     * @param boolean $includeMeta to include the doctrine meta columns, so foreign key can be retrieve without join (column name: xxxx_id)
     * @return array
     */
    public function getQueryArray($includeMeta = false)
    {
        return $this->getQuery()
            ->setHint(Query::HINT_INCLUDE_META_COLUMNS, $includeMeta)
            ->getArrayResult();
    }

    /**
     * 
     * @return BaseEntity[]|array
     */
    public function getQueryResult()
    {
        return $this->getQuery()->getResult();
    }

    /**
     * Get the row number without getting it all to php memory and count()
     * @return int
     */
    public function getCount()
    {
        $dqlStr = $this->getQuery()->getDQL();
        $stmt = Connection::conn()->prepare("SELECT count(*) AS total FROM ({$this->getQuery()->getSQL()}) data");
        $params = $this->getQuery()->getParameters();

        $orderParam = array();
        foreach ($params as $param) {
            /* @var $param \Doctrine\ORM\Query\Parameter */
            $orderParam[ strpos($dqlStr, ":{$param->getName()}") ] = $param;
        }
        ksort($orderParam);

        $orderParamSorted = array_values($orderParam);
        foreach ($orderParamSorted as $index => $param) {
            $stmt->bindValue(1 + $index, $param->getValue(), $param->getType());
        }
        $stmt->execute();

        $row = $stmt->fetch();
        return (int) $row['total'];
    }

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="1-line where with parameter">

    /**
     * 
     * @param string $clause put ? as the parameter
     * @param string|int $param
     * @return static
     */
    public function whereParam($clause, $param)
    {
        $parameterName = ':param' . (++ static::$whereParamIndex);
        return $this->where(str_replace('?', $parameterName, $clause))->setParameter($parameterName, $param);
    }

    /**
     * 
     * @param string $clause put ? as the parameter
     * @param string|int $param
     * @return static
     */
    public function andWhereParam($clause, $param)
    {
        $parameterName = ':param' . (++ static::$whereParamIndex);
        return $this->andWhere(str_replace('?', $parameterName, $clause))->setParameter($parameterName, $param);
    }

    /**
     * 
     * @param string $clause put ? as the parameter
     * @param string|int $param
     * @return static
     */
    public function orWhereParam($clause, $param)
    {
        $parameterName = ':param' . (++ static::$whereParamIndex);
        return $this->orWhere(str_replace('?', $parameterName, $clause))->setParameter($parameterName, $param);
    }

// </editor-fold>
    
}

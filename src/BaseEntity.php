<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Hopeter1018\DoctrineExtension;

/**
 * Description of BaseEntity
 *
 * @version $id$
 * @author peter.ho
 */
class BaseEntity extends \Zms5Library\Framework\SuperClass implements \ArrayAccess
{

    private $additionalColumn = array ();

// <editor-fold defaultstate="collapsed" desc="Contructer">

    public function __construct($array = null)
    {
        if (is_array($array)) {
            $this->fromArray($array);
        }
    }

    public function fromArray($array)
    {
        foreach ($array as $field => $value) {
            $this->{'set' . ucfirst($field)}($value);
        }
    }

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="PHP Array Access">

    public function offsetExists($offset)
    {
        $offset = strtolower($offset);
        return isset($this->$offset);
    }

    public function offsetGet($offset)
    {
        $offset = strtolower($offset);
        if (isset($this->$offset)) {
            $offset = ucfirst($offset);
            return $this->{"get$offset"}();
        } elseif (isset($this->additionalColumn[$offset])) {
            return $this->additionalColumn[$offset];
        }
    }

    public function offsetSet($offset, $value)
    {
        $offset = strtolower($offset);
        if (isset($this->$offset)) {
            $offset = ucfirst($offset);
            $this->{"set$offset"}($value);
        } else {
            $this->additionalColumn[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        $offset = strtolower($offset);
        unset($this->$offset);
    }

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="DQL alias helper">

    /**
     * 
     * @param type $alias
     * @return Zms5\Common\DoctrineAlias
     */
    public static function alias($alias = null)
    {
        return new Alias($alias);
    }

    public function getAlias()
    {
        return $this->alias;
    }

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="DQL shortcut">
    
    /**
     * Shortcut to DQL -> select -> from
     * 
     * @param string $select t || t.id
     * @param string $alias t
     * @param string $indexBy t.id
     * @return \Doctrine\ORM\QueryBuilder
     */
    public static function selectFrom($select = 't', $alias = 't', $indexBy = null)
    {
        if ($alias instanceof DoctrineAlias) {
            $alias = $alias->getAlias();
        }
        if ($select instanceof DoctrineAlias) {
            $select = $select->getAlias();
        }
        return self::dql()->from(get_class(new static), $alias, $indexBy)->select($select);
    }

    /**
     * Shortcut to DQL -> delete
     * 
     * @param string $alias
     * @return \Doctrine\ORM\QueryBuilder
     */
    public static function deleteFrom($alias = 't')
    {
        return self::dql()->delete(get_class(new static), $alias);
    }

    /**
     * Shortcut to DQL -> update
     * 
     * @param string $alias
     * @return \Doctrine\ORM\QueryBuilder
     */
    public static function updateFrom($alias = 't')
    {
        return self::dql()->update(get_class(new static), $alias);
    }

// </editor-fold>

    /**
     * 
     * @return \Doctrine\ORM\EntityRepository
     */
    public static function repo()
    {
        return Connection::entityManager()->getRepository(static::className());
    }

    /**
     * One function to insert / update
     * @return \Zms5\Common\DoctrineBase
     */
    public function saveEx($delay = false)
    {
        $entityManager = Connection::entityManager();

        if ($this->getPrimaryKey() === null) {
            $entityManager->persist($this);
        } else {
            $entityManager->merge($this);
        }
        if (!$delay) {
            $entityManager->flush();
        }
        return $this;
    }

    public function delete()
    {
        Connection::entityManager()->remove($this);
        Connection::entityManager()->flush();
    }

    private function getPrimaryKey()
    {
        return $this[Connection::entityManager()->getClassMetadata(get_class($this))->getSingleIdentifierFieldName()];
    }

    /**
     * @todo
     * @return array|string[]
     */
    public function getAllFieldNames()
    {
        Connection::em()->getClassMetadata(get_class($this))->getFieldNames();
    }

    /**
     * 
     * @param self|null $orm
     */
    public static function toArray($orm)
    {
        if ($orm === null) {
            $result = null;
        } else {
            $fieldNames = $orm->getAllFieldNames();
            foreach ($fieldNames as $property) {
                /* @var $property \ReflectionProperty */
                $result[ $property ] = $orm->{'get' . ucfirst($property)}();
            }
        }
        return $result;
    }

    public function instArray()
    {
        return static::toArray($this);
    }

}

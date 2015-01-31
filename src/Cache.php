<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Hopeter1018\DoctrineExtension;

use Hopeter1018\Framework\SystemPath;

/**
 * Description of Cache
 *
 * @version $id$
 * @author peter.ho
 */
class Cache
{

    /**
     * 
     * @param type $namespace
     * @return \Doctrine\Common\Cache\Cache
     */
    public static function instance($namespace)
    {
        if (extension_loaded('apc')) {
            $cache = new \Doctrine\Common\Cache\ApcCache();
        } elseif (extension_loaded('xcache')) {
            $cache = new \Doctrine\Common\Cache\XcacheCache();
        } elseif (extension_loaded('memcache')) {
            $memcache = new \Memcache();
            $memcache->connect('127.0.0.1');
            $cache = new \Doctrine\Common\Cache\MemcacheCache();
            $cache->setMemcache($memcache);
        } elseif (extension_loaded('redis')) {
            $redis = new \Redis();
            $redis->connect('127.0.0.1');
            $cache = new \Doctrine\Common\Cache\RedisCache();
            $cache->setRedis($redis);
        } else {
            $cache = self::file($namespace);
        }

        $cache->setNamespace($namespace);
        return $cache;
    }

    public static function file($namespace)
    {
        $cache = new \Doctrine\Common\Cache\PhpFileCache(
            SystemPath::storagePath($namespace)
        );
        $cache->setNamespace($namespace);
        return $cache;
    }

}

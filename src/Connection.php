<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Hopeter1018\DoctrineExtension;

use PDO;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

/**
 * Description of Connection
 *
 * @version $id$
 * @author peter.ho
 */
final class Connection
{
    /**
     * Static property to indicate the doctrine connect is initiated in request or not
     * @var boolean 
     */
    private static $init = false;

    /**
     *
     * @var \Doctrine\DBAL\Connection 
     */
    private static $conn = null;
    /**
     *
     * @var \Doctrine\ORM\Configuration 
     */
    private static $config = null;
    /**
     *
     * @var \Doctrine\ORM\EntityManager 
     */
    private static $entityManager = null;

    /**
     * Register the doctrine connection and entity manager
     * Should run once in each request
     * 
     * @link http://doctrine-orm.readthedocs.org/en/latest/reference/advanced-configuration.html description
     * @param string $host
     * @param int $port
     * @param string $user
     * @param string $password
     * @param string $database
     * @param boolean $isDev
     * @param string $prefix
     * @return array
     */
    public static function register($host = DB_HOST, $port = DB_PORT, $user = DB_USER, $password = DB_PW, $database = DB_DB, $isDev = false, $prefix = DB_PREFIX)
    {
        $config = Setup::createYAMLMetadataConfiguration(array(PathHelper::getYamlRoot()), $isDev);
        $config->setProxyDir(PathHelper::getProxiesRoot());
//        $config->setProxyNamespace('DoctrineORMModule\Proxy');
//        $config->setAutoGenerateProxyClasses(\Doctrine\Common\Proxy\AbstractProxyFactory::AUTOGENERATE_NEVER);
        static::registerMySqlStringFunction($config);

        $dbh = new PDO("mysql:host={$host};port={$port};dbname={$database}", $user, $password, array (PDO::ATTR_PERSISTENT => true,));
        $dbh->exec("SET NAMES utf8");

        $evm = new \Doctrine\Common\EventManager;

        // Table Prefix
        $tablePrefix = new TablePrefix($prefix);
        $evm->addEventListener(\Doctrine\ORM\Events::loadClassMetadata, $tablePrefix);

        // obtaining the entity manager
        $entityManager = EntityManager::create(
            $conn = array ('driver' => 'pdo_mysql','charset' => 'utf8','pdo' => $dbh,),
            $config,
            $evm
        );

        static::init($conn, $config, $entityManager);
        return array($conn, $config, $entityManager);
    }

    /**
     * Register extra string function to DQL
     * 
     * @param type $config
     */
    private static function registerMySqlStringFunction($config)
    {
        $config->addCustomStringFunction('PASSWORD', 'Hopeter1018\DoctrineExtension\MySql\Password');
        $config->addCustomStringFunction('DATEADD', 'Hopeter1018\DoctrineExtension\MySql\DateAdd');
        $config->addCustomStringFunction('DATEDIFF', 'Hopeter1018\DoctrineExtension\MySql\DateDiff');
        $config->addCustomStringFunction('DATE_FORMAT', 'Hopeter1018\DoctrineExtension\MySql\DateFormat');
        $config->addCustomStringFunction('GROUPCONCAT', 'Hopeter1018\DoctrineExtension\MySql\GroupConcat');
        $config->addCustomStringFunction('DATE', 'Hopeter1018\DoctrineExtension\MySql\Date');
    }

    /**
     * 
     * @param \Doctrine\ORM\Connection $conn
     * @param \Doctrine\ORM\Configuration $config
     * @param \Doctrine\ORM\EntityManager $entityManager
     */
    private static function init($conn, $config, $entityManager)
    {
        if (! self::$init or ! self::$entityManager->isOpen()) {
            self::$init = true;
            self::$config = $config;
            self::$entityManager = $entityManager;
            self::$conn = $entityManager->getConnection();
        }
    }

    /**
     * Return the static stored doctrine conn (em->getConnection)
     * @return \Doctrine\DBAL\Connection
     */
    public static function conn()
    {
        return self::$conn;
    }

    /**
     * Return the static stored doctrine entityManager (em)
     * @return \Doctrine\ORM\EntityManager
     */
    public static function em()
    {
        return self::$entityManager;
    }

    /**
     * Return new QueryBuilder
     * @return \Doctrine\ORM\QueryBuilder|QueryBuilderExtended
     */
    public static function dql()
    {
//        return self::$entityManager->createQueryBuilder();
        return new QueryBuilderExtended(self::$entityManager);
    }

// <editor-fold defaultstate="collapsed" desc="Transaction Functions">

    /**
     * try: ($conn, $em)<br />
     * catch: ($conn, $em, $ex)<br />
     * finally: ($conn, $em, $ex)<br />
     * 
     * @deprecated since version number Please use netbean-template "p_trans" to generate try {} catch {}
     * @param \Closure $try lambda function with $conn, $em
     * @param \Closure $catch
     * @param \Closure $finally
     */
    public static function trans(\Closure $try, \Closure $catch = null, \Closure $finally = null)
    {
        $conn = self::$conn;
        $conn->beginTransaction();
        $catches = false;
        $ex = null;
        try {
            $result = $try($conn, self::$entityManager);

            self::$entityManager->flush();
            $conn->commit();
        } catch (Exception $ex) {
            $conn->rollback();
            error_log($ex->getMessage() . "\r\n" . $ex->getTraceAsString());

            $catches = true;
            if (APP_IS_DEV) {
                header('dt-trans: ' . $ex->getMessage());
            }
            if ($catch !== null) {
                $result = $catch($conn, self::$entityManager, $ex);
            } else {
                $result = null;
            }
        }

        if ($finally !== null) {
            $finally($conn, self::$entityManager, $ex);
        }

        self::$entityManager->close();
        self::$entityManager = null;
        $conn->close();

        return $result;
    }

// </editor-fold>

}

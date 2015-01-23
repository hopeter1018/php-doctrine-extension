<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Hopeter1018\DoctrineExtension;

/**
 * Description of PathHelper
 *
 * @version $id$
 * @author peter.ho
 */
final class PathHelper
{

    const FOLDER_ENTITIES = 'Entities';
    const FOLDER_REPOSITORIES = 'Repositories';
    const FOLDER_YAML = 'yaml';
    const FOLDER_PROXIES = 'Proxies';

    /**
     * Return absolute path to entities<br />
     * {workbench root}/application/generated/doctrine-files/Entities/<br />
     * <br />
     * if parameter '<b>paths</b>' provided, will be appended without ending '/'<br />
     * 
     * @param mixed $paths,...
     * @return string
     */
    public static function getEntitiesRoot($paths = null)
    {
        $dirPath = \Zms5Library\Framework\SystemPath::doctrineFilesPath(static::FOLDER_ENTITIES, func_get_args());
        return ($paths === null) ? $dirPath : rtrim($dirPath, '/');
    }

    /**
     * Return absolute path to repositories<br />
     * {workbench root}/application/generated/doctrine-files/Repositories/<br />
     * <br />
     * if parameter '<b>paths</b>' provided, will be appended without ending '/'<br />
     * 
     * @param mixed $paths,...
     * @return string
     */
    public static function getRepositoriesRoot($paths = null)
    {
        $dirPath = \Zms5Library\Framework\SystemPath::doctrineFilesPath(static::FOLDER_REPOSITORIES, func_get_args());
        return ($paths === null) ? $dirPath : rtrim($dirPath, '/');
    }

    /**
     * Return absolute path to yaml<br />
     * {workbench root}/application/generated/doctrine-files/yaml/<br />
     * <br />
     * if parameter '<b>paths</b>' provided, will be appended without ending '/'<br />
     * 
     * @param mixed $paths,...
     * @return string
     */
    public static function getYamlRoot($paths = null)
    {
        $dirPath = \Zms5Library\Framework\SystemPath::doctrineFilesPath(static::FOLDER_YAML, func_get_args());
        return ($paths === null) ? $dirPath : rtrim($dirPath, '/');
    }

    /**
     * Return absolute path to proxies<br />
     * {workbench root}/application/generated/doctrine-files/Proxies/<br />
     * <br />
     * if parameter '<b>paths</b>' provided, will be appended without ending '/'<br />
     * 
     * @param mixed $paths,...
     * @return string
     */
    public static function getProxiesRoot($paths = null)
    {
        $dirPath = \Zms5Library\Framework\SystemPath::doctrineFilesPath(static::FOLDER_PROXIES, func_get_args());
        return ($paths === null) ? $dirPath : rtrim($dirPath, '/');
    }

    /**
     * Preform a clean-up task to doctrine-generated folders except Repositories
     * 
     */
    public static function cleanUp()
    {
        foreach (glob(static::getYamlRoot() . '*') as $file) {
            is_file($file) and unlink($file);
        }
        foreach (glob('{' . static::getEntitiesRoot() . '*,' . static::getProxiesRoot() . '*' . '}', GLOB_BRACE) as $file) {
            is_file($file) and unlink($file);
            is_dir(basename($file)) and rmdir(basename($file));
            is_dir($file) and rmdir($file);
        }
        !is_dir(static::getYamlRoot()) and mkdir(static::getYamlRoot(), 0777, true);
        !is_dir(static::getEntitiesRoot()) and mkdir(static::getEntitiesRoot(), 0777, true);
        !is_dir(static::getProxiesRoot()) and mkdir(static::getProxiesRoot(), 0777, true);
    }

}

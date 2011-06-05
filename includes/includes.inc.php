<?php
/**
 * Global Includes File
 *
 * Global Includes File - should be included on every page!
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2006-2011 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Set timezone
 */
date_default_timezone_set('Europe/Berlin');

/**
 * Database-Connection Information
 */
require_once(DIR_CONFIG."dbconnect.cfg.php");

/**
 * Global Functions
 */
require_once(DIR_INCLUDES."functions/global.func.php");

/**
 * File Functions
 */
require_once(DIR_INCLUDES."functions/file.func.php");

/**
 * Database Functions
 */
require_once(DIR_INCLUDES."functions/database.func.php");

/**
 * Set Autoloader
 */
spl_autoload_register("ruinsAutoload");

/**
 * Doctrine ClassLoaders
 */
require_once(DIR_INCLUDES_DOCTRINE."Doctrine/Common/ClassLoader.php");

$classLoader = new \Doctrine\Common\ClassLoader('Doctrine', DIR_INCLUDES_DOCTRINE);
$classLoader->register();

$classLoader = new \Doctrine\Common\ClassLoader('Symfony', DIR_INCLUDES_DOCTRINE . 'Doctrine/');
$classLoader->register();

$classLoader = new \Doctrine\Common\ClassLoader('Entities', DIR_MAIN);
$classLoader->register();

$classLoader = new \Doctrine\Common\ClassLoader('Manager', DIR_MAIN . 'lib/');
$classLoader->register();

$classLoader = new \Doctrine\Common\ClassLoader('Layers', DIR_MAIN . 'lib/');
$classLoader->register();

/**
 * Doctrine Initialization
 */
require_once(DIR_COMMON_EXTERNAL."doctrine2_init.php");


/**
 * Smarty Initialization
 */
require_once(DIR_COMMON_EXTERNAL."smarty_init.php");

?>

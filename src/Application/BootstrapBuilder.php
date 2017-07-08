<?php
/**
 * Created by IntelliJ IDEA.
 * User: Weiye Sun
 * Date: 2017/6/29
 * Time: 22:10
 */

namespace Sanwei\Joomla\Application;


class BootstrapBuilder
{
    protected $applicationRootPath;

    protected $conf;

    protected static function getDefaultJoomlaPath() {
        return realpath(__DIR__ . '/../../../../joomla');
    }

    public function __construct()
    {
        $this->applicationRootPath = $_SERVER['DOCUMENT_ROOT'];
        $this->conf = include $this->applicationRootPath . DIRECTORY_SEPARATOR . 'settings.php';
        if(empty($this->conf)) {
            $this->exitOnError('The settings file is not found.');
        }
        if (version_compare(PHP_VERSION, $this->conf['system']['php_version'], '<'))
        {
            $this->exitOnError('Your host needs to use PHP ' . $this->conf['system']['php_version'] . ' or higher to run this version of Joomla!');
        }

        $environment = getenv('ENVIRONMENT');
        // Set the environment as development if it is not set.
        $this->conf['environment'] = $environment !== false ? $environment : 'development';
    }

    /**
     * @return Bootstrap
     */
    public function getBootstrap() {
        $this->initializeDefines();

        //@todo Check the application is running as frontend or backend
        return new Bootstrap($this->conf);
    }

    protected function exitOnError($message) {
        //@todo Set error code to HTTP header or write to error log
        die($message);
    }

    public function initializeDefines() {
        //@todo Use symfony file system isAbsolutePath to check if it is absolute path
        $joomlaPath = is_dir($this->conf['joomla']['path']) ? $this->conf['joomla']['path'] : self::getDefaultJoomlaPath();
        $joomlaIncludesPath = $joomlaPath . DIRECTORY_SEPARATOR . 'includes';

        define('_JDEFINES', 1);

        define('APPLICATION_ROOT', $this->applicationRootPath);

        define('JPATH_ROOT',          $joomlaPath);
        define('JPATH_SITE',          JPATH_ROOT);
        define('JPATH_CONFIGURATION', APPLICATION_ROOT);
        define('JPATH_ADMINISTRATOR', JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator');
        define('JPATH_LIBRARIES',     JPATH_ROOT . DIRECTORY_SEPARATOR . 'libraries');
        define('JPATH_PLUGINS',       JPATH_ROOT . DIRECTORY_SEPARATOR . 'plugins');
        define('JPATH_INSTALLATION',  APPLICATION_ROOT . DIRECTORY_SEPARATOR . 'installation');
        define('JPATH_THEMES',        APPLICATION_ROOT . DIRECTORY_SEPARATOR . 'templates');
        define('JPATH_CACHE',         APPLICATION_ROOT . DIRECTORY_SEPARATOR . 'cache');
        define('JPATH_MANIFESTS',     JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'manifests');

        /**
         * @todo Define different path for JPATH_BASE
         * JPATH_BASE is the root path for the current requested application....
         * so if you are in the administrator application, JPATH_BASE == JPATH_ADMINISTRATOR...
         * if you are in the site application JPATH_BASE == JPATH_SITE...
         * if you are in the installation application JPATH_BASE == JPATH_INSTALLATION.
         */
        define('JPATH_BASE', $joomlaPath);
    }
}
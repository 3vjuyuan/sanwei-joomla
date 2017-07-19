<?php
/**
 * Created by IntelliJ IDEA.
 * User: Weiye Sun
 * Date: 2017/6/29
 * Time: 22:10
 */

namespace Sanwei\Joomla\Application;

use Symfony\Component\Filesystem\Filesystem;

class BootstrapBuilder
{
    protected $applicationRootPath;

    protected $conf;

    protected static function getJoomlaApplicationPath($path = '') {
        $fs = new Filesystem();
        if(is_dir($path)) {
            return realpath($fs->isAbsolutePath($path) ? $path : APP_ROOT . DIRECTORY_SEPARATOR . $path);
        } else {
            return realpath(__DIR__ . '/../../../../joomla');
        }
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
        $bootstrap = new Bootstrap();

        $appConf = $this->getApplicationConfigurations();
        $bootstrap->setApplication($appConf['type']);
        $bootstrap->setApplicationUriPath($appConf['app_uri']);

        call_user_func_array(array($bootstrap, 'addRequiredFiles'), $appConf['requiredFiles']);

        return $bootstrap;
    }

    /**
     * Get the configuration for the application, which should be bootstrapped to
     * and set the JPATH_BASE value, the root path for the current requested application.
     *
     */
    protected function getApplicationConfigurations() {
        $configurations = [];
        $uriParameters = explode('/', trim($_SERVER['REQUEST_URI'], '/') . '/');
        $appUriRequest = strpos($uriParameters[0], '.') === false ? $uriParameters[0] : $uriParameters[1];

        // @todo remove framework.php
        switch ($appUriRequest) {
            case $this->conf['joomla']['admin_uri']:
                define('JPATH_BASE', JPATH_ADMINISTRATOR);
                $configurations['type'] = Bootstrap::APPLICATION_ADMIN;
                $configurations['requiredFiles'] = [
                    JPATH_BASE . '/includes/framework.php',
                    JPATH_BASE . '/includes/helper.php',
                    JPATH_BASE . '/includes/toolbar.php'
                ];
                break;
            case 'install':
                define('JPATH_BASE', JPATH_INSTALLATION);
                $configurations['type'] = Bootstrap::APPLICATION_INSTALL;
                break;
            default:
                $appUriRequest = isset($this->conf['joomla']['site_uri']) ? $this->conf['joomla']['site_uri'] : '';
                define('JPATH_BASE', JPATH_SITE);
                $configurations['type'] = Bootstrap::APPLICATION_SITE;
                $configurations['requiredFiles'] = [
                    JPATH_BASE . '/includes/framework.php',
                ];
        }

        $configurations['app_uri'] = $appUriRequest;
        define('JPATH_THEMES', JPATH_BASE . DIRECTORY_SEPARATOR . 'templates');

        return $configurations;
    }

    protected function exitOnError($message) {
        //@todo Set error code to HTTP header or write to error log
        die($message);
    }

    public function initializeDefines() {
        define('_JDEFINES', 1);
        define('APP_ROOT', $this->applicationRootPath);
        define('APP_EXTENSIONS', APP_ROOT . DIRECTORY_SEPARATOR . 'extensions');
        define('APP_THEMES', APP_EXTENSIONS . DIRECTORY_SEPARATOR . 'themes');

        define('JPATH_ROOT',          self::getJoomlaApplicationPath($this->conf['joomla']['path']));
        define('JPATH_SITE',          JPATH_ROOT);
        define('JPATH_CONFIGURATION', APP_ROOT);
        define('JPATH_ADMINISTRATOR', JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator');
        define('JPATH_LIBRARIES',     JPATH_ROOT . DIRECTORY_SEPARATOR . 'libraries');
        define('JPATH_PLUGINS',       JPATH_ROOT . DIRECTORY_SEPARATOR . 'plugins');
        define('JPATH_INSTALLATION',  APP_ROOT . DIRECTORY_SEPARATOR . 'installation');
        define('JPATH_CACHE',         APP_ROOT . DIRECTORY_SEPARATOR . 'cache');
        define('JPATH_MANIFESTS',     JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'manifests');
    }
}
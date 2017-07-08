<?php
/**
 * Created by IntelliJ IDEA.
 * User: Weiye Sun
 * Date: 2017/6/29
 * Time: 22:33
 */

namespace Sanwei\Joomla\Application;

use JProfiler;
use JFactory;

class Bootstrap
{
    /**
     * Base root path of application
     * @var string
     */
    protected $basePath;
    protected $joomlaApplication;
    protected $startTime;
    protected $startMem;
    protected $joomlaPath;
    protected $loadPaths;

    public function __construct()
    {

    }

    public function run() {
        $this->start();
        $this->execute();
    }

    protected function start() {
        define('_JEXEC', 1);

        $this->startTime = microtime(1);
        $this->startMem  = memory_get_usage();



        require_once JPATH_BASE . '/includes/framework.php';
    }

    protected function findFile($name) {
        foreach ($this->loadPaths as $path) {
            if(file_exists($file = $path . DIRECTORY_SEPARATOR . $name)) {
                return $file;
            }
        }
    }

    protected function execute() {
        // Set profiler start time and memory usage and mark afterLoad in the profiler.
        JDEBUG ? JProfiler::getInstance('Application')->setStart($this->startTime, $this->startMem)->mark('afterLoad') : null;

        // Instantiate the application.
        $app = JFactory::getApplication('site');

        // Execute the application.
        $app->execute();
    }

    protected function loadFramework() {
        // Joomla system checks.
        @ini_set('magic_quotes_runtime', 0);

// System includes
        require_once JPATH_LIBRARIES . '/import.legacy.php';

// Set system error handling
        JError::setErrorHandling(E_NOTICE, 'message');
        JError::setErrorHandling(E_WARNING, 'message');
        JError::setErrorHandling(E_ERROR, 'callback', array('JError', 'customErrorPage'));

// Bootstrap the CMS libraries.
        require_once JPATH_LIBRARIES . '/cms.php';

        $version = new JVersion;

// Installation check, and check on removal of the install directory.
//        if (!file_exists(JPATH_CONFIGURATION . '/configuration.php')
//            || (filesize(JPATH_CONFIGURATION . '/configuration.php') < 10)
//            || (file_exists(JPATH_INSTALLATION . '/index.php') && (false === $version->isInDevelopmentState())))
//        {
//            if (file_exists(JPATH_INSTALLATION . '/index.php'))
//            {
//                header('Location: ' . substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], 'index.php')) . 'installation/index.php');
//
//                exit;
//            }
//            else
//            {
//                echo 'No configuration file found and no installation code available. Exiting...';
//
//                exit;
//            }
//        }

// Pre-Load configuration. Don't remove the Output Buffering due to BOM issues, see JCode 26026
        ob_start();
        require_once JPATH_CONFIGURATION . '/configuration.php';
        ob_end_clean();

// System configuration.
        $config = new JConfig;

// Set the error_reporting
        switch ($config->error_reporting)
        {
            case 'default':
            case '-1':
                break;

            case 'none':
            case '0':
                error_reporting(0);

                break;

            case 'simple':
                error_reporting(E_ERROR | E_WARNING | E_PARSE);
                ini_set('display_errors', 1);

                break;

            case 'maximum':
                error_reporting(E_ALL);
                ini_set('display_errors', 1);

                break;

            case 'development':
                error_reporting(-1);
                ini_set('display_errors', 1);

                break;

            default:
                error_reporting($config->error_reporting);
                ini_set('display_errors', 1);

                break;
        }

        define('JDEBUG', $config->debug);

        unset($config);

// System profiler
        if (JDEBUG)
        {
            // @deprecated 4.0 - The $_PROFILER global will be removed
            $_PROFILER = JProfiler::getInstance('Application');
        }
    }
}
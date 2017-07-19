<?php

namespace Sanwei\Joomla\Application;

use JApplicationCms;
use JProfiler;
use JFactory;
use Sanwei\Joomla\Uri\SiteUri;

/**
 * Class Bootstrap
 * @package Sanwei\Joomla\Application
 * @todo Use Dependency injection
 */
class Bootstrap
{
    const APPLICATION_SITE = 0;
    const APPLICATION_ADMIN = 1;
    const APPLICATION_INSTALL = 2;

    protected static $applicationNames = [
        'site',
        'administrator',
        'InstallationApplicationWeb'
    ];


    protected $loadPaths;

    /**
     * @var int
     */
    protected $applicationType;

    /**
     * @var string
     */
    protected $applicationName;

    /**
     * @var string
     */
    protected $applicationUriPath;

    /**
     * @var array
     */
    protected $requiredFilesBeforeExecute = [];

    /**
     * @var int
     */
    protected $startTime;

    /**
     * @var int
     */
    protected $startMem;

    /**
     * @param int $type
     */
    public function setApplication(int $type = Bootstrap::APPLICATION_SITE)
    {
        if (!isset(self::$applicationNames[$type])) {
            throw new \RuntimeException('Unavailable Application Type.');
        }

        define('APP_TYPE', $type);

        $this->applicationType = $type;
        $this->applicationName = self::$applicationNames[$type];
    }

    public function setApplicationUriPath(string $uriPath)
    {
        $this->applicationUriPath = $uriPath;
    }

    public function addRequiredFiles(... $files)
    {
        foreach ($files as $file) {
            if (is_string($file)) {
                $this->requiredFilesBeforeExecute[] = $file;
            } elseif (is_array($file)) {
                call_user_func_array(array($this, 'addRequiredFiles'), $file);
            }
        }
    }

    public function run()
    {
        define('_JEXEC', 1);
        $this->start();
        $this->execute();
    }

    protected function start()
    {
        $this->startTime = microtime(1);
        $this->startMem = memory_get_usage();

        foreach ($this->requiredFilesBeforeExecute as $file) {
            require_once $file;
        }
    }

    protected function execute()
    {
        // Set profiler start time and memory usage and mark afterLoad in the profiler.
        JDEBUG ? JProfiler::getInstance('Application')->setStart($this->startTime, $this->startMem)->mark('afterLoad') : null;

        /** @var JApplicationCms $app */
        // Instantiate the application.
        $app = JFactory::getApplication($this->applicationName);

        SiteUri::initializeUriInstances();

        // Execute the application.
        $app->execute();
    }

    protected function initializeApplication(JApplicationCms $app)
    {
        // @todo Load the additional configurations
        // $app->loadConfiguration();

        $app->set('site_uri', $this->applicationUriPath);
    }

    protected function findFile($name)
    {
        foreach ($this->loadPaths as $path) {
            if (file_exists($file = $path . DIRECTORY_SEPARATOR . $name)) {
                return $file;
            }
        }
    }
}
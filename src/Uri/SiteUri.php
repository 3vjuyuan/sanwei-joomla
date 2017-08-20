<?php
/**
 * Created by IntelliJ IDEA.
 * User: Weiye Sun
 * Date: 2017/7/16
 * Time: 16:20
 */

namespace Sanwei\Joomla\Uri;

use JUri;

class SiteUri extends JUri
{
    public static function initializeUriInstances() {
        // Set the root in the URI based on the application name
        JUri::root(null, rtrim(dirname(JUri::base(true)), '/\\'));


        if(empty(static::$instances)) {
        }
    }


  /**
   * Checks if the supplied URL is internal
   *
   * @param   string  $url  The URL to check.
   *
   * @return  boolean  True if Internal.
   *
   * @since   11.1
   */
  public static function isInternal($url)
  {
    $uri = static::getInstance($url);
    $base = $uri->toString(array('scheme', 'host', 'port', 'path'));
    $host = $uri->toString(array('scheme', 'host', 'port'));

    // @see JUriTest
    if (empty($host) && strpos($uri->path, 'index.php') === 0
      || !empty($host) && preg_match('#' . preg_quote(static::base(), '#') . '#', $base)
      || !empty($host) && $host === static::getInstance(static::base())->host && strpos($uri->path, 'index.php') !== false
      || !empty($host) && $base === $host && preg_match('#' . preg_quote($base, '#') . '#', static::base()))
    {
      return true;
    }

    return false;
  }


}
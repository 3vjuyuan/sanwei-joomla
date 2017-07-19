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
//        $uri = JUri::getInstance($this->get('uri.request'));   https://depianyi.dev.intern.3vjuyuan.com/backend
        // Set the root in the URI based on the application name
        JUri::root(null, rtrim(dirname(JUri::base(true)), '/\\'));


        if(empty(static::$instances)) {

        }
    }
}
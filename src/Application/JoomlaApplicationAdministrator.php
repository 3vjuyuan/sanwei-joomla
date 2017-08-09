<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Sanwei\Joomla\Application;


use Joomla\Registry\Registry;
use JApplicationAdministrator;
use JUri;
/**
 * Joomla! Administrator Application class
 *
 * @since  3.2
 */
class JoomlaApplicationAdministrator extends JApplicationAdministrator
{
  /**
   * @override
   * @todo override the loadSystemUris function in JApplicationWeb
   */
  protected function loadSystemUris($requestUri = null) {
    $uri = JUri::getInstance();
    parent::loadSystemUris();
  }

}

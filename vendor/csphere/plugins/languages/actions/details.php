<?php

/**
 * Display theme details
 *
 * PHP Version 5
 *
 * @category  Plugins
 * @package   Languages
 * @author    Hans-Joachim Piepereit <contact@csphere.eu>
 * @copyright 2013 cSphere Team
 * @license   http://opensource.org/licenses/bsd-license Simplified BSD License
 * @link      http://www.csphere.eu
 **/

$loader = \csphere\core\service\Locator::get();

$short = \csphere\core\http\Input::get('get', 'short');

// Add breadcrumb navigation
$bread = new \csphere\core\template\Breadcrumb('languages');

$bread->add('control');
$bread->add('details', 'languages/details/short/' . $short);
$bread->trace();

// Get plugin details if it exists
$meta = new \csphere\core\translation\Metadata();

$exists = $meta->exists($short);

if ($exists === true) {

    $xml = $loader->load('xml', 'language');

    $data = $xml->source('plugin', 'default', $short);

    $view = $loader->load('view');

    $view->template('languages', 'details', $data);
}
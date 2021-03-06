<?php

/**
 * List action
 *
 * PHP Version 5
 *
 * @category  Plugins
 * @package   Tags
 * @author    Hans-Joachim Piepereit <contact@csphere.eu>
 * @copyright 2013 cSphere Team
 * @license   http://opensource.org/licenses/bsd-license Simplified BSD License
 * @link      http://www.csphere.eu
 **/

 // Get RAD class for this action
$rad = new \csphere\core\rad\Listed('tags');

$rad->map('manage', 'manage');

// Define order columns
$order = array('tag_name', 'tag_since');

$rad->search(array('tag_name'), true);
$rad->delegate('tag_name', $order);
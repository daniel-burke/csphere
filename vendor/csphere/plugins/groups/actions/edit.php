<?php

/**
 * Edit action
 *
 * PHP Version 5
 *
 * @category  Plugins
 * @package   Groups
 * @author    Hans-Joachim Piepereit <contact@csphere.eu>
 * @copyright 2013 cSphere Team
 * @license   http://opensource.org/licenses/bsd-license Simplified BSD License
 * @link      http://www.csphere.eu
 **/

 // Get RAD class for this action
$rad = new \csphere\core\rad\Edit('groups');

// Delegate action
$rad->delegate();
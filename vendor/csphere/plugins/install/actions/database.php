<?php

/**
 * Database action
 *
 * PHP Version 5
 *
 * @category  Plugins
 * @package   Install
 * @author    Hans-Joachim Piepereit <contact@csphere.eu>
 * @copyright 2013 cSphere Team
 * @license   http://opensource.org/licenses/bsd-license Simplified BSD License
 * @link      http://www.csphere.eu
 **/

$loader = \csphere\core\service\Locator::get();

// Add breadcrumb navigation
$bread = new \csphere\core\template\Breadcrumb('install');

$bread->add('language');
$bread->add('database');
$bread->trace();

// Define basic stuff
$test     = false;
$db_error = null;
$data     = array();

// List of database drivers
$db_driverlist = array('pdo_sqlsrv' => 'Microsoft SQL Server / Microsoft LocalDB',
                       'pdo_mysql'  => 'MySQL / MariaDB',
                       'pdo_pgsql'  => 'PostgreSQL',
                       'pdo_sqlite' => 'SQLite');

// Get and format post data
$post           = \csphere\core\http\Input::getAll('post');
$db             = array();
$db_driver      = isset($post['database_driver']) ? $post['database_driver'] : '';
$db_driver      = isset($db_driverlist[$db_driver]) ? $db_driver : '';
$db['driver']   = empty($db_driver) ? 'pdo_mysql' : $db_driver;
$db['host']     = isset($post['database_host']) ? $post['database_host'] : '';
$db['username'] = isset($post['database_user']) ? $post['database_user'] : '';
$db['password'] = isset($post['database_pass']) ? $post['database_pass'] : '';
$db['prefix']   = isset($post['database_prefix']) ? $post['database_prefix'] : '';
$db['schema']   = isset($post['database_schema']) ? $post['database_schema'] : '';
$db['file']     = isset($post['database_file']) ? $post['database_file'] : '';

// Check if database settings are valid
if (isset($post['csphere_form'])) {

    $test = true;

    try {

        // Establish connection
        $db_test = $loader->load('database', $db['driver'], $db, true);

        // Get plugin metadata
        $meta = new \csphere\core\plugins\Metadata();

        $plugins = $meta->details();

        // Install database tables of all plugins
        foreach ($plugins AS $plugin) {

            $database = new \csphere\core\plugins\Database($plugin['short']);
            $exists   = $database->exists();

            if ($exists === true) {

                $database->uninstall();
                $database->install(true, false);
            }
        }

        // Install database data of all plugins
        foreach ($plugins AS $plugin) {

            $database = new \csphere\core\plugins\Database($plugin['short']);
            $exists   = $database->exists();

            if ($exists === true) {

                $database->install(false, true);
            }
        }

    } catch (\Exception $exception) {

        // Set error for form output
        $db_error = $exception;
    }
}

// Check if test was run with success
if ($test === true AND $db_error === null) {

    // Save database settings to session
    $session = new \csphere\core\session\Session();

    foreach ($db AS $key => $value) {

        $session->set('db_' . $key, $value);
    }

    // Show message to continue
    $previous = \csphere\core\url\Link::href('install', 'admin');
    $plugin   = \csphere\core\translation\Fetch::key('install', 'install');
    $action   = \csphere\core\translation\Fetch::key('install', 'database');
    $message  = \csphere\core\translation\Fetch::key('install', 'database_ok');

    $data = array('previous'    => $previous,
                  'type'        => 'green',
                  'plugin_name' => $plugin,
                  'action_name' => $action,
                  'message'     => $message);

    // Send data to view
    $view = $loader->load('view');

    $view->template('default', 'message', $data);

} else {

    // Check for database test errors
    $data['error'] = '';

    if (is_object($db_error)) {

        $data['error'] = $db_error->getMessage();
    }

    // Set database data
    $db['password'] = '';

    if (empty($db['prefix'])) {

        $db['prefix'] = 'csphere';
    }

    $data['database'] = $db;

    // Create database driver dropdown
    $db_list = array();

    foreach ($db_driverlist AS $driver => $name) {

        $active = $db['driver'] == $driver ? 'yes' : 'no';

        $db_list[] = array('short' => $driver, 'name' => $name, 'active' => $active);
    }

    $data['database']['drivers'] = $db_list;

    // Send data to view
    $view = $loader->load('view');

    $view->template('install', 'database', $data);
}
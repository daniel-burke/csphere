<?php

/**
 * Creates objects of a specified type using the configuration
 *
 * PHP Version 5
 *
 * @category  Core
 * @package   Service
 * @author    Hans-Joachim Piepereit <contact@csphere.eu>
 * @copyright 2013 cSphere Team
 * @license   http://opensource.org/licenses/bsd-license Simplified BSD License
 * @link      http://www.csphere.eu
 **/

namespace csphere\core\service;

/**
 * Creates objects of a specified type using the configuration
 *
 * @category  Core
 * @package   Service
 * @author    Hans-Joachim Piepereit <contact@csphere.eu>
 * @copyright 2013 cSphere Team
 * @license   http://opensource.org/licenses/bsd-license Simplified BSD License
 * @link      http://www.csphere.eu
 **/

class Loader
{
    /**
     * Array with config variables that is fetched later on
     **/
    private $_config = array();

    /**
     * Array with drivers that have already been configured
     **/
    private $_driver = array();

    /**
     * Handles object creation errors by creating a log entry
     *
     * @param string     $component Name of the core component with driver support
     * @param string     $driver    Driver name without any prefixes
     * @param \Exception $exception Exception that occured and got catched
     *
     * @return string
     **/

    private function _error($component, $driver, \Exception $exception)
    {
        $msg = 'Message: Service Container failed to load component "'
             . $component . '" with driver "' . $driver . '"' . "\n"
             . 'Exception: ' . $exception->getMessage() . "\n"
             . 'Code: ' . $exception->getCode() . "\n"
             . 'File: ' . $exception->getFile() . "\n"
             . 'Line: ' . $exception->getLine();

        return $msg;
    }

    /**
     * Tries to create the requested component driver
     *
     * @param string $component Name of the core component with driver support
     * @param string $driver    Driver name without any prefixes
     * @param array  $config    Configuration options for the driver
     *
     * @return object
     **/

    private function _container($component, $driver, array $config)
    {
        $object = null;

        // Create the destination object and return it
        $class = '\csphere\core\\' . $component . '\\driver_' . $driver;

        try {
            $object = new $class($config);
        }
        catch (\Exception $driver_error) {

            // Try to use a fallback to keep the process alive
            if ($driver != 'none') {

                $object = $this->_container($component, 'none', array());
            }

            // Log the error
            $log = ($component == 'logs') ? $object : $this->load('logs');

            $msg = $this->_error($component, $driver, $driver_error);

            $log->log('errors', $msg);
        }

        return $object;
    }

    /**
     * Get configuration
     *
     * @param array $config Array with config flags to store
     *
     * @return \csphere\core\service\Loader
     **/

    public function __construct(array $config)
    {
        // Get content of config array and store it
        $this->_config = $config;
    }

    /**
     * Service provider for core components
     *
     * @param string  $component Name of the core component with driver support
     * @param string  $driver    Driver name without any prefixes
     * @param array   $config    Configuration options for the driver
     * @param boolean $default   Set new driver as default for component
     *
     * @return object
     **/

    public function load(
        $component, $driver = '', array $config = array(), $default = false
    ) {
        // Check for configuration on empty name
        if (empty($driver)) {

            if (isset($this->_config[$component]['driver'])) {

                $driver = $this->_config[$component]['driver'];

                $config = array_merge($this->_config[$component], $config);

            } else {

                $driver = 'none';
            }
        }

        // If it is unclear which object to use create it
        $key = $component . '_driver_' . $driver;

        if (empty($this->_driver[$key])) {

            $config['driver'] = $driver;

            $this->_driver[$key] = $this->_container($component, $driver, $config);
        }

        // Store config of new default
        if ($default === true) {

            $this->_config[$component] = $config;
            $this->_config[$component]['driver'] = $driver;
        }

        return $this->_driver[$key];
    }
}
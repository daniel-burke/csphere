<?php

/**
 * Provides database connectivity
 *
 * PHP Version 5
 *
 * @category  Core
 * @package   Database
 * @author    Hans-Joachim Piepereit <contact@csphere.eu>
 * @copyright 2013 cSphere Team
 * @license   http://opensource.org/licenses/bsd-license Simplified BSD License
 * @link      http://www.csphere.eu
 **/

namespace csphere\core\database;

/**
 * Provides database connectivity
 *
 * @category  Core
 * @package   Database
 * @author    Hans-Joachim Piepereit <contact@csphere.eu>
 * @copyright 2013 cSphere Team
 * @license   http://opensource.org/licenses/bsd-license Simplified BSD License
 * @link      http://www.csphere.eu
 **/

abstract class Base extends \csphere\core\service\Drivers
{
    /**
     * Stores the database connection
     **/
    protected $con = null;

    /**
     * Stores the database prefix
     **/
    protected $prefix = '';

    /**
     * Defines the drivers function name for a random ORDER BY
     **/
    protected $random = 'RANDOM()';

    /**
     * Logs database queries
     *
     * @param string  $query The query that is executed
     * @param boolean $log   Defaults to true which enables log files if used
     *
     * @return void
     **/

    private function _log($query, $log = true)
    {
        $this->_log = $this->loader->load('logs');

        $this->_log->log('database', $query, $log);
    }

    /**
     * Check connection and transform data
     *
     * @param array $assoc Array with columns and values
     *
     * @return array
     **/

    private function _start(array $assoc)
    {
        // Establish lazy connection if not done already
        if (!is_object($this->con)) {

            $this->connect();
        }

        // Rewrite assoc array to use named placeholders
        $data = array();

        foreach ($assoc AS $key => $value) {

            $data[':' . $key] = $value;
        }

        return $data;
    }

    /**
     * Establishes the connection with the database
     *
     * @return void
     **/

    abstract protected function connect();

    /**
     * Handles errors for the database connection
     *
     * @param string $query The database query for this case
     * @param array  $assoc Array with columns and values
     * @param string $msg   The error message if already known
     * @param string $more  Append query and and data to message
     *
     * @throws \Exception
     *
     * @return void
     **/

    protected function error($query, array $assoc, $msg = '', $more = true)
    {
        // Check for error message if not provided
        if (empty($msg)) {

            $info = $this->con->errorInfo();

            $msg = isset($info[2]) ? $info[2] : $info;
        }

        $error = empty($msg) ? 'Unknown' : $msg;

        if ($more === true) {

            // Append query string and data keys
            $error .= "\n" . 'Query: ' . $query . "\n" . 'Data: Array(';

            $data = '';

            foreach ($assoc AS $key => $value) {

                $data .= '\'' . $key . '\', ';
            }

            // Clear unused var
            unset($value);

            $error .= substr($data, 0, -2) . ')';
        }

        // Throw exception
        throw new \Exception($error);
    }

    /**
     * Sends a command to the database and gets the affected rows
     *
     * @param string  $prepare  Prepared query string with placeholders
     * @param array   $assoc    Array with columns and values
     * @param boolean $replace  If more than {pre} needs to be replaced
     * @param boolean $insertid Return the last insert id instead of a rowcount
     * @param boolean $log      Defaults to true which enables log files if used
     *
     * @return integer
     **/

    public function exec(
        $prepare, array $assoc, $replace = false, $insertid = false, $log = true
    ) {
        // Check connection and transform data array
        $data = $this->_start($assoc);

        // Apply replaces if required
        if (!empty($replace)) {

            $prepare = $this->replace($prepare);
        }

        $prepare = str_replace('{pre}', $this->prefix . '_', $prepare);

        // Prepare and execute the statement
        $result = 0;

        $sth = $this->con->prepare($prepare);

        if (is_object($sth)) {

            try {

                $sth->execute($data);
            }
            catch (\PDOException $pdo_error) {

                $this->error($prepare, $assoc, $pdo_error->getMessage());
            }

            // Determine what to return
            if (empty($insertid)) {

                $result = $sth->rowCount();
            } else {

                $result = $this->con->lastInsertId();
            }
        } else {

            $this->error($prepare, $assoc);
        }

        $this->_log($prepare, $log);

        return $result;
    }

    /**
     * Returns a formatted array with statistics
     *
     * @return array
     **/

    public function info()
    {
        // Establish lazy connection if not done already
        if (!is_object($this->con)) {

            $this->connect();
        }

        // Build array with information to return
        $info = array();

        $info['client']  = $this->con->getAttribute(\PDO::ATTR_CLIENT_VERSION);
        $info['server']  = $this->con->getAttribute(\PDO::ATTR_SERVER_VERSION);
        $info['version'] = phpversion($this->config['driver']);

        $config = $this->config;

        $info['host']   = isset($config['host']) ? $config['host']: '';
        $info['schema'] = isset($config['schema']) ? $config['schema']: '';

        return $info;
    }

    /**
     * Handle PDO based transactions
     *
     * @param string $command One of these strings: begin, commit, rollback
     *
     * @return boolean
     **/

    public function transaction($command)
    {
        // Establish lazy connection if not done already
        if (!is_object($this->con)) {

            $this->connect();
        }

        // Execute requested command
        if ($command == 'commit') {

            $result = $this->con->commit();

        } elseif ($command == 'rollback') {

            $result = $this->con->rollBack();

        } else {

            $result = $this->con->beginTransaction();
        }

        return $result;
    }

    /**
     * Builds the query string part for limit and offset
     *
     * @param integer $first Number of the first dataset to show
     * @param integer $max   Number of datasets to show from first on
     *
     * @return string
     **/

    abstract protected function limits($first, $max);

    /**
     * Sends a query to the database and fetches the result
     *
     * @param string  $prepare Prepared query string with placeholders
     * @param array   $assoc   Array with columns and values
     * @param integer $first   Number of the first dataset to show
     * @param integer $max     Number of datasets to show from first on
     * @param boolean $random  Adds a driver specific ORDER BY random to the query
     *
     * @return array
     **/

    public function query(
        $prepare, array $assoc, $first = 0, $max = 1, $random = false
    ) {
        // Check connection and transform data array
        $data = $this->_start($assoc);

        // Apply replaces if required
        if (!empty($random)) {

            $prepare .= 'ORDER BY ' . $this->random;
        }

        // Attach limit vars first and max
        if ($first != 0 OR $max != 0) {

            $prepare .= ' ' . $this->limits($first, $max);
        }

        $prepare = str_replace('{pre}', $this->prefix . '_', $prepare);

        // Prepare and execute the statement
        $result = array();

        $sth = $this->con->prepare($prepare);

        if (is_object($sth)) {

            try {

                $sth->execute($data);
            }
            catch (\PDOException $pdo_error) {

                $this->error($prepare, $assoc, $pdo_error->getMessage());
            }

            // Determine what to return
            if ($max == 1) {

                $result = $sth->fetch(\PDO::FETCH_ASSOC);

                if (!is_array($result)) {

                    $result = ($result === false) ? array() : array($result);
                }

            } else {

                $result = $sth->fetchAll(\PDO::FETCH_ASSOC);
            }
        } else {

            $this->error($prepare, $assoc);
        }

        $this->_log($prepare, false);

        return $result;
    }

    /**
     * Replaces driver specific query placeholders
     *
     * @param string $replace The string to use for replaces
     *
     * @return string
     **/

    abstract protected function replace($replace);
}
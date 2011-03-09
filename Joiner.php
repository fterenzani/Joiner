<?php
/**
 * @author Francesco Terenzani <f.terenzani@gmail.com>
 * @copyright Copyright (c) 2011, Francesco Terenzani
 */

/**
 * Joiner
 *
 * This class provide the main interface to work with Joiner_Adapter instances.
 *
 * @package Joiner
 */
abstract class Joiner {

    /**
     * @var string The current path used to load libraries
     */
    static $path = '';

    /**
     * @var array A map of Joiner_Adapter instances by name.
     */
    static protected $adapters = array();

    /**
     * @var string The current key of the Joiner::$adapters map
     */
    static protected $current;

    /**
     * Set up, strore and return a new Joiner_Adapter instance
     *
     * @param string $name The name of the new Joiner_Adapter instance
     * @param string $dsn A PDO dsn
     * @param string $username
     * @param string $password
     * @param string $options PDO options
     * @return Joiner_Adapter
     */
    static function setAdapter($name, $dsn, $username = NULL, $password = NULL, $options = array()) {

        // First call, set up the default adapter and the current path
        if (!isset(self::$current)) {

            require_once self::$path . '/Adapter.php';

            self::$path = dirname(__FILE__);
            self::$current = $name;

        }

        // Load the driver's Adapter or use the generic one
        preg_match('#^[^:]+#', $dsn, $driver);
        $driver = ucfirst($driver[0]);

        if (file_exists(self::$path . '/' . $driver . '/Adapter.php')) {

            require_once self::$path . '/' . $driver . '/Adapter.php';
            $class = 'Joiner_' . ucfirst($driver) . '_Adapter';

        } else {

            $class = 'Joiner_Adapter';

        }
        $adapter = new $class($dsn, $username, $password, $options);

        // Store the adapter
        self::$adapters[$name] = $adapter;

        return $adapter;


    }

    /**
     * Set the current adapter. The current adapter is the one that is returned
     * when Joiner::getAdapter() is called without the first parameter.
     *
     * @param string $name An adapter name
     */
    static function setCurrentAdapter($name) {

        self::$current = $name;

    }


    /**
     * Get a previously defined adapter by name. If the name is omitted, it
     * return the current adapter.
     *
     * @param string $name An adapter name
     * @throws Exception if the name don't match
     * @return Joiner_Adapter
     */
    static function getAdapter($name = NULL) {

        if (!$name)
            $name = self::$current;

        if (isset(self::$adapters[$name])) {
            return self::$adapters[$name];

        } else {
            throw new Exception("No adapter \"$name\" found");

        }

    }

}

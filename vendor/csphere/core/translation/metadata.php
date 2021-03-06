<?php

/**
 * Collects the important data from all translations for a central registry
 *
 * PHP Version 5
 *
 * @category  Core
 * @package   Translation
 * @author    Hans-Joachim Piepereit <contact@csphere.eu>
 * @copyright 2013 cSphere Team
 * @license   http://opensource.org/licenses/bsd-license Simplified BSD License
 * @link      http://www.csphere.eu
 **/

namespace csphere\core\translation;

/**
 * Collects the important data from all translations for a central registry
 *
 * @category  Core
 * @package   Translation
 * @author    Hans-Joachim Piepereit <contact@csphere.eu>
 * @copyright 2013 cSphere Team
 * @license   http://opensource.org/licenses/bsd-license Simplified BSD License
 * @link      http://www.csphere.eu
 **/

class Metadata extends \csphere\core\xml\Metadata
{
    /**
     * Type of registry
     **/
    protected $driver = 'language';

    /**
     * Generate registry from XML files
     *
     * @return array
    **/

    protected function generate()
    {
        // Try to load metadata from cache
        $key = 'metadata_' . $this->driver;
        $reg = $this->cache->load($key);

        // If cache loading fails load it and create cache file
        if ($reg == false) {

            // Get a directory listing of all default languages
            $origin = $this->path . 'csphere/plugins/default/languages';
            $files  = \csphere\core\files\File::search($origin);

            // Load xml content into an array
            $xml = $this->loader->load('xml', 'language');
            $reg = array();

            foreach ($files AS $file) {

                $lang   = explode('.xml', $file);
                $source = $xml->source('plugin', 'default', $lang[0]);

                // Definitions are just wasting space here
                unset($source['definitions']);

                $reg[$lang[0]] = $source;
            }

            $this->cache->save($key, $reg);
        }

        return $reg;
    }

    /**
     * List all language names with their short tag
     *
     * @return array
    **/

    public function names()
    {
        // Create a list of languages
        $reg   = $this->generate();
        $names = array();

        foreach ($reg AS $short => $info) {

            $names[$info['name']] = array('name'     => $info['name'],
                                          'short'    => $short,
                                          'icon_url' => $info['icon']['url']);
        }

        ksort($names);

        $names = array_values($names);

        return $names;
    }
}
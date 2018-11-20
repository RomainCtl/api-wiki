<?php

/**
 * Config
 *
 * Object to get content of config file.
 * This class using Singleton pattern.
 */
class Config {

    public static $config=null;
    private $config_file;

    /**
     * Constructor
     */
    private function __construct(){
        $this->config_file = "../src/utils/config.json";
    }

    /**
     * Function specific to Singleton pattern
     * @return object instance
     */
    public static function getInstance(){
        if(is_null(self::$config))
            self::$config = new Config();
        return self::$config;
    }

    /**
     * Get config properties
     * @return false if file or propertie not fond
     * @return array with propertie => value
     */
    public function get(array $args){
        $content = file_get_contents($this->config_file);
        if (!$content)
            return false;
        $json = json_decode($content, true);

        $res = array();
        foreach ($args as $a){
            if (!array_key_exists($a, $json)) return false;
            $res[$a] = $json[$a];
        }
        return $res;
    }
}
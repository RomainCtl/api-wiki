<?php

class Config {

    public static $config=null;
    private $config_file;

    private function __construct(){
        /**
         * Constructor
         */
        $this->config_file = "./src/utils/config.json";
    }

    public static function getInstance(){
        /**
         * Function specific to Singleton pattern
         * @return object instance
         */
        if(is_null(self::$config))
            self::$config = new Config();
        return self::$config;
    }

    public function get(array $args){
        /**
         * Get config properties
         * @return false if file or propertie not fond
         * @return array with propertie => value
         */
        $content = file_get_contents($this->config_file);
        if (!$content)
            return false;
        $json = json_decode($content, true);

        $res = array();
        foreach ($args as $a){
            if (!in_array($a, $json)) return false;
            $res[$a] = $json[$a];
        }
        return $res;
    }
}
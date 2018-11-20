<?php

require "../src/resources/Config.php";

/**
 * Register
 *
 * Object to read and write content of register file.
 * This class using Singleton pattern.
 */
class Register {

    public static $register=null;
    private $register_file, $config;

    /**
     * Constructor
     */
    private function __construct(){
        $this->config = Config::getInstance();
        $this->register_file = $this->config->get(array("data_path"))['data_path']."register.json";
        if (!file_exists($this->register_file)) $this->set_default();
    }

    /**
     * Function specific to Singleton pattern
     * @return object instance
     */
    public static function getInstance(){
        if(is_null(self::$register))
            self::$register = new Register();
        return self::$register;
    }

    /**
     * Write content on register file
     * @param array $content to write
     * @return bool true or false
     */
    public function write(array $content){
        $fp = fopen($this->register_file, 'w');
        return $fp && fwrite($fp, json_encode($content)) && fclose($fp);
    }

    /**
     * Read the register file
     * @return false if file not fond
     * @return json_decode
     */
    public function read(){
        $content = file_get_contents($this->register_file);
        if (!$content)
            return false;
        return json_decode($content, true);
    }

    /**
     * Create register.json default file
     *
     * @throws Exception if can't create file
     */
    private function set_default(){
        $config = $this->config->get(array("data_path", "father_file", "father_default_content"));
        $fp = fopen($config['data_path'].$config['father_file'].".md", "w");
        if (!($fp && fwrite($fp, $config['father_default_content']) && fclose($fp)) || !$this->write(array("home" => array(
            "__me__"=> array(
                "filename"=> $config['father_file'],
                "author"=> "Admin",
                "last_edit_date"=> date("Y-m-d")."T".date("H:i:s.v")."Z",
                "create_date"=> date("Y-m-d")."T".date("H:i:s.v")."Z"
            )
        )))) throw new Exception("Error ! Unable to create register file !");
    }
}
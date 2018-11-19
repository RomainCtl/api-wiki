<?php

require "../src/resources/Config.php";

class Register {

    public static $register=null;
    private $register_file, $config;

    private function __construct(){
        /**
         * Constructor
         */
        $this->config = Config::getInstance();
        $this->register_file = $this->config->get(array("data_path"))['data_path']."register.json";
        if (!file_exists($this->register_file)) $this->set_default();
    }

    public static function getInstance(){
        /**
         * Function specific to Singleton pattern
         * @return object instance
         */
        if(is_null(self::$register))
            self::$register = new Register();
        return self::$register;
    }

    public function write(array $content){
        /**
         * Write content on register file
         * @param array $content to write
         * @return bool true or false
         */
        $fp = fopen($this->register_file, 'w');
        return $fp && fwrite($fp, json_encode($content)) && fclose($fp);
    }

    public function read(){
        /**
         * Read the register file
         * @return false if file not fond
         * @return json_decode
         */
        $content = file_get_contents($this->register_file);
        if (!$content)
            return false;
        return json_decode($content, true);
    }

    private function set_default(){
        /**
         * Create register.json default file
         *
         * @throws Exception if can't create file
         */
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
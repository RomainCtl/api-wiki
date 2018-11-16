<?php

class Register {

    public static $register=null;
    private $register_file;

    private function __construct(){
        /**
         * Constructor
         */
        $this->register_file = "../data/register.json";
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

    public function write(string $content){
        /**
         * Write content on register file
         * @param string $content to write
         * @return array(true or false, message)
         */
        $fp = fopen($this->register_file, 'w');
        if (!$fp)
            return array(false,"Error opening register file !");
        if (fwrite($fp, json_encode($content)))
            return array(false,"Error writing register file !");
        if (fclose($fp))
            return array(false,"Error closing register file !");
        return array(true,"Successfully writes");
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
}
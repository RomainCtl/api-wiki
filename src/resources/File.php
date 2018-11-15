<?php

require "./src/resources/Register.php";
require "./src/resources/Config.php";

class File {

    private $register, $config;

    public function __construct(){
        $this->register = Register::getInstance();
        $this->config = Config::getInstance();
    }

    public function get(string $filename, int $nb=3){
        $content = $this->register->read();
    }

    public function post(array $data){
        // TODO check array
        $max = $this->config->get(array("max_child")); // max path size
    }

    public function put(string $filename, array $data){}

    public function delete(string $filename){
        // TODO if file have children ? Error or not ?
        // TODO delete md file too
    }
}

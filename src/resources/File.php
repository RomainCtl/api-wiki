<?php

require "../src/resources/Register.php";

class File {

    private $register, $config, $data_path;

    public function __construct(){
        $this->register = Register::getInstance();
        $this->config = Config::getInstance();
        $this->data_path = $this->config->get(array("data_path"))['data_path'];
    }

    public function get(string $filename_path, int $nb=3){
        $content = $this->register->read();
        $path = explode("/",strtolower($filename_path));
        $last = $content;
        foreach ($path as $p){
            if (array_key_exists($p, $last))
                $last = $last[$p];
            else return array(array("message" => "Error ! Unknown file name"), 404);
        }
        $last["__me__"]["path"] = $filename_path;
        return $this->read($last["__me__"]);
    }

    public function post(array $data){
        $required = array("name", "parents");
        $d = array_keys($data);
        if (sort($required) != sort($d)) return array(array("message"=> "Bad attribute"), 400);

        $max_path_size = $this->config->get(array("max_child"))['max_child'];
        $content = $this->register->read();
        $path = explode("/",$data['parents']);

        if (count($path) > $max_path_size) return array(array("message"=> "Max path size achieved"), 403);

        $filename = $this->generate_filename();
        $file = array(
            "__me__" => array(
                "filename"=> $filename,
                "author"=> "Anonymous", /** @todo auth feature */
                "last_edit_date"=> date("Y-m-d")."T".date("H:i:s.v")."Z",
                "create_date"=> date("Y-m-d")."T".date("H:i:s.v")."Z"
            )
        );
        try {
            $content = $this->add_child($content, strtolower($data['name']), $file, $path);
        } catch (Exception $e) {
            return array(array("message"=> $e->getMessage()), $e->getCode());
        }

        $md = "# ".ucfirst(strtolower($data['name']));
        echo $filename."==\n";
        if (!$this->write($filename, $md) || !$this->register->write($content))
            return array(array("message"=> "Error creation file"), 500);
        else {
            $file['__me__']['content'] = $md;
            unset($file['__me__']['filename']);
            return array($file['__me__'], 201);
        }
    }

    public function put(string $filename, array $data){
        // can be name or|and content

        if (!file_exists("")) return array(array("message"=> "Error, unknown file name"), 404);
        /** @todo write content etc... */
    }

    public function delete(string $filename){
        /**
         * @todo if file have children ? Error or not ?
         * @todo delete md file too
         */
    }

    private function add_child(array $f, string $name, array $child, array $path){
        /**
         * Recursive function to add child
         * @param array $f register content
         * @param string $name name
         * @param array $child '__me__' child element
         * @param array $path list of parents
         *
         * @throws InvalidArgumentException if path var has any unknown element
         * @throws OverflowException if name already exist
         *
         * @return array $f register content with new child
         */
        $path0 = array_shift($path);
        if (!array_key_exists($path0, $f)) throw new InvalidArgumentException("Error ! Unknown path !", 404);
        if (count($path) == 0){
            if (array_key_exists($name, $f[$path0])) throw new OverflowException("Error ! File already exist !", 409);
            $f[$path0][$name] = $child;
        } else
            $f[$path0] = $this->add_child($f[$path0], $name, $child, $path);
        return $f;
    }

    private function write(string $name, string $content, string $mode = "w"){
        /**
         * Write content on file
         * @param string $name filename
         * @param string $content to write
         * @return boolean true or false
         */
        $fp = fopen($this->data_path.$name.".md", $mode);
        return $fp && fwrite($fp, $content) && fclose($fp);
    }

    private function read(array $file){
        /**
         * Read file
         * @param array $file file object
         * @return false if file not fond
         * @return array content
         */
        $file['content'] = file_get_contents($this->data_path.$file["filename"].".md");
        unset($file["filename"]);
        return (!$file['content']) ? array(array("message" => "Error ! Unknown file name"), 404) : array($file, 200);
    }

    private function generate_filename(){
        /**
         * Generate file name id
         * @return string
         */
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        return "WIKI-".date("Y-m-d")."-".substr(str_shuffle($chars), 0, 8);
    }
}

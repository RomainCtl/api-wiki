<?php

require "../src/resources/Register.php";
require "../src/utils/main.php";

/**
 * File
 *
 * Object to execute api routes about files.
 */
class File {

    private $register, $config, $data_path;

    /**
     * Constructor
     * Get Config & Register Instance and init $data_path var
     */
    public function __construct(){
        $this->register = Register::getInstance();
        $this->config = Config::getInstance();
        $this->data_path = $this->config->get(array("data_path"))['data_path'];
    }

    /**
     * get content of file
     *
     * @param string $filename_path path to file
     * @param bool $unset_filename true if it need to delete filename on return
     *
     * @return array(content, status_code)
     */
    public function get(string $filename_path, bool $unset_filename=true){
        $content = $this->register->read();
        $path = explode("/",strtolower($filename_path));
        $last = $content;
        foreach ($path as $p){
            if (array_key_exists($p, $last))
                $last = $last[$p];
            else return array(array("message" => "Error ! Unknown file name"), 404);
        }
        $last["__me__"]["path"] = $filename_path;
        return $this->read($last["__me__"], $unset_filename);
    }

    /**
     * get all child path from $filename_path
     *
     * @param string $filename_path
     * @param int $nb number of child returned
     *
     * @return array(content, status_code)
     */
    public function get_list(string $filename_path, int $nb=NULL){
        if (!isset($nb)) $nb = $this->config->get(array('max_child'))['max_child'];
        $content = $this->register->read();
        $path = explode("/",strtolower($filename_path));
        $last = $content;
        foreach ($path as $p){
            if (array_key_exists($p, $last))
                $last = $last[$p];
            else return array(array("message" => "Error ! Unknown file name"), 404);
        }
        $res = $last['__me__'];
        $res["path"] = $filename_path;
        $res['child_paths'] = $this->get_path($last, $filename_path, $nb);
        unset($res["filename"]);
        return array($res, 200);
    }

    /**
     * Create file, insert into register and create .md
     *
     * @param array $data with name : filename & parents : parents path
     *
     * @return array(content, status_code)
     */
    public function post(array $data){
        $required = array("name", "parents");
        if (!check_input($required, array_keys($data))) return array(array("message"=> "Bad attribute"), 400);

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
            $content = $this->add_child($content, strtolower($data['name']), $file, $path, function($f, $me, $name, $last_name){
                if (array_key_exists($name, $f)) throw new OverflowException("Error ! File already exist !", 409);
                $f[$name] = $me;
                return $f;
            });
        } catch (Exception $e) {
            return array(array("message"=> $e->getMessage()), $e->getCode());
        }

        $md = "# ".ucfirst(strtolower($data['name']));
        if (!$this->write($filename, $md) || !$this->register->write($content))
            return array(array("message"=> "Error creation file"), 500);
        else {
            $file['__me__']['content'] = $md;
            unset($file['__me__']['filename']);
            return array($file['__me__'], 201);
        }
    }

    /**
     * Edit md file content or file name
     *
     * @param string $filename_path path
     * @param array $data with content & name attribute
     *
     * @return array(content, status_code)
     */
    public function put(string $filename_path, array $data){
        $required = array("content", "name");
        if (!check_input($required, array_keys($data))) return array(array("message"=> "Bad attribute"), 400);

        $file = $this->get($filename_path, false)[0];

        if (!file_exists($this->data_path.$file['filename'].".md")) return array(array("message"=> "Error, unknown file name"), 404);

        if ($data['content'] != $file['content'] && !$this->write($file['filename'], $data['content']))
            return array(array("message"=>"Error editing file"), 500);

        $path = explode("/", $filename_path);
        $file["last_edit_date"] = date("Y-m-d")."T".date("H:i:s.v")."Z";
        $file['content'] = $data['content'];

        $npath = $path;
        $npath[count($npath)-1] = strtolower($data['name']);
        $file['path'] = join("/", $npath);
        $last_name = $path[count($path)-1];
        unset($path[count($path)-1]);

        $content = $this->register->read();
        try {
            $content = $this->add_child($content, strtolower($data['name']), $file, $path, function($f, $me, $name, $last_name){
                unset($me['content']);
                $tmpme = $f[$last_name];
                $tmpme['__me__']['last_edit_date'] = $me['last_edit_date'];
                unset($f[$last_name]);
                unset($tmpme['path']);
                $f[$name] = $tmpme;
                return $f;
            }, $last_name);
        } catch (Exception $e) {
            return array(array("message"=> $e->getMessage()), $e->getCode());
        }
        if (!$this->register->write($content))
            return array(array("message" => "Error editing register"), 500);

        unset($file["filename"]);
        return array($file, 200);
    }

    /**
     * Delete file
     *
     * @param string $filename_path path
     *
     * @return array(content, status_code)
     */
    public function delete(string $filename_path){
        $file = $this->get($filename_path, false)[0];

        if (!file_exists($this->data_path.$file['filename'].".md")) return array(array("message"=> "Error, unknown file name"), 404);

        $content = $this->register->read();
        $path = explode("/",$filename_path);
        $name = $path[count($path)-1];
        unset($path[count($path)-1]);
        try {
            $content = $this->add_child($content, $name, $file, $path, function($f, $me, $name, $last_name){
                if (count($f[$name]) > 1) throw new LengthException("Error ! File has some childs !", 403);
                unset($f[$name]);
                return $f;
            });
        } catch (Exception $e) {
            return array(array("message"=> $e->getMessage()), $e->getCode());
        }
        if (!$this->register->write($content))
            return array(array("message" => "Error editing register"), 500);

        if (!unlink($this->data_path.$file['filename'].".md")) return array(array("message"=> "Error, failure to delete !"), 500);

        return array(array("message"=> "Successfull delete !"), 205);
    }

    /**
     * Recursive function to make action on child
     * @param array $f register content
     * @param string $name name
     * @param array $child '__me__' child element
     * @param array $path list of parents
     * @param function $func action todo
     * @param string $last_name name to change
     *
     * @throws InvalidArgumentException if path var has any unknown element
     *
     * @return array $f register content with new child
     */
    private function add_child(array $f, string $name, array $child, array $path, $func, string $last_name = null) {
        $path0 = array_shift($path);
        if (!array_key_exists($path0, $f)) throw new InvalidArgumentException("Error ! Unknown path !", 404);
        if (count($path) == 0)
            $f[$path0] = $func($f[$path0], $child, $name, $last_name);
        else
            $f[$path0] = $this->add_child($f[$path0], $name, $child, $path, $func, $last_name);
        return $f;
    }

    /**
     * Get all path from dict
     *
     * @param array $f object where we get path (keys)
     * @param string father's path
     * @param int $nb number of child
     *
     * @return array wit all childs path
     */
    private function get_path(array $f, string $par_path, int $nb){
        $res = array();
        $keys = array_keys($f);
        foreach ($keys as $k) {
            if ($nb > 0 && $k != "__me__"){
                array_push($res, $par_path."/".$k);
                if (count(array_keys($f[$k])) > 1)
                    $res =array_merge($res, $this->get_path($f[$k], $par_path."/".$k, $nb-1));
            }
        }
        return $res;
    }

    /**
     * Write content on file
     * @param string $name filename
     * @param string $content to write
     * @return bool true or false
     */
    private function write(string $name, string $content, string $mode = "w"){
        $fp = fopen($this->data_path.$name.".md", $mode);
        return $fp && fwrite($fp, $content) && fclose($fp);
    }

    /**
     * Read file
     * @param array $file file object
     * @param bool $unset_filename
     * @return false if file not fond
     * @return array content
     */
    private function read(array $file, $unset_filename=true){
        $file['content'] = file_get_contents($this->data_path.$file["filename"].".md");
        if ($unset_filename) unset($file["filename"]);
        return (!$file['content']) ? array(array("message" => "Error ! Unknown file name"), 404) : array($file, 200);
    }

    /**
     * Generate file name id
     * @return string
     */
    private function generate_filename(){
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        return "WIKI-".date("Y-m-d")."-".substr(str_shuffle($chars), 0, 8);
    }
}

<?php

if (!function_exists("check_input")) {
    /**
     * Check difference between two array
     *
     * @param array $required required attribute
     * @param array $data_keys gived attribute
     *
     * @return bool true if $required == $data_keys else false
     */
    function check_input(array $required, array $data_keys){
        return array_diff($required, $data_keys) == array_diff($data_keys, $required);
    }
}

if (!function_exists("create_response_file")) {
    /**
     * Exec File methods and display info / warning / error from api request
     *
     * @param Object $app Slim app instance
     * @param string $methods GET / POSt / PUT / DELETE
     * @param array $params $func parameters
     *
     * @return static responseWithJson
     */
    function create_response_file($app, string $methods, array $params){
        $app->logger->info($methods." ".json_encode($params, JSON_UNESCAPED_SLASHES));
        include "../src/resources/File.php";
        $file = new File();
        $res = call_user_func_array(array($file, strtolower($methods)), $params);
        if ($res[1] % 500 < 100)
            $app->logger->error("[".$res[1]."] ".$res[0]['message']);
        else if ($res[1] % 400 < 100)
            $app->logger->warning("[".$res[1]."] ".$res[0]['message']);
        else
            $app->logger->info("[".$res[1]."] ".(array_key_exists('message', $res) ? $res[0]['message'] : ""));

        return call_user_func_array(
            array($app->response, "withJson"),
            array_merge($res, array(JSON_UNESCAPED_SLASHES))
        );
    }
}
<?php

if (!function_exists("check_input")) {
    function check_input(array $required, array $data_keys){
        /**
         * Check difference between two array
         *
         * @param array $required required attribute
         * @param array $data_keys gived attribute
         *
         * @return bool true if $required == $data_keys else false
         */
        return array_diff($required, $data_keys) == array_diff($data_keys, $required);
    }
}

if (!function_exists("api_logger")) {
    function api_logger($app, string $methods, array $params){ //, $func){
        /**
         * Display info / warning / error front api request
         *
         * @param Object $app Slim app instance
         * @param string $methods GET / POSt / PUT / DELETE
         * @param array $params $func parameters
         * @param function $func to execute
         *
         * @return array $res result of $func
         */
        $app->logger->info($methods." ".json_encode($params));
        include "../src/resources/File.php";
        $file = new File();
        $res = call_user_func_array(array($file, strtolower($methods)), $params);
        // $res = $func($params);
        if ($res[1] % 500 < 100)
            $app->logger->error("[".$res[1]."] ".$res[0]['message']);
        else if ($res[1] % 400 < 100)
            $app->logger->warning("[".$res[1]."] ".$res[0]['message']);
        else
            $app->logger->info("[".$res[1]."] ".(array_key_exists('message', $res) ? $res[0]['message'] : ""));

        return $res;
    }
}
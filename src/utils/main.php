<?php

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
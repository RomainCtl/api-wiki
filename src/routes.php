<?php

use Slim\Http\Request;
use Slim\Http\Response;

require "../src/utils/main.php";

// Routes

// GET File
$app->get('/file', function(Request $request, Response $response){
    return call_user_func_array(
        array($this->response, "withJson"),
        api_logger($this, "GET", array("home"))
    );
});
$app->get('/file/{filename_path}', function(Request $request, Response $response, array $args){
    return call_user_func_array(
        array($this->response, "withJson"),
        api_logger($this, "GET", $args)
    );
});
$app->get('/file/{filename_path}/childs/{nb}', function(Request $request, Response $response, array $args){
    return call_user_func_array(
        array($this->response, "withJson"),
        api_logger($this, "GET", $args)
    );
});

// POST File
$app->post('/file', function(Request $request, Response $response){
    $input = $request->getParsedBody();
    return call_user_func_array(
        array($this->response, "withJson"),
        api_logger($this, "POST", array($input))
    );
});

// PUT File
$app->put('/file/{filename_path}', function(Request $request, Response $response, array $args){
    $input = $request->getParsedBody();
    return call_user_func_array(
        array($this->response, "withJson"),
        api_logger($this, "PUT", array($args['filename_path'], $input))
    );
});

// DELETE File
$app->delete('/file/{filename_path}', function(Request $request, Response $response, array $args){
    return call_user_func_array(
        array($this->response, "withJson"),
        api_logger($this, "DELETE", $args)
    );
});
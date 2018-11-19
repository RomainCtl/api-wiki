<?php

use Slim\Http\Request;
use Slim\Http\Response;

require "../src/resources/File.php";

// Routes

// GET File
$app->get('/file', function(Request $request, Response $response){
    $file = new File();
    return call_user_func_array(array($this->response, "withJson"), $file->get("home"));
});
$app->get('/file/{filename_path}', function(Request $request, Response $response, array $args){
    $file = new File();
    $this->logger->info($args['filename_path']);
    return call_user_func_array(array($this->response, "withJson"), $file->get($args['filename_path']));
});
$app->get('/file/{filename_path}/childs/{nb}', function(Request $request, Response $response, array $args){
    $file = new File();
    return call_user_func_array(array($this->response, "withJson"), $file->get($args['filename_path'], $args['nb']));
});

// POST File
$app->post('/file', function(Request $request, Response $response){
    $input = $request->getParsedBody();
    $file = new File();
    return call_user_func_array(array($this->response, "withJson"), $file->post($input));
});

// PUT File
$app->put('/file/{filename_path}', function(Request $request, Response $response, array $args){
    $input = $request->getParsedBody();
    $file = new File();
    return call_user_func_array(array($this->response, "withJson"), $file->put($args['filename_path'], $input));
});

// DELETE File
$app->delete('/file/{filename_path}', function(Request $request, Response $response, array $args){
    $file = new File();
    return call_user_func_array(array($this->response, "withJson"), $file->delete($args['filename_path']));
});
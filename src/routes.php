<?php

use Slim\Http\Request;
use Slim\Http\Response;

require "../src/utils/main.php";

// Routes

// GET File
$app->get('/file', function(Request $request, Response $response){
    return create_response_file($this, "GET", array('home'));
});
$app->get('/file/{filename_path}', function(Request $request, Response $response, array $args){
    return create_response_file($this, "GET", $args);
});
$app->get('/file/list/{filename_path}', function(Request $request, Response $response, array $args){
    return create_response_file($this, "GET_LIST", $args);
});
$app->get('/file/list/{filename_path}/limit/{nb}', function(Request $request, Response $response, array $args){
    return create_response_file($this, "GET_LIST", $args);
});

// POST File
$app->post('/file', function(Request $request, Response $response){
    $input = $request->getParsedBody();
    return create_response_file($this, "POST", array($input));
});

// PUT File
$app->put('/file/{filename_path}', function(Request $request, Response $response, array $args){
    $input = $request->getParsedBody();
    return create_response_file($this, "PUT", array($args['filename_path'], $input));
});

// DELETE File
$app->delete('/file/{filename_path}', function(Request $request, Response $response, array $args){
    return create_response_file($this, "DELETE", $args);
});

// Allow Cross Origin
$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
            ->withHeader('Access-Control-Allow-Origin', 'http://localhost:3000')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});
<?php

use Slim\Http\Request;
use Slim\Http\Response;

require "./src/resources/File.php";

// Routes

$app->get('/[{name}]', function (Request $request, Response $response, array $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    // Render index view
    return $this->renderer->render($response, 'index.phtml', $args);
});

$app->get('/api/file', function(Request $request, Response $response, array $args){
    $file = new File();
    return $this->response->withJson($file->get("WIKI-father"));
});
$app->get('/api/file/{filename}', function(Request $request, Response $response, array $args){
    $file = new File();
    return $this->response->withJson($file->get($args['filename']));
});
$app->get('/api/file/{filename}/childs/{nb}', function(Request $request, Response $response, array $args){
    $file = new File();
    return $this->response->withJson($file->get($args['filename'], $args['nb']));
});

$app->post('/api/file', function(Request $request, Response $response){
    $input = $request->getParsedBody();
    // $this->logger->addInfo(var_dump($input));
    $file = new File();
    return $this->response->withJson($file->post($input));
});

$app->put('/api/file/{filename}', function(Request $request, Response $response, array $args){});

$app->delete('/api/file/{filename}', function(Request $request, Response $response, array $args){});
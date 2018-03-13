<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

$app->get('/', function (Request $request, Response $response, array $args) {
    // Sample log message
    $this->logger->info("'/' route");

    // Render index view
    return $this->renderer->render($response, 'index.phtml', $args);
});

$container = $app->getContainer();
$container['upload_directory'] = __DIR__ . '/uploads';

$app->post('/', function (Request $request, Response $response) {
    $this->logger->info("'/' file uploaded");

    $directory = $this->get('upload_directory');

    $uploadedFiles = $request->getUploadedFiles();
    $uploadedFile = $uploadedFiles['image'];

    if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
        var_dump($uploadedFile);
    }
});

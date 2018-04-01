<?php

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\UploadedFile;

$container = $app->getContainer();
$container['upload_directory'] = __DIR__ . '/uploads';

$app->get('/', function (Request $request, Response $response, array $args) {
    return $this->renderer->render($response, 'index.phtml', $args);
});

$app->post('/', function (Request $request, Response $response) {
    $uploadedFiles = $request->getUploadedFiles();

    if (empty($uploadedFiles['image'])) {
        $this->renderer->render($response, 'index.phtml', ['error' =>
            'You need to upload a file first!']);
    }

    $uploadedFile = $uploadedFiles['image'];
    if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
        $result = translateToLatex($uploadedFile);
        $this->renderer->render($response, 'index.phtml', $result);
    } else {
        $this->renderer->render($response, 'index.phtml', ['error' =>
            'An error has occured while uploading the file, please try again.']);
    }
});

/**
 * curl -X POST https://api.mathpix.com/v3/latex \
 * -H 'app_id: trial' \
 * -H 'app_key: 34f1a4cea0eaca8540c95908b4dc84ab' \
 * -H 'Content-Type: application/json' \
 * --data '{ "src": "data:image/jpeg;base64,'$(base64 -i limit.jpg)'" }'
 * @param UploadedFile $image
 * @return array
 */
function translateToLatex(UploadedFile $image)
{
    $SECRETS = require __DIR__ . '/../secrets.php';

    $api = new RestClient([
        'base_url' => "https://api.mathpix.com/v3/",
        'headers' => [
            'app_id' => $SECRETS['app_id'],
            'app_key' => $SECRETS['app_key'],
            'Content-Type' => 'application/json'
        ]
    ]);

    $fileContents = base64_encode($image->getStream()->getContents());

    $result = $api->post("latex",
        '{"src": "data:image/jpeg;base64,' . $fileContents . '" }');

	$response = $result->response;
	$latex = [];
	$error = [];
	if (!empty($response))
		$latex = json_decode($response)->latex;
	else
		$error = $result->error;
	
    return ['latex' => $latex, 'error' => $error];
}

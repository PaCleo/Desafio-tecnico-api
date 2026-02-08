<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use App\Config\Database;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

new Database();

$app = AppFactory::create();

$app->addErrorMiddleware(true, true, true);

$app->add(new \App\Middleware\LoggingMiddleware());
$app->add(new \App\Middleware\AuthMiddleware());
$app->addBodyParsingMiddleware();

$app->get('/', function (Request $request, Response $response, $args) {
    $payload = json_encode(['message' => 'API is running!']);
    $response->getBody()->write($payload);
    return $response
        ->withHeader('Content-Type', 'application/json');
});
$app->post('/login', \App\Controllers\AuthController::class . ':login');
$app->post('/register', \App\Controllers\AuthController::class . ':register');

$app->group('/api', function ($group) {
    $group->get('/associados', \App\Controllers\AssociadoController::class . ':index');
    $group->post('/associados', \App\Controllers\AssociadoController::class . ':store');
    $group->get('/associados/{id}', \App\Controllers\AssociadoController::class . ':show');
    $group->put('/associados/{id}', \App\Controllers\AssociadoController::class . ':update');
    $group->delete('/associados/{id}', \App\Controllers\AssociadoController::class . ':destroy');
});

$app->get('/docs/openapi.json', function (Request $request, Response $response) {
    $openapi = (new \OpenApi\Generator())->generate([__DIR__ . '/../src']);
    $response->getBody()->write($openapi->toJson());
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/docs', function (Request $request, Response $response) {
    $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Swagger UI</title>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css">
    <style>
        body { margin: 0; padding: 0; }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
    <script>
        window.onload = () => {
             window.ui = SwaggerUIBundle({
                 url: '/docs/openapi.json',
                 dom_id: '#swagger-ui',
             });
        };
    </script>
</body>
</html>
HTML;
    $response->getBody()->write($html);
    return $response->withHeader('Content-Type', 'text/html');
});

$app->run();

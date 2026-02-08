<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as SlimResponse;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthMiddleware
{
    private $secretKey;
    private $ignorePaths = [
        '/',
        '/login',
        '/register',
        '/docs',
        '/docs/openapi.json'
    ];

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $uri = $request->getUri()->getPath();

        // Check if path is in ignore list (or starts with /docs)
        if (in_array($uri, $this->ignorePaths) || str_starts_with($uri, '/docs')) {
            return $handler->handle($request);
        }

        $authHeader = $request->getHeaderLine('Authorization');

        if (!$authHeader) {
            return $this->unauthorized();
        }

        list($jwt) = sscanf($authHeader, 'Bearer %s');

        if (!$jwt) {
            return $this->unauthorized();
        }

        try {
            $decoded = JWT::decode($jwt, new Key($this->secretKey, 'HS256'));
            // Adicionar dados do usuário ao request
            $request = $request->withAttribute('user_id', $decoded->sub);
        } catch (\Exception $e) {
            return $this->unauthorized('Invalid Token: ' . $e->getMessage());
        }

        return $handler->handle($request);
    }

    public function __construct()
    {
        $this->secretKey = $_ENV['JWT_SECRET'] ?? 'default_fallback_secret';
    }

    private function unauthorized($message = 'Unauthorized'): Response
    {
        $response = new SlimResponse();
        $response->getBody()->write(json_encode(['error' => $message]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }
}

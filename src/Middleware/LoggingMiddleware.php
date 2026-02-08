<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use App\Models\Log;

class LoggingMiddleware
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $response = $handler->handle($request);

        $method = $request->getMethod();
        $uri = (string) $request->getUri();
        $statusCode = $response->getStatusCode();
        $userId = $request->getAttribute('user_id');

        $details = null;
        if (in_array($method, ['POST', 'PUT'])) {
            $body = $request->getParsedBody();
            if (isset($body['password'])) {
                $body['password'] = '********';
            }
            $details = json_encode($body);
        }

        try {
            Log::create([
                'method' => $method,
                'url' => $uri,
                'status' => $statusCode,
                'user_id' => $userId,
                'details' => $details
            ]);
        } catch (\Exception $e) {
            error_log("Failed to log request: " . $e->getMessage());
        }

        return $response;
    }
}

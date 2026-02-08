<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\User;
use Firebase\JWT\JWT;
use OpenApi\Attributes as OA;

class AuthController
{
    private $secretKey;

    public function __construct()
    {
        $this->secretKey = $_ENV['JWT_SECRET'] ?? 'default_fallback_secret';
    }

    #[OA\Post(
        path: "/login",
        summary: "Login user and return JWT token",
        tags: ["Auth"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "user@example.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "secret")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful login",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "token", type: "string")
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Invalid credentials"
            )
        ]
    )]
    public function login(Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody();
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        $user = User::where('email', $email)->first();

        if (!$user) {
            return $this->jsonResponse($response, ['error' => 'User not found'], 401);
        }

        if (!password_verify($password, $user->password)) {
            return $this->jsonResponse($response, ['error' => 'Invalid password'], 401);
        }

        $payload = [
            'iss' => 'sicredi-api',
            'sub' => $user->id,
            'iat' => time(),
            'exp' => time() + 3600 // 1 hora
        ];

        $jwt = JWT::encode($payload, $this->secretKey, 'HS256');

        return $this->jsonResponse($response, ['token' => $jwt]);
    }

    #[OA\Post(
        path: "/register",
        summary: "Register a new user",
        tags: ["Auth"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "email", "password"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "John Doe"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "newuser@example.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "secret")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "User created",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "User created"),
                        new OA\Property(property: "user_id", type: "integer")
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: "Email already exists"
            )
        ]
    )]
    public function register(Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody();
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        $name = $data['name'] ?? 'User';

        if (User::where('email', $email)->exists()) {
            return $this->jsonResponse($response, ['error' => 'Email already exists'], 400);
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT)
        ]);

        return $this->jsonResponse($response, ['message' => 'User created', 'user_id' => $user->id], 201);
    }

    private function jsonResponse(Response $response, array $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}

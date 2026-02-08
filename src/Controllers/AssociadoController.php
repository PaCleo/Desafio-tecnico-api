<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Associado;
use OpenApi\Attributes as OA;

class AssociadoController
{
    #[OA\Get(
        path: "/api/associados",
        summary: "List all Associados",
        tags: ["Associados"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "List of associados",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(ref: "#/components/schemas/Associado")
                )
            )
        ]
    )]
    public function index(Request $request, Response $response, $args)
    {
        $associados = Associado::all();
        $response->getBody()->write($associados->toJson());
        return $response->withHeader('Content-Type', 'application/json');
    }

    #[OA\Post(
        path: "/api/associados",
        summary: "Create a new Associado",
        tags: ["Associados"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/Associado")
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Associado created",
                content: new OA\JsonContent(ref: "#/components/schemas/Associado")
            ),
            new OA\Response(
                response: 400,
                description: "Validation error"
            )
        ]
    )]
    public function store(Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody();

        $errors = $this->validate($data);
        if (!empty($errors)) {
            $response->getBody()->write(json_encode(['errors' => $errors]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $associado = Associado::create($data);
            $response->getBody()->write($associado->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => 'Database error', 'details' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    #[OA\Get(
        path: "/api/associados/{id}",
        summary: "Get Associado by ID",
        tags: ["Associados"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Associado found",
                content: new OA\JsonContent(ref: "#/components/schemas/Associado")
            ),
            new OA\Response(
                response: 404,
                description: "Associado not found"
            )
        ]
    )]
    public function show(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $associado = Associado::find($id);

        if (!$associado) {
            $response->getBody()->write(json_encode(['error' => 'Associado not found']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $response->getBody()->write($associado->toJson());
        return $response->withHeader('Content-Type', 'application/json');
    }

    #[OA\Put(
        path: "/api/associados/{id}",
        summary: "Update Associado",
        tags: ["Associados"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/Associado")
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Associado updated",
                content: new OA\JsonContent(ref: "#/components/schemas/Associado")
            ),
            new OA\Response(
                response: 404,
                description: "Associado not found"
            )
        ]
    )]
    public function update(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $associado = Associado::find($id);

        if (!$associado) {
            $response->getBody()->write(json_encode(['error' => 'Associado not found']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $data = $request->getParsedBody();

        if (isset($data['cpf'])) {
            $existing = Associado::where('cpf', $data['cpf'])
                ->where('id', '!=', $id)
                ->first();

            if ($existing) {
                $response->getBody()->write(json_encode(['error' => 'CPF already exists']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
        }

        $associado->update($data);
        $response->getBody()->write($associado->toJson());
        return $response->withHeader('Content-Type', 'application/json');
    }

    #[OA\Delete(
        path: "/api/associados/{id}",
        summary: "Delete Associado",
        tags: ["Associados"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Associado deleted"
            ),
            new OA\Response(
                response: 404,
                description: "Associado not found"
            )
        ]
    )]
    public function destroy(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $associado = Associado::find($id);

        if (!$associado) {
            $response->getBody()->write(json_encode(['error' => 'Associado not found']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $associado->delete();
        $response->getBody()->write(json_encode(['message' => 'Associado deleted']));
        return $response->withHeader('Content-Type', 'application/json');
    }

    private function validate($data)
    {
        $errors = [];
        $required = ['cpf', 'nome', 'cidade', 'estado', 'email'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $errors[] = "$field is required";
            }
        }

        if (!empty($data['cpf']) && !preg_match("/^\d{3}\.?\d{3}\.?\d{3}-?\d{2}$/", $data['cpf'])) {
            $errors[] = "Invalid format for CPF";
        }

        return $errors;
    }
}

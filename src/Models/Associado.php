<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Associado",
    title: "Associado Model",
    required: ["cpf", "nome", "cidade", "estado", "email"],
    properties: [
        new OA\Property(property: "id", type: "integer"),
        new OA\Property(property: "cpf", type: "string"),
        new OA\Property(property: "nome", type: "string"),
        new OA\Property(property: "cidade", type: "string"),
        new OA\Property(property: "estado", type: "string"),
        new OA\Property(property: "telefone", type: "string"),
        new OA\Property(property: "email", type: "string", format: "email")
    ]
)]
class Associado extends Model
{
    protected $table = 'associados';
    protected $fillable = ['cpf', 'nome', 'cidade', 'estado', 'telefone', 'email'];
    public $timestamps = true;
}

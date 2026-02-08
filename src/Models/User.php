<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "User",
    title: "User Model",
    required: ["name", "email", "password"],
    properties: [
        new OA\Property(property: "id", type: "integer"),
        new OA\Property(property: "name", type: "string"),
        new OA\Property(property: "email", type: "string", format: "email"),
        new OA\Property(property: "password", type: "string", format: "password")
    ]
)]
class User extends Model
{
    protected $table = 'users';
    protected $fillable = ['name', 'email', 'password'];
    public $timestamps = true;
}

<?php

namespace App\Swagger\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "User",
    description: "Utilisateur du système",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "name", type: "string", example: "mouckaki sadibi raph oldrich"),
        new OA\Property(property: "email", type: "string", example: "raph@example.com"),
        new OA\Property(property: "role", type: "string", example: "agent"),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time"),
    ]
)]
class UserSchema {}

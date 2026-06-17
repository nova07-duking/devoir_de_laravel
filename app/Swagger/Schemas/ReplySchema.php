<?php

namespace App\Swagger\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Reply",
    description: "Réponse d’un ticket",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 12),
        new OA\Property(property: "message", type: "string", example: "Nous traitons votre demande."),
        new OA\Property(property: "user_id", type: "integer", example: 3),
        new OA\Property(property: "ticket_id", type: "integer", example: 5),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time"),
        new OA\Property(property: "user", ref: "#/components/schemas/User")
    ]
)]
class ReplySchema {}

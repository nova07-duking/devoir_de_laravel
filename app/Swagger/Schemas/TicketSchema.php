<?php

namespace App\Swagger\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Ticket",
    description: "Ticket de support",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 5),
        new OA\Property(property: "title", type: "string", example: "Problème de connexion"),
        new OA\Property(property: "message", type: "string", example: "Je n'arrive pas à me connecter."),
        new OA\Property(property: "urgency", type: "string", example: "medium"),
        new OA\Property(property: "status", type: "string", example: "open"),
        new OA\Property(property: "user_id", type: "integer", example: 1),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time"),

        new OA\Property(property: "user", ref: "#/components/schemas/User"),

        new OA\Property(
            property: "replies",
            type: "array",
            items: new OA\Items(ref: "#/components/schemas/Reply")
        )
    ]
)]
class TicketSchema {}

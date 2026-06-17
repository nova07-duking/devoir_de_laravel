<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ReplyController extends Controller
{
    #[OA\Get(
        path: "/api/tickets/{ticket}/replies",
        summary: "Lister les réponses d’un ticket",
        tags: ["Replies"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "ticket",
                in: "path",
                required: true,
                description: "ID du ticket",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste des réponses",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(ref: "#/components/schemas/Reply")
                )
            ),
            new OA\Response(response: 403, description: "Non autorisé")
        ]
    )]
    public function index(Request $request, Ticket $ticket)
    {
        $user = $request->user();

        if ($user->role !== 'agent' && $ticket->user_id !== $user->id) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        return response()->json($ticket->replies()->with('user')->get());
    }

    #[OA\Post(
        path: "/api/tickets/{ticket}/replies",
        summary: "Ajouter une réponse à un ticket",
        tags: ["Replies"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "ticket",
                in: "path",
                required: true,
                description: "ID du ticket",
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["message"],
                properties: [
                    new OA\Property(property: "message", type: "string", example: "Merci pour votre retour.")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Réponse créée",
                content: new OA\JsonContent(ref: "#/components/schemas/Reply")
            ),
            new OA\Response(response: 403, description: "Non autorisé")
        ]
    )]
    public function store(Request $request, Ticket $ticket)
    {
        $user = $request->user();

        if ($user->role !== 'agent' && $ticket->user_id !== $user->id) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $validated = $request->validate([
            'message' => 'required|string'
        ]);

        $reply = $ticket->replies()->create([
            'user_id' => $user->id,
            'message' => $validated['message']
        ]);

        return response()->json($reply, 201);
    }
}

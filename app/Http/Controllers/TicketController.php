<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class TicketController extends Controller
{
    #[OA\Get(
        path: "/api/tickets",
        summary: "Lister les tickets de l'utilisateur ou tous les tickets si agent",
        tags: ["Tickets"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste des tickets",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(ref: "#/components/schemas/Ticket")
                )
            )
        ]
    )]
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'agent') {
            $tickets = Ticket::with('user')->get();
        } else {
            $tickets = $user->tickets()->get();
        }

        return response()->json($tickets);
    }

    #[OA\Post(
        path: "/api/tickets",
        summary: "Créer un nouveau ticket",
        tags: ["Tickets"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["title", "message"],
                properties: [
                    new OA\Property(property: "title", type: "string", example: "Problème de connexion"),
                    new OA\Property(property: "message", type: "string", example: "Je n'arrive pas à me connecter."),
                    new OA\Property(property: "urgency", type: "string", enum: ["low", "medium", "high"], example: "medium")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Ticket créé",
                content: new OA\JsonContent(ref: "#/components/schemas/Ticket")
            ),
            new OA\Response(response: 422, description: "Erreur de validation")
        ]
    )]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'urgency' => 'in:low,medium,high'
        ]);

        $ticket = $request->user()->tickets()->create([
            'title' => $validated['title'],
            'message' => $validated['message'],
            'urgency' => $validated['urgency'] ?? 'low',
            'status' => 'open'
        ]);

        return response()->json($ticket, 201);
    }

    #[OA\Get(
        path: "/api/tickets/{ticket}",
        summary: "Afficher un ticket",
        tags: ["Tickets"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "ticket",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Ticket trouvé",
                content: new OA\JsonContent(ref: "#/components/schemas/Ticket")
            ),
            new OA\Response(response: 403, description: "Non autorisé"),
            new OA\Response(response: 404, description: "Ticket introuvable")
        ]
    )]
    public function show(Request $request, Ticket $ticket)
    {
        $user = $request->user();

        if ($user->role !== 'agent' && $ticket->user_id !== $user->id) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $ticket->load(['user', 'replies.user']);

        return response()->json($ticket);
    }

    #[OA\Patch(
        path: "/api/tickets/{ticket}",
        summary: "Mettre à jour le statut d’un ticket (agent uniquement)",
        tags: ["Tickets"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "ticket",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["status"],
                properties: [
                    new OA\Property(property: "status", type: "string", enum: ["open", "in_progress", "resolved", "closed"])
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Ticket mis à jour",
                content: new OA\JsonContent(ref: "#/components/schemas/Ticket")
            ),
            new OA\Response(response: 403, description: "Seuls les agents peuvent modifier le statut")
        ]
    )]
    public function update(Request $request, Ticket $ticket)
    {
        $user = $request->user();

        if ($user->role !== 'agent') {
            return response()->json(['message' => 'Seuls les agents peuvent modifier le statut'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed'
        ]);

        $ticket->update(['status' => $validated['status']]);

        return response()->json($ticket);
    }
}

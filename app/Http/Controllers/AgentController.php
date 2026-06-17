<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AgentController extends Controller
{
    protected function authorizeAgent(Request $request)
    {
        if ($request->user()?->role !== 'agent') {
            abort(response()->json(['message' => 'Non autorisé'], 403));
        }
    }

    #[OA\Get(
        path: "/api/agent/tickets",
        summary: "Liste de tous les tickets (réservé aux agents)",
        tags: ["Agent"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Liste des tickets"),
            new OA\Response(response: 403, description: "Non autorisé")
        ]
    )]
    public function tickets(Request $request)
    {
        $this->authorizeAgent($request);
        return response()->json(Ticket::with(['user', 'replies.user'])->get());
    }

    #[OA\Get(
        path: "/api/agent/tickets/{ticket}",
        summary: "Afficher un ticket",
        tags: ["Agent"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "ticket", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Ticket trouvé"),
            new OA\Response(response: 403, description: "Non autorisé"),
            new OA\Response(response: 404, description: "Ticket introuvable")
        ]
    )]
    public function show(Request $request, Ticket $ticket)
    {
        $this->authorizeAgent($request);
        $ticket->load(['user', 'replies.user']);
        return response()->json($ticket);
    }

    #[OA\Patch(
        path: "/api/agent/tickets/{ticket}/status",
        summary: "Mettre à jour le statut d’un ticket",
        tags: ["Agent"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "ticket", in: "path", required: true, schema: new OA\Schema(type: "integer"))
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
            new OA\Response(response: 200, description: "Statut mis à jour"),
            new OA\Response(response: 403, description: "Non autorisé")
        ]
    )]
    public function updateStatus(Request $request, Ticket $ticket)
    {
        $this->authorizeAgent($request);
        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed'
        ]);

        $ticket->update(['status' => $validated['status']]);
        return response()->json($ticket);
    }

    #[OA\Get(
        path: "/api/agent/tickets/{ticket}/replies",
        summary: "Liste des réponses d’un ticket",
        tags: ["Agent"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "ticket", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Liste des réponses"),
            new OA\Response(response: 403, description: "Non autorisé")
        ]
    )]
    public function replies(Request $request, Ticket $ticket)
    {
        $this->authorizeAgent($request);
        return response()->json($ticket->replies()->with('user')->get());
    }

    #[OA\Post(
        path: "/api/agent/tickets/{ticket}/reply",
        summary: "Ajouter une réponse à un ticket",
        tags: ["Agent"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "ticket", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["message"],
                properties: [
                    new OA\Property(property: "message", type: "string")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Réponse créée"),
            new OA\Response(response: 403, description: "Non autorisé")
        ]
    )]
    public function reply(Request $request, Ticket $ticket)
    {
        $this->authorizeAgent($request);
        $validated = $request->validate([
            'message' => 'required|string'
        ]);

        $reply = $ticket->replies()->create([
            'user_id' => $request->user()->id,
            'message' => $validated['message']
        ]);
        
        return response()->json($reply, 201);
    }
}


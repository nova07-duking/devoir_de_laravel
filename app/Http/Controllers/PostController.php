<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class PostController extends Controller
{
    #[OA\Get(
        path: "/api/posts",
        summary: "Récupérer tous les posts",
        tags: ["Posts"],
        responses: [
            new OA\Response(response: 200, description: "Succès")
        ]
    )]
    public function index()
    {
        return response()->json(['message' => 'Hello Swagger']);
    }
}

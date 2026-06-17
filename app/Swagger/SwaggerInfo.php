<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'API Laravel 13 - Documentation',
    version: '1.0.0',
    description: 'Documentation de l\'API générée avec Swagger'
)]
#[OA\Server(
    url: 'http://localhost:8000',
    description: 'Serveur local'
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT"
)]
class SwaggerInfo {}

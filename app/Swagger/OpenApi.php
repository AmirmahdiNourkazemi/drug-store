<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "Drug API",
    description: "مستندات API جستجوی دارو"
)]
#[OA\Server(
    url: "http://localhost:8000",
    description: "Local Server"
)]
class OpenApi {}

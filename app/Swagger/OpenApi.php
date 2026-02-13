<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "Drug API",
    description: "مستندات API جستجوی دارو"
)]
#[OA\Server(
    url: "https://drug.approagency.ir",
    description: "جستجوی دارو"
)]
class OpenApi {}

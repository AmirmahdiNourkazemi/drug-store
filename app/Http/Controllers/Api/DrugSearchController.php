<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DrugSearchRequest;
use App\Services\DrugSearchService;
use Illuminate\Http\JsonResponse;
use App\Models\DrugInfo;
use OpenApi\Attributes as OA;
class DrugSearchController extends Controller
{
    protected $drugSearchService;
    
    public function __construct()
    {
        $this->drugSearchService = new DrugSearchService();
    }
    
    #[OA\Get(
        path: "/api/drugs/search",
        operationId: "searchDrugs",
        tags: ["Drugs"],
        summary: "جستجوی دارو",
        description: "جستجوی دارو بر اساس نام با امکان فیلتر و صفحه‌بندی",
        parameters: [
            new OA\QueryParameter(
                name: "q",
                in: "query",
                required: true,
                description: "نام دارو برای جستجو",
                schema: new OA\Schema(type: "string", minLength: 2, maxLength: 100)
            ),
            new OA\QueryParameter(
                name: "per_page",
                in: "query",
                required: false,
                description: "تعداد آیتم در هر صفحه",
                schema: new OA\Schema(type: "integer", default: 20, minimum: 1, maximum: 100)
            ),
            new OA\QueryParameter(
                name: "with_relations",
                in: "query",
                required: false,
                description: "دریافت اطلاعات مرتبط (گروه‌های دارویی و درمانی)",
                schema: new OA\Schema(type: "boolean", default: false)
            ),
            new OA\QueryParameter(
                name: "goroh_daroei_cod",
                in: "query",
                required: false,
                description: "فیلتر بر اساس گروه دارویی",
                schema: new OA\Schema(type: "integer")
            ),
            new OA\QueryParameter(
                name: "goroh_darmani_cod",
                in: "query",
                required: false,
                description: "فیلتر بر اساس گروه درمانی",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "موفق - لیست داروها",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "نتایج جستجو"),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "integer", example: 1),
                                    new OA\Property(property: "name", type: "string", example: "استامینوفن"),
                                    new OA\Property(property: "brand", type: "string", example: "تیلنول"),
                                    new OA\Property(property: "dosage", type: "string", example: "500mg"),
                                    new OA\Property(
                                        property: "goroh_daroei",
                                        type: "object",
                                        nullable: true,
                                        properties: [
                                            new OA\Property(property: "id", type: "integer"),
                                            new OA\Property(property: "name", type: "string")
                                        ]
                                    )
                                ]
                            )
                        ),
                        new OA\Property(
                            property: "meta",
                            type: "object",
                            properties: [
                                new OA\Property(property: "current_page", type: "integer", example: 1),
                                new OA\Property(property: "last_page", type: "integer", example: 5),
                                new OA\Property(property: "per_page", type: "integer", example: 20),
                                new OA\Property(property: "total", type: "integer", example: 100)
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: "خطای درخواست - پارامترهای نامعتبر",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "خطا در اعتبارسنجی داده‌ها"),
                        new OA\Property(
                            property: "errors",
                            type: "object",
                            properties: [
                                new OA\Property(property: "q", type: "array", items: new OA\Items(type: "string", example: "فیلد q الزامی است"))
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "خطای داخلی سرور",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "خطا در جستجو"),
                        new OA\Property(property: "error", type: "string", example: "پیغام خطای فنی")
                    ]
                )
            )
        ]
    )]

    public function search(DrugSearchRequest $request): JsonResponse
    {
        try {
            $params = $request->validated();
            
            $query = $this->drugSearchService->search($params);
            
            if ($request->boolean('with_relations')) {
                $query->with(['gorohDaroei', 'gorohDarmani', 'gorohDarmaniDetail']);
            }
            
            $perPage = $params['per_page'] ?? 20;
            $drugs = $query->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'message' => 'نتایج جستجو',
                'data' => $drugs->items(),
                'meta' => [
                    'current_page' => $drugs->currentPage(),
                    'last_page' => $drugs->lastPage(),
                    'per_page' => $drugs->perPage(),
                    'total' => $drugs->total(),
                ],
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در جستجو',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
#[OA\Get(
    path: "/api/drugs/{cod}",
    operationId: "getDrugById",
    tags: ["Drugs"],
    summary: "دریافت اطلاعات کامل یک دارو",
    description: "دریافت اطلاعات کامل یک دارو بر اساس کد (شناسه) دارو همراه با اطلاعات مرتبط",
    parameters: [
        new OA\Parameter(
            name: "cod",
            in: "path",
            required: true,
            description: "کد (شناسه) دارو",
            schema: new OA\Schema(type: "integer", format: "int64", example: 123),
            example: 123
        )
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: "موفق - اطلاعات دارو",
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "success", type: "boolean", example: true),
                    new OA\Property(property: "message", type: "string", example: "اطلاعات دارو"),
                    new OA\Property(
                        property: "data",
                        type: "object",
                        properties: [
                            new OA\Property(property: "cod", type: "integer", example: 123),
                            new OA\Property(property: "name", type: "string", example: "استامینوفن"),
                            new OA\Property(property: "brand", type: "string", example: "تیلنول"),
                            new OA\Property(property: "generic_name", type: "string", example: "پاراستامول"),
                            new OA\Property(property: "dosage", type: "string", example: "500mg"),
                            new OA\Property(property: "form", type: "string", example: "قرص"),
                            new OA\Property(property: "description", type: "string", example: "مسکن و تب‌بر"),
                            new OA\Property(
                                property: "goroh_daroei",
                                type: "object",
                                nullable: true,
                                properties: [
                                    new OA\Property(property: "id", type: "integer", example: 1),
                                    new OA\Property(property: "name", type: "string", example: "مسکن‌ها"),
                                    new OA\Property(property: "code", type: "string", example: "A01"),
                                    new OA\Property(property: "description", type: "string", example: "گروه داروهای مسکن")
                                ]
                            ),
                            new OA\Property(
                                property: "goroh_darmani",
                                type: "object",
                                nullable: true,
                                properties: [
                                    new OA\Property(property: "id", type: "integer", example: 2),
                                    new OA\Property(property: "name", type: "string", example: "عمومی"),
                                    new OA\Property(property: "code", type: "string", example: "GEN"),
                                    new OA\Property(property: "description", type: "string", example: "داروهای عمومی")
                                ]
                            ),
                            new OA\Property(
                                property: "goroh_darmani_detail",
                                type: "object",
                                nullable: true,
                                properties: [
                                    new OA\Property(property: "id", type: "integer", example: 3),
                                    new OA\Property(property: "name", type: "string", example: "مسکن‌های غیراستروئیدی"),
                                    new OA\Property(property: "code", type: "string", example: "NSAID"),
                                    new OA\Property(property: "description", type: "string", example: "داروهای ضدالتهاب غیراستروئیدی")
                                ]
                            ),
                            new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2024-01-01T10:00:00Z"),
                            new OA\Property(property: "updated_at", type: "string", format: "date-time", example: "2024-01-01T10:00:00Z")
                        ]
                    )
                ]
            )
        ),
        new OA\Response(
            response: 404,
            description: "یافت نشد - دارو با کد مورد نظر وجود ندارد",
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "success", type: "boolean", example: false),
                    new OA\Property(property: "message", type: "string", example: "دارو یافت نشد")
                ]
            )
        ),
        new OA\Response(
            response: 500,
            description: "خطای داخلی سرور",
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "success", type: "boolean", example: false),
                    new OA\Property(property: "message", type: "string", example: "خطا در دریافت اطلاعات"),
                    new OA\Property(property: "error", type: "string", example: "پیغام خطای فنی")
                ]
            )
        )
    ]
)]
    public function show(int $cod): JsonResponse
    {
        try {
            $drug = DrugInfo::with(['gorohDaroei', 'gorohDarmani', 'gorohDarmaniDetail'])
                ->find($cod);
            
            if (!$drug) {
                return response()->json([
                    'success' => false,
                    'message' => 'دارو یافت نشد',
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'اطلاعات دارو',
                'data' => $drug,
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در دریافت اطلاعات',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    #[OA\Get(
        path: "/api/drugs/autocomplete",
        operationId: "autocompleteDrugs",
        tags: ["Drugs"],
        summary: "جستجوی سریع دارو",
        description: "جستجوی سریع دارو برای تکمیل خودکار (autocomplete)",
        parameters: [
            new OA\QueryParameter(
                name: "query",
                in: "query",
                required: true,
                description: "متن جستجو برای تکمیل خودکار",
                schema: new OA\Schema(type: "string", minLength: 2, maxLength: 50)
            ),
            new OA\QueryParameter(
                name: "limit",
                in: "query",
                required: false,
                description: "تعداد نتایج",
                schema: new OA\Schema(type: "integer", default: 10, minimum: 1, maximum: 50)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "موفق - نتایج جستجوی سریع",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "نتایج جستجوی سریع"),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "integer", example: 1),
                                    new OA\Property(property: "name", type: "string", example: "استامینوفن"),
                                    new OA\Property(property: "brand", type: "string", example: "تیلنول"),
                                    new OA\Property(property: "dosage", type: "string", example: "500mg")
                                ]
                            )
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: "خطای درخواست",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "خطا در اعتبارسنجی داده‌ها")
                    ]
                )
            )
        ]
    )]

    public function autocomplete(DrugSearchRequest $request): JsonResponse
    {
        try {
            $request->validate([
                'query' => 'required|string|min:2|max:50',
            ]);
            
            $term = $request->input('query');
            $limit = $request->input('limit', 10);
            
            $results = $this->drugSearchService->autocomplete($term, $limit);
            
            return response()->json([
                'success' => true,
                'message' => 'نتایج جستجوی سریع',
                'data' => $results,
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در جستجوی سریع',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
   #[OA\Get(
        path: "/api/drugs/goroh-daroei",
        operationId: "getGorohDaroei",
        tags: ["Drugs"],
        summary: "دریافت لیست گروه‌های دارویی",
        description: "دریافت تمامی گروه‌های دارویی موجود در سیستم",
        responses: [
            new OA\Response(
                response: 200,
                description: "موفق - لیست گروه‌های دارویی",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "لیست گروه‌های دارویی"),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "integer", example: 1),
                                    new OA\Property(property: "name", type: "string", example: "مسکن‌ها"),
                                    new OA\Property(property: "code", type: "string", example: "A01"),
                                    new OA\Property(property: "description", type: "string", example: "گروه داروهای مسکن")
                                ]
                            )
                        )
                    ]
                )
            )
        ]
    )]  
    public function getGorohDaroei(): JsonResponse
    {
        try {
            $gorohDaroei = \App\Models\GorohDaroei::all();
            
            return response()->json([
                'success' => true,
                'message' => 'لیست گروه‌های دارویی',
                'data' => $gorohDaroei,
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در دریافت گروه‌های دارویی',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    #[OA\Get(
        path: "/api/drugs/goroh-darmani",
        operationId: "getGorohDarmani",
        tags: ["Drugs"],
        summary: "دریافت لیست گروه‌های درمانی",
        description: "دریافت تمامی گروه‌های درمانی موجود در سیستم",
        responses: [
            new OA\Response(
                response: 200,
                description: "موفق - لیست گروه‌های درمانی",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "لیست گروه‌های درمانی"),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "integer", example: 1),
                                    new OA\Property(property: "name", type: "string", example: "قلبی و عروقی"),
                                    new OA\Property(property: "code", type: "string", example: "CVD"),
                                    new OA\Property(property: "description", type: "string", example: "بیماری‌های قلبی و عروقی")
                                ]
                            )
                        )
                    ]
                )
            )
        ]
    )]
    
    public function getGorohDarmani(): JsonResponse
    {
        try {
            $gorohDarmani = \App\Models\GorohDarmani::all();
            
            return response()->json([
                'success' => true,
                'message' => 'لیست گروه‌های درمانی',
                'data' => $gorohDarmani,
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در دریافت گروه‌های درمانی',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
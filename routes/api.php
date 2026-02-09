<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DrugSearchController;

// routes/web.php
Route::get('/drugs-ui', function () {
return view('drugs.index');
});
Route::get('/drugs-ui/{cod}', function ($cod) {
return view('drugs.show', compact('cod'));
});


// API Routes برای جستجوی دارو
Route::prefix('drugs')->group(function () {
    Route::get('/autocomplete', [DrugSearchController::class, 'autocomplete']);
    
    // دریافت لیست گروه‌ها برای فیلتر - باید قبل از {cod} باشد
    Route::get('/goroh-daroei', [DrugSearchController::class, 'getGorohDaroei']);
    Route::get('/goroh-darmani', [DrugSearchController::class, 'getGorohDarmani']);
    
    // جستجوی پیشرفته - در صورت استفاده از GET
    Route::get('/search', [DrugSearchController::class, 'search']);  // از POST به GET تغییر دهید
    
    // دریافت اطلاعات یک دارو - باید در انتها باشد
    Route::get('/{cod}', [DrugSearchController::class, 'show']);
    

});

// برای تست API
Route::get('/test', function () {
    return response()->json([
        'message' => 'Drug Store API is working!',
        'version' => '1.0.0',
        'endpoints' => [
            'POST /api/drugs/search' => 'جستجوی پیشرفته داروها',
            'GET /api/drugs/autocomplete?query=...' => 'جستجوی سریع',
            'POST /api/drugs/search-all' => 'جستجو در همه فیلدها',
            'GET /api/drugs/{cod}' => 'دریافت اطلاعات یک دارو',
            'GET /api/drugs/goroh-daroei/list' => 'لیست گروه‌های دارویی',
            'GET /api/drugs/goroh-darmani/list' => 'لیست گروه‌های درمانی',
        ],
    ]);
});
<?php

namespace App\Services;

use App\Models\DrugInfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class DrugSearchService
{
    /**
     * جستجوی پیشرفته در داروها
     */
   public function search(array $params = []): Builder
    {
        $query = DrugInfo::query();
        $searchTerm = $params['q'] ?? $params['query'] ?? null;
        
        // جستجو در عبارت عمومی
        if (!empty($searchTerm)) {
            $searchFields = $params['search_fields'] ?? ['nam_fa', 'nam_en', 'mavaredmasraf'];
            
            $query->where(function (Builder $q) use ($searchTerm, $searchFields) {
                foreach ($searchFields as $field) {
                    $q->orWhere($field, 'LIKE', "%{$searchTerm}%");
                }
            });
        }
        
        // فیلتر بر اساس گروه دارویی (goroh_daroei_cod)
        if (!empty($params['goroh_daroei_cod'])) {
            $query->where('goroh_daroei_cod', $params['goroh_daroei_cod']);
        }
        
        // فیلتر بر اساس گروه درمانی (goroh_darmani_cod)
        if (!empty($params['goroh_darmani_cod'])) {
            $query->where('goroh_darmani_cod', $params['goroh_darmani_cod']);
        }
        
        // جستجو در فیلدهای خاص
        if (!empty($params['search_in_mavaredmasraf'])) {
            $query->where('mavaredmasraf', 'LIKE', "%{$params['search_in_mavaredmasraf']}%");
        }
        
        if (!empty($params['search_in_avarez'])) {
            $query->where('avarez', 'LIKE', "%{$params['search_in_avarez']}%");
        }
        
        if (!empty($params['search_in_tadakhol'])) {
            $query->where('tadakhol', 'LIKE', "%{$params['search_in_tadakhol']}%");
        }
        
        // مرتب‌سازی
        $sortBy = $params['sort_by'] ?? 'nam_fa';
        $sortOrder = $params['sort_order'] ?? 'asc';
        $query->orderBy($sortBy, $sortOrder);
        
        return $query;
    }
    /**
     * جستجو در همه فیلدها
     */
    public function searchInAllFields(string $searchTerm)
    {
        return DrugInfo::where(function (Builder $query) use ($searchTerm) {
            $fields = [
                'nam_fa',
                'nam_en',
                'mavaredmasraf',
                'meghdarmasraf',
                'masrafdarhamelegi',
                'masrafdarshirdehi',
                'manemasraf',
                'avarez',
                'tadakhol',
                'mekanismtasir',
                'nokte',
                'hoshdar',
                'sharayetnegahdari',
                'ashkal_daroei',
            ];
            
            foreach ($fields as $field) {
                $query->orWhere($field, 'LIKE', "%{$searchTerm}%");
            }
        })
        ->orderBy('nam_fa', 'asc')
        ->get();
    }
    
    /**
     * جستجوی سریع (AutoComplete)
     */
    public function autocomplete(string $term, int $limit = 10)
    {
        return DrugInfo::where('nam_fa', 'LIKE', "{$term}%")
            ->orWhere('nam_en', 'LIKE', "{$term}%")
            ->select('cod', 'nam_fa', 'nam_en', 'goroh_daroei_cod')
            ->limit($limit)
            ->get();
    }
    
    /**
     * جستجوی پیشرفته با قابلیت‌های بیشتر
     */
    public function advancedSearch(array $filters)
    {
        $query = DrugInfo::query();
        
        if (!empty($filters['name'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('nam_fa', 'LIKE', "%{$filters['name']}%")
                  ->orWhere('nam_en', 'LIKE', "%{$filters['name']}%");
            });
        }
        
        if (!empty($filters['goroh_daroei'])) {
            $query->whereIn('goroh_daroei_cod', (array) $filters['goroh_daroei']);
        }
        
        if (!empty($filters['goroh_darmani'])) {
            $query->whereIn('goroh_darmani_cod', (array) $filters['goroh_darmani']);
        }
        
        if (!empty($filters['has_interactions'])) {
            $query->whereNotNull('tadakhol')->where('tadakhol', '!=', '');
        }
        
        if (!empty($filters['side_effects'])) {
            $query->where('avarez', 'LIKE', "%{$filters['side_effects']}%");
        }
        
        // مرتب‌سازی
        $sortBy = $filters['sort_by'] ?? 'nam_fa';
        $sortOrder = $filters['sort_order'] ?? 'asc';
        $query->orderBy($sortBy, $sortOrder);
        
        return $query;
    }
    
    /**
     * جستجوی داروها با عوارض جانبی خاص
     */
    public function searchBySideEffects(string $sideEffect, array $filters = [])
    {
        $query = DrugInfo::where('avarez', 'LIKE', "%{$sideEffect}%");
        
        if (!empty($filters['goroh_daroei_cod'])) {
            $query->where('goroh_daroei_cod', $filters['goroh_daroei_cod']);
        }
        
        return $query->orderBy('nam_fa')->get();
    }
    
    /**
     * جستجوی داروها با تداخل دارویی خاص
     */
    public function searchByInteractions(string $interaction)
    {
        return DrugInfo::where('tadakhol', 'LIKE', "%{$interaction}%")
            ->orderBy('nam_fa')
            ->get();
    }
    
    /**
     * دریافت داروهای یک گروه درمانی
     */
    public function getDrugsByTherapeuticGroup(int $gorohDarmaniCod)
    {
        return DrugInfo::where('goroh_darmani_cod', $gorohDarmaniCod)
            ->with(['gorohDaroei', 'gorohDarmaniDetail'])
            ->orderBy('nam_fa')
            ->get();
    }
    
    /**
     * دریافت داروهای یک گروه دارویی
     */
    public function getDrugsByDrugGroup(int $gorohDaroeiCod)
    {
        return DrugInfo::where('goroh_daroei_cod', $gorohDaroeiCod)
            ->with(['gorohDarmani', 'gorohDarmaniDetail'])
            ->orderBy('nam_fa')
            ->get();
    }
    
    /**
     * جستجوی چند کلمه‌ای
     */
    public function multiWordSearch(string $searchTerm)
    {
        $words = explode(' ', $searchTerm);
        
        return DrugInfo::where(function ($query) use ($words) {
            foreach ($words as $word) {
                if (strlen($word) > 2) {
                    $query->where(function ($q) use ($word) {
                        $q->where('nam_fa', 'LIKE', "%{$word}%")
                          ->orWhere('nam_en', 'LIKE', "%{$word}%")
                          ->orWhere('mavaredmasraf', 'LIKE', "%{$word}%");
                    });
                }
            }
        })
        ->orderBy('nam_fa')
        ->get();
    }
}
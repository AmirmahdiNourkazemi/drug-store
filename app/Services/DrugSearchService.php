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
    
    // جستجو در عبارت عمومی با بهبود برای زبان فارسی
    if (!empty($searchTerm)) {
        $searchFields = $params['search_fields'] ?? ['nam_fa', 'nam_en', 'mavaredmasraf'];
        
        // حذف فاصله‌های اضافی و نرمال‌سازی متن فارسی
        $normalizedTerm = $this->normalizePersianText($searchTerm);
        
        // ایجاد چندین الگو برای جستجو
        $searchPatterns = $this->generatePersianSearchPatterns($normalizedTerm);
        
        $query->where(function (Builder $q) use ($searchPatterns, $searchFields) {
            foreach ($searchFields as $field) {
                foreach ($searchPatterns as $pattern) {
                    $q->orWhere($field, 'LIKE', "%{$pattern}%");
                }
            }
        });
        
        // اگر نتیجه‌ای پیدا نشد، جستجوی fuzzy انجام بده
        if ($this->shouldPerformFuzzySearch($query)) {
            $this->addFuzzySearch($query, $searchTerm, $searchFields);
        }
    }
    
    // فیلتر بر اساس گروه دارویی (goroh_daroei_cod)
    if (!empty($params['goroh_daroei_cod'])) {
        $query->where('goroh_daroei_cod', $params['goroh_daroei_cod']);
    }
    
    // فیلتر بر اساس گروه درمانی (goroh_darmani_cod)
    if (!empty($params['goroh_darmani_cod'])) {
        $query->where('goroh_darmani_cod', $params['goroh_darmani_cod']);
    }
    
    // جستجو در فیلدهای خاص با نرمال‌سازی
    if (!empty($params['search_in_mavaredmasraf'])) {
        $normalized = $this->normalizePersianText($params['search_in_mavaredmasraf']);
        $query->where('mavaredmasraf', 'LIKE', "%{$normalized}%");
    }
    
    if (!empty($params['search_in_avarez'])) {
        $normalized = $this->normalizePersianText($params['search_in_avarez']);
        $query->where('avarez', 'LIKE', "%{$normalized}%");
    }
    
    if (!empty($params['search_in_tadakhol'])) {
        $normalized = $this->normalizePersianText($params['search_in_tadakhol']);
        $query->where('tadakhol', 'LIKE', "%{$normalized}%");
    }
    
    // مرتب‌سازی
    $sortBy = $params['sort_by'] ?? 'nam_fa';
    $sortOrder = $params['sort_order'] ?? 'asc';
    $query->orderBy($sortBy, $sortOrder);
    
    return $query;
}

/**
 * نرمال‌سازی متن فارسی
 */
private function normalizePersianText(string $text): string
{
    // حذف فاصله‌های اضافی
    $text = preg_replace('/\s+/', ' ', trim($text));
    
    // نرمال‌سازی کاراکترهای فارسی
    $persianReplacements = [
        // یکسان‌سازی کاف و گاف
        'ك' => 'ک',
        'ي' => 'ی',
        'ة' => 'ه',
        'ۀ' => 'ه',
        '‌' => ' ', // نیم‌فاصله به فاصله تبدیل شود
        // یکسان‌سازی اعداد
        '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
        '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
    ];
    
    return strtr($text, $persianReplacements);
}

/**
 * تولید الگوهای مختلف جستجو برای فارسی
 */
private function generatePersianSearchPatterns(string $term): array
{
    $patterns = [$term];
    
    // حذف فاصله برای کلمات مرکب (سر درد -> سردرد)
    $patterns[] = str_replace(' ', '', $term);
    
    // اضافه کردن فاصله برای کلمات بدون فاصله (سردرد -> سر درد)
    $words = preg_split('/\s+/', $term);
    if (count($words) > 1) {
        $patterns[] = implode('', $words); // بدون فاصله
    } else {
        // اگر تک کلمه است، ممکن است نیاز به فاصله داشته باشد
        $patterns[] = $this->addPossibleSpaces($term);
    }
    
    // جایگزینی حروف مشابه (برای مواردی مثل متوکورامین و متوفرامین)
    $similarLetters = $this->getSimilarPersianLetters();
    $variations = $this->generateLetterVariations($term, $similarLetters);
    $patterns = array_merge($patterns, $variations);
    
    return array_unique($patterns);
}

/**
 * حروف مشابه در فارسی که ممکن است اشتباه نوشته شوند
 */
private function getSimilarPersianLetters(): array
{
    return [
        'ک' => ['ک', 'ك'],
        'ی' => ['ی', 'ي', 'ئ'],
        'ه' => ['ه', 'ة', 'ۀ'],
        'ق' => ['ق', 'غ'],
        'ت' => ['ت', 'ط'],
        'س' => ['س', 'ص', 'ث'],
        'ز' => ['ز', 'ذ', 'ض', 'ظ'],
        'ب' => ['ب', 'پ'],
        'ف' => ['ف', 'ق'],
        'و' => ['و', 'ؤ'],
        'ا' => ['ا', 'آ', 'أ', 'إ'],
    ];
}

/**
 * تولید تغییرات حروف برای کلمات مشابه
 */
private function generateLetterVariations(string $term, array $similarLetters): array
{
    $variations = [];
    
    // بررسی هر کاراکتر
    for ($i = 0; $i < mb_strlen($term); $i++) {
        $char = mb_substr($term, $i, 1);
        
        // اگر حرف در لیست حروف مشابه بود
        foreach ($similarLetters as $standard => $variants) {
            if (in_array($char, $variants)) {
                // حرف را با سایر گزینه‌ها جایگزین کن
                foreach ($variants as $variant) {
                    if ($variant !== $char) {
                        $newTerm = mb_substr($term, 0, $i) . $variant . mb_substr($term, $i + 1);
                        $variations[] = $newTerm;
                    }
                }
            }
        }
    }
    
    return $variations;
}

/**
 * اضافه کردن فاصله‌های احتمالی در کلمه
 */
private function addPossibleSpaces(string $word): string
{
    // لیست کلمات مرکب رایج در فارسی پزشکی
    $commonPrefixes = ['سر', 'گوش', 'دست', 'پا', 'چشم', 'دل', 'معده', 'کبد'];
    $commonSuffixes = ['درد', 'خور', 'خوراکی', 'خون', 'آور'];
    
    foreach ($commonPrefixes as $prefix) {
        if (mb_strpos($word, $prefix) === 0 && mb_strlen($word) > mb_strlen($prefix)) {
            $rest = mb_substr($word, mb_strlen($prefix));
            return $prefix . ' ' . $rest;
        }
    }
    
    foreach ($commonSuffixes as $suffix) {
        if (mb_substr($word, -mb_strlen($suffix)) === $suffix) {
            $prefix = mb_substr($word, 0, mb_strlen($word) - mb_strlen($suffix));
            return $prefix . ' ' . $suffix;
        }
    }
    
    return $word;
}

/**
 * بررسی آیا جستجوی fuzzy انجام شود
 */
private function shouldPerformFuzzySearch($query): bool
{
    // clone query to check count without running it
    $clone = clone $query;
    return $clone->count() < 3; // اگر کمتر از 3 نتیجه داشت
}

/**
 * اضافه کردن جستجوی fuzzy
 */
private function addFuzzySearch($query, string $term, array $fields): void
{
    // استفاده از SOUNDEX یا متافون برای فارسی
    // یا استفاده از Levenshtein distance
    $query->orWhere(function ($q) use ($term, $fields) {
        foreach ($fields as $field) {
            // این یک روش ساده است، برای دقت بیشتر می‌توانید از
            // کتابخانه‌های تخصصی مثل PersianString یا Elasticsearch استفاده کنید
            $q->orWhere($field, 'REGEXP', $this->createFuzzyPattern($term));
        }
    });
}

/**
 * ایجاد الگوی fuzzy برای حروف مشابه
 */
private function createFuzzyPattern(string $term): string
{
    $letters = $this->getSimilarPersianLetters();
    $pattern = '';
    
    for ($i = 0; $i < mb_strlen($term); $i++) {
        $char = mb_substr($term, $i, 1);
        $pattern .= '[' . implode('', $this->findLetterGroup($char, $letters)) . ']';
    }
    
    return $pattern;
}

/**
 * پیدا کردن گروه حروف مشابه
 */
private function findLetterGroup(string $char, array $letters): array
{
    foreach ($letters as $group) {
        if (in_array($char, $group)) {
            return $group;
        }
    }
    return [$char];
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
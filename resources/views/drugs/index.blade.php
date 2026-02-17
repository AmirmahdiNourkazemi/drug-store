{{-- resources/views/drugs/index.blade.php --}}
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<head>
    <meta charset="UTF-8">
    <title>جستجوی دارو</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=yes">
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Add FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Add Persian font -->
    <link href="https://v1.fontapi.ir/css/Vazir" rel="stylesheet">
    <style>
        body {
            font-family: Vazir, sans-serif;
        }
        .loading {
            display: none;
        }
        .loading.active {
            display: inline-block;
        }
        
        /* بهبود نمایش در موبایل */
        @media (max-width: 640px) {
            .container {
                padding-left: 12px;
                padding-right: 12px;
            }
            
            /* جلوگیری از سرریز شدن متن در موبایل */
            .line-clamp-2 {
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }
            
            /* بهبود نمایش دکمه‌ها در موبایل */
            button, 
            [type="button"], 
            [type="submit"] {
                -webkit-tap-highlight-color: transparent;
            }
            
            /* افزایش فضای کلیک برای موبایل */
            .pagination-btn {
                min-width: 44px;
                min-height: 44px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }
        }
        
        /* بهبود اسکرول پیشنهادات در موبایل */
        #suggestions {
            max-height: 50vh;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        /* جلوگیری از زوم خودکار در iOS */
        select, input, textarea {
            font-size: 16px !important;
        }
        
        /* بهبود کلیک در موبایل */
        .hover\:shadow-lg:hover {
            box-shadow: none;
        }
        
        @media (min-width: 768px) {
            .hover\:shadow-lg:hover {
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            }
        }
    </style>
</head><body class="bg-gray-50 min-h-screen">

<div class="max-w-6xl mx-auto p-3 sm:p-4 md:p-6">
    <!-- Header - بهبود یافته برای موبایل -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-2xl p-4 sm:p-6 mb-4 sm:mb-6 shadow-lg">
        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold mb-2 flex items-center">
            <i class="fas fa-pills ml-2"></i>
            <span class="break-words">سیستم جستجوی دارو</span>
        </h1>
        <p class="text-blue-100 text-sm sm:text-base">جستجو در بانک اطلاعاتی داروها</p>
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded-2xl shadow-xl p-4 sm:p-6 mb-6">
        <!-- Search Section - بهبود یافته برای موبایل -->
        <div class="mb-6 sm:mb-8">
            <label class="block text-gray-700 text-sm sm:text-base font-bold mb-2 sm:mb-3">
                <i class="fas fa-search ml-2"></i>جستجوی دارو
            </label>
            
            <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
                <div class="relative flex-grow w-full">
                    <div class="relative">
                        <input 
                            id="query" 
                            type="text" 
                            placeholder="نام فارسی یا انگلیسی دارو..." 
                            class="w-full border-2 border-gray-300 rounded-xl p-3 sm:p-4 pr-10 sm:pr-12 text-base sm:text-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-300"
                            onkeypress="if(event.key === 'Enter') search(1)"
                            oninput="autocomplete()"
                            autocomplete="off"
                        >
                        <div class="absolute left-3 top-3 sm:top-4 text-gray-400">
                            <i class="fas fa-pills text-lg sm:text-xl"></i>
                        </div>
                    </div>
                    
                    <!-- Autocomplete - بهبود یافته برای موبایل -->
                    <div id="suggestions" class="absolute z-50 bg-white w-full mt-1 border border-gray-200 rounded-xl shadow-lg max-h-60 sm:max-h-80 overflow-y-auto hidden"></div>
                </div>
                
                <!-- دکمه جستجو - بهینه شده برای موبایل -->
                <button 
                    onclick="search(1)" 
                    class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-4 sm:px-8 py-3 sm:py-4 rounded-xl font-bold text-base sm:text-lg shadow-md hover:shadow-lg transition-all duration-300 flex items-center justify-center gap-2 min-h-[48px] sm:min-h-0"
                    id="searchBtn"
                >
                    <span id="searchText" class="whitespace-nowrap">جستجو</span>
                    <i class="fas fa-search" id="searchIcon"></i>
                    <i class="fas fa-spinner fa-spin loading" id="searchSpinner"></i>
                </button>
            </div>
            
            <!-- Filters - بهبود یافته برای موبایل -->
            <div class="mt-4 sm:mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                <div class="w-full">
                    <label class="block text-gray-600 text-xs sm:text-sm font-bold mb-1 sm:mb-2">گروه دارویی</label>
                    <select id="goroh_daroei_cod" class="w-full border border-gray-300 rounded-lg p-2.5 sm:p-3 text-sm sm:text-base focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-white">
                        <option value="">همه گروه‌ها</option>
                    </select>
                </div>
                <div class="w-full">
                    <label class="block text-gray-600 text-xs sm:text-sm font-bold mb-1 sm:mb-2">گروه درمانی</label>
                    <select id="goroh_darmani_cod" class="w-full border border-gray-300 rounded-lg p-2.5 sm:p-3 text-sm sm:text-base focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-white">
                        <option value="">همه گروه‌ها</option>
                    </select>
                </div>
                <div class="w-full sm:col-span-2 lg:col-span-1">
                    <label class="block text-gray-600 text-xs sm:text-sm font-bold mb-1 sm:mb-2">تعداد در هر صفحه</label>
                    <select id="per_page" class="w-full border border-gray-300 rounded-lg p-2.5 sm:p-3 text-sm sm:text-base focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-white">
                        <option value="10">۱۰</option>
                        <option value="20" selected>۲۰</option>
                        <option value="50">۵۰</option>
                        <option value="100">۱۰۰</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Results Section -->
        <div id="resultsContainer" class="hidden">
            <!-- Header نتایج - بهبود یافته برای موبایل -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 mb-4">
                <h2 class="text-lg sm:text-xl font-bold text-gray-800">
                    <i class="fas fa-list ml-2"></i>نتایج جستجو
                </h2>
                <div id="resultsInfo" class="text-sm sm:text-base text-gray-600"></div>
            </div>
            
            <!-- لیست نتایج - بهبود یافته برای موبایل -->
            <div id="results" class="space-y-3 sm:space-y-4"></div>
            
            <!-- Loading Indicator -->
            <div id="loading" class="text-center py-6 sm:py-8 hidden">
                <div class="inline-block animate-spin rounded-full h-10 w-10 sm:h-12 sm:w-12 border-t-2 border-b-2 border-blue-500"></div>
                <p class="mt-2 text-sm sm:text-base text-gray-600">در حال بارگذاری...</p>
            </div>
            
            <!-- No Results - بهبود یافته برای موبایل -->
            <div id="noResults" class="text-center py-8 sm:py-12 hidden">
                <i class="fas fa-search text-gray-300 text-4xl sm:text-6xl mb-3 sm:mb-4"></i>
                <h3 class="text-lg sm:text-xl font-bold text-gray-600 mb-2">دارویی یافت نشد</h3>
                <p class="text-sm sm:text-base text-gray-500">لطفاً عبارت جستجوی خود را تغییر دهید</p>
            </div>

            <!-- Pagination - بهبود یافته برای موبایل -->
            <div id="pagination" class="flex justify-center gap-1 sm:gap-2 mt-6 sm:mt-8 flex-wrap"></div>
        </div>
    </div>
    
    <!-- Footer - بهبود یافته برای موبایل -->
    <div class="text-center text-gray-500 text-xs sm:text-sm mt-6 sm:mt-8 px-2">
        <p>© ۲۰۲۶ توسعه داده شده توسط امیرمهدی نورکاظمی و تیم اپروایجنسی</p>
        <p class="mt-1">تمامی حقوق محفوظ است</p>
    </div>
</div>

<script>
let debounceTimer;
let currentPage = 1;
let totalResults = 0;

// Load filter options on page load
document.addEventListener('DOMContentLoaded', function() {
    loadFilterOptions();
});

async function loadFilterOptions() {
    try {
        // Load Goroh Daroei
        const daroeiRes = await fetch('/api/drugs/goroh-daroei');
        const daroeiData = await daroeiRes.json();
        if (daroeiData.success) {
            const select = document.getElementById('goroh_daroei_cod');
            daroeiData.data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.cod;
                option.textContent = item.nam;
                select.appendChild(option);
            });
        }
        
        // Load Goroh Darmani
        const darmaniRes = await fetch('/api/drugs/goroh-darmani');
        const darmaniData = await darmaniRes.json();
        if (darmaniData.success) {
            const select = document.getElementById('goroh_darmani_cod');
            darmaniData.data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.cod;
                option.textContent = item.nam_fa;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error loading filter options:', error);
    }
}

async function search(page = 1) {
    const query = document.getElementById('query').value;
    currentPage = page;
    
    // Show loading state
    showLoading(true);
    document.getElementById('resultsContainer').classList.remove('hidden');
    document.getElementById('noResults').classList.add('hidden');
    
    // Build query parameters
    const params = new URLSearchParams({
        q: query,
        page: page,
        per_page: document.getElementById('per_page').value,
        with_relations: true // برای دریافت اطلاعات گروه‌ها
    });
    
    // Add filters if selected
    const gorohDaroei = document.getElementById('goroh_daroei_cod').value;
    const gorohDarmani = document.getElementById('goroh_darmani_cod').value;
    
    if (gorohDaroei) params.append('goroh_daroei_cod', gorohDaroei);
    if (gorohDarmani) params.append('goroh_darmani_cod', gorohDarmani);
    
    try {
        const res = await fetch(`/api/drugs/search?${params}`);
        const json = await res.json();
        
        showLoading(false);
        
        if (json.success) {
            renderResults(json.data, json.meta);
        } else {
            showNoResults(json.message || 'خطا در جستجو');
        }
    } catch (error) {
        showLoading(false);
        showNoResults('خطا در ارتباط با سرور');
        console.error('Search error:', error);
    }
}

// تابع renderResults را به این صورت بهبود دهید:
function renderResults(data, meta) {
    const box = document.getElementById('results');
    const info = document.getElementById('resultsInfo');
    
    if (!data || data.length === 0) {
        showNoResults('دارویی با مشخصات جستجو یافت نشد');
        return;
    }
    
    totalResults = meta.total;
    
    // Update results info
    const start = (meta.current_page - 1) * meta.per_page + 1;
    const end = Math.min(meta.current_page * meta.per_page, meta.total);
    
    // نمایش اطلاعات فیلترها
    const gorohDaroei = document.getElementById('goroh_daroei_cod');
    const gorohDarmani = document.getElementById('goroh_darmani_cod');
    const gorohDaroeiText = gorohDaroei.options[gorohDaroei.selectedIndex]?.text || '';
    const gorohDarmaniText = gorohDarmani.options[gorohDarmani.selectedIndex]?.text || '';
    
    let filterInfo = '';
    if (gorohDaroei.value) filterInfo += `گروه دارویی: ${gorohDaroeiText}`;
    if (gorohDarmani.value) {
        if (filterInfo) filterInfo += ' | ';
        filterInfo += `گروه درمانی: ${gorohDarmaniText}`;
    }
    
    if (filterInfo) {
        info.innerHTML = `نمایش ${start} تا ${end} از ${meta.total} نتیجه <br><span class="text-sm text-blue-600">${filterInfo}</span>`;
    } else {
        info.innerHTML = `نمایش ${start} تا ${end} از ${meta.total} نتیجه`;
    }
    
    // Clear previous results
    box.innerHTML = '';
    
    // Render results
    data.forEach((d, index) => {
        const item = document.createElement('div');
        item.className = 'bg-gradient-to-r from-gray-50 to-white border border-gray-200 rounded-xl p-5 hover:shadow-lg transition-all duration-300 hover:border-blue-300';
        
        // اطلاعات گروه‌ها
        let groupsInfo = '';
        if (d.goroh_daroei) {
            groupsInfo += `<span class="bg-blue-100 text-blue-600 px-2 py-1 rounded text-xs ml-2">${escapeHtml(d.goroh_daroei.name)}</span>`;
        }
        if (d.goroh_darmani) {
            groupsInfo += `<span class="bg-green-100 text-green-600 px-2 py-1 rounded text-xs">${escapeHtml(d.goroh_darmani.name)}</span>`;
        }
        
        item.innerHTML = `
            <div class="flex justify-between items-start">
                <div class="flex-grow">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="bg-blue-100 text-blue-600 w-8 h-8 rounded-lg flex items-center justify-center font-bold">
                            ${index + 1 + (meta.current_page - 1) * meta.per_page}
                        </div>
                        <div>
                            <h3 class="font-bold text-xl text-gray-800">${escapeHtml(d.nam_fa || 'نام نامشخص')}</h3>
                            ${d.nam_en ? `<p class="text-gray-600 text-sm">${escapeHtml(d.nam_en)}</p>` : ''}
                            <div class="flex gap-2 mt-1">${groupsInfo}</div>
                        </div>
                    </div>
                    ${d.description ? `<p class="text-gray-600 mt-3 line-clamp-2">${escapeHtml(d.description)}</p>` : ''}
                </div>
                <a href="/api/drugs-ui/${d.cod}" class="bg-blue-50 text-blue-600 hover:bg-blue-100 px-4 py-2 rounded-lg font-bold transition-colors duration-200 flex items-center gap-2 whitespace-nowrap">
                    <i class="fas fa-eye"></i>
                    مشاهده جزئیات
                </a>
            </div>
        `;
        box.appendChild(item);
    });
    
    renderPagination(meta);
}

function renderResults(data, meta) {
    const box = document.getElementById('results');
    const info = document.getElementById('resultsInfo');
    
    if (!data || data.length === 0) {
        showNoResults('دارویی با مشخصات جستجو یافت نشد');
        return;
    }
    
    totalResults = meta.total;
    
    // Update results info
    const start = (meta.current_page - 1) * meta.per_page + 1;
    const end = Math.min(meta.current_page * meta.per_page, meta.total);
    info.innerHTML = `نمایش ${start} تا ${end} از ${meta.total} نتیجه`;
    
    // Clear previous results
    box.innerHTML = '';
    
    // Render results
    data.forEach((d, index) => {
        const item = document.createElement('div');
        item.className = 'bg-gradient-to-r from-gray-50 to-white border border-gray-200 rounded-xl p-5 hover:shadow-lg transition-all duration-300 hover:border-blue-300';
        item.innerHTML = `
            <div class="flex justify-between items-start">
                <div class="flex-grow">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="bg-blue-100 text-blue-600 w-8 h-8 rounded-lg flex items-center justify-center font-bold">
                            ${index + 1 + (meta.current_page - 1) * meta.per_page}
                        </div>
                        <div>
                            <h3 class="font-bold text-xl text-gray-800">${escapeHtml(d.nam_fa || 'نام نامشخص')}</h3>
                            ${d.nam_en ? `<p class="text-gray-600 text-sm">${escapeHtml(d.nam_en)}</p>` : ''}
                        </div>
                    </div>
                    ${d.description ? `<p class="text-gray-600 mt-3 line-clamp-2">${escapeHtml(d.description)}</p>` : ''}
                </div>
                <a href="/api/drugs-ui/${d.cod}" class="bg-blue-50 text-blue-600 hover:bg-blue-100 px-4 py-2 rounded-lg font-bold transition-colors duration-200 flex items-center gap-2 whitespace-nowrap">
                    <i class="fas fa-eye"></i>
                    مشاهده جزئیات
                </a>
            </div>
        `;
        box.appendChild(item);
    });
    
    renderPagination(meta);
}

function renderPagination(meta) {
    const p = document.getElementById('pagination');
    p.innerHTML = '';
    
    if (meta.last_page <= 1) return;
    
    // Previous button
    if (meta.current_page > 1) {
        const prevBtn = document.createElement('button');
        prevBtn.className = 'px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-200 flex items-center gap-2';
        prevBtn.innerHTML = '<i class="fas fa-chevron-right"></i> قبلی';
        prevBtn.onclick = () => search(meta.current_page - 1);
        p.appendChild(prevBtn);
    }
    
    // Page numbers
    const maxVisiblePages = 5;
    let startPage = Math.max(1, meta.current_page - Math.floor(maxVisiblePages / 2));
    let endPage = Math.min(meta.last_page, startPage + maxVisiblePages - 1);
    
    if (endPage - startPage + 1 < maxVisiblePages) {
        startPage = Math.max(1, endPage - maxVisiblePages + 1);
    }
    
    for (let i = startPage; i <= endPage; i++) {
        const pageBtn = document.createElement('button');
        pageBtn.className = `px-4 py-2 border rounded-lg font-bold transition-all duration-200 ${i === meta.current_page ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 hover:bg-gray-50'}`;
        pageBtn.textContent = i;
        pageBtn.onclick = () => search(i);
        p.appendChild(pageBtn);
    }
    
    // Next button
    if (meta.current_page < meta.last_page) {
        const nextBtn = document.createElement('button');
        nextBtn.className = 'px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-200 flex items-center gap-2';
        nextBtn.innerHTML = 'بعدی <i class="fas fa-chevron-left"></i>';
        nextBtn.onclick = () => search(meta.current_page + 1);
        p.appendChild(nextBtn);
    }
}

async function autocomplete() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(async () => {
        const query = document.getElementById('query').value.trim();
        const box = document.getElementById('suggestions');
        
        if (query.length < 2) {
            box.classList.add('hidden');
            return;
        }
        
        try {
            const res = await fetch(`/api/drugs/autocomplete?query=${encodeURIComponent(query)}`);
            const json = await res.json();
            
            if (json.success && json.data.length > 0) {
                box.innerHTML = '';
                json.data.forEach(d => {
                    const item = document.createElement('div');
                    item.className = 'p-3 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0 flex items-center gap-3 transition-colors duration-150';
                    item.innerHTML = `
                        <i class="fas fa-pills text-blue-400"></i>
                        <div>
                            <div class="font-bold">${escapeHtml(d.nam_fa || d.name || 'نام نامشخص')}</div>
                            ${d.nam_en ? `<div class="text-sm text-gray-500">${escapeHtml(d.nam_en)}</div>` : ''}
                        </div>
                    `;
                    item.onclick = () => {
                        document.getElementById('query').value = d.nam_fa || d.name;
                        box.classList.add('hidden');
                        search(1);
                    };
                    box.appendChild(item);
                });
                box.classList.remove('hidden');
            } else {
                box.classList.add('hidden');
            }
        } catch (error) {
            console.error('Autocomplete error:', error);
            box.classList.add('hidden');
        }
    }, 300);
}

// Helper functions
function showLoading(show) {
    const btn = document.getElementById('searchBtn');
    const spinner = document.getElementById('searchSpinner');
    const text = document.getElementById('searchText');
    const icon = document.getElementById('searchIcon');
    const loadingDiv = document.getElementById('loading');
    
    if (show) {
        btn.disabled = true;
        spinner.classList.add('active');
        icon.classList.add('hidden');
        text.textContent = 'در حال جستجو...';
        loadingDiv.classList.remove('hidden');
    } else {
        btn.disabled = false;
        spinner.classList.remove('active');
        icon.classList.remove('hidden');
        text.textContent = 'جستجو';
        loadingDiv.classList.add('hidden');
    }
}

function showNoResults(message) {
    const noResultsDiv = document.getElementById('noResults');
    noResultsDiv.classList.remove('hidden');
    noResultsDiv.innerHTML = `
        <i class="fas fa-search text-gray-300 text-6xl mb-4"></i>
        <h3 class="text-xl font-bold text-gray-600 mb-2">${message}</h3>
        <p class="text-gray-500">لطفاً عبارت جستجوی خود را تغییر دهید</p>
    `;
    document.getElementById('results').innerHTML = '';
    document.getElementById('pagination').innerHTML = '';
    document.getElementById('resultsInfo').innerHTML = '';
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Close suggestions when clicking outside
document.addEventListener('click', function(event) {
    const suggestions = document.getElementById('suggestions');
    const queryInput = document.getElementById('query');
    
    if (!queryInput.contains(event.target) && !suggestions.contains(event.target)) {
        suggestions.classList.add('hidden');
    }
});

// Close suggestions on escape key
document.getElementById('query').addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        document.getElementById('suggestions').classList.add('hidden');
    }
});
</script>

</body>
</html>
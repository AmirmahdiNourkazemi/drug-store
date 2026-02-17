{{-- resources/views/drugs/show.blade.php --}}
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>جزئیات دارو | سیستم جستجوی دارو</title>
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
        .drug-card {
            transition: all 0.3s ease;
        }
        .info-section {
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .info-section:last-child {
            border-bottom: none;
            padding-bottom: 0;
            margin-bottom: 0;
        }
        
        /* بهبود نمایش در موبایل */
        @media (max-width: 640px) {
            .container {
                padding-left: 12px;
                padding-right: 12px;
            }
            
            /* جلوگیری از سرریز شدن متن */
            .line-clamp-3 {
                display: -webkit-box;
                -webkit-line-clamp: 3;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }
            
            /* بهبود فضای کلیک */
            button, 
            [type="button"], 
            [type="submit"],
            a {
                -webkit-tap-highlight-color: transparent;
                min-height: 44px;
                min-width: 44px;
            }
            
            /* بهبود اسکرول */
            .overflow-y-auto {
                -webkit-overflow-scrolling: touch;
            }
            
            /* جلوگیری از زوم خودکار در iOS */
            select, input, textarea, button {
                font-size: 16px !important;
            }
            
            /* بهبود خوانایی متن */
            .text-justify {
                text-align: right;
            }
            
            p {
                word-break: break-word;
            }
        }
        
        /* حذف hover effects در موبایل */
        @media (hover: none) {
            .hover\:shadow-lg:hover {
                box-shadow: none;
            }
            .hover\:bg-gray-50:hover {
                background-color: transparent;
            }
        }
        
        /* انیمیشن برای بازگشت به بالا */
        .back-to-top {
            position: fixed;
            bottom: 20px;
            left: 20px;
            display: none;
            z-index: 99;
        }
        
        .back-to-top.show {
            display: block;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

<!-- دکمه بازگشت به بالا - فقط در موبایل -->
<button onclick="scrollToTop()" class="back-to-top bg-blue-600 text-white w-12 h-12 rounded-full shadow-lg flex items-center justify-center md:hidden">
    <i class="fas fa-arrow-up"></i>
</button>

<div class="max-w-5xl mx-auto p-3 sm:p-4">
    <!-- Header - بهبود یافته برای موبایل -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-2xl p-4 sm:p-6 mb-4 sm:mb-6 shadow-lg">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl md:text-3xl font-bold mb-2 flex items-center">
                    <i class="fas fa-pills ml-2"></i>
                    <span>جزئیات دارو</span>
                </h1>
                <p class="text-blue-100 text-sm sm:text-base">مشاهده اطلاعات کامل دارو</p>
            </div>
            <!-- دکمه بازگشت بهینه شده -->
            <a href="/api/drugs-ui" class="bg-white/20 hover:bg-white/30 text-white px-3 sm:px-4 py-2 rounded-lg transition-all duration-200 flex items-center gap-2 text-sm sm:text-base min-h-[44px]">
                <i class="fas fa-arrow-right"></i>
                <span class="hidden xs:inline">بازگشت به جستجو</span>
                <span class="xs:hidden">بازگشت</span>
            </a>
        </div>
    </div>

    <!-- Loading State - بهبود یافته -->
    <div id="loading" class="text-center py-8 sm:py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 sm:h-16 sm:w-16 border-t-2 border-b-2 border-blue-500"></div>
        <p class="mt-3 sm:mt-4 text-sm sm:text-base text-gray-600">در حال بارگذاری اطلاعات دارو...</p>
    </div>

    <!-- Error State - بهبود یافته -->
    <div id="error" class="hidden bg-white rounded-2xl shadow-xl p-6 sm:p-12 text-center">
        <i class="fas fa-exclamation-triangle text-red-500 text-4xl sm:text-7xl mb-4 sm:mb-6"></i>
        <h2 class="text-xl sm:text-3xl font-bold text-gray-800 mb-2 sm:mb-4">خطا در دریافت اطلاعات</h2>
        <p id="errorMessage" class="text-sm sm:text-base text-gray-600 mb-6 sm:mb-8">داروی مورد نظر یافت نشد</p>
        <a href="/api/drugs-ui" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 sm:px-8 py-2.5 sm:py-3 rounded-xl font-bold transition-all duration-200 text-sm sm:text-base min-h-[44px]">
            <i class="fas fa-search ml-2"></i>
            بازگشت به صفحه جستجو
        </a>
    </div>

    <!-- Drug Details -->
    <div id="drugDetails" class="hidden">
        <!-- Main Info Card - بهبود یافته -->
        <div class="bg-white rounded-2xl shadow-xl p-4 sm:p-8 mb-4 sm:mb-6 drug-card">
            <div class="flex flex-col md:flex-row justify-between items-start gap-4 sm:gap-6">
                <div class="flex-grow w-full">
                    <!-- Drug Name and Code -->
                    <div class="flex items-center gap-3 sm:gap-4 mb-4 sm:mb-6">
                        <div class="bg-blue-100 text-blue-600 w-12 h-12 sm:w-16 sm:h-16 rounded-xl sm:rounded-2xl flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-capsules text-xl sm:text-3xl"></i>
                        </div>
                        <div class="min-w-0 flex-grow">
                            <h2 id="drugNameFa" class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-800 mb-1 sm:mb-2 break-words"></h2>
                            <p id="drugNameEn" class="text-sm sm:text-base text-gray-500 break-words"></p>
                        </div>
                    </div>
                    
                    <!-- Quick Info Badges - بهینه شده برای موبایل -->
                    <div class="flex flex-wrap gap-2 sm:gap-3 mb-4 sm:mb-6">
                        <span id="drugCode" class="bg-gray-100 text-gray-600 px-3 sm:px-4 py-1.5 sm:py-2 rounded-full text-xs sm:text-sm flex items-center gap-1 sm:gap-2">
                            <i class="fas fa-hashtag"></i>
                            <span class="truncate max-w-[150px] sm:max-w-none"></span>
                        </span>
                        <span id="drugGroup" class="bg-blue-100 text-blue-600 px-3 sm:px-4 py-1.5 sm:py-2 rounded-full text-xs sm:text-sm flex items-center gap-1 sm:gap-2">
                            <i class="fas fa-layer-group"></i>
                            <span class="truncate max-w-[150px] sm:max-w-none"></span>
                        </span>
                        <span id="drugTherapeuticGroup" class="bg-green-100 text-green-600 px-3 sm:px-4 py-1.5 sm:py-2 rounded-full text-xs sm:text-sm flex items-center gap-1 sm:gap-2">
                            <i class="fas fa-heartbeat"></i>
                            <span class="truncate max-w-[150px] sm:max-w-none"></span>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Information Grid - بهبود یافته -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
            <!-- موارد مصرف -->
            <div class="bg-white rounded-2xl shadow-xl p-4 sm:p-6 drug-card">
                <h3 class="text-base sm:text-xl font-bold text-gray-800 mb-3 sm:mb-4 flex items-center gap-2">
                    <i class="fas fa-check-circle text-green-500 text-lg sm:text-xl"></i>
                    <span>موارد مصرف</span>
                </h3>
                <p id="mavaredmasraf" class="text-sm sm:text-base text-gray-600 leading-relaxed"></p>
            </div>

            <!-- مقدار مصرف -->
            <div class="bg-white rounded-2xl shadow-xl p-4 sm:p-6 drug-card">
                <h3 class="text-base sm:text-xl font-bold text-gray-800 mb-3 sm:mb-4 flex items-center gap-2">
                    <i class="fas fa-prescription-bottle text-blue-500 text-lg sm:text-xl"></i>
                    <span>مقدار مصرف</span>
                </h3>
                <p id="meghdarmasraf" class="text-sm sm:text-base text-gray-600 leading-relaxed"></p>
            </div>

            <!-- مصرف در حاملگی -->
            <div class="bg-white rounded-2xl shadow-xl p-4 sm:p-6 drug-card">
                <h3 class="text-base sm:text-xl font-bold text-gray-800 mb-3 sm:mb-4 flex items-center gap-2">
                    <i class="fas fa-fetus text-pink-500 text-lg sm:text-xl"></i>
                    <span>مصرف در حاملگی</span>
                </h3>
                <p id="masrafdarhamelegi" class="text-sm sm:text-base text-gray-600 leading-relaxed"></p>
            </div>

            <!-- مصرف در شیردهی -->
            <div class="bg-white rounded-2xl shadow-xl p-4 sm:p-6 drug-card">
                <h3 class="text-base sm:text-xl font-bold text-gray-800 mb-3 sm:mb-4 flex items-center gap-2">
                    <i class="fas fa-baby text-purple-500 text-lg sm:text-xl"></i>
                    <span>مصرف در شیردهی</span>
                </h3>
                <p id="masrafdarshirdehi" class="text-sm sm:text-base text-gray-600 leading-relaxed"></p>
            </div>

            <!-- موارد منع مصرف -->
            <div class="bg-white rounded-2xl shadow-xl p-4 sm:p-6 drug-card">
                <h3 class="text-base sm:text-xl font-bold text-gray-800 mb-3 sm:mb-4 flex items-center gap-2">
                    <i class="fas fa-ban text-red-500 text-lg sm:text-xl"></i>
                    <span>موارد منع مصرف</span>
                </h3>
                <p id="manemasraf" class="text-sm sm:text-base text-gray-600 leading-relaxed"></p>
            </div>

            <!-- عوارض جانبی -->
            <div class="bg-white rounded-2xl shadow-xl p-4 sm:p-6 drug-card">
                <h3 class="text-base sm:text-xl font-bold text-gray-800 mb-3 sm:mb-4 flex items-center gap-2">
                    <i class="fas fa-exclamation-triangle text-yellow-500 text-lg sm:text-xl"></i>
                    <span>عوارض جانبی</span>
                </h3>
                <p id="avarez" class="text-sm sm:text-base text-gray-600 leading-relaxed"></p>
            </div>

            <!-- تداخلات دارویی -->
            <div class="bg-white rounded-2xl shadow-xl p-4 sm:p-6 drug-card lg:col-span-2">
                <h3 class="text-base sm:text-xl font-bold text-gray-800 mb-3 sm:mb-4 flex items-center gap-2">
                    <i class="fas fa-pills text-red-500 text-lg sm:text-xl"></i>
                    <span>تداخلات دارویی</span>
                </h3>
                <p id="tadakhol" class="text-sm sm:text-base text-gray-600 leading-relaxed"></p>
            </div>

            <!-- مکانیسم اثر -->
            <div class="bg-white rounded-2xl shadow-xl p-4 sm:p-6 drug-card lg:col-span-2">
                <h3 class="text-base sm:text-xl font-bold text-gray-800 mb-3 sm:mb-4 flex items-center gap-2">
                    <i class="fas fa-dna text-indigo-500 text-lg sm:text-xl"></i>
                    <span>مکانیسم اثر</span>
                </h3>
                <p id="mekanismtasir" class="text-sm sm:text-base text-gray-600 leading-relaxed"></p>
            </div>

            <!-- نکات -->
            <div class="bg-white rounded-2xl shadow-xl p-4 sm:p-6 drug-card">
                <h3 class="text-base sm:text-xl font-bold text-gray-800 mb-3 sm:mb-4 flex items-center gap-2">
                    <i class="fas fa-lightbulb text-yellow-500 text-lg sm:text-xl"></i>
                    <span>نکات</span>
                </h3>
                <p id="nokte" class="text-sm sm:text-base text-gray-600 leading-relaxed"></p>
            </div>

            <!-- هشدارها -->
            <div class="bg-white rounded-2xl shadow-xl p-4 sm:p-6 drug-card">
                <h3 class="text-base sm:text-xl font-bold text-gray-800 mb-3 sm:mb-4 flex items-center gap-2">
                    <i class="fas fa-exclamation-circle text-orange-500 text-lg sm:text-xl"></i>
                    <span>هشدارها</span>
                </h3>
                <p id="hoshdar" class="text-sm sm:text-base text-gray-600 leading-relaxed"></p>
            </div>

            <!-- شرایط نگهداری -->
            <div class="bg-white rounded-2xl shadow-xl p-4 sm:p-6 drug-card">
                <h3 class="text-base sm:text-xl font-bold text-gray-800 mb-3 sm:mb-4 flex items-center gap-2">
                    <i class="fas fa-temperature-low text-blue-500 text-lg sm:text-xl"></i>
                    <span>شرایط نگهداری</span>
                </h3>
                <p id="sharayetnegahdari" class="text-sm sm:text-base text-gray-600 leading-relaxed"></p>
            </div>

            <!-- اشکال دارویی -->
            <div class="bg-white rounded-2xl shadow-xl p-4 sm:p-6 drug-card">
                <h3 class="text-base sm:text-xl font-bold text-gray-800 mb-3 sm:mb-4 flex items-center gap-2">
                    <i class="fas fa-tablets text-green-500 text-lg sm:text-xl"></i>
                    <span>اشکال دارویی</span>
                </h3>
                <p id="ashkal_daroei" class="text-sm sm:text-base text-gray-600 leading-relaxed"></p>
            </div>
        </div>
    </div>

    <!-- Footer - بهبود یافته -->
    <div class="text-center text-gray-500 text-xs sm:text-sm mt-6 sm:mt-8 px-2">
        <p>© ۲۰۲۶ توسعه داده شده توسط امیرمهدی نورکاظمی و تیم اپروایجنسی</p>
        <p class="mt-1">تمامی حقوق محفوظ است</p>
    </div>
</div>

<script>
// تشخیص موبایل
const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);

document.addEventListener('DOMContentLoaded', function() {
    loadDrugDetails();
    
    // نمایش دکمه بازگشت به بالا هنگام اسکرول در موبایل
    if (isMobile) {
        window.addEventListener('scroll', function() {
            const backToTop = document.querySelector('.back-to-top');
            if (window.scrollY > 300) {
                backToTop.classList.add('show');
            } else {
                backToTop.classList.remove('show');
            }
        });
    }
});

function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

function loadDrugDetails() {
    const loading = document.getElementById('loading');
    const error = document.getElementById('error');
    const details = document.getElementById('drugDetails');
    
    // Get drug code from URL
    const pathParts = window.location.pathname.split('/');
    const cod = pathParts[pathParts.length - 1];
    
    if (!cod || isNaN(cod)) {
        showError('کد دارو معتبر نیست');
        return;
    }
    
    fetch(`/api/drugs/${cod}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(result => {
            loading.classList.add('hidden');
            
            if (result.success && result.data) {
                displayDrugDetails(result.data);
                details.classList.remove('hidden');
            } else {
                showError(result.message || 'داروی مورد نظر یافت نشد');
            }
        })
        .catch(err => {
            console.error('Error:', err);
            loading.classList.add('hidden');
            showError('خطا در ارتباط با سرور');
        });
}

function displayDrugDetails(drug) {
    // Set document title
    document.title = `${drug.nam_fa || 'دارو'} | سیستم جستجوی دارو`;
    
    // Basic Info
    document.getElementById('drugNameFa').textContent = drug.nam_fa || 'نامشخص';
    document.getElementById('drugNameEn').textContent = drug.nam_en || '';
    
    // Code Badge
    const codeBadge = document.getElementById('drugCode');
    codeBadge.querySelector('span').textContent = isMobile ? drug.cod || 'نامشخص' : `کد دارو: ${drug.cod || 'نامشخص'}`;
    
    // Groups
    if (drug.goroh_daroei) {
        const groupBadge = document.getElementById('drugGroup');
        groupBadge.querySelector('span').textContent = isMobile ? 
            (drug.goroh_daroei.nam || '').substring(0, 15) + '...' : 
            (drug.goroh_daroei.nam || 'گروه دارویی');
    } else {
        document.getElementById('drugGroup').classList.add('hidden');
    }
    
    if (drug.goroh_darmani) {
        const therapeuticBadge = document.getElementById('drugTherapeuticGroup');
        therapeuticBadge.querySelector('span').textContent = isMobile ? 
            (drug.goroh_darmani.nam_fa || '').substring(0, 15) + '...' : 
            (drug.goroh_darmani.nam_fa || 'گروه درمانی');
    } else {
        document.getElementById('drugTherapeuticGroup').classList.add('hidden');
    }
    
    // Detailed Info
    setTextContent('mavaredmasraf', drug.mavaredmasraf);
    setTextContent('meghdarmasraf', drug.meghdarmasraf);
    setTextContent('masrafdarhamelegi', drug.masrafdarhamelegi);
    setTextContent('masrafdarshirdehi', drug.masrafdarshirdehi);
    setTextContent('manemasraf', drug.manemasraf);
    setTextContent('avarez', drug.avarez);
    setTextContent('tadakhol', drug.tadakhol);
    setTextContent('mekanismtasir', drug.mekanismtasir);
    setTextContent('nokte', drug.nokte);
    setTextContent('hoshdar', drug.hoshdar);
    setTextContent('sharayetnegahdari', drug.sharayetnegahdari);
    setTextContent('ashkal_daroei', drug.ashkal_daroei);
}

function setTextContent(elementId, value) {
    const element = document.getElementById(elementId);
    if (element) {
        // در موبایل متن‌های طولانی را محدود کن
        if (isMobile && value && value.length > 200) {
            element.textContent = value.substring(0, 200) + '...';
            
            // اضافه کردن دکمه "ادامه مطلب"
            const showMoreBtn = document.createElement('button');
            showMoreBtn.className = 'text-blue-600 text-sm mt-2 block';
            showMoreBtn.textContent = 'ادامه مطلب';
            showMoreBtn.onclick = function() {
                element.textContent = value;
                this.remove();
            };
            element.parentNode.appendChild(showMoreBtn);
        } else {
            element.textContent = value || 'اطلاعاتی ثبت نشده است';
        }
        
        if (!value) {
            element.classList.add('text-gray-400', 'italic');
        } else {
            element.classList.remove('text-gray-400', 'italic');
        }
    }
}

function showError(message) {
    const error = document.getElementById('error');
    const errorMessage = document.getElementById('errorMessage');
    errorMessage.textContent = message || 'خطای ناشناخته';
    error.classList.remove('hidden');
}
</script>

</body>
</html>
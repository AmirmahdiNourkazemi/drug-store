{{-- resources/views/drugs/show.blade.php --}}
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

<div class="max-w-5xl mx-auto p-4">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-t-2xl p-6 mb-6 shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">
                    <i class="fas fa-pills ml-2"></i>جزئیات دارو
                </h1>
                <p class="text-blue-100">مشاهده اطلاعات کامل دارو</p>
            </div>
            <a href="/drugs-ui" class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg transition-all duration-200 flex items-center gap-2">
                <i class="fas fa-arrow-right"></i>
                <span>بازگشت به جستجو</span>
            </a>
        </div>
    </div>

    <!-- Loading State -->
    <div id="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-16 w-16 border-t-2 border-b-2 border-blue-500"></div>
        <p class="mt-4 text-gray-600 text-lg">در حال بارگذاری اطلاعات دارو...</p>
    </div>

    <!-- Error State -->
    <div id="error" class="hidden bg-white rounded-2xl shadow-xl p-12 text-center">
        <i class="fas fa-exclamation-triangle text-red-500 text-7xl mb-6"></i>
        <h2 class="text-3xl font-bold text-gray-800 mb-4">خطا در دریافت اطلاعات</h2>
        <p id="errorMessage" class="text-gray-600 text-lg mb-8">داروی مورد نظر یافت نشد</p>
        <a href="/drugs-ui" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-xl font-bold transition-all duration-200">
            <i class="fas fa-search ml-2"></i>
            بازگشت به صفحه جستجو
        </a>
    </div>

    <!-- Drug Details -->
    <div id="drugDetails" class="hidden">
        <!-- Main Info Card -->
        <div class="bg-white rounded-2xl shadow-xl p-8 mb-6 drug-card">
            <div class="flex flex-col md:flex-row justify-between items-start gap-6">
                <div class="flex-grow">
                    <!-- Drug Name and Code -->
                    <div class="flex items-center gap-4 mb-6">
                        <div class="bg-blue-100 text-blue-600 w-16 h-16 rounded-2xl flex items-center justify-center">
                            <i class="fas fa-capsules text-3xl"></i>
                        </div>
                        <div>
                            <h2 id="drugNameFa" class="text-3xl font-bold text-gray-800 mb-2"></h2>
                            <p id="drugNameEn" class="text-gray-500 text-lg"></p>
                        </div>
                    </div>
                    
                    <!-- Quick Info Badges -->
                    <div class="flex flex-wrap gap-3 mb-6">
                        <span id="drugCode" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-full text-sm flex items-center gap-2">
                            <i class="fas fa-hashtag"></i>
                            <span></span>
                        </span>
                        <span id="drugGroup" class="bg-blue-100 text-blue-600 px-4 py-2 rounded-full text-sm flex items-center gap-2">
                            <i class="fas fa-layer-group"></i>
                            <span></span>
                        </span>
                        <span id="drugTherapeuticGroup" class="bg-green-100 text-green-600 px-4 py-2 rounded-full text-sm flex items-center gap-2">
                            <i class="fas fa-heartbeat"></i>
                            <span></span>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Information Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- موارد مصرف -->
            <div class="bg-white rounded-2xl shadow-xl p-6 drug-card">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-check-circle text-green-500"></i>
                    موارد مصرف
                </h3>
                <p id="mavaredmasraf" class="text-gray-600 leading-relaxed"></p>
            </div>

            <!-- مقدار مصرف -->
            <div class="bg-white rounded-2xl shadow-xl p-6 drug-card">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-prescription-bottle text-blue-500"></i>
                    مقدار مصرف
                </h3>
                <p id="meghdarmasraf" class="text-gray-600 leading-relaxed"></p>
            </div>

            <!-- مصرف در حاملگی -->
            <div class="bg-white rounded-2xl shadow-xl p-6 drug-card">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-fetus text-pink-500"></i>
                    مصرف در حاملگی
                </h3>
                <p id="masrafdarhamelegi" class="text-gray-600 leading-relaxed"></p>
            </div>

            <!-- مصرف در شیردهی -->
            <div class="bg-white rounded-2xl shadow-xl p-6 drug-card">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-baby text-purple-500"></i>
                    مصرف در شیردهی
                </h3>
                <p id="masrafdarshirdehi" class="text-gray-600 leading-relaxed"></p>
            </div>

            <!-- موارد منع مصرف -->
            <div class="bg-white rounded-2xl shadow-xl p-6 drug-card">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-ban text-red-500"></i>
                    موارد منع مصرف
                </h3>
                <p id="manemasraf" class="text-gray-600 leading-relaxed"></p>
            </div>

            <!-- عوارض جانبی -->
            <div class="bg-white rounded-2xl shadow-xl p-6 drug-card">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-exclamation-triangle text-yellow-500"></i>
                    عوارض جانبی
                </h3>
                <p id="avarez" class="text-gray-600 leading-relaxed"></p>
            </div>

            <!-- تداخلات دارویی -->
            <div class="bg-white rounded-2xl shadow-xl p-6 drug-card lg:col-span-2">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-pills text-red-500"></i>
                    تداخلات دارویی
                </h3>
                <p id="tadakhol" class="text-gray-600 leading-relaxed"></p>
            </div>

            <!-- مکانیسم اثر -->
            <div class="bg-white rounded-2xl shadow-xl p-6 drug-card lg:col-span-2">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-dna text-indigo-500"></i>
                    مکانیسم اثر
                </h3>
                <p id="mekanismtasir" class="text-gray-600 leading-relaxed"></p>
            </div>

            <!-- نکات -->
            <div class="bg-white rounded-2xl shadow-xl p-6 drug-card">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-lightbulb text-yellow-500"></i>
                    نکات
                </h3>
                <p id="nokte" class="text-gray-600 leading-relaxed"></p>
            </div>

            <!-- هشدارها -->
            <div class="bg-white rounded-2xl shadow-xl p-6 drug-card">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-exclamation-circle text-orange-500"></i>
                    هشدارها
                </h3>
                <p id="hoshdar" class="text-gray-600 leading-relaxed"></p>
            </div>

            <!-- شرایط نگهداری -->
            <div class="bg-white rounded-2xl shadow-xl p-6 drug-card">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-temperature-low text-blue-500"></i>
                    شرایط نگهداری
                </h3>
                <p id="sharayetnegahdari" class="text-gray-600 leading-relaxed"></p>
            </div>

            <!-- اشکال دارویی -->
            <div class="bg-white rounded-2xl shadow-xl p-6 drug-card">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-tablets text-green-500"></i>
                    اشکال دارویی
                </h3>
                <p id="ashkal_daroei" class="text-gray-600 leading-relaxed"></p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="text-center text-gray-500 text-sm mt-8">
       <p>© 2026 توسعه داده شده توسط امیرمهدی نورکاظمی و تیم اپروایجنسی - تمامی حقوق محفوظ است</p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadDrugDetails();
});

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
    codeBadge.querySelector('span').textContent = `کد دارو: ${drug.cod || 'نامشخص'}`;
    
    // Groups
    if (drug.goroh_daroei) {
        const groupBadge = document.getElementById('drugGroup');
        groupBadge.querySelector('span').textContent = drug.goroh_daroei.nam || 'گروه دارویی';
    } else {
        document.getElementById('drugGroup').classList.add('hidden');
    }
    
    if (drug.goroh_darmani) {
        const therapeuticBadge = document.getElementById('drugTherapeuticGroup');
        therapeuticBadge.querySelector('span').textContent = drug.goroh_darmani.nam_fa || 'گروه درمانی';
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
        element.textContent = value || 'اطلاعاتی ثبت نشده است';
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
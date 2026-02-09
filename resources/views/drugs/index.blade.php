{{-- resources/views/drugs/index.blade.php --}}
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<title>جستجوی دارو</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">

<div class="max-w-4xl mx-auto bg-white shadow rounded p-6">
    <h1 class="text-2xl font-bold mb-4">💊 جستجوی دارو</h1>

    <!-- Search box -->
    <div class="relative mb-4">
        <input id="query" type="text" placeholder="نام دارو..." class="w-full border rounded p-2" oninput="autocomplete()">
        <div id="suggestions" class="absolute z-10 bg-white border w-full hidden"></div>
    </div>

    <button onclick="search(1)" class="bg-blue-600 text-white px-4 py-2 rounded mb-4">جستجو</button>

    <!-- Results -->
    <div id="results" class="space-y-2"></div>

    <!-- Pagination -->
    <div id="pagination" class="flex justify-center gap-2 mt-4"></div>
</div>

<script>
let debounceTimer;

async function search(page = 1) {
    const q = document.getElementById('query').value;
    if (!q) return;

    const res = await fetch(`/api/drugs/search?q=${q}&page=${page}`);
    const json = await res.json();

    const box = document.getElementById('results');
    box.innerHTML = '';

    json.data.forEach(d => {
        box.innerHTML += `
            <div class="border p-3 rounded">
                <div class="font-bold">${d.nam_fa ?? ''}</div>
                <div class="text-sm text-gray-500">${d.nam_en ?? ''}</div>
                <a href="/drugs-ui/${d.cod}" class="text-blue-600 text-sm">مشاهده جزئیات</a>
            </div>
        `;
    });

    renderPagination(json.meta);
}

function renderPagination(meta) {
    const p = document.getElementById('pagination');
    p.innerHTML = '';

    for (let i = 1; i <= meta.last_page; i++) {
        p.innerHTML += `
            <button onclick="search(${i})" class="px-3 py-1 border rounded ${i === meta.current_page ? 'bg-blue-600 text-white' : ''}">
                ${i}
            </button>
        `;
    }
}

async function autocomplete() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(async () => {
        const q = document.getElementById('query').value;
        const box = document.getElementById('suggestions');

        if (q.length < 2) {
            box.classList.add('hidden');
            return;
        }

        const res = await fetch(`/api/drugs/autocomplete?query=${q}`);
        const json = await res.json();

        box.innerHTML = '';
        json.data.forEach(d => {
            box.innerHTML += `
                <div class="p-2 hover:bg-gray-100 cursor-pointer" onclick="selectSuggestion('${d.name ?? d.nam_fa}')">
                    ${d.name ?? d.nam_fa}
                </div>
            `;
        });

        box.classList.remove('hidden');
    }, 300);
}

function selectSuggestion(val) {
    document.getElementById('query').value = val;
    document.getElementById('suggestions').classList.add('hidden');
    search(1);
}
</script>

</body>
</html>
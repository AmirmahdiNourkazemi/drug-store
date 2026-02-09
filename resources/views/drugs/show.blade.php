{{-- resources/views/drugs/show.blade.php --}}
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<title>جزئیات دارو</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">


<div class="max-w-3xl mx-auto bg-white p-6 rounded shadow">
<a href="/api/drugs-ui" class="text-blue-600">← بازگشت</a>
<pre id="data" class="mt-4"></pre>
</div>


<script>
fetch(`/api/drugs/{{ $cod }}`)
.then(r => r.json())
.then(d => {
document.getElementById('data').innerText = JSON.stringify(d.data, null, 2);
});
</script>


</body>
</html>
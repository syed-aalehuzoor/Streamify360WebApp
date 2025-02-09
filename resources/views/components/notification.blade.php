@if (session('success'))
    <div id="success-message" class="mb-4 p-3 bg-green-50 text-green-700 border border-green-200 rounded-lg shadow-sm">
        {{ session('success') }}
    </div>
    <script>
        setTimeout(() => document.getElementById('success-message').style.display = 'none', 5000);
    </script>
@endif

@if (session('error'))
    <div class="mb-4 p-3 bg-red-50 text-red-700 border border-red-200 rounded-lg shadow-sm">
        {{ session('error') }}
    </div>
@endif
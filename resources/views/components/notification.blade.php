@if (session('success'))
    <div id="success-message" class="mb-4 p-3 bg-green-50 text-green-700 border border-green-200 rounded-lg shadow-sm">
        {{ session('success') }}
    </div>
    <script>
        setTimeout(() => document.getElementById('success-message').style.display = 'none', 3000);
    </script>
@endif
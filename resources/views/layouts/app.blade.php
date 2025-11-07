<!-- resources/views/layouts/app.blade.php -->
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', '車両点検管理システム')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .nav-item.active {
            border-bottom-color: #3b82f6;
            /* blue-500 */
            color: #1e40af;
            /* blue-800 */
            font-weight: 600;
        }
    </style>
    <link rel="stylesheet" href="{{ asset('/css/style.css') }}">
</head>

<body class="bg-gray-100">
    <header class="bg-white shadow-md sticky top-0 z-40">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="text-xl font-bold text-gray-800">車両点検管理システム</div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">ログアウト</button>
                </form>
            </div>
        </div>
    </header>
    <nav class="bg-white border-b-2 border-gray-200 sticky top-16 z-40">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex space-x-4 sm:space-x-8">
                <a href="{{ route('admin.dashboard') }}" class="nav-item py-4 px-1 border-b-2 text-sm sm:text-base font-medium hover:text-gray-800 {{ request()->routeIs('admin.dashboard*') ? 'active' : 'text-gray-500 border-transparent' }}">週次点検履歴</a>
                <a href="{{ route('admin.vehicles.index') }}" class="nav-item py-4 px-1 border-b-2 text-sm sm:text-base font-medium hover:text-gray-800 {{ request()->routeIs('admin.vehicles*') ? 'active' : 'text-gray-500 border-transparent' }}">車両・重機管理</a>
                <a href="{{ route('admin.users.index') }}" class="nav-item py-4 px-1 border-b-2 text-sm sm:text-base font-medium hover:text-gray-800 {{ request()->routeIs('admin.users*') ? 'active' : 'text-gray-500 border-transparent' }}">担当者管理</a>
                <a href="{{ route('admin.patterns.index') }}" class="nav-item py-4 px-1 border-b-2 text-sm sm:text-base font-medium hover:text-gray-800 {{ request()->routeIs('admin.patterns*') ? 'active' : 'text-gray-500 border-transparent' }}">点検パターン管理</a>
            </div>
        </div>
    </nav>
    <main class="container mx-auto p-4 sm:p-6 lg:p-8">
        @yield('content')
    </main>
    @stack('scripts')
    <script src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.14.1/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
</body>

</html>
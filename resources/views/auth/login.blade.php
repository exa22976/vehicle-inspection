<!-- resources/views/auth/login.blade.php -->
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>ログイン</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">
    <div class="flex flex-col items-center justify-center min-h-screen p-4">
        <div class="w-full max-w-md p-8 space-y-8 bg-white rounded-xl shadow-lg">
            <h1 class="text-2xl font-bold text-center text-gray-800">車両点検管理システム</h1>
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div>
                    <label for="email" class="sr-only">メールアドレス</label>
                    <input type="email" name="email" id="email" class="w-full px-4 py-3 text-lg border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="メールアドレス" required autofocus>
                    @error('email')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <button type="submit" class="w-full px-4 py-3 font-semibold text-white bg-blue-600 rounded-lg shadow-md hover:bg-blue-700">ログイン</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>
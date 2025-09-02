<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>車両点検フォーム</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">
    <div class="bg-white max-w-md mx-auto min-h-screen shadow-2xl">
        <header class="bg-blue-600 text-white p-4 text-center sticky top-0 z-10">
            <h1 class="text-lg font-bold">車両点検フォーム</h1>
        </header>

        <main class="p-4">
            <form action="{{ route('inspection.submit', ['token' => $record->one_time_token]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="p-4 bg-gray-50 rounded-lg mb-4">
                    <h2 class="text-lg font-bold text-gray-900">対象: {{ $vehicle->model_name }}</h2>
                    @if ($inspectionRequest->remarks)
                    <div class="mt-2 p-3 bg-yellow-100 text-yellow-800 rounded-md text-sm">
                        <p class="font-semibold">管理者からの申し送り事項:</p>
                        <p>{{ $inspectionRequest->remarks }}</p>
                    </div>
                    @endif
                </div>

                @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <ul>@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
                </div>
                @endif

                <div class="space-y-4">
                    @foreach ($items as $item)
                    <div class="p-4 bg-white rounded-lg border" x-data="{ result: '{{ old('results.'.$item->id.'.check_result', '正常') }}' }">
                        <h3 class="font-semibold text-gray-800">{{ $item->item_name }} @if($item->is_required)<span class="text-red-500">*</span>@endif</h3>
                        @if ($item->remarks)
                        <p class="text-sm text-gray-500 mt-1">{{ $item->remarks }}</p>
                        @endif
                        <input type="hidden" name="results[{{ $item->id }}][check_result]" x-model="result">
                        <div class="flex justify-around mt-4">
                            <button type="button" @click="result = '正常'" :class="result === '正常' ? 'bg-green-500 text-white' : 'bg-gray-50'" class="flex-1 py-2 px-4 text-sm font-medium rounded-l-md border border-gray-300">正常</button>
                            <button type="button" @click="result = '要確認'" :class="result === '要確認' ? 'bg-yellow-500 text-white' : 'bg-gray-50'" class="flex-1 py-2 px-4 text-sm font-medium border-t border-b border-gray-300">要確認</button>
                            <button type="button" @click="result = '異常'" :class="result === '異常' ? 'bg-red-500 text-white' : 'bg-gray-50'" class="flex-1 py-2 px-4 text-sm font-medium rounded-r-md border border-gray-300">異常</button>
                        </div>
                        <div x-show="result === '要確認' || result === '異常'" class="pt-4 mt-4 border-t space-y-4">
                            <label class="text-sm font-medium text-gray-700">コメント</label>
                            <textarea name="results[{{ $item->id }}][comment]" rows="3" class="w-full border border-gray-300 rounded-md shadow-sm sm:text-sm">{{ old('results.'.$item->id.'.comment') }}</textarea>
                            <label class="text-sm font-medium text-gray-700">写真添付</label>
                            <input type="file" name="results[{{ $item->id }}][photo]" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="pt-6">
                    <button type="submit" class="w-full py-3 text-lg font-semibold text-white bg-blue-600 rounded-lg shadow-md hover:bg-blue-700">点検報告</button>
                </div>
            </form>
        </main>
    </div>
    <script src="//unpkg.com/alpinejs" defer></script>
</body>

</html>
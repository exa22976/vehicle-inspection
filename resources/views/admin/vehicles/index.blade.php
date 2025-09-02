@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold">車両・重機マスター管理</h2>
        <div class="flex items-center space-x-2">
            <a href="{{ route('admin.vehicles.create') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded shadow-md">
                ＋ 新規登録
            </a>
            <div x-data="{ open: false }" class="relative">
                <button @click="open = true" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded shadow-md flex items-center">
                    ↑ CSVインポート
                </button>
                <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-80 bg-white border rounded-lg shadow-xl p-4 z-10">
                    <form action="{{ route('admin.vehicles.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="csv_file" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                        <button type="submit" class="mt-2 w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">アップロード</button>
                    </form>
                </div>
            </div>
            <a href="{{ route('admin.vehicles.export') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded shadow-md flex items-center">
                ↓ CSVダウンロード
            </a>
        </div>
    </div>

    <div class="mb-4">
        <form id="searchForm" action="{{ route('admin.vehicles.index') }}" method="GET">
            <input type="text" id="searchInput" name="search" placeholder="型式、種別、カテゴリ、管理番号で検索..."
                value="{{ $search ?? '' }}"
                class="w-full md:w-1/3 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </form>
    </div>

    <div class="bg-white p-6 rounded-lg shadow">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <x-sort-link label="型式" column="model_name" />
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <x-sort-link label="車両種別" column="vehicle_type" />
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <x-sort-link label="カテゴリ" column="category" />
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <x-sort-link label="管理番号" column="asset_number" />
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            担当者
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            アクション
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($vehicles as $vehicle)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $vehicle->model_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $vehicle->vehicle_type }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $vehicle->category }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $vehicle->asset_number }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ $vehicle->users->pluck('name')->join(', ') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <a href="{{ route('admin.vehicles.edit', $vehicle) }}" class="text-indigo-600 hover:text-indigo-900">編集</a>
                            <form action="{{ route('admin.vehicles.destroy', $vehicle) }}" method="POST" class="inline-block ml-2" onsubmit="return confirm('本当に削除しますか？');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">削除</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">該当する車両・重機が見つかりません。</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $vehicles->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // 検索ボックスの要素を取得
    const searchInput = document.getElementById('searchInput');
    const searchForm = document.getElementById('searchForm');
    let searchTimeout;

    // キー入力のイベントを監視
    searchInput.addEventListener('keyup', function() {
        // 既にあるタイマーをクリア
        clearTimeout(searchTimeout);
        // 500ミリ秒（0.5秒）後にフォームを送信するタイマーをセット
        searchTimeout = setTimeout(() => {
            searchForm.submit();
        }, 500);
    });
</script>
@endpush
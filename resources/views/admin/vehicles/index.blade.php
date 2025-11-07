@extends('layouts.app')

@section('content')
<div class="p-6 bg-white rounded-xl shadow-lg">
    <div class="container mx-auto px-4" x-data="{ importModalOpen: false, filterOpen: false }">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-xl font-bold text-gray-800">車両・重機管理</h1>

            <div class="flex items-center space-x-3">
                <a href="{{ route('admin.vehicles.create') }}" class="px-4 py-2 text-sm font-semibold text-white bg-green-600 rounded-lg shadow-md hover:bg-green-700">
                    ＋ 新規登録
                </a>

                <button @click="importModalOpen = true" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 cursor-pointer">
                    ↑CSVインポート
                </button>

                <a href="{{ route('admin.vehicles.export') }}" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 cursor-pointer">
                    ↓CSVダウンロード
                </a>
            </div>
        </div>

        @if (session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded-md" role="alert">
            <p>{{ session('success') }}</p>
        </div>
        @endif

        @if ($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded-md" role="alert">
            <p class="font-bold">インポートエラー</p>
            <ul class="mt-2 list-disc list-inside">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
        @if (session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded-md" role="alert">
            <p>{{ session('error') }}</p>
        </div>
        @endif

        <div class="border rounded-lg mb-6">
            <button @click="filterOpen = !filterOpen" class="w-full px-6 py-4 text-left font-semibold text-gray-700 flex justify-between items-center">
                <span>絞り込みフィルター</span>
                <svg class="w-5 h-5 transform transition-transform" :class="{ 'rotate-180': filterOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="filterOpen" x-collapse class="border-t">
                <form action="{{ route('admin.vehicles.index') }}" method="GET">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 p-6">
                        {{-- メーカーフィルター --}}
                        <div>
                            <h4 class="font-semibold text-sm mb-2 text-gray-600">メーカー</h4>
                            <div class="space-y-1 max-h-40 overflow-y-auto">
                                @foreach($filterOptions['makers'] as $maker)
                                <label class="flex items-center text-sm">
                                    <input type="checkbox" name="filters[maker][]" value="{{ $maker }}" {{ in_array($maker, $filters['maker'] ?? []) ? 'checked' : '' }} class="rounded border-gray-300">
                                    <span class="ml-2 text-gray-800">{{ $maker }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        {{-- 種別フィルター --}}
                        <div>
                            <h4 class="font-semibold text-sm mb-2 text-gray-600">種別</h4>
                            <div class="space-y-1 max-h-40 overflow-y-auto">
                                @foreach($filterOptions['vehicle_types'] as $type)
                                <label class="flex items-center text-sm">
                                    <input type="checkbox" name="filters[vehicle_type][]" value="{{ $type }}" {{ in_array($type, $filters['vehicle_type'] ?? []) ? 'checked' : '' }} class="rounded border-gray-300">
                                    <span class="ml-2 text-gray-800">{{ $type }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        {{-- カテゴリフィルター --}}
                        <div>
                            <h4 class="font-semibold text-sm mb-2 text-gray-600">カテゴリ</h4>
                            <div class="space-y-1 max-h-40 overflow-y-auto">
                                @foreach($filterOptions['categories'] as $category)
                                <label class="flex items-center text-sm">
                                    <input type="checkbox" name="filters[category][]" value="{{ $category }}" {{ in_array($category, $filters['category'] ?? []) ? 'checked' : '' }} class="rounded border-gray-300">
                                    <span class="ml-2 text-gray-800">{{ $category }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        {{-- 部署フィルター --}}
                        <div>
                            <h4 class="font-semibold text-sm mb-2 text-gray-600">部署</h4>
                            <div class="space-y-1 max-h-40 overflow-y-auto">
                                @foreach($filterOptions['departments'] as $department)
                                <label class="flex items-center text-sm">
                                    <input type="checkbox" name="filters[department][]" value="{{ $department->id }}" {{ in_array($department->id, $filters['department'] ?? []) ? 'checked' : '' }} class="rounded border-gray-300">
                                    <span class="ml-2 text-gray-800">{{ $department->name }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="px-6 pb-4 flex justify-end items-center space-x-3 rounded-b-lg">
                        <a href="{{ route('admin.vehicles.index') }}" class="text-sm text-gray-600 hover:text-gray-900">リセット</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">絞り込む</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr class="border-b-2 border-gray-200 bg-gray-50">
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">名称</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">メーカー</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">種別</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">カテゴリ</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">部署</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">担当者</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">アクション</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($vehicles as $vehicle)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">{{ $vehicle->model_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $vehicle->maker }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $vehicle->vehicle_type }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $vehicle->category ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                $departments = $vehicle->users->map(fn($user) => $user->department->name ?? null)->filter()->unique()->implode(', ');
                                @endphp
                                {{ $departments ?: '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="space-y-1">
                                    @forelse($vehicle->users as $user)
                                    <p>{{ $user->name }}</p>
                                    @empty
                                    <span class="text-gray-400">担当者なし</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('admin.vehicles.edit', $vehicle) }}" class="text-indigo-600 hover:text-indigo-900">編集</a>
                                <form action="{{ route('admin.vehicles.destroy', $vehicle) }}" method="POST" onsubmit="return confirm('本当に削除しますか？');" class="inline-block ml-4">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">削除</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">該当する車両・重機が見つかりません。</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div x-show="importModalOpen" class="fixed z-50 inset-0 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div x-show="importModalOpen" @click.away="if (!isFileDialogOpen) importModalOpen = false"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

                <div x-show="importModalOpen"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full">

                    <form action="{{ route('admin.vehicles.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">CSVインポート</h3>
                            <div class="mt-4">
                                <p class="text-sm text-gray-600">
                                    以下の列順のCSVファイルを選択してください。<br>
                                    <strong>・新規登録の場合:</strong> ID列を空にしてください。<br>
                                    <strong>・情報を更新する場合:</strong> ID列に既存のIDを入力してください。
                                </p>
                                <p class="mt-2 text-xs text-gray-500 bg-gray-50 p-2 rounded">
                                    <strong>A列:</strong> ID, <strong>B列:</strong> 名称, <strong>C列:</strong> メーカー, ...
                                </p>
                            </div>
                            <div class="mt-4">
                                {{-- ★★★★★ input に @click と @change を追加 ★★★★★ --}}
                                <input type="file" name="file" required @click="isFileDialogOpen = true" @change="isFileDialogOpen = false" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm">
                                インポート実行
                            </button>
                            <button type="button" @click="importModalOpen = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">
                                キャンセル
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
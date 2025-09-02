<!-- resources/views/admin/patterns/edit.blade.php -->
@extends('layouts.app')

@section('title', '点検パターン 編集')

@section('content')
<div class="space-y-8">
    @if (session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
        {{ session('success') }}
    </div>
    @endif
    @if ($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        <ul>@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
    </div>
    @endif

    <!-- パターン名編集フォーム -->
    <div class="p-6 bg-white rounded-xl shadow-lg">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-800">パターン名編集</h2>
            <a href="{{ route('admin.patterns.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">&lt; 一覧へ戻る</a>
        </div>
        <form action="{{ route('admin.patterns.update', $pattern) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="flex items-center gap-4">
                <div class="flex-grow">
                    <label for="name" class="sr-only">パターン名</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $pattern->name) }}" required class="block w-full border-gray-300 rounded-md shadow-sm">
                </div>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700">パターン名更新</button>
            </div>
        </form>
    </div>

    <!-- 点検項目一覧 -->
    <div class="p-6 bg-white rounded-xl shadow-lg">
        <h2 class="text-xl font-bold text-gray-800 mb-6">点検項目</h2>

        <!-- ★車両共通の項目 -->
        <div class="mb-8">
            <h3 class="text-lg font-semibold text-gray-700 mb-3 border-b pb-2">車両</h3>
            <div class="space-y-3">
                @forelse ($items['車両共通'] ?? [] as $item)
                @include('admin.patterns._item_form', ['item' => $item])
                @empty
                <p class="text-gray-500">登録されている項目はありません。</p>
                @endforelse
            </div>
        </div>

        <!-- ★重機共通の項目 -->
        <div>
            <h3 class="text-lg font-semibold text-gray-700 mb-3 border-b pb-2">重機</h3>
            <div class="space-y-3">
                @forelse ($items['重機共通'] ?? [] as $item)
                @include('admin.patterns._item_form', ['item' => $item])
                @empty
                <p class="text-gray-500">登録されている項目はありません。</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- 新規点検項目追加フォーム -->
    <div class="p-6 bg-white rounded-xl shadow-lg">
        <h2 class="text-xl font-bold text-gray-800 mb-6">新規項目追加</h2>
        <form action="{{ route('admin.patterns.items.store', $pattern) }}" method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-start">
            @csrf
            <div class="md:col-span-5 space-y-2">
                <label for="new_item_name" class="sr-only">項目名</label>
                <input type="text" name="item_name" id="new_item_name" required class="block w-full border-gray-300 rounded-md shadow-sm" placeholder="項目名">
                <label for="new_remarks" class="sr-only">備考</label>
                <input type="text" name="remarks" id="new_remarks" class="block w-full border-gray-300 rounded-md shadow-sm text-sm" placeholder="備考（担当者への指示など）">
            </div>
            <div class="md:col-span-3">
                <label for="new_category" class="sr-only">カテゴリ</label>
                <select name="category" id="new_category" class="block w-full border-gray-300 rounded-md shadow-sm">
                    <option value="車両共通">車両</option>
                    <option value="重機共通">重機</option>
                </select>
            </div>
            <div class="md:col-span-2 flex items-center pt-2">
                <input type="hidden" name="is_required" value="0">
                <input type="checkbox" name="is_required" value="1" id="new_is_required" class="h-4 w-4 text-blue-600 border-gray-300 rounded" checked>
                <label for="new_is_required" class="ml-2 block text-sm text-gray-900">必須</label>
            </div>
            <div class="md:col-span-2 flex justify-end pt-2">
                <button type="submit" class="w-full px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md shadow-sm hover:bg-green-700">追加</button>
            </div>
        </form>
    </div>
</div>
@endsection
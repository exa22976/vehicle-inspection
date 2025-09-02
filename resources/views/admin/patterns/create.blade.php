@extends('layouts.app')
@section('title', '点検パターン 新規登録')
@section('content')
<div class="p-6 bg-white rounded-xl shadow-lg">
    <h2 class="text-xl font-bold text-gray-800 mb-6">点検パターン 新規登録</h2>
    <form action="{{ route('admin.patterns.store') }}" method="POST">
        @csrf
        <div class="space-y-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">パターン名</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            </div>
        </div>
        <div class="mt-8 flex justify-end space-x-3">
            <a href="{{ route('admin.patterns.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50">キャンセル</a>
            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700">登録して項目編集へ</button>
        </div>
    </form>
</div>
@endsection
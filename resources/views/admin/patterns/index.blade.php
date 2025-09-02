@extends('layouts.app')
@section('title', '点検パターン管理')
@section('content')
<div class="p-6 bg-white rounded-xl shadow-lg">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-gray-800">点検パターン管理</h2>
        <a href="{{ route('admin.patterns.create') }}" class="px-4 py-2 text-sm font-semibold text-white bg-green-600 rounded-lg shadow-md hover:bg-green-700">＋ 新規パターン登録</a>
    </div>
    <!-- (成功・エラーメッセージ表示) -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">パターン名</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">項目数</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">アクション</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($patterns as $pattern)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap font-medium">{{ $pattern->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $pattern->items_count }} 件</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                        <a href="{{ route('admin.patterns.edit', $pattern) }}" class="text-blue-600 hover:text-blue-900">編集</a>
                        <form action="{{ route('admin.patterns.destroy', $pattern) }}" method="POST" class="inline-block" onsubmit="return confirm('本当に削除しますか？');">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900">削除</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="px-6 py-4 text-center text-gray-500">データがありません。</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
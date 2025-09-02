@extends('layouts.app')
@section('title', '点検結果詳細')
@section('content')
<div class="space-y-8">
    @if (session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
        {{ session('success') }}
    </div>
    @endif

    <!-- 現在の点検結果 -->
    <div class="p-6 bg-white rounded-xl shadow-lg">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-800">点検結果詳細</h2>
            <a href="{{ route('admin.dashboard', ['week' => $inspectionRecord->inspectionRequest->target_week_start]) }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">&lt; 一覧へ戻る</a>
        </div>

        <!-- 基本情報 -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6 p-4 bg-gray-50 rounded-lg text-sm">
            <div><span class="font-semibold text-gray-600">型式:</span> {{ $inspectionRecord->vehicle->model_name ?? '削除された車両' }}</div>
            <div><span class="font-semibold text-gray-600">管理番号:</span> {{ $inspectionRecord->vehicle->asset_number ?? '-' }}</div>
            <div><span class="font-semibold text-gray-600">点検実施者:</span> {{ $inspectionRecord->user->name ?? '未実施' }}</div>
            <div><span class="font-semibold text-gray-600">点検日時:</span> {{ $inspectionRecord->inspected_at ? \Carbon\Carbon::parse($inspectionRecord->inspected_at)->format('Y/m/d H:i') : '-' }}</div>
            <div><span class="font-semibold text-gray-600">総合結果:</span>
                @if($inspectionRecord->result === '正常') <span class="font-bold text-green-600">〇 正常</span>
                @elseif($inspectionRecord->result === '要確認') <span class="font-bold text-yellow-600">△ 要確認</span>
                @elseif($inspectionRecord->result === '異常') <span class="font-bold text-red-600">✕ 異常</span>
                @else <span class="font-bold text-gray-500">- 未点検</span> @endif
            </div>
        </div>

        <!-- 点検項目詳細 -->
        <div class="space-y-4 mb-8">
            <h3 class="text-lg font-semibold text-gray-800 border-b pb-2">点検項目</h3>
            @forelse ($inspectionRecord->details as $detail)
            <div class="p-3 border rounded-md @if($detail->check_result === '異常') bg-red-50 border-red-200 @elseif($detail->check_result === '要確認') bg-yellow-50 border-yellow-200 @endif">
                <div class="flex items-center justify-between">
                    <span class="font-medium text-gray-800">{{ $detail->item->item_name }}</span>
                    @if($detail->check_result === '正常') <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">正常</span>
                    @elseif($detail->check_result === '要確認') <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">要確認</span>
                    @elseif($detail->check_result === '異常') <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">異常</span>
                    @endif
                </div>
                @if ($detail->comment || $detail->photo_path)
                <div class="mt-3 pt-3 border-t @if($detail->check_result === '異常') border-red-200 @elseif($detail->check_result === '要確認') border-yellow-200 @endif">
                    @if ($detail->comment)
                    <p class="text-sm text-gray-700"><span class="font-semibold">コメント:</span> {{ $detail->comment }}</p>
                    @endif
                    @if ($detail->photo_path)
                    <div class="mt-2">
                        <span class="font-semibold text-sm text-gray-700">添付写真:</span>
                        <a href="{{ asset('storage/' . $detail->photo_path) }}" target="_blank" rel="noopener noreferrer">
                            <img src="{{ asset('storage/' . $detail->photo_path) }}" alt="添付写真" class="mt-1 rounded-md border" style="max-width: 300px;">
                        </a>
                    </div>
                    @endif
                </div>
                @endif
            </div>
            @empty
            <p class="text-gray-500">この点検はまだ実施されていません。</p>
            @endforelse
        </div>
    </div>

    <!-- 管理者対応欄 -->
    <div class="p-6 bg-white rounded-xl shadow-lg">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">管理者対応</h3>
        <form action="{{ route('admin.records.updateStatus', $inspectionRecord) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">異常対応状況</label>
                    <div class="mt-2 flex space-x-4">
                        <label class="inline-flex items-center"><input type="radio" name="issue_status" value="未対応" class="form-radio text-red-600" @if($inspectionRecord->issue_status == '未対応') checked @endif> <span class="ml-2">未対応</span></label>
                        <label class="inline-flex items-center"><input type="radio" name="issue_status" value="対応済み" class="form-radio text-green-600" @if($inspectionRecord->issue_status == '対応済み') checked @endif> <span class="ml-2">対応済み</span></label>
                    </div>
                </div>
                <div>
                    <label for="resolved_at" class="block text-sm font-medium text-gray-700">対応完了日</label>
                    <input type="date" name="resolved_at" id="resolved_at" value="{{ old('resolved_at', $inspectionRecord->resolved_at) }}" class="mt-1 block w-full max-w-xs border-gray-300 rounded-md shadow-sm">
                </div>
                <div>
                    <label for="admin_comment" class="block text-sm font-medium text-gray-700">管理者コメント（新規追加）</label>
                    <textarea name="admin_comment" id="admin_comment" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm" placeholder="対応内容のメモなどを入力して更新ボタンを押してください"></textarea>
                </div>
                <div class="text-right">
                    <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg shadow-md hover:bg-blue-700">更新する</button>
                </div>
            </div>
        </form>
        @if($inspectionRecord->adminComments->isNotEmpty())
        <div class="mt-6 pt-6 border-t">
            <h4 class="text-md font-semibold text-gray-800 mb-4">コメント履歴</h4>
            <div class="space-y-3 max-h-48 overflow-y-auto">
                @foreach($inspectionRecord->adminComments as $comment)
                <div class="text-sm p-3 bg-gray-50 rounded-md">
                    <p class="text-gray-800">{{ $comment->comment }}</p>
                    <p class="text-xs text-gray-500 text-right mt-1">- {{ $comment->user->name }} ({{ $comment->created_at->format('Y/m/d H:i') }})</p>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- 過去の点検履歴 -->
    @if($historicalRecords->isNotEmpty())
    <div class="p-6 bg-white rounded-xl shadow-lg">
        <h2 class="text-xl font-bold text-gray-800 mb-6">過去の点検履歴</h2>
        <div class="space-y-4">
            @foreach($historicalRecords as $history)
            <div class="p-4 border rounded-md hover:bg-gray-50">
                <a href="{{ route('admin.records.show', $history) }}" class="block">
                    <div class="flex justify-between items-center">
                        <div>
                            <p><strong>点検日時:</strong> {{ $history->inspected_at ? \Carbon\Carbon::parse($history->inspected_at)->format('Y/m/d H:i') : '未実施' }}</p>
                            <p><strong>結果:</strong> {{ $history->result ?? '-' }}</p>
                        </div>
                        <span class="text-blue-600 text-sm">詳細を見る &gt;</span>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
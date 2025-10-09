@extends('layouts.app')

@php
use Illuminate\Support\Carbon;
@endphp

@section('content')
<div class="container mx-auto px-4">
    @if (session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
        <p>{{ session('success') }}</p>
    </div>
    @endif

    @if (session('error'))
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
        <p>{{ session('error') }}</p>
    </div>
    @endif

    @if ($errors->any())
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
        <p class="font-bold">入力内容にエラーがありました。</p>
        <ul class="list-disc ml-5">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    <h2 class="text-2xl font-bold mb-4">週次点検履歴</h2>

    <!-- 週ナビゲーション -->
    <div class="flex justify-between items-center mb-4">
        <a href="{{ route('admin.dashboard', ['week' => $prevWeek]) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
            &larr; 前の週
        </a>
        <span class="text-xl font-semibold">
            {{ $weekStartDate->format('Y年m月d日') }} &ndash; {{ $weekStartDate->copy()->endOfWeek(Carbon::SUNDAY)->format('Y年m月d日') }}
        </span>
        <a href="{{ route('admin.dashboard', ['week' => $nextWeek]) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
            次の週 &rarr;
        </a>
    </div>

    @if ($inspectionRequest)
    <!-- ダッシュボード ... -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-4 rounded-lg shadow">
            <h3 class="text-gray-500">進捗率</h3>
            <p class="text-3xl font-bold">{{ $stats['progress_rate'] }}%</p>
            <p class="text-gray-600">{{ $stats['completed'] }} / {{ $stats['total'] }} 件完了</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
            <h3 class="text-gray-500">正常</h3>
            <p class="text-3xl font-bold text-green-500">{{ $stats['results']['正常'] }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
            <h3 class="text-gray-500">要確認</h3>
            <p class="text-3xl font-bold text-yellow-500">{{ $stats['results']['要確認'] }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
            <h3 class="text-gray-500">異常</h3>
            <p class="text-3xl font-bold text-red-500">{{ $stats['results']['異常'] }}</p>
        </div>
    </div>


    <!-- 点検一覧 -->
    <div class="bg-white p-6 rounded-lg shadow">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold">点検状況 ({{ $inspectionRequest->pattern->name }})</h3>

            <div class="flex items-center space-x-3">
                <a href="{{ route('admin.dashboard', ['week' => $weekStartDate->format('Y-m-d')]) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-3 rounded text-sm">
                    更新
                </a>
                <form action="{{ route('admin.inspection-requests.destroy', $inspectionRequest) }}" method="POST" onsubmit="return confirm('この週の点検依頼とすべての点検記録を完全に削除します。よろしいですか？');" class="m-0">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-600 hover:bg-red-800 text-white font-bold py-2 px-3 rounded text-sm">
                        この週の依頼を削除
                    </button>
                </form>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <!-- ★★★★★ ここから修正 ★★★★★ -->
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <x-sort-link column="vehicle.model_name" label="車両・重機" />
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <x-sort-link column="user.name" label="担当者" />
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <div x-data="{ open: false }" @click.away="open = false" class="relative">
                                <button @click="open = !open" class="flex items-center space-x-1 focus:outline-none">
                                    <span>ステータス</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>

                                    @if($filterStatus)
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
                                    </svg>
                                    @endif
                                </button>

                                {{-- ドロップダウンリスト本体 --}}
                                <div x-show="open"
                                    x-transition
                                    class="absolute z-10 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5"
                                    style="display: none;">
                                    <div class="py-1" role="menu" aria-orientation="vertical">
                                        <a href="{{ route('admin.dashboard', ['week' => $weekStartDate->format('Y-m-d')]) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                            すべて表示
                                        </a>
                                        @foreach($statuses as $status)
                                        <a href="{{ route('admin.dashboard', ['week' => $weekStartDate->format('Y-m-d'), 'status' => $status]) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ $filterStatus === $status ? 'font-bold' : '' }}" role="menuitem">
                                            {{ $status }}
                                        </a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <x-sort-link column="result" label="結果" />
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <x-sort-link column="inspected_at" label="点検日時" />
                        </th>
                        <!-- ★★★★★ ここまで修正 ★★★★★ -->
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">アクション</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($sortedRecords as $record)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ optional($record->vehicle)->model_name ?? '削除された車両' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $record->user->name ?? '未実施' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <!-- ★★★★★ ここから修正 ★★★★★ -->
                            @php
                            $statusColor = match($record->status) {
                            '依頼中' => 'bg-yellow-100 text-yellow-800',
                            '再依頼' => 'bg-yellow-100 text-yellow-800',
                            '点検済み' => 'bg-green-100 text-green-800',
                            default => 'bg-gray-100 text-gray-800',
                            };
                            @endphp
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColor }}">
                                {{ $record->status }}
                            </span>
                            <!-- ★★★★★ ここまで修正 ★★★★★ -->
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <!-- ★★★★★ ここから修正 ★★★★★ -->
                            @if($record->result)
                            @php
                            $resultColor = match($record->result) {
                            '正常' => 'bg-green-100 text-green-800',
                            '要確認' => 'bg-yellow-100 text-yellow-800',
                            '異常' => 'bg-red-100 text-red-800',
                            default => 'bg-gray-100 text-gray-800',
                            };
                            @endphp
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $resultColor }}">
                                {{ $record->result }}
                            </span>
                            @else
                            N/A
                            @endif
                            <!-- ★★★★★ ここまで修正 ★★★★★ -->
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $record->inspected_at ? Carbon::parse($record->inspected_at)->format('Y/m/d H:i') : 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <a href="{{ route('admin.records.show', $record) }}" class="text-indigo-600 hover:text-indigo-900">詳細</a>
                            <form action="{{ route('admin.records.reRequest', $record) }}" method="POST" class="inline-block ml-2" onsubmit="return confirm('この車両の再点検依頼をしますか？');">
                                @csrf
                                <button type="submit" class="text-red-600 hover:text-red-900">再依頼</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">点検対象の車両がありません。</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @else
    <div class="text-center bg-white p-8 rounded-lg shadow">
        <p class="text-gray-500 mb-4">この週の点検依頼はまだ作成されていません。</p>
        <button onclick="document.getElementById('requestModal').classList.remove('hidden')" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
            ＋ 新規点検依頼を作成
        </button>
    </div>
    @endif
</div>

<div id="requestModal" class="fixed z-50 inset-0 overflow-y-auto hidden">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <div x-data="{
                selectedPatternId: '',
                patterns: {{ $patterns->toJson() }},
                get selectedItemsGrouped() {
                    const pattern = this.patterns.find(p => p.id == this.selectedPatternId);
                    if (!pattern || !pattern.items) return {};

                    return pattern.items.reduce((groups, item) => {
                        const category = item.category || 'カテゴリなし';
                        if (!groups[category]) {
                            groups[category] = [];
                        }
                        groups[category].push(item);
                        return groups;
                    }, {});
                }
            }"
            class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form action="{{ route('admin.inspection-requests.store') }}" method="POST">
                @csrf
                <input type="hidden" name="target_week_start" value="{{ $weekStartDate->format('Y-m-d') }}">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                        新規点検依頼を作成
                    </h3>
                    <div class="mt-4">
                        <label for="inspection_pattern_id" class="block text-sm font-medium text-gray-700">点検パターン</label>
                        <select x-model="selectedPatternId" id="inspection_pattern_id" name="inspection_pattern_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" required>
                            <option value="">選択してください</option>
                            @foreach ($patterns as $pattern)
                            <option value="{{ $pattern->id }}">{{ $pattern->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mt-4">
                        <label for="remarks" class="block text-sm font-medium text-gray-700">備考（担当者への申し送り事項など）</label>
                        <textarea id="remarks" name="remarks" rows="3" class="mt-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border border-gray-300 rounded-md"></textarea>
                    </div>

                    <div x-show="selectedPatternId" class="mt-6 border-t pt-4">
                        <h4 class="text-md font-medium text-gray-800 mb-2">点検項目プレビュー</h4>
                        <div class="max-h-60 overflow-y-auto pr-2">
                            <template x-if="Object.keys(selectedItemsGrouped).length === 0">
                                <p class="text-sm text-gray-500">このパターンには点検項目が登録されていません。</p>
                            </template>
                            <template x-for="(items, category) in selectedItemsGrouped" :key="category">
                                <div class="mb-3">
                                    <h5 class="text-sm font-semibold text-gray-600 bg-gray-50 p-2 rounded" x-text="category"></h5>
                                    <ul class="list-disc list-inside mt-1 space-y-1">
                                        <template x-for="item in items" :key="item.id">
                                            <li class="text-sm text-gray-700" x-text="item.item_name"></li>
                                        </template>
                                    </ul>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        依頼を送信
                    </button>
                    <button type="button" onclick="document.getElementById('requestModal').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                        キャンセル
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
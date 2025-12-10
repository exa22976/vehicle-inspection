@extends('layouts.app')

@php
use Illuminate\Support\Carbon;
@endphp

@section('title', '週次点検履歴')

@section('content')
{{-- ★ 全体を x-data で囲み、スクロール状態とヘッダーの高さを管理 --}}
<div x-data="{
        isScrolled: false,
        headerHeight: 0,
        updateScroll() {
            this.isScrolled = window.pageYOffset > 50;
            this.updateHeaderHeight();
        },
        updateHeaderHeight() {
            this.$nextTick(() => {
                if(this.$refs.stickyHeader) {
                    this.headerHeight = this.$refs.stickyHeader.offsetHeight;
                }
            });
        }
     }"
    @scroll.window="updateScroll()"
    @resize.window="updateHeaderHeight()"
    x-init="updateHeaderHeight()"
    class="relative">

    <div class="p-6 bg-white/0"> {{-- bg-whiteを削除または透過調整 --}}

        {{-- アラート表示エリア（ここはスクロールで流れて良いと判断、固定エリアの上に配置） --}}
        @if (session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded-md shadow-sm" role="alert">
            <p>{{ session('success') }}</p>
        </div>
        @endif

        @if (session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded-md shadow-sm" role="alert">
            <p>{{ session('error') }}</p>
        </div>
        @endif

        @if ($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded-md shadow-sm" role="alert">
            <p class="font-bold">入力内容にエラーがありました。</p>
            <ul class="list-disc ml-5 mt-2">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- ★★★ 固定ヘッダーエリア開始 ★★★ --}}
        <div x-ref="stickyHeader" class="sticky top-0 z-40 bg-gray-50/95 backdrop-blur border-b shadow-md -mx-6 px-6 pt-4 pb-2">

            {{-- 1. ナビゲーション & タイトル --}}
            <div class="flex justify-between items-center" :class="isScrolled ? 'mb-2' : 'mb-6'">
                <a href="{{ route('admin.dashboard', ['week' => $prevWeek]) }}" class="px-3 py-1 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 shadow-sm">&lt; 前の週</a>

                <h2 class="font-bold text-gray-800" :class="isScrolled ? 'text-lg' : 'text-xl'">
                    {{ $weekStartDate->format('Y年m月d日') }} 〜 {{ $weekStartDate->copy()->endOfWeek(Carbon::SUNDAY)->format('m月d日') }} の点検履歴
                </h2>

                <a href="{{ route('admin.dashboard', ['week' => $nextWeek]) }}" class="px-3 py-1 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 shadow-sm">次の週 &gt;</a>
            </div>

            @if ($inspectionRequest)

            {{-- 2. 集計情報 (Stats) --}}
            <div class="transition-all duration-300">
                {{-- A: 通常表示 (Grid) --}}
                <div x-show="!isScrolled" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="grid grid-cols-2 md:grid-cols-5 gap-5 mb-6">
                    <div class="bg-blue-50 p-4 rounded-lg text-center border border-blue-100 shadow-sm">
                        <div class="text-sm text-blue-600 font-semibold">総件数</div>
                        <div class="text-2xl font-bold text-blue-800">{{ $stats['total'] }}</div>
                        <div class="text-xs text-gray-500">({{ $stats['completed'] }} 件完了)</div>
                    </div>
                    <div class="bg-indigo-50 p-4 rounded-lg text-center border border-indigo-100 shadow-sm">
                        <div class="text-sm text-indigo-600 font-semibold">進捗率</div>
                        <div class="text-2xl font-bold text-indigo-800">{{ $stats['progress_rate'] }}%</div>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg text-center border border-green-100 shadow-sm">
                        <div class="text-sm text-green-600 font-semibold">正常</div>
                        <div class="text-2xl font-bold text-green-800">{{ $stats['results']['正常'] }}</div>
                    </div>
                    <div class="bg-yellow-50 p-4 rounded-lg text-center border border-yellow-100 shadow-sm">
                        <div class="text-sm text-yellow-600 font-semibold">要確認</div>
                        <div class="text-2xl font-bold text-yellow-800">{{ $stats['results']['要確認'] }}</div>
                    </div>
                    <div class="bg-red-50 p-4 rounded-lg text-center border border-red-100 shadow-sm col-span-2 md:col-span-1">
                        <div class="text-sm text-red-600 font-semibold">異常</div>
                        <div class="text-2xl font-bold text-red-800">{{ $stats['results']['異常'] }}</div>
                    </div>
                </div>

                {{-- B: 簡易表示 (Flex Row) --}}
                <div x-show="isScrolled" x-cloak class="flex flex-wrap items-center justify-center gap-4 mb-2 pb-2 border-b border-gray-200 bg-white/50 rounded-lg p-2 shadow-inner">
                    <div class="flex items-center space-x-2 text-sm">
                        <span class="font-bold text-blue-700">総件数: {{ $stats['total'] }}</span>
                        <span class="text-gray-400">|</span>
                        <span class="font-bold text-indigo-700">進捗: {{ $stats['progress_rate'] }}%</span>
                    </div>
                    <div class="flex items-center space-x-3 text-sm">
                        <span class="px-2 py-0.5 rounded bg-green-100 text-green-800 font-bold border border-green-200">正常: {{ $stats['results']['正常'] }}</span>
                        <span class="px-2 py-0.5 rounded bg-yellow-100 text-yellow-800 font-bold border border-yellow-200">要確認: {{ $stats['results']['要確認'] }}</span>
                        <span class="px-2 py-0.5 rounded bg-red-100 text-red-800 font-bold border border-red-200">異常: {{ $stats['results']['異常'] }}</span>
                    </div>
                </div>
            </div>

            {{-- 3. アクションバー (旧: テーブルカードのヘッダー) --}}
            <div class="flex flex-wrap justify-between items-center py-2 gap-2">
                <h3 class="font-semibold text-gray-700 flex items-center gap-2">
                    <span class="bg-gray-200 text-gray-600 text-xs px-2 py-1 rounded">点検状況</span>
                    <span>{{ $inspectionRequest->pattern->name }}</span>
                </h3>

                <div class="flex items-center space-x-2">
                    <form action="{{ route('admin.inspection-requests.resendPending', $inspectionRequest) }}" method="POST" onsubmit="return confirm('ステータスが「依頼中」のすべての対象者に再依頼メールを送信します。\n本当によろしいですか？');" class="m-0">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-white bg-orange-500 rounded-md shadow-sm hover:bg-orange-600 transition duration-150 ease-in-out flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            一括再依頼
                        </button>
                    </form>
                    <a href="{{ route('admin.dashboard', ['week' => $weekStartDate->format('Y-m-d')]) }}" class="px-3 py-1.5 text-xs font-semibold text-white bg-blue-600 rounded-md shadow-sm hover:bg-blue-700">更新</a>
                    <form action="{{ route('admin.inspection-requests.destroy', $inspectionRequest) }}" method="POST" onsubmit="return confirm('この週の点検依頼とすべての点検記録を完全に削除します。よろしいですか？');" class="m-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-white bg-red-600 rounded-md shadow-sm hover:bg-red-700">
                            この週の依頼を削除
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </div>
        {{-- ★★★ 固定ヘッダーエリア終了 ★★★ --}}


        @if ($inspectionRequest)
        {{-- 固定ヘッダーが浮いているため、テーブル自体のカードスタイルを少し調整 (上部の角丸などを削除) --}}
        {{-- ★ 高さ(h-[75vh])は画面サイズに合わせて調整してください。flex-colで縦並びレイアウトを制御します --}}
        <div class="bg-white rounded-lg shadow border overflow-hidden flex flex-col h-[75vh]">

            {{-- アクションバー（スクロール領域の外に配置することで常時表示されます） --}}
            <!-- <div class="flex-none flex flex-wrap justify-between items-center p-4 border-b bg-gray-50 gap-2 z-20">
                <h3 class="text-lg font-semibold text-gray-700">点検状況 ({{ $inspectionRequest->pattern->name }})</h3>

                <div class="flex items-center space-x-3">
                    <form action="{{ route('admin.inspection-requests.resendPending', $inspectionRequest) }}" method="POST" onsubmit="return confirm('ステータスが「依頼中」のすべての対象者に再依頼メールを送信します。\n本当によろしいですか？');" class="m-0">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-white bg-orange-500 rounded-md shadow-sm hover:bg-orange-600 transition duration-150 ease-in-out flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            一括再依頼
                        </button>
                    </form>
                    <a href="{{ route('admin.dashboard', ['week' => $weekStartDate->format('Y-m-d')]) }}" class="px-3 py-1.5 text-xs font-semibold text-white bg-blue-600 rounded-md shadow-sm hover:bg-blue-700">更新</a>
                    <form action="{{ route('admin.inspection-requests.destroy', $inspectionRequest) }}" method="POST" onsubmit="return confirm('この週の点検依頼とすべての点検記録を完全に削除します。よろしいですか？');" class="m-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-white bg-red-600 rounded-md shadow-sm hover:bg-red-700">
                            この週の依頼を削除
                        </button>
                    </form>
                </div>
            </div> -->

            {{-- ここを overflow-auto にし、縦横スクロールを一括管理します --}}
            <div class="flex-1 overflow-auto relative">
                <table class="min-w-full divide-y divide-gray-200">
                    {{-- ヘッダーを「コンテナの上」に固定。top-0だけで機能します --}}
                    <thead class="bg-gray-50 sticky top-0 z-10 shadow-sm">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap bg-gray-50">
                                管理番号
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap bg-gray-50">
                                <x-sort-link column="vehicle.model_name" label="車両・重機" :params="['week' => $weekStartDate->format('Y-m-d'), 'status' => $filterStatus, 'result' => $filterResult]" />
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap bg-gray-50">
                                <x-sort-link column="user.name" label="担当者" :params="['week' => $weekStartDate->format('Y-m-d'), 'status' => $filterStatus, 'result' => $filterResult]" />
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap bg-gray-50">
                                {{-- ステータスフィルター --}}
                                <div x-data="{ open: false }" @click.away="open = false" class="relative inline-block text-left">
                                    <div>
                                        <button @click="open = !open" type="button" class="inline-flex justify-center w-full rounded-md border border-gray-300 shadow-sm px-2 py-1 bg-white text-xs font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" id="menu-button" aria-expanded="true" aria-haspopup="true">
                                            ステータス
                                            @if($filterStatus) <span class="ml-1 font-bold text-blue-600">({{ $filterStatus }})</span> @endif
                                            <svg class="-mr-1 ml-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </div>
                                    <div x-show="open" x-transition class="origin-top-left absolute left-0 mt-2 w-40 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50" style="display: none;">
                                        <div class="py-1" role="none">
                                            <a href="{{ route('admin.dashboard', ['week' => $weekStartDate->format('Y-m-d'), 'result' => $filterResult]) }}" class="text-gray-700 block px-4 py-2 text-sm hover:bg-gray-100" role="menuitem" tabindex="-1">すべて表示</a>
                                            @foreach($statuses as $status)
                                            <a href="{{ route('admin.dashboard', ['week' => $weekStartDate->format('Y-m-d'), 'status' => $status, 'result' => $filterResult]) }}" class="text-gray-700 block px-4 py-2 text-sm hover:bg-gray-100 {{ $filterStatus === $status ? 'font-bold bg-gray-100' : '' }}" role="menuitem" tabindex="-1">{{ $status }}</a>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <x-sort-link column="status" label="" :params="['week' => $weekStartDate->format('Y-m-d'), 'status' => $filterStatus, 'result' => $filterResult]" />
                            </th>

                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap bg-gray-50">
                                {{-- 結果フィルター --}}
                                <div x-data="{ open: false }" @click.away="open = false" class="relative inline-block text-left">
                                    <div>
                                        <button @click="open = !open" type="button" class="inline-flex justify-center w-full rounded-md border border-gray-300 shadow-sm px-2 py-1 bg-white text-xs font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" id="menu-button-result" aria-expanded="true" aria-haspopup="true">
                                            結果
                                            @if($filterResult) <span class="ml-1 font-bold text-blue-600">({{ $filterResult }})</span> @endif
                                            <svg class="-mr-1 ml-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </div>
                                    <div x-show="open" x-transition class="origin-top-left absolute left-0 mt-2 w-40 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50" style="display: none;">
                                        <div class="py-1" role="none">
                                            <a href="{{ route('admin.dashboard', ['week' => $weekStartDate->format('Y-m-d'), 'status' => $filterStatus]) }}" class="text-gray-700 block px-4 py-2 text-sm hover:bg-gray-100" role="menuitem" tabindex="-1">すべて表示</a>
                                            @foreach($resultOptions as $result)
                                            <a href="{{ route('admin.dashboard', ['week' => $weekStartDate->format('Y-m-d'), 'status' => $filterStatus, 'result' => $result]) }}" class="text-gray-700 block px-4 py-2 text-sm hover:bg-gray-100 {{ $filterResult === $result ? 'font-bold bg-gray-100' : '' }}" role="menuitem" tabindex="-1">{{ $result }}</a>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <x-sort-link column="result" label="" :params="['week' => $weekStartDate->format('Y-m-d'), 'status' => $filterStatus, 'result' => $filterResult]" />
                            </th>

                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap bg-gray-50">
                                <x-sort-link column="inspected_at" label="点検日時" :params="['week' => $weekStartDate->format('Y-m-d'), 'status' => $filterStatus, 'result' => $filterResult]" />
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap bg-gray-50">アクション</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($sortedRecords as $record)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ optional($record->vehicle)->asset_number ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ optional($record->vehicle)->model_name ?? '削除された車両' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $record->user->name ?? '未実施' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @php
                                $statusColor = match($record->status) {
                                '依頼中' => 'bg-orange-100 text-orange-800',
                                '再依頼' => 'bg-purple-100 text-purple-800',
                                '点検済み' => 'bg-blue-100 text-blue-800',
                                default => 'bg-gray-100 text-gray-800',
                                };
                                @endphp
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColor }}">
                                    {{ $record->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
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
                                <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $record->inspected_at ? Carbon::parse($record->inspected_at)->format('Y/m/d H:i') : '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                <a href="{{ route('admin.records.show', $record) }}" class="text-indigo-600 hover:text-indigo-900">詳細</a>
                                @if($record->status !== '点検済み')
                                <form action="{{ route('admin.records.reRequest', $record) }}" method="POST" class="inline-block" onsubmit="return confirm('この車両の再点検依頼をしますか？');">
                                    @csrf
                                    <button type="submit" class="text-red-600 hover:text-red-900">再依頼</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-sm text-gray-500">点検対象の車両がありません。</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @else
        <div class="text-center bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-lg shadow mt-6">
            <p class="text-yellow-700 mb-4">この週の点検依頼はまだ作成されていません。</p>
            <button onclick="document.getElementById('requestModal').classList.remove('hidden')" class="px-4 py-2 text-sm font-semibold text-white bg-green-600 rounded-lg shadow-md hover:bg-green-700">
                ＋ 新規点検依頼を作成
            </button>
        </div>
        @endif
    </div>

    {{-- 新規点検依頼作成モーダル (内容は変更なし) --}}
    <div id="requestModal" class="fixed z-50 inset-0 overflow-y-auto hidden">
        {{-- ... モーダルの中身は元のコードのまま ... --}}
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
                         groups[category].sort((a, b) => a.item_name.localeCompare(b.item_name, 'ja'));
                         return groups;
                     }, {});
                 },
                 get sortedCategories() {
                     return Object.keys(this.selectedItemsGrouped).sort((a, b) => {
                         if (a === 'カテゴリなし') return 1;
                         if (b === 'カテゴリなし') return -1;
                         return a.localeCompare(b, 'ja');
                     });
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

                        <div x-show="selectedPatternId" class="mt-6 border-t pt-4" style="display: none;">
                            <h4 class="text-md font-medium text-gray-800 mb-2">点検項目プレビュー</h4>
                            <div class="max-h-60 overflow-y-auto pr-2 border rounded p-2 bg-gray-50">
                                <template x-if="Object.keys(selectedItemsGrouped).length === 0">
                                    <p class="text-sm text-gray-500">このパターンには点検項目が登録されていません。</p>
                                </template>
                                <template x-for="category in sortedCategories" :key="category">
                                    <div class="mb-3">
                                        <h5 class="text-sm font-semibold text-gray-600 bg-gray-100 p-1.5 rounded sticky top-0" x-text="category"></h5>
                                        <ul class="list-disc list-inside mt-1 space-y-1 pl-2">
                                            <template x-for="item in selectedItemsGrouped[category]" :key="item.id">
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
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow"> {{-- ★ robots メタタグを追加 --}}
    <title>車両点検フォーム - {{ $vehicle->model_name }}</title> {{-- ★ $vehicle から取得 --}}
    <script src="https://cdn.tailwindcss.com"></script>
    {{-- ★ Alpine.js を <head> に移動 (defer付き) --}}
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    {{-- ★ style タグを追加 --}}
    <style>
        /* エラーメッセージ用のスタイル */
        .is-invalid {
            border-color: #e53e3e;
            /* red-600 */
        }

        /* 必須マーク */
        label span.required {
            color: #e53e3e;
            /* red-600 */
            margin-left: 0.25rem;
        }

        /* 結果選択ボタン */
        .result-button {
            cursor: pointer;
            padding: 0.5rem 1rem;
            border: 1px solid #d1d5db;
            /* gray-300 */
            border-radius: 9999px;
            /* rounded-full */
            transition: all 0.2s;
            background-color: white;
            color: #374151;
            /* gray-700 */
        }

        .result-button.selected-normal {
            background-color: #d1fae5;
            border-color: #10b981;
            color: #065f46;
            /* green */
        }

        .result-button.selected-review {
            background-color: #fef3c7;
            border-color: #f59e0b;
            color: #92400e;
            /* yellow */
        }

        .result-button.selected-abnormal {
            background-color: #fee2e2;
            border-color: #ef4444;
            color: #991b1b;
            /* red */
        }

        /* カテゴリヘッダー */
        .category-header {
            position: sticky;
            top: 0;
            background-color: #f9fafb;
            /* gray-50 */
            z-index: 5;
        }

        /* z-index調整 */
        /* スマホで横にはみ出さないように */
        body {
            overflow-x: hidden;
        }

        .container {
            max-width: 100%;
        }

        /* コンテナ幅調整 */
        @media (min-width: 640px) {

            /* sm ブレークポイント以上 */
            .container {
                max-width: 640px;
            }

            /* スマホ以上は中央寄せ */
        }
    </style>
</head>

<body class="bg-gray-100">
    {{-- ★ 全体を囲む div を追加 --}}
    <div class="container mx-auto">
        <div class="bg-white min-h-screen shadow-lg"> {{-- ★ max-w-md, shadow-2xl を調整 --}}
            <header class="bg-blue-600 text-white p-4 text-center sticky top-0 z-10">
                <h1 class="text-lg font-bold">車両点検フォーム</h1>
            </header>

            <main class="p-4">
                {{-- ★★★ form の action と method を修正 ★★★ --}}
                <form action="{{ route('inspection.submit', ['token' => $record->one_time_token]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    {{-- ★ 車両情報表示エリア --}}
                    <div class="p-4 bg-gray-50 rounded-lg mb-6 border"> {{-- ★ mb-4 を mb-6 に、border追加 --}}
                        <h2 class="text-lg font-semibold text-gray-800"> {{-- ★ font-bold を semibold に --}}
                            対象: {{ $vehicle->model_name }}
                            @if($vehicle->asset_number)
                            <span class="text-sm font-medium text-gray-500 ml-2">({{ $vehicle->asset_number }})</span> {{-- ★ text-base を sm に --}}
                            @endif
                        </h2>
                        @if ($inspectionRequest->remarks)
                        <div class="mt-2 text-sm text-gray-700 bg-blue-50 border border-blue-200 rounded p-2"> {{-- ★ スタイル調整 --}}
                            <strong>【管理者からの備考】</strong><br>
                            {!! nl2br(e($inspectionRequest->remarks)) !!} {{-- ★ nl2br で改行を反映 --}}
                        </div>
                        @endif
                    </div>

                    {{-- ★ エラー表示エリア --}}
                    @if (session('error'))
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded-md" role="alert">
                        <p>{{ session('error') }}</p>
                    </div>
                    @endif
                    @if ($errors->any())
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded-md" role="alert">
                        <p class="font-bold">入力内容にエラーがありました。</p>
                        <ul class="list-disc ml-5 mt-2 text-sm">
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    {{-- ★★★ 担当者選択プルダウン ★★★ --}}
                    <div class="mb-6">
                        <label for="inspector_id" class="block text-sm font-medium text-gray-700 mb-1">点検実施者 <span class="required">*</span></label>
                        <select id="inspector_id" name="inspector_id" required
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('inspector_id') is-invalid @enderror">
                            <option value="">選択してください</option>
                            @foreach($assignedUsers as $user)
                            <option value="{{ $user->id }}" {{ old('inspector_id', $record->user_id) == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('inspector_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        @if($assignedUsers->isEmpty())
                        <p class="text-orange-600 text-xs mt-1">注意: この車両には担当者が割り当てられていません。管理者に連絡して担当者を割り当ててください。</p>
                        @endif
                    </div>

                    {{-- ★★★ 走行距離の入力欄を削除 ★★★ --}}
                    {{--
                    <div class="mb-6">
                        <label for="mileage" class="block text-sm font-medium text-gray-700 mb-1">走行距離 (km)</label>
                        <input type="number" id="mileage" name="mileage" value="{{ old('mileage', $record->mileage) }}"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('mileage') is-invalid @enderror"
                    placeholder="例: 12345">
                    @error('mileage')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
        </div>
        --}}

        {{-- ★ 点検項目タイトル --}}
        <h2 class="text-lg font-semibold mb-4 text-gray-700 border-b pb-2">点検項目</h2>

        {{-- カテゴリごとに項目を表示 --}}
        @forelse ($itemsGrouped as $category => $itemsInCategory)
        <div class="mb-6 border rounded-lg overflow-hidden"> {{-- ★ カテゴリごとに枠線を追加 --}}
            <h3 class="category-header text-md font-semibold text-gray-600 p-3 bg-gray-50 border-b">{{ $category ?: 'カテゴリなし' }}</h3>
            <div class="p-4 space-y-5"> {{-- ★ 内側に padding と space-y 追加 --}}
                @foreach ($itemsInCategory as $item)
                {{-- ★ Alpine.js データスコープ --}}
                <div x-data="{ result: '{{ old('results.'.$item->id.'.check_result', '正常') }}' }"> {{-- ★ デフォルトを '正常' に --}}
                    <label class="block text-sm font-medium text-gray-800 mb-2">{{ $item->item_name }} <span class="required">*</span></label>
                    @if ($item->remarks) {{-- ★ 点検項目の備考表示 --}}
                    <p class="text-xs text-gray-500 mb-2 bg-blue-50 p-1.5 rounded border border-blue-200">【確認内容】 {{ $item->remarks }}</p>
                    @endif

                    {{-- 結果選択ボタン --}}
                    <div class="flex flex-wrap gap-2 mb-3"> {{-- ★ gap-2 追加 --}}
                        {{-- 結果の input name を results[id][check_result] に統一 --}}
                        <input type="hidden" name="results[{{ $item->id }}][check_result]" :value="result">
                        <button type="button" @click="result = '正常'" :class="{'selected-normal': result === '正常'}" class="result-button">正常</button>
                        <button type="button" @click="result = '要確認'" :class="{'selected-review': result === '要確認'}" class="result-button">要確認</button>
                        <button type="button" @click="result = '異常'" :class="{'selected-abnormal': result === '異常'}" class="result-button">異常</button>
                    </div>
                    @error('results.'.$item->id.'.check_result') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror

                    {{-- 状況報告と写真添付 (要確認または異常の場合) --}}
                    {{-- ★ x-show の条件修正, style="display: none;" 追加 --}}
                    <div x-show="result === '要確認' || result === '異常'" x-transition class="pt-4 mt-4 border-t space-y-3" style="display: none;">
                        <div>
                            {{-- ★ label の for 属性を追加 --}}
                            <label :for="'comment_' + {{ $item->id }}" class="block text-xs font-medium text-gray-600 mb-1">状況報告 <span class="required">*</span></label>
                            <textarea :id="'comment_' + {{ $item->id }}" name="results[{{ $item->id }}][comment]" rows="2"
                                class="w-full border-gray-300 rounded-md shadow-sm sm:text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('results.'.$item->id.'.comment') is-invalid @enderror"
                                placeholder="具体的な状況を入力">{{ old('results.'.$item->id.'.comment') }}</textarea>
                            @error('results.'.$item->id.'.comment') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            {{-- ★ label の for 属性を追加 --}}
                            <label :for="'photo_' + {{ $item->id }}" class="block text-xs font-medium text-gray-600 mb-1">写真添付 (任意)</label>
                            {{-- ★★★ capture="environment" を追加 ★★★ --}}
                            <input type="file" :id="'photo_' + {{ $item->id }}" name="results[{{ $item->id }}][photo]" accept="image/*" capture="environment"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 @error('results.'.$item->id.'.photo') is-invalid @enderror" />
                            @error('results.'.$item->id.'.photo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @empty
        <p class="text-center text-gray-500 my-6">この車両カテゴリに割り当てられた点検項目がありません。</p>
        @endforelse

        {{-- ★ 全体備考 --}}
        <div class="mb-6">
            <label for="overall_remarks" class="block text-sm font-medium text-gray-700 mb-1">全体備考</label>
            <textarea id="overall_remarks" name="overall_remarks" rows="3"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('overall_remarks') is-invalid @enderror"
                placeholder="申し送り事項などあれば入力">{{ old('overall_remarks', $record->remarks) }}</textarea>
            @error('overall_remarks')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- ★ 送信ボタン --}}
        <div class="pt-6 border-t mt-8 text-center"> {{-- ★ mt-8, text-center, border-t を追加 --}}
            <button type="submit"
                {{-- ★ 担当者がいない場合は disabled --}}
                @if($assignedUsers->isEmpty()) disabled @endif
                {{-- ★ スタイル調整 --}}
                class="w-full sm:w-auto inline-flex justify-center items-center px-8 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                点検報告を送信する
            </button>
        </div>
        </form>
        </main>
    </div>
    </div>
</body>

</html>
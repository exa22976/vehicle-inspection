@extends('layouts.app')

@section('title', '担当者マスター管理')

@section('content')
<div class="p-6 bg-white rounded-xl shadow-lg" x-data="{ importModalOpen: false, isFileDialogOpen: false, departmentModalOpen: false }">
    <div class="flex flex-wrap items-center justify-between mb-6 gap-4">
        <h2 class="text-xl font-bold text-gray-800">担当者マスター管理</h2>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.users.create') }}" class="px-4 py-2 text-sm font-semibold text-white bg-green-600 rounded-lg shadow-md hover:bg-green-700">＋ 新規登録</a>
            <button @click="departmentModalOpen = true" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 cursor-pointer">
                部署を編集
            </button>

            <!-- <button @click="importModalOpen = true" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 cursor-pointer">
                ↑CSVインポート
            </button>
            <a href="{{ route('admin.users.export') }}" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">
                ↓CSVダウンロード
            </a> -->
        </div>
    </div>

    @if (session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
        {{ session('success') }}
    </div>
    @endif
    @if (session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        {{ session('error') }}
    </div>
    @endif

    <div class="mb-6">
        <form action="{{ route('admin.users.index') }}" method="GET">
            <div class="flex flex-wrap gap-4 items-end">
                <div class="flex-grow min-w-[200px]">
                    <label for="keyword" class="block text-sm font-medium text-gray-700">キーワード</label>
                    <input type="text" name="keyword" id="keyword" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" value="{{ $keyword ?? '' }}" placeholder="氏名, メールアドレス">
                </div>
                <div class="flex-grow min-w-[200px]">
                    <label for="department_id" class="block text-sm font-medium text-gray-700">部署</label>
                    <select name="department_id" id="department_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">すべての部署</option>
                        @foreach ($departments as $department)
                        <option value="{{ $department->id }}" {{ ($filterDepartment ?? '') == $department->id ? 'selected' : '' }}>
                            {{ $department->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-grow min-w-[200px]">
                    <label for="is_admin" class="block text-sm font-medium text-gray-700">権限</label>
                    <select name="is_admin" id="is_admin" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">すべての権限</option>
                        <option value="1" {{ ($filterIsAdmin ?? '') === '1' ? 'selected' : '' }}>管理者</option>
                        <option value="0" {{ ($filterIsAdmin ?? '') === '0' ? 'selected' : '' }}>担当者</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg shadow-md hover:bg-blue-700">絞り込み</button>
                    <a href="{{ route('admin.users.index') }}" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">リセット</a>
                </div>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <x-sort-link label="氏名" column="name" />
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <x-sort-link label="メールアドレス" column="email" />
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <x-sort-link label="部署" column="department_name" />
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        権限
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        アクション
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($users as $user)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $user->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $user->email }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $user->department->name ?? '未所属' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if ($user->is_admin)
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">管理者</span>
                        @else
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">担当者</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                        <a href="{{ route('admin.users.edit', $user) }}" class="text-blue-600 hover:text-blue-900">編集</a>
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline-block" onsubmit="return confirm('本当に削除しますか？');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900">削除</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">データがありません。</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- CSVインポートモーダル --}}
    {{-- (省略：変更なし) --}}
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

                <form action="{{ route('admin.users.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">担当者CSVインポート</h3>
                        <div class="mt-4">
                            <p class="text-sm text-gray-600">
                                以下の列順のCSVファイルを選択してください。<br>
                                <strong>・新規登録の場合:</strong> ID列を空にしてください。<br>
                                <strong>・情報を更新する場合:</strong> ID列に既存のIDを入力してください。
                            </p>
                            <p class="mt-2 text-xs text-gray-500 bg-gray-50 p-2 rounded">
                                <strong>A列:</strong> ID, <strong>B列:</strong> 名前, <strong>C列:</strong> メールアドレス, <strong>D列:</strong> 管理者フラグ(はい/いいえ)
                            </p>
                        </div>
                        <div class="mt-4">
                            <input type="file" name="csv_file" required @click="isFileDialogOpen = true" @change="isFileDialogOpen = false" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
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

    {{-- 部署編集モーダル --}}
    <div x-show="departmentModalOpen" class="fixed z-50 inset-0 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            {{-- ★★★ 背景DIVから @click.away を削除 ★★★ --}}
            <div x-show="departmentModalOpen"
                x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

            {{-- ★★★ モーダル本体DIVに @click.outside を追加 ★★★ --}}
            <div x-show="departmentModalOpen" @click.outside="departmentModalOpen = false"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-2xl sm:w-full"
                x-data="departmentManager()" x-init="init()">

                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">部署の編集</h3>

                    <div x-show="successMessage" x-text="successMessage" class="mt-2 text-sm text-green-600 bg-green-50 p-2 rounded" style="display: none;"></div>
                    <div x-show="errorMessage" x-text="errorMessage" class="mt-2 text-sm text-red-600 bg-red-50 p-2 rounded" style="display: none;"></div>

                    {{-- 新規追加フォーム --}}
                    <div class="mt-4">
                        <form @submit.prevent="addDepartment" class="flex items-center gap-2">
                            <input type="text" x-model="newDepartmentName" placeholder="新しい部署名" class="flex-grow border-gray-300 rounded-md shadow-sm" required>
                            <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-green-600 rounded-lg shadow-md hover:bg-green-700">追加</button>
                        </form>
                    </div>

                    {{-- 部署一覧 --}}
                    <div class="mt-4 max-h-96 overflow-y-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">部署名</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">所属人数</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">アクション</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="dept in departments" :key="dept.id">
                                    <tr>
                                        <td class="px-4 py-2">
                                            <input type="text" x-model="dept.name" @change="updateDepartment(dept)" class="w-full border-gray-300 rounded-md shadow-sm">
                                        </td>
                                        <td class="px-4 py-2" x-text="dept.users_count"></td>
                                        <td class="px-4 py-2 text-right">
                                            <button @click="deleteDepartment(dept.id)" :disabled="dept.users_count > 0"
                                                class="text-red-600 hover:text-red-900 disabled:text-gray-400 disabled:cursor-not-allowed">
                                                削除
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                                <template x-if="isLoading">
                                    <tr>
                                        <td colspan="3" class="text-center py-4">読み込み中...</td>
                                    </tr>
                                </template>
                                <template x-if="!isLoading && departments.length === 0">
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-gray-500">部署が登録されていません。</td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    {{-- 閉じるボタンの @click 動作を修正 (window.location.reload() を削除) --}}
                    <button type="button" @click="departmentModalOpen = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">
                        閉じる
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function departmentManager() {
        return {
            departments: [],
            newDepartmentName: '',
            isLoading: true,
            successMessage: '',
            errorMessage: '',
            init() {
                // コンポーネントが初期化されたら部署データを取得
                this.fetchDepartments();
            },
            async fetchDepartments() {
                this.isLoading = true;
                this.clearMessages(); // メッセージをクリア
                try {
                    const response = await fetch('{{ route("admin.departments.index") }}');
                    if (!response.ok) throw new Error('サーバーエラーが発生しました。'); // エラーチェック追加
                    const data = await response.json(); // 先にJSONとしてパース
                    this.departments = data.sort((a, b) => a.name.localeCompare(b.name, 'ja')); // ロード時にソート
                } catch (error) {
                    this.setErrorMessage('部署の読み込みに失敗しました: ' + error.message);
                    console.error(error);
                } finally {
                    this.isLoading = false;
                }
            },
            async addDepartment() {
                this.clearMessages();
                if (!this.newDepartmentName.trim()) {
                    this.setErrorMessage('部署名を入力してください。');
                    return; // 空の場合は処理中断
                }
                try {
                    const response = await fetch('{{ route("admin.departments.store") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json' // サーバーにJSONを期待していることを伝える
                        },
                        body: JSON.stringify({
                            name: this.newDepartmentName
                        })
                    });
                    const result = await response.json();
                    if (!response.ok) throw new Error(result.message || '登録に失敗しました。');

                    this.departments.push(result.department);
                    this.departments.sort((a, b) => a.name.localeCompare(b.name, 'ja')); // 追加後にもソート
                    this.newDepartmentName = '';
                    this.setSuccessMessage(result.message);
                } catch (error) {
                    this.setErrorMessage('登録エラー: ' + error.message);
                }
            },
            async updateDepartment(department) {
                this.clearMessages();
                if (!department.name.trim()) {
                    this.setErrorMessage('部署名を空にすることはできません。');
                    this.fetchDepartments(); // 元のデータに戻す
                    return;
                }
                try {
                    const response = await fetch(`/admin/departments/${department.id}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            name: department.name
                        })
                    });
                    const result = await response.json();
                    if (!response.ok) throw new Error(result.message || '更新に失敗しました。');

                    // 必要であればリストを再ソート
                    this.departments.sort((a, b) => a.name.localeCompare(b.name, 'ja'));
                    this.setSuccessMessage(result.message);
                } catch (error) {
                    this.setErrorMessage('更新エラー: ' + error.message);
                    // If update fails, revert the change by re-fetching
                    this.fetchDepartments();
                }
            },
            async deleteDepartment(id) {
                if (!confirm('本当にこの部署を削除しますか？\n所属している担当者がいる場合は削除できません。')) return; // 確認メッセージを改善
                this.clearMessages();
                try {
                    const response = await fetch(`/admin/departments/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    });
                    const result = await response.json();
                    if (!response.ok) throw new Error(result.message || '削除に失敗しました。');

                    this.departments = this.departments.filter(d => d.id !== id);
                    this.setSuccessMessage(result.message);
                } catch (error) {
                    this.setErrorMessage('削除エラー: ' + error.message);
                }
            },
            setSuccessMessage(message) {
                this.successMessage = message;
                setTimeout(() => this.successMessage = '', 3000);
            },
            setErrorMessage(message) {
                this.errorMessage = message;
                // エラーメッセージは長めに表示 (5秒)
                setTimeout(() => this.errorMessage = '', 5000);
            },
            clearMessages() {
                this.successMessage = '';
                this.errorMessage = '';
            }
        }
    }
</script>
@endsection
@php
// old()ヘルパーはバリデーション失敗時に前の入力を復元し、なければ$vehicleの担当者IDを取得
$assignedUserIds = old('user_ids', $vehicle->users->pluck('id')->all());
@endphp

{{-- ★★★★★ Alpine.jsのセットアップ ★★★★★ --}}
<div class="space-y-6" x-data="{
    selectedDepartment: 'all',
    allUsers: {{ Js::from($allUsersForFiltering) }},
    assignedUsers: {{ Js::from($assignedUserIds) }}
}">
    <div>
        <label for="model_name" class="block text-sm font-medium text-gray-700">名称<span class="text-red-500">*</span></label>
        <input type="text" name="model_name" id="model_name" value="{{ old('model_name', $vehicle->model_name) }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
    </div>
    <div>
        <label for="maker" class="block text-sm font-medium text-gray-700">メーカー</label>
        <input type="text" name="maker" id="maker" value="{{ old('maker', $vehicle->maker) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
    </div>
    <div>
        <label for="vehicle_type" class="block text-sm font-medium text-gray-700">種別<span class="text-red-500">*</span></label>
        <input type="text" name="vehicle_type" id="vehicle_type" value="{{ old('vehicle_type', $vehicle->vehicle_type) }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
    </div>
    <div>
        <label for="category" class="block text-sm font-medium text-gray-700">カテゴリ<span class="text-red-500">*</span></label>
        <select name="category" id="category" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            <option value="">選択してください</option>
            <option value="車両" @if(old('category', $vehicle->category) == '車両') selected @endif>車両</option>
            <option value="重機" @if(old('category', $vehicle->category) == '重機') selected @endif>重機</option>
        </select>
    </div>
    <div>
        <label for="asset_number" class="block text-sm font-medium text-gray-700">管理番号</label>
        <input type="text" name="asset_number" id="asset_number" value="{{ old('asset_number', $vehicle->asset_number) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
    </div>
    <div>
        <label for="manufacturing_year" class="block text-sm font-medium text-gray-700">年式</label>
        <input type="text" name="manufacturing_year" id="manufacturing_year" value="{{ old('manufacturing_year', $vehicle->manufacturing_year) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
    </div>

    {{-- ★★★★★ ここから担当者選択のブロックを修正 ★★★★★ --}}
    <div>
        <label class="block text-sm font-medium text-gray-700">担当者</label>

        {{-- 部署絞り込み用のドロップダウンリスト --}}
        <div class="mt-2">
            <label for="department_filter" class="text-xs text-gray-600">部署で絞り込み:</label>
            <select id="department_filter" x-model="selectedDepartment" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm text-sm">
                <option value="all">すべての部署</option>
                @foreach ($departments as $department)
                <option value="{{ $department->id }}">{{ $department->name }}</option>
                @endforeach
                @if($usersWithoutDepartment->isNotEmpty())
                <option value="none">未所属</option>
                @endif
            </select>
        </div>

        {{-- 担当者チェックボックスリスト --}}
        <div class="mt-2 border border-gray-300 rounded-md p-3 max-h-60 overflow-y-auto space-y-2">

            <template x-for="user in allUsers" :key="user.id">
                <div x-show="
                    selectedDepartment === 'all' || 
                    (selectedDepartment === 'none' && user.department_id === null) ||
                    user.department_id == selectedDepartment
                ">
                    <label class="flex items-center font-normal">
                        <input type="checkbox" name="user_ids[]" :value="user.id"
                            :checked="assignedUsers.includes(user.id)"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <span class="ml-2 text-gray-700" x-text="user.name"></span>
                    </label>
                </div>
            </template>

        </div>
    </div>
    {{-- ★★★★★ ここまで担当者選択のブロックを修正 ★★★★★ --}}
</div>
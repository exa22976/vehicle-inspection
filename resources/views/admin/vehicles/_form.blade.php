<div class="space-y-4">
    <div>
        <label for="model_name" class="block text-sm font-medium text-gray-700">型式</label>
        <input type="text" name="model_name" id="model_name" value="{{ old('model_name', $vehicle->model_name ?? '') }}"
            class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm text-base focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
    </div>

    <div>
        <label for="vehicle_type" class="block text-sm font-medium text-gray-700">車両種別</label>
        <input type="text" name="vehicle_type" id="vehicle_type" value="{{ old('vehicle_type', $vehicle->vehicle_type ?? '') }}"
            class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm text-base focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
    </div>

    <div>
        <label for="category" class="block text-sm font-medium text-gray-700">カテゴリ</label>
        <select name="category" id="category" class="mt-1 block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md shadow-sm text-base focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
            <option value="車両" {{ old('category', $vehicle->category ?? '') == '車両' ? 'selected' : '' }}>車両</option>
            <option value="重機" {{ old('category', $vehicle->category ?? '') == '重機' ? 'selected' : '' }}>重機</option>
        </select>
    </div>

    <div>
        <label for="asset_number" class="block text-sm font-medium text-gray-700">管理番号</label>
        <input type="number" name="asset_number" id="asset_number" value="{{ old('asset_number', $vehicle->asset_number ?? '') }}"
            class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm text-base focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
    </div>

    <div>
        <label for="manufacturing_year" class="block text-sm font-medium text-gray-700">製造年</label>
        <input type="number" name="manufacturing_year" id="manufacturing_year" placeholder="例: 2020" value="{{ old('manufacturing_year', $vehicle->manufacturing_year ?? '') }}"
            class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm text-base focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
    </div>

    <div x-data="{ selectedDepartment: '' }">
        <label class="block text-sm font-medium text-gray-700">担当者</label>
        <div class="mt-2 p-4 border border-gray-200 rounded-md">
            <div class="mb-4">
                <label for="department_filter" class="block text-sm font-medium text-gray-600">部署で絞り込み:</label>
                <select id="department_filter" x-model="selectedDepartment" class="mt-1 block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md shadow-sm text-base focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">すべての部署</option>
                    @foreach($departments as $department)
                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="max-h-60 overflow-y-auto">
                <div class="space-y-2">
                    @foreach($users as $departmentName => $departmentUsers)
                    @php
                    //部署に所属している最初のユーザーから部署IDを取得する
                    $departmentId = $departmentUsers->first()->department_id ?? null;
                    @endphp
                    <div x-show="selectedDepartment === '' || selectedDepartment == {{ $departmentId ?? 'null' }}">
                        <h4 class="font-semibold text-gray-800">{{ $departmentName ?: '部署未所属' }}</h4>
                        <div class="pl-4 space-y-1">
                            @foreach($departmentUsers as $user)
                            <label class="flex items-center">
                                <input type="checkbox" name="user_ids[]" value="{{ $user->id }}"
                                    {{ in_array($user->id, old('user_ids', $assignedUserIds ?? [])) ? 'checked' : '' }}
                                    class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                <span class="ml-2 text-gray-700">{{ $user->name }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
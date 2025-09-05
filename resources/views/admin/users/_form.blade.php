@if ($errors->any())
<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
    <ul>@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
</div>
@endif

<div class="space-y-6" x-data="{ department_selection: '{{ old('department_id', $user->department_id ?? 'existing') }}' }">
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700">氏名</label>
        <input type="text" name="name" id="name" value="{{ old('name', $user->name ?? '') }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
    </div>
    <div>
        <label for="email" class="block text-sm font-medium text-gray-700">メールアドレス</label>
        <input type="email" name="email" id="email" value="{{ old('email', $user->email ?? '') }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
    </div>
    <div>
        <label for="department_id" class="block text-sm font-medium text-gray-700">所属部署</label>
        {{-- ★★★★★ ここの :disabled="..." を削除しました ★★★★★ --}}
        <select name="department_id" id="department_id" x-model="department_selection" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            <option value="existing" disabled>-- 部署を選択 --</option>
            @foreach ($departments as $department)
            <option value="{{ $department->id }}" @if(old('department_id', $user->department_id ?? '') == $department->id) selected @endif>{{ $department->name }}</option>
            @endforeach
            <option value="new">（新規部署を追加）</option>
        </select>
    </div>
    <div x-show="department_selection === 'new'">
        <label for="new_department" class="block text-sm font-medium text-gray-700">新規部署名</label>
        <input type="text" name="new_department" id="new_department" value="{{ old('new_department') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="新しい部署名を入力">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">権限</label>
        <select name="is_admin" id="is_admin" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            <option value="0" @if(old('is_admin', $user->is_admin ?? 0) == 0) selected @endif>担当者</option>
            <option value="1" @if(old('is_admin', $user->is_admin ?? 0) == 1) selected @endif>管理者</option>
        </select>
    </div>
</div>

@if(Request::is('*/edit'))
<div class="space-y-6 mt-6 border-t pt-6">
    <div>
        <label for="password" class="block text-sm font-medium text-gray-700">パスワード</label>
        <input type="password" name="password" id="password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
        <p class="mt-2 text-sm text-gray-500">変更する場合のみ入力してください。</p>
    </div>
    <div>
        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">パスワード（確認）</label>
        <input type="password" name="password_confirmation" id="password_confirmation" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
    </div>
</div>
@endif

<div class="mt-8 flex justify-end space-x-3">
    <a href="{{ route('admin.users.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50">キャンセル</a>
    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700">{{ $submitButtonText ?? '保存' }}</button>
</div>
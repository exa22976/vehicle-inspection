<form action="{{ route('admin.items.update', $item) }}" method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-start p-3 border rounded-md bg-gray-50">
    @csrf
    @method('PUT')
    <div class="md:col-span-5 space-y-2">
        <input type="text" name="item_name" value="{{ $item->item_name }}" class="block w-full border-gray-300 rounded-md shadow-sm" placeholder="項目名">
        <input type="text" name="remarks" value="{{ $item->remarks }}" class="block w-full border-gray-300 rounded-md shadow-sm text-sm" placeholder="備考（担当者への指示など）">
    </div>
    <div class="md:col-span-3">
        <select name="category" class="block w-full border-gray-300 rounded-md shadow-sm">
            <option value="車両共通" @if($item->category == '車両共通') selected @endif>車両</option>
            <option value="重機共通" @if($item->category == '重機共通') selected @endif>重機</option>
        </select>
    </div>
    <div class="md:col-span-2 flex items-center pt-2">
        <input type="hidden" name="is_required" value="0">
        <input type="checkbox" name="is_required" value="1" id="is_required_{{ $item->id }}" class="h-4 w-4 text-blue-600 border-gray-300 rounded" @if($item->is_required) checked @endif>
        <label for="is_required_{{ $item->id }}" class="ml-2 block text-sm text-gray-900">必須</label>
    </div>
    <div class="md:col-span-2 flex justify-end gap-2 pt-2">
        <button type="submit" class="text-sm text-white bg-blue-500 hover:bg-blue-600 px-3 py-1 rounded">更新</button>
        <button type="button" onclick="document.getElementById('delete-item-{{ $item->id }}').submit();" class="text-sm text-white bg-red-500 hover:bg-red-600 px-3 py-1 rounded">削除</button>
    </div>
</form>
<form id="delete-item-{{ $item->id }}" action="{{ route('admin.items.destroy', $item) }}" method="POST" class="hidden">
    @csrf @method('DELETE')
</form>
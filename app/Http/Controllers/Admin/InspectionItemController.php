<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InspectionItem;
use App\Models\InspectionPattern; // ★追記
use Illuminate\Http\Request;

class InspectionItemController extends Controller
{
    // 新しい点検項目をパターンに追加する
    public function store(Request $request, InspectionPattern $pattern)
    {
        $validated = $request->validate([
            'item_name' => 'required|string|max:255',
            'category' => 'required|string|in:車両共通,重機共通',
            'is_required' => 'required|boolean',
            'remarks' => 'nullable|string|max:255',
        ]);
        $pattern->items()->create($validated);
        return back()->with('success', '点検項目を追加しました。');
    }

    // 既存の点検項目を更新する
    public function update(Request $request, InspectionItem $item)
    {
        $validated = $request->validate([
            'item_name' => 'required|string|max:255',
            'category' => 'required|string|in:車両共通,重機共通',
            'is_required' => 'required|boolean',
            'remarks' => 'nullable|string|max:255',
        ]);
        $item->update($validated);
        return back()->with('success', '点検項目を更新しました。');
    }

    // 点検項目を削除する
    public function destroy(InspectionItem $item)
    {
        $item->delete();
        return back()->with('success', '点検項目を削除しました。');
    }
}

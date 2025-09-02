<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InspectionPattern;
use Illuminate\Http\Request;

class InspectionPatternController extends Controller
{
    public function index()
    {
        $patterns = InspectionPattern::withCount('items')->latest()->paginate(10);
        return view('admin.patterns.index', compact('patterns'));
    }

    public function create()
    {
        return view('admin.patterns.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate(['name' => 'required|string|max:255|unique:inspection_patterns,name']);
        $pattern = InspectionPattern::create($validated);
        // 登録後、そのまま項目編集画面に遷移する
        return redirect()->route('admin.patterns.edit', $pattern)->with('success', '点検パターンを登録しました。続けて点検項目を登録してください。');
    }

    public function edit(InspectionPattern $pattern)
    {
        $items = $pattern->items()->orderBy('display_order')->get()->groupBy('category');

        return view('admin.patterns.edit', compact('pattern', 'items'));
    }

    public function update(Request $request, InspectionPattern $pattern)
    {
        $validated = $request->validate(['name' => 'required|string|max:255|unique:inspection_patterns,name,' . $pattern->id]);
        $pattern->update($validated);
        return redirect()->route('admin.patterns.edit', $pattern)->with('success', 'パターン名を更新しました。');
    }

    public function destroy(InspectionPattern $pattern)
    {
        $pattern->delete();
        return redirect()->route('admin.patterns.index')->with('success', '点検パターンを削除しました。');
    }
}

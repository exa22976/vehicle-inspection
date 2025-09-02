<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Exports\UserExport;
use App\Imports\UserImport;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $sortColumn = $request->get('sort', 'id');
        $sortDirection = $request->get('direction', 'asc');

        $allowedColumns = ['id', 'name', 'email'];
        if (!in_array($sortColumn, $allowedColumns)) {
            $sortColumn = 'id';
        }

        $users = User::with('department')
            ->orderBy($sortColumn, $sortDirection)
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get();
        return view('admin.users.create', compact('departments'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'is_admin' => 'required|boolean',
            'department_id' => 'required_without:new_department|nullable|exists:departments,id',
            'new_department' => 'required_without:department_id|nullable|string|max:255|unique:departments,name',
        ]);

        if (!empty($validated['new_department'])) {
            $department = Department::create(['name' => $validated['new_department']]);
            $validated['department_id'] = $department->id;
        }

        // パスワードは使いませんが必須項目のためダミーを設定
        $validated['password'] = Hash::make(str()->random(10));

        User::create($validated);

        return redirect()->route('admin.users.index')->with('success', '担当者を登録しました。');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        $departments = Department::orderBy('name')->get();
        return view('admin.users.edit', compact('user', 'departments'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'is_admin' => 'required|boolean',
            'department_id' => 'required_without:new_department|nullable|exists:departments,id',
            'new_department' => 'required_without:department_id|nullable|string|max:255|unique:departments,name',
        ]);

        // 新しい部署が入力された場合
        if (!empty($validated['new_department'])) {
            $department = Department::create(['name' => $validated['new_department']]);
            $validated['department_id'] = $department->id;
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')->with('success', '担当者情報を更新しました。');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        // 自分自身は削除できないようにする
        if ($user->id === auth()->id()) {
            return back()->with('error', 'ログイン中のユーザーは削除できません。');
        }
        $user->delete(); // 論理削除
        return redirect()->route('admin.users.index')->with('success', '担当者を削除しました。');
    }

    public function export()
    {
        return Excel::download(new UserExport, 'users.csv');
    }

    public function import(Request $request)
    {
        $request->validate(['csv_file' => 'required|mimes:csv,txt']);
        try {
            Excel::import(new UserImport, $request->file('csv_file'));
        } catch (\Exception $e) {
            return back()->with('error', 'CSVインポートに失敗しました。ファイルの内容を確認してください。');
        }
        return redirect()->route('admin.users.index')->with('success', 'CSVをインポートしました。');
    }
}

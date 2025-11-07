<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
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
        $query = User::query()->with('department', 'vehicles');

        $filterDepartment = $request->input('department_id');
        $keyword = $request->input('keyword');
        $filterIsAdmin = $request->input('is_admin');

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%");
            });
        }

        if ($filterDepartment) {
            $query->where('department_id', $filterDepartment);
        }

        if ($filterIsAdmin !== null && $filterIsAdmin !== '') {
            $query->where('is_admin', $filterIsAdmin);
        }

        $sort = $request->get('sort', 'name');
        $direction = $request->get('direction', 'asc');

        if ($sort === 'department_name') {
            $query->select('users.*')
                ->leftJoin('departments', 'users.department_id', '=', 'departments.id')
                ->orderBy('departments.name', $direction);
        } else {
            $query->orderBy($sort, $direction);
        }

        $users = $query->get();

        $departments = Department::orderBy('name')->get();

        return view('admin.users.index', compact('users', 'departments', 'filterDepartment', 'keyword', 'filterIsAdmin'));
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
            'email' => 'required|string|email|max:255',
            'department_id' => 'required|string',
            'new_department' => 'nullable|string|max:255|required_if:department_id,new',
            'is_admin' => 'required|boolean',
        ]);

        $departmentId = $validated['department_id'];
        if ($departmentId === 'new') {
            $department = Department::create(['name' => $validated['new_department']]);
            $departmentId = $department->id;
        }

        $password = Str::random(12);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'department_id' => $departmentId,
            'is_admin' => $validated['is_admin'],
            'password' => Hash::make($password),
        ]);

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
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'department_id' => ['required', 'string', Rule::notIn(['existing'])], // Rule::notIn を使用
            'new_department' => 'nullable|string|max:255|required_if:department_id,new',
            'is_admin' => 'required|boolean',
            'password' => 'nullable|string|min:8|confirmed',
        ], [
            'department_id.not_in' => '所属部署を選択してください。',
        ]);

        $departmentId = $validated['department_id'];
        if ($departmentId === 'new') {
            $department = Department::create(['name' => $validated['new_department']]);
            $departmentId = $department->id;
        }

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'department_id' => $departmentId,
            'is_admin' => $validated['is_admin'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

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
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        try {
            $file = $request->file('csv_file');

            Excel::import(new UserImport, $file, \Maatwebsite\Excel\Excel::CSV);

            return redirect()->route('admin.users.index')
                ->with('success', 'CSVファイルのインポートが完了しました。');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errors = [];
            foreach ($failures as $failure) {
                $errors[] = "{$failure->row()}行目: " . implode(', ', $failure->errors());
            }
            return redirect()->back()->with('error', 'CSVのバリデーションに失敗しました: ' . implode(' | ', $errors));
        } catch (\Exception $e) {
            Log::error('CSV Import Error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'CSVファイルのインポート中に予期せぬエラーが発生しました。ファイル形式や内容を確認してください。');
        }
    }
}
